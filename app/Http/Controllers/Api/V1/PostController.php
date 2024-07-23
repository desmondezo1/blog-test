<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use App\Models\User;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use App\Http\Requests\Api\V1\StorePostRequest;
use App\Http\Requests\Api\V1\SchedulePostRequest;
use App\Http\Requests\Api\V1\UpdatePostRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PostController extends Controller
{
    use ApiResponses;

    /**
     * @OA\GET(
     *     path="/api/v1/posts",
     *     tags={"Posts"},
     *     summary="Fetch all Posts",
     *     description="Fetch all published posts for non-admins 
     *          and All post types for admins",
     * 
     *  @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Posts retrieved successfully"),
     *             @OA\Property(property="data", type="object", example="{...}"),
     *          @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=50),
     *                     @OA\Property(property="per_page", type="integer", example=15),
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=4)
     *                 )
     *          )
     *      )
     * )
     * 
     * 
     *   Fetch all posts
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);

            // Check if user is logged in and is an admin
            $isAdmin = $request->user() && $request->user()->isAdministrator();

            // setup the query
            $postsQuery = Post::query()->with('user');

            // Non admins should only get published posts
            if ($isAdmin) {
                if ($request->has('status')) {
                    $postsQuery->where('status', $request->status);
                }
            } elseif (!$isAdmin) {
                $postsQuery->where('status', 'published');
            }
            
            // Sort the results
            $postsQuery->when($request->has('sort_by'), function ($query) use ($request) {
                return $query->orderBy(
                    $request->sort_by,
                    $request->order ?? 'asc'
                );
            }, function ($query) {
                return $query->latest('published_at');
            });

            $posts = $postsQuery->paginate($perPage, ['*'], 'page', $page);

            return $this->ok("Posts fetched successfully", PostResource::collection($posts));
        } catch (QueryException $e) {
            return $this->error(
                'An error occurred while retrieving posts',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->error(
                'An unexpected error has occurred.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     *   Search for posts
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('query');
            $posts = Post::with('user')
                ->where(function ($queryBuilder) use ($query) {
                    $queryBuilder->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->paginate(15);

            return $this->ok(
                "Posts fetched successfully",
                PostResource::collection($posts)
            );
        } catch (\Exception $e) {
            return $this->error(
                'An error occurred while searching posts: '.$e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     *   Get posts by Post Author
     */
    public function getByAuthor(Request $request, int $userId): JsonResponse
    {
        try {
            $user = User::findOrFail($userId);
            $status = $request->query('status');

            $postsQuery = $user->posts();

            if ($status && $user->isAdministrator()) {
                $postsQuery->where('status', $status);
            }
    
            $posts = $postsQuery->paginate(15);

            return $this->ok(
                "Posts fetched successfully",
                PostResource::collection($posts)
            );
        } catch (ModelNotFoundException $e) {
            return $this->error(
                'User not found.',
                Response::HTTP_NOT_FOUND,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->error(
                'An error occurred while retrieving posts.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/posts",
     *     tags={"Admin"},
     *     summary="Create a post",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Post Title"),
     *             @OA\Property(property="content", type="string", example="Post content"),
     *             @OA\Property(property="summary", type="string", example="Helping Dogs")
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post Created successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     )
     * )
     * 
     * Create a post .
     * 
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validated();

            $slug = Str::slug($request->title);   //  Create a slug with helper

            $count = Post::where('slug', 'LIKE', "{$slug}%")->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }

            $validated['user_id'] = $user->id;
            $validated['slug'] = $slug;
            $validated['author'] = $user->name;

            $post = Post::create($validated);
            $postResource = new PostResource($post);
            
            return $this->success(
                'Post created Successfuly',
                $postResource,
                Response::HTTP_CREATED
            );
        } catch (QueryException $e) {
            return $this->error(
                'An error occured creating post',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->error(
                'An error occured creating post',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     *  Fetch a post.
     */
    public function show(mixed $id): JsonResponse
    {
        try {
           
            $post = $this->getPostBySlugOrId($id);
            $postResource = new PostResource($post);

            return $this->ok('Post fetched', $postResource);
        } catch (ModelNotFoundException $e) {
            return $this->error(
                'Post not found.',
                Response::HTTP_NOT_FOUND,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->error(
                'An error occurred trying to retrieve the post: ',
                Response::HTTP_INTERNAL_SERVER_ERROR, 
                $e->getMessage()
            );
        }
    }

    /**
     *   Get a post by slug or ID
     */
    private function getPostBySlugOrId($slugOrId): Post
    {
        if (is_numeric($slugOrId)) {
            $post = Post::findOrFail($slugOrId);
            return $post;
        }
        
        $post = Post::where('slug', $slugOrId)->firstOrFail();
        return $post;
    }

    /**
     * @OA\Put(
     *     path="/api/v1/posts/{id}",
     *     tags={"Posts"},
     *     summary="Update a post",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post updated successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     )
     * )
     * 
     * Update existing post [Requires API token].
     * 
     */
    public function update(
        UpdatePostRequest $request,
        int $id
    ): JsonResponse {
        try {
            $post = Post::findOrFail($id);

            Gate::authorize('update', $post); // Verify this user can update a post

            $post->update($request->validated());
            $postResource = new PostResource($post);
            return $this->ok('Post Updated Successfully', $postResource);
        } catch (ModelNotFoundException $e) {
            return $this->error(
                'Post not found.',
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            return $this->error(
                'Error updating the post',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/posts/{id}",
     *     tags={"Posts"},
     *     summary="Delete a post",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Post deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found"
     *     )
     * )
     * 
     * Remove a post [Requires API token].
     * 
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);
            Gate::authorize('delete', $post); // Verify this user can delete a post
            $post->delete();
            return $this->success('Post deleted successfully', [], Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return $this->error(
                'Post not found.',
                Response::HTTP_NOT_FOUND,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->error(
                'Error deleting the post',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     *  Fetch all comments for a post.
     */
    public function getComments(int $id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);
            $comments = $post->comments()->paginate(15);
            return $this->ok('Comments fetched', $comments);
        } catch (ModelNotFoundException $e) {
            return $this->error(
                'Post not found.',
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            return $this->error(
                'retrieving comments',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     *  Method to schedule posts in draft
     */
    public function schedule(
        SchedulePostRequest $request,
        int $id
    ): JsonResponse {
        try {
            $post = Post::findOrFail($id);
            Gate::authorize('update', $post);  // Verify this user can update a post
            
            $post->update([
                'status' => 'scheduled',
                'published_at' => $request->scheduled_for,
            ]);
            $postResource = new PostResource($post);
            return $this->ok('Post Scheduled', $postResource);
        } catch (ModelNotFoundException $e) {
            return $this->error(
                'Post not found.',
                Response::HTTP_NOT_FOUND,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->error(
                'scheduling the post',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     *   Unpublish a post
     */
    public function unpublish(int $id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);
            Gate::authorize('update', $post); // Verify this user can update a post

            $post->update([
                'status' => 'draft',
                'published_at' => null,
            ]);
            $postResource = new PostResource($post);
            return $this->ok('Post Unpublished', $postResource);
        } catch (ModelNotFoundException $e) {
            return $this->error(
                'Post not found.',
                Response::HTTP_NOT_FOUND,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->error(
                'Error occured unpublishing the post ',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     *   Publish a post
     */
    public function publish(int $id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);
            Gate::authorize('update', $post); // Verify this user can update a post
            $post->update(['status' => 'published', 'published_at' => now()]);

            $postResource = new PostResource($post);
            return $this->ok('Post Published!', $postResource);
        } catch (ModelNotFoundException $e) {
            return $this->error(
                'Post not found.',
                Response::HTTP_NOT_FOUND,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->error(
                'Error occured publishing the post ',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }
}
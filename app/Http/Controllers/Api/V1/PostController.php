<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;


use App\Models\Post;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;





use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use App\Http\Requests\Api\V1\StorePostRequest;
use Illuminate\Support\Facades\Gate;
// use App\Http\Requests\Api\V1\UpdatePostRequest;
// use App\Models\Post;

class PostController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);

            $posts = Post::query()
                ->when($request->has('status'), function ($query) use ($request) {
                    return $query->where('status', $request->status);
                })
                ->when($request->has('sort_by'), function ($query) use ($request) {
                    return $query->orderBy($request->sort_by, $request->order ?? 'asc');
                })
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->ok('Posts fetched Successfully',$posts);

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
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        try {
            $post = Post::create($request->validated());

            return $this->success(
                'Post created Successfuly',
                $post,Response::HTTP_CREATED 
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
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);
            return $this->ok('Post found', $post);
        } catch (ModelNotFoundException $e) {
            return $this->error(
                'Post not found.', 
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            return $this->error(
                'An error occurred trying to retrieving the post.', 
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request,  int $id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);
            Gate::authorize('update', $post); // To check if User can update a post
            $post->update($request->validated());

            return $this->ok('Post Updated Successfully', $post );

        } catch (ModelNotFoundException $e) {
            return $this->error(
                'Post not found.', 
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            return $this->error(
                'updating the post', 
                Response::HTTP_INTERNAL_SERVER_ERROR, 
                $e->getMessage()
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $post = Post::findOrFail($id);
            Gate::authorize('delete', $post);
            $post->delete();
            return $this->success('Post deleted successfully', '',  Response::HTTP_NO_CONTENT);
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
}

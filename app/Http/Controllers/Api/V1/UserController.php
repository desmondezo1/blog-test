<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Filters\V1\UserFilter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use ApiResponses;

    /**
     *   Fetch all users [For Admins Only]
     */
    /**
     * @OA\Get(
     *     path="/api/v1/admin/users",
     *     tags={"Admin"},
     *     summary="Get all users",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items()
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);

            $usersQuery = User::query();
            $filter = new UserFilter($request);

            $usersQuery = $filter->apply(
                $usersQuery,
                $request->user()->isAdministrator()
            );

            $users = $usersQuery->paginate($perPage, ['*'], 'page', $page);

            return $this->ok(
                "Users fetched successfully",
                UserResource::collection($users)
            );
        } catch (QueryException $e) {
            return $this->error(
                'An error occurred while fetching users.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->error(
                'An unexpected error occurred while fetching users.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }


    /**
     * 
     *  Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($request->password),
                'role' => 'reader',
                'is_active' => $request->status ?? 1,
            ]);

            // Create a token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->success(
                'User registered successfully',
                [
                    'user' => new UserResource($user),
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return $this->error(
                'An error occurred while registering the user ',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        return $this->ok('Yes', $id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            Gate::authorize('update', $user);

            $validated = $request->validated();

            $validated['email'] = $request->user()->isAdministrator() ? $validated['email'] : $user->email;
            $validated['role'] = $request->user()->isAdministrator() ? $validated['role'] : 'reader';

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            $user->update($validated);
            $userResource = new UserResource($user);

            return $this->ok('User updated', $userResource);
        } catch (ModelNotFoundException $e) {
            return $this->error(
                'User not found',
                Response::HTTP_NOT_FOUND,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->error(
                'An error occurred while updating the user.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     *  Delete a user
     */
    public function destroy(int $id)
    {
        try {
            $user = User::findOrFail($id);
            Gate::authorize('delete', $user);

            $user->delete();

            return $this->success(
                'User deleted!',
                null,
                Response::HTTP_NO_CONTENT
            );
        } catch (ModelNotFoundException $e) {
            return $this->error(
                'User not found.',
                Response::HTTP_NOT_FOUND,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            return $this->error(
                'An error occurred while deleting the user.: ' . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }
}
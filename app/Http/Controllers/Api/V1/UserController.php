<?php

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


class UserController extends Controller
{
    use ApiResponses;

    /**
     *   Fetch all users [For Admins Only]
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);

            $usersQuery = User::query();
            $filter = new UserFilter($request);

            $usersQuery = $filter->apply($usersQuery, $request->user()->isAdministrator());

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
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            
            $validated = $request->validated();
     
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'user',
                'status' => $request->status ?? 'active',
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
    public function update(UpdateUserRequest $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}

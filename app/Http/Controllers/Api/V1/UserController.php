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
    public function store(StoreUserRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
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

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use Illuminate\Http\Request;


class AuthorController extends Controller
{
    public function __construct(UserController $userController)
    {
        $this->userController = $userController;
    }

    public function store(StoreUserRequest $request)
    {
        try {
            //  Set the role to Author 
            $request->merge(['role'=>'author']);
            
            //  Call UserController to create author user
            $result = $this->userController->store($request);
            return $result;

        } catch (\Exception $e) {
            return $this->error(
                'An error occurred while creating the an author: '.$e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Api\LoginUserRequest;

class AuthController extends Controller
{

   use ApiResponses;

   public function login(LoginUserRequest $request) {

    $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return $this->error('Invalid credentials', 401);
        }

        $user = User::firstWhere('email', $request->email);

        return $this->ok(
            'Authenticated',
            [
                'token' => $user->createToken('TOKEN FOR ' . $user->email)->plainTextToken
            ]
        );
   }

   public function logout(Request $request){
    $request->user()->currentAccessToken()->delete();
    return $this->ok('Logged out Successfully');
   }
}

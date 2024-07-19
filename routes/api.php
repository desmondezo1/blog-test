<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Api\V1\PostController;

// Base API route 

//  - login
//  - register 




/* Versioning the Api Endpoint (Laravel 11)

*/
Route::prefix('v1')->group(base_path('routes/api_v1.php'));
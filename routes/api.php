<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\AuthController;

// Base API routes

/*
 * Authentication endpoints
 */

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
});

/*
 * Versioning the Api Endpoint (Laravel 11)
 */
Route::prefix('v1')->group(base_path('routes/api_v1.php'));
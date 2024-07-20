<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Middleware\IsAdmin;

// Public Post Routes
Route::prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);
    Route::get('/search', [PostController::class, 'search']);
    Route::get('/author/{userId}', [PostController::class, 'getByAuthor']);
    Route::get('/{id}/comments', [PostController::class, 'getComments']);
    Route::get('/{id}/tags', [PostController::class, 'getTags']);
    Route::get('/{id}', [PostController::class, 'show']);
});

// Public User Routes
Route::apiResource('users', UserController::class)->only(['show', 'store']);

// Protected Routes [Requires token]
Route::middleware(['auth:sanctum'])->group(function () {
    // User Routes (authenticated)
    Route::apiResource('users', UserController::class)->only(['update', 'destroy']);

    // Admin Routes
    Route::prefix('admin')->middleware(IsAdmin::class)->group(function () {
        // Admin Post Routes
        Route::prefix('posts')->group(function () {
            Route::get('/', [PostController::class, 'index']);
            Route::post('/', [PostController::class, 'store']);
            Route::get('/status/{status}', [PostController::class, 'getByStatus']);
            Route::post('/{id}/publish', [PostController::class, 'publish']);
            Route::post('/{id}/unpublish', [PostController::class, 'unpublish']);
            Route::post('/{id}/schedule', [PostController::class, 'schedule']);
            Route::put('/{id}', [PostController::class, 'update']);
            Route::delete('/{id}', [PostController::class, 'destroy']);
        });

        // Admin User Routes 
        Route::apiResource('users', UserController::class)->only(['index']);
        Route::apiResource('users', UserController::class)->except(['show', 'update']);
    });
});
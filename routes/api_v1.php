<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\UserController;

Route::apiResource('users', UserController::class);


/**
 *   Public Post Routes 
 */
Route::prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);    
    Route::get('/search', [PostController::class, 'search']);
    Route::get('/author/{userId}', [PostController::class, 'getByAuthor']);    
    Route::get('/{id}/comments', [PostController::class, 'getComments']);
    Route::get('/{id}/tags', [PostController::class, 'getTags']);
    Route::get('/{id}', [PostController::class, 'show']);
});

/**
 *   Routes for the admin dashboard
 */
Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {
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
});




Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [UserController::class, 'index'])->middleware('admin');
    Route::get('/{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store'])->middleware('admin');
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('admin');
});

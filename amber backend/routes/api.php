<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\TeamMemberController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Public subscriber routes
Route::post('/subscribers/subscribe', [SubscriberController::class, 'subscribe']);
Route::post('/subscribers/unsubscribe', [SubscriberController::class, 'unsubscribe']);

// Protected user routes
Route::get('/user', [AuthController::class, 'userDetails'])->middleware('auth:sanctum');
Route::put('/user/update', [AuthController::class, 'updateUser'])->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Protected API resources
Route::middleware('auth:sanctum')->group(function () {
    // Categories CRUD
    Route::apiResource('categories', CategoryController::class);
    
    // Tags CRUD
    Route::apiResource('tags', TagController::class);
    
    // Articles CRUD
    Route::apiResource('articles', ArticleController::class);
    
    // Article interactions
    Route::post('/articles/{article}/like', [ArticleController::class, 'like']);
    Route::delete('/articles/{article}/like', [ArticleController::class, 'unlike']);
    Route::post('/articles/{article}/share', [ArticleController::class, 'share']);
    
    // Subscribers CRUD
    Route::apiResource('subscribers', SubscriberController::class);
    
    // Team members CRUD
    Route::apiResource('team-members', TeamMemberController::class);
});
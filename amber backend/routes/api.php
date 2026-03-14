<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\TeamMemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API
|--------------------------------------------------------------------------
*/

// Health
Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'timestamp' => now()->toIso8601String(),
    'service' => 'La Verdad Herald API',
]));

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Email verification
Route::get('/email/verify-token', [AuthController::class, 'verifyEmail'])->middleware('throttle:6,1');
Route::post('/email/resend-verification', [AuthController::class, 'resendVerification'])->middleware('throttle:6,1');

// Current user
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'userDetails']);
    Route::put('/user/update', [AuthController::class, 'updateUser']);
    Route::delete('/user/delete/{email}', [AuthController::class, 'deleteUser']);
    Route::get('/user/liked-articles', [ArticleController::class, 'likedArticles']);
    Route::get('/user/shared-articles', [ArticleController::class, 'sharedArticles']);
});

// Public
Route::post('/subscribers/subscribe', [SubscriberController::class, 'subscribe']);
Route::post('/subscribers/unsubscribe', [SubscriberController::class, 'unsubscribe']);
Route::get('/articles/public', [ArticleController::class, 'publicIndex']);
Route::get('/articles/public/{article}', [ArticleController::class, 'publicShow']);
Route::get('/latest-articles', [ArticleController::class, 'latestArticles']);
Route::get('/categories/{slug}/articles', [CategoryController::class, 'articles']);

// Protected resources
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('tags', TagController::class);
    Route::apiResource('articles', ArticleController::class);
    Route::post('/articles/{article}/like', [ArticleController::class, 'like']);
    Route::delete('/articles/{article}/like', [ArticleController::class, 'unlike']);
    Route::post('/articles/{article}/share', [ArticleController::class, 'share']);
    Route::apiResource('subscribers', SubscriberController::class);
    Route::apiResource('team-members', TeamMemberController::class);
});

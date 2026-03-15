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
| RESTful API v1
|--------------------------------------------------------------------------
| Following REST principles:
| - Resource-based URLs
| - HTTP verbs (GET, POST, PUT, PATCH, DELETE)
| - Stateless authentication
| - Proper status codes
| - Versioned endpoints
*/

// Health Check
Route::get('/health', function() {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'service' => 'La Verdad Herald API',
        'version' => '1.0.1',
        'cors_fix_deployed' => true,
        'git_commit' => '145ff3f',
    ]);
});

/*
|--------------------------------------------------------------------------
| Authentication Resources
|--------------------------------------------------------------------------
*/

// User Registration
Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');

// User Login (Create Session/Token)
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1');

// User Logout (Delete Session/Token)
Route::delete('/auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

// Password Reset Request
Route::post('/auth/password/forgot', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:3,1');

// Password Reset Confirmation
Route::post('/auth/password/reset', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:5,1');

// Email Verification (primary API endpoint used by tests and clients)
Route::get('/auth/email/verify', [AuthController::class, 'verifyEmail'])
    ->middleware('throttle:6,1');

// Email Verification (frontend SPA alias: /verify-email -> /api/email/verify-token)
Route::get('/email/verify-token', [AuthController::class, 'verifyEmail'])
    ->middleware('throttle:6,1');

// Resend Email Verification
Route::post('/auth/email/resend', [AuthController::class, 'resendVerification'])
    ->middleware('throttle:3,1');

/*
|--------------------------------------------------------------------------
| User Resource (Authenticated)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Get Current User
    Route::get('/user', [AuthController::class, 'userDetails']);
    
    // Update Current User
    Route::patch('/user', [AuthController::class, 'updateUser']);
    
    // Delete User (Admin only)
    Route::delete('/users/{email}', [AuthController::class, 'deleteUser']);
    
    // User's Liked Articles
    Route::get('/user/articles/liked', [ArticleController::class, 'likedArticles']);
    
    // User's Shared Articles
    Route::get('/user/articles/shared', [ArticleController::class, 'sharedArticles']);
});

/*
|--------------------------------------------------------------------------
| Public Article Resources
|--------------------------------------------------------------------------
*/

// List Published Articles (Public)
Route::get('/articles', [ArticleController::class, 'publicIndex']);

// List Published Articles (Public) - Alias for backward compatibility
Route::get('/articles/public', [ArticleController::class, 'publicIndex']);

// Get Single Published Article (Public)
Route::get('/articles/{article}', [ArticleController::class, 'publicShow']);

// Get Latest Articles (Public)
Route::get('/articles/latest', [ArticleController::class, 'latestArticles']);

/*
|--------------------------------------------------------------------------
| Category Resources
|--------------------------------------------------------------------------
*/

// List Categories (Public)
Route::get('/categories', [CategoryController::class, 'index']);

// Get Single Category (Public)
Route::get('/categories/{category}', [CategoryController::class, 'show']);

// Get Articles by Category (Public)
Route::get('/categories/{category}/articles', [CategoryController::class, 'articles']);

// Protected Category Operations
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::patch('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Tag Resources
|--------------------------------------------------------------------------
*/

// List Tags (Public)
Route::get('/tags', [TagController::class, 'index']);

// Get Single Tag (Public)
Route::get('/tags/{tag}', [TagController::class, 'show']);

// Protected Tag Operations
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/tags', [TagController::class, 'store']);
    Route::patch('/tags/{tag}', [TagController::class, 'update']);
    Route::delete('/tags/{tag}', [TagController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Article Management (Authenticated)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Create Article
    Route::post('/articles', [ArticleController::class, 'store']);
    
    // Update Article
    Route::patch('/articles/{article}', [ArticleController::class, 'update']);
    
    // Delete Article
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy']);
    
    // Like Article (Create Like)
    Route::post('/articles/{article}/likes', [ArticleController::class, 'like']);
    
    // Unlike Article (Delete Like)
    Route::delete('/articles/{article}/likes', [ArticleController::class, 'unlike']);
    
    // Share Article (Create Share Record)
    Route::post('/articles/{article}/shares', [ArticleController::class, 'share']);
});

/*
|--------------------------------------------------------------------------
| Subscriber Resources
|--------------------------------------------------------------------------
*/

// Subscribe (Create Subscriber)
Route::post('/subscribers', [SubscriberController::class, 'subscribe']);

// Unsubscribe (Delete Subscriber)
Route::delete('/subscribers/{email}', [SubscriberController::class, 'unsubscribe']);

// List Subscribers (Admin only)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/subscribers', [SubscriberController::class, 'index']);
    Route::get('/subscribers/{subscriber}', [SubscriberController::class, 'show']);
    Route::delete('/subscribers/{subscriber}', [SubscriberController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Team Member Resources (Admin only)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/team-members', [TeamMemberController::class, 'index']);
    Route::post('/team-members', [TeamMemberController::class, 'store']);
    Route::get('/team-members/{teamMember}', [TeamMemberController::class, 'show']);
    Route::patch('/team-members/{teamMember}', [TeamMemberController::class, 'update']);
    Route::delete('/team-members/{teamMember}', [TeamMemberController::class, 'destroy']);
});

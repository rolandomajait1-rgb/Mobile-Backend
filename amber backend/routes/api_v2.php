<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\SubscriberController;
use App\Http\Controllers\Api\TeamMemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - RESTful Structure
|--------------------------------------------------------------------------
|
| Clean, RESTful API following industry best practices
| Version: 2.0
|
*/

// ============================================================================
// HEALTH CHECK
// ============================================================================
Route::get('/health', [AuthController::class, 'health']);

// ============================================================================
// AUTHENTICATION - /auth/*
// ============================================================================
Route::prefix('auth')->group(function () {
    // Registration & Login
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Password Management
    Route::post('/password/forgot', [AuthController::class, 'forgotPassword']);
    Route::put('/password/reset', [AuthController::class, 'resetPassword']);
    
    // Email Verification
    Route::post('/email/verify', [AuthController::class, 'resendVerification'])->middleware('auth:sanctum');
    Route::get('/email/verify/{token}', [AuthController::class, 'verifyEmail']);
    
    // Logout (requires authentication)
    Route::delete('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// ============================================================================
// CURRENT USER - /users/me
// ============================================================================
Route::middleware('auth:sanctum')->prefix('users/me')->group(function () {
    Route::get('/', [UserController::class, 'show']);
    Route::put('/', [UserController::class, 'update']);
    Route::delete('/', [UserController::class, 'destroy']);
    
    // User's liked articles
    Route::get('/likes', [UserController::class, 'likes']);
    
    // User's shared articles
    Route::get('/shares', [UserController::class, 'shares']);
});

// ============================================================================
// USERS MANAGEMENT - /users (Admin only)
// ============================================================================
Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'showById']);
    Route::delete('/{id}', [UserController::class, 'destroyById']);
});

// ============================================================================
// ARTICLES - /articles
// ============================================================================

// Public article routes
Route::prefix('articles')->group(function () {
    Route::get('/', [ArticleController::class, 'index']); // ?status=published&sort=latest
    Route::get('/{id}', [ArticleController::class, 'show']);
});

// Protected article routes
Route::middleware('auth:sanctum')->prefix('articles')->group(function () {
    Route::post('/', [ArticleController::class, 'store']);
    Route::put('/{id}', [ArticleController::class, 'update']);
    Route::delete('/{id}', [ArticleController::class, 'destroy']);
    
    // Article interactions
    Route::post('/{id}/likes', [ArticleController::class, 'like']);
    Route::delete('/{id}/likes', [ArticleController::class, 'unlike']);
    Route::post('/{id}/shares', [ArticleController::class, 'share']);
});

// ============================================================================
// CATEGORIES - /categories
// ============================================================================

// Public category routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::get('/{id}/articles', [CategoryController::class, 'articles']);
});

// Protected category routes
Route::middleware('auth:sanctum')->prefix('categories')->group(function () {
    Route::post('/', [CategoryController::class, 'store']);
    Route::put('/{id}', [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
});

// ============================================================================
// TAGS - /tags
// ============================================================================

// Public tag routes
Route::prefix('tags')->group(function () {
    Route::get('/', [TagController::class, 'index']);
    Route::get('/{id}', [TagController::class, 'show']);
});

// Protected tag routes
Route::middleware('auth:sanctum')->prefix('tags')->group(function () {
    Route::post('/', [TagController::class, 'store']);
    Route::put('/{id}', [TagController::class, 'update']);
    Route::delete('/{id}', [TagController::class, 'destroy']);
});

// ============================================================================
// SUBSCRIBERS - /subscribers
// ============================================================================

// Public subscriber routes
Route::prefix('subscribers')->group(function () {
    Route::post('/', [SubscriberController::class, 'store']); // Subscribe
    Route::delete('/{email}', [SubscriberController::class, 'destroy']); // Unsubscribe
});

// Protected subscriber routes (admin)
Route::middleware('auth:sanctum')->prefix('subscribers')->group(function () {
    Route::get('/', [SubscriberController::class, 'index']);
    Route::get('/{id}', [SubscriberController::class, 'show']);
});

// ============================================================================
// TEAM MEMBERS - /team-members
// ============================================================================

// Public team member routes
Route::prefix('team-members')->group(function () {
    Route::get('/', [TeamMemberController::class, 'index']);
    Route::get('/{id}', [TeamMemberController::class, 'show']);
});

// Protected team member routes (admin)
Route::middleware('auth:sanctum')->prefix('team-members')->group(function () {
    Route::post('/', [TeamMemberController::class, 'store']);
    Route::put('/{id}', [TeamMemberController::class, 'update']);
    Route::delete('/{id}', [TeamMemberController::class, 'destroy']);
});

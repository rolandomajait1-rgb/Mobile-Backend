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
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Public subscriber routes
Route::post('/subscribers/subscribe', [SubscriberController::class, 'subscribe']);
Route::post('/subscribers/unsubscribe', [SubscriberController::class, 'unsubscribe']);

// Public articles routes (for landing page)
Route::get('/articles/public', [ArticleController::class, 'publicIndex']);
Route::get('/articles/public/{article}', [ArticleController::class, 'publicShow']);
Route::get('/latest-articles', [ArticleController::class, 'latestArticles']);
Route::get('/categories/{slug}/articles', [CategoryController::class, 'articles']);

// Email verification routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/email/verify', function () {
        return response()->json(['message' => 'Email already verified']);
    })->name('verification.notice');
    
    Route::get('/email/verify/{id}/{hash}', function (Illuminate\Http\Request $request, string $id, string $hash) {
        $user = \App\Models\User::findOrFail($id);
        
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link'], 400);
        }
        
        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }
        
        $user->markEmailAsVerified();
        
        return response()->json(['message' => 'Email verified successfully']);
    })->name('verification.verify');
    
    Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        
        return response()->json(['message' => 'Verification link sent']);
    })->middleware('throttle:6,1')->name('verification.send');
});

// Protected user routes
Route::get('/user', [AuthController::class, 'userDetails'])->middleware('auth:sanctum');
Route::put('/user/update', [AuthController::class, 'updateUser'])->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::delete('/user/delete/{email}', [AuthController::class, 'deleteUser'])->middleware('auth:sanctum');

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
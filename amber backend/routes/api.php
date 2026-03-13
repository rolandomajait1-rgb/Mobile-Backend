<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\TeamMemberController;
use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'Amber Backend API'
    ]);
});

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
    
    Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        
        return response()->json(['message' => 'Verification link sent']);
    })->middleware('throttle:6,1')->name('verification.send');
});

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
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

// Token-based email verification (for MailService)
Route::get('/email/verify-token', function (Illuminate\Http\Request $request) {
    $token = $request->query('token');
    
    if (!$token) {
        return response()->json(['message' => 'Token required'], 400);
    }
    
    $record = \DB::table('email_verification_tokens')
        ->where('token', hash('sha256', $token))
        ->first();
    
    if (!$record) {
        return response()->json(['message' => 'Invalid verification token'], 400);
    }
    
    // Check if token expired (24 hours)
    if (now()->diffInHours($record->created_at) > 24) {
        return response()->json(['message' => 'Verification token expired'], 400);
    }
    
    $user = \App\Models\User::where('email', $record->email)->first();
    
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }
    
    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified']);
    }
    
    $user->markEmailAsVerified();
    \DB::table('email_verification_tokens')->where('email', $record->email)->delete();
    
    return response()->json(['message' => 'Email verified successfully']);
})->middleware('throttle:6,1');

// Protected user routes
Route::get('/user', [AuthController::class, 'userDetails'])->middleware('auth:sanctum');
Route::put('/user/update', [AuthController::class, 'updateUser'])->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::delete('/user/delete/{email}', [AuthController::class, 'deleteUser'])->middleware('auth:sanctum');
Route::get('/user/liked-articles', [ArticleController::class, 'likedArticles'])->middleware('auth:sanctum');
Route::get('/user/shared-articles', [ArticleController::class, 'sharedArticles'])->middleware('auth:sanctum');

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
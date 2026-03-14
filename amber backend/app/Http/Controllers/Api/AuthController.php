<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    /**
     * Health check endpoint
     * 
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'service' => 'La Verdad Herald API',
            'version' => '2.0'
        ]);
    }

    /**
     * Register a new user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/^[a-zA-Z0-9._%+-]+@student\.laverdad\.edu\.ph$/'
            ],
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.regex' => 'Only @student.laverdad.edu.ph emails are allowed'
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user',
            ]);

            $token = $user->createToken('auth-token')->plainTextToken;

            // Send verification email directly
            try {
                $user->sendEmailVerificationNotification();
                \Log::info('Verification email sent', ['email' => $user->email]);
            } catch (\Exception $e) {
                \Log::error('Email verification failed but registration succeeded', [
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'email_verified_at' => $user->email_verified_at,
                    ],
                    'token' => $token,
                ],
                'message' => 'Registration successful! Check your email to verify your account.',
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Login user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Please verify your email before logging in. Check your inbox for the verification link.'],
            ]);
        }

        // Delete old tokens
        $user->tokens()->delete();
        
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'email_verified_at' => $user->email_verified_at,
                ],
                'token' => $token,
            ],
            'message' => 'Login successful',
        ]);
    }

    /**
     * Logout user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Send password reset link
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'If an account exists with this email, a password reset link has been sent.'
            ], 200); // Don't reveal if user exists
        }

        $token = Str::random(64);
        
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $validated['email']],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        try {
            $mailService = app(\App\Services\MailService::class);
            $mailService->sendPasswordResetEmail($user, $token);
            
            return response()->json([
                'message' => 'Password reset link sent to your email'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email', [
                'email' => $validated['email'],
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Failed to send email. Please try again later.'
            ], 500);
        }
    }

    /**
     * Reset password
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $resetRecord = \DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'message' => 'Invalid reset token'
            ], 400);
        }

        if (!Hash::check($validated['token'], $resetRecord->token)) {
            return response()->json([
                'message' => 'Invalid reset token'
            ], 400);
        }

        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            return response()->json([
                'message' => 'Reset token expired'
            ], 400);
        }

        $user = User::where('email', $validated['email'])->first();
        $user->password = Hash::make($validated['password']);
        $user->save();

        \DB::table('password_reset_tokens')
            ->where('email', $validated['email'])
            ->delete();

        return response()->json([
            'message' => 'Password reset successfully'
        ]);
    }

    /**
     * Resend email verification
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function resendVerification(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification link sent'
        ]);
    }

    /**
     * Verify email with token
     * 
     * @param Request $request
     * @param string $token
     * @return JsonResponse
     */
    public function verifyEmail(Request $request, string $token): JsonResponse
    {
        $record = \DB::table('email_verification_tokens')
            ->where('token', hash('sha256', $token))
            ->first();

        if (!$record) {
            return response()->json([
                'message' => 'Invalid verification token'
            ], 400);
        }

        if (now()->diffInHours($record->created_at) > 24) {
            return response()->json([
                'message' => 'Verification token expired'
            ], 400);
        }

        $user = User::where('email', $record->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ]);
        }

        $user->markEmailAsVerified();
        \DB::table('email_verification_tokens')
            ->where('email', $record->email)
            ->delete();

        return response()->json([
            'message' => 'Email verified successfully'
        ]);
    }
}

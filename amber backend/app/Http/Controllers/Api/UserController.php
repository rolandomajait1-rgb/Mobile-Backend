<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get all users (admin only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $users = User::select('id', 'name', 'email', 'role', 'email_verified_at', 'created_at')
            ->paginate(15);

        return response()->json([
            'data' => $users
        ]);
    }

    /**
     * Get current authenticated user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()
        ]);
    }

    /**
     * Get user by ID (admin only)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function showById(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'data' => $user
        ]);
    }

    /**
     * Update current user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
            $user->email_verified_at = null; // Reset verification
        }

        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'data' => $user,
            'message' => 'Profile updated successfully'
        ]);
    }

    /**
     * Delete current user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }

    /**
     * Delete user by ID (admin only)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroyById(Request $request, int $id): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Cannot delete your own account'
            ], 400);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get user's liked articles
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function likes(Request $request): JsonResponse
    {
        $articles = $request->user()
            ->likedArticles()
            ->with(['category', 'tags'])
            ->paginate(15);

        return response()->json([
            'data' => $articles
        ]);
    }

    /**
     * Get user's shared articles
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function shares(Request $request): JsonResponse
    {
        $articles = $request->user()
            ->sharedArticles()
            ->with(['category', 'tags'])
            ->paginate(15);

        return response()->json([
            'data' => $articles
        ]);
    }
}

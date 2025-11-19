<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // Register a new user but do not send any emails or verification codes.
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $email = strtolower($data['email']);

        if (User::where('email', $email)->exists()) {
            return response()->json([
                'message' => 'Email is already registered. Please log in.',
                'code' => 'email_exists'
            ], 409);
        }

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Hash::make($data['password']),
            ]);
            // Intentionally do NOT send any notifications (no verification, no welcome email)
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Registration failed.',
            ], 500);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }


    //  Log a user in and issue a token.

    public function login(LoginRequest $request): JsonResponse
    {

        $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    
    //  Log the current user out by revoking the current token.
   
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    // Return the authenticated user's profile
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return response()->json([
            'user' => $user,
        ]);
    }
}

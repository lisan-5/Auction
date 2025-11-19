<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Notifications\PasswordResetLinkNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ForgotPasswordController extends Controller
{
    // Step 1: Send password reset link
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Unable to send reset link.'], 404);
        }
        $token = Password::createToken($user);
        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($user->email));
        $user->notify(new PasswordResetLinkNotification($resetUrl));
        return response()->json(['message' => 'Password reset link sent.'], 200);
    }

    // Step 2: Reset password using token
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );
        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password has been reset.'], 200);
        }
        return response()->json(['message' => 'Invalid token or email.'], 422);
    }
}

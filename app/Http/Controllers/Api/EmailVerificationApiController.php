<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Models\User;
use App\Notifications\VerificationCodeNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class EmailVerificationApiController extends Controller
{
    private const CODE_TTL_MINUTES = 10;
    private const RESEND_MAX_ATTEMPTS = 3;
    private const RESEND_DECAY_SECONDS = 60; // seconds
    private const MAX_CODE_ATTEMPTS = 5;

    // Step 1: Request a code (and provide name/password for pending registration)
    public function requestCode(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', Password::min(8), 'confirmed'],
        ]);

        $email = strtolower($data['email']);

        if (User::where('email', $email)->exists()) {
            return response()->json([
                'message' => 'Email is already registered. Please log in.',
                'code' => 'email_exists'
            ], 409);
        }

        // Throttle requests per email + IP
        $key = 'api:verify:request:' . sha1($email . '|' . $request->ip());
        if (RateLimiter::tooManyAttempts($key, self::RESEND_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Too many requests. Try again in ' . $seconds . ' seconds.',
                'retry_after' => $seconds,
            ], 429);
        }
        RateLimiter::hit($key, self::RESEND_DECAY_SECONDS);

        // Invalidate any previous unconsumed codes for this email
        EmailVerification::where('email', $email)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $code = EmailVerification::generateCode();
        $magicToken = EmailVerification::generateMagicToken();
        $magicTokenExpires = now()->addMinutes(self::CODE_TTL_MINUTES);
        $verification = EmailVerification::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            'magic_token' => $magicToken,
            'magic_token_expires_at' => $magicTokenExpires,
            'ip' => $request->ip(),
        ]);

        $magicLink = url(route('api.verify.magic', ['token' => $magicToken], false)) . '?name=' . urlencode($data['name']) . '&password=' . urlencode($data['password']);

        Notification::route('mail', $email)
            ->notify(new VerificationCodeNotification($code, self::CODE_TTL_MINUTES, $magicLink));

        return response()->json([
            'message' => 'Verification code and magic link sent.',
            'email' => $email,
            'magic_link' => $magicLink,
        ], 200);
    }

    // Step 2: Magic link registration (GET)
    public function magic(Request $request, string $token)
    {
        $verification = EmailVerification::where('magic_token', $token)
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        if (!$verification || $verification->isMagicTokenExpired()) {
            return response()->json(['message' => 'Invalid or expired magic link.'], 422);
        }
        if (User::where('email', $verification->email)->exists()) {
            return response()->json([
                'message' => 'Email is already registered. Please log in.',
                'code' => 'email_exists'
            ], 409);
        }

        // For simplicity, require name and password in query params (could be improved to store them hashed in DB)
        $name = $request->query('name');
        $password = $request->query('password');
        if (!$name || !$password) {
            return response()->json(['message' => 'Name and password are required for magic link registration.'], 422);
        }
        if (strlen($password) < 8) {
            return response()->json(['message' => 'Password must be at least 8 characters.'], 422);
        }

        try {
            $user = DB::transaction(function () use ($verification, $name, $password) {
                $verification->update(['consumed_at' => now()]);
                return User::create([
                    'name' => $name,
                    'email' => $verification->email,
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Could not complete registration.',
                'code' => 'registration_failed'
            ], 500);
        }

        $user->notify(new WelcomeNotification());
        $token = $user->createToken('default')->plainTextToken;

        return response()->json([
            'message' => 'Account created and email verified via magic link.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ], 201);
    }

    // Step 3: Complete registration by providing code or magic link
    public function complete(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', Password::min(8), 'confirmed'],
        ]);

        $email = strtolower($data['email']);

        if (User::where('email', $email)->exists()) {
            return response()->json([
                'message' => 'Email is already registered. Please log in.',
                'code' => 'email_exists'
            ], 409);
        }

        $record = EmailVerification::where('email', $email)
            ->where('code', $data['code'])
            ->latest()
            ->first();

        if (!$record) {
            // Bump attempts on the latest pending record for this email
            EmailVerification::where('email', $email)
                ->whereNull('consumed_at')
                ->latest()
                ->first()?->increment('attempts');
            return response()->json(['message' => 'Invalid code.'], 422);
        }
        if ($record->isConsumed()) {
            return response()->json(['message' => 'Code already used.'], 422);
        }
        if ($record->isExpired()) {
            return response()->json(['message' => 'Code expired.'], 422);
        }
        if ($record->attempts >= self::MAX_CODE_ATTEMPTS) {
            return response()->json(['message' => 'Too many invalid attempts. Request a new code.'], 429);
        }

        try {
            $user = DB::transaction(function () use ($record, $data, $email) {
                $record->update(['consumed_at' => now()]);

                return User::create([
                    'name' => $data['name'],
                    'email' => $email,
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Could not complete registration.',
                'code' => 'registration_failed'
            ], 500);
        }

        $user->notify(new WelcomeNotification());

        $token = $user->createToken('default')->plainTextToken;

        return response()->json([
            'message' => 'Account created and email verified.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ], 201);
    }

    // Resend code with throttling
    public function resend(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower($data['email']);

        $key = 'api:verify:resend:' . sha1($email . '|' . $request->ip());
        if (RateLimiter::tooManyAttempts($key, self::RESEND_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => 'Too many requests. Try again in ' . $seconds . ' seconds.',
                'retry_after' => $seconds,
            ], 429);
        }
        RateLimiter::hit($key, self::RESEND_DECAY_SECONDS);

        // Invalidate previous unconsumed codes
        EmailVerification::where('email', $email)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        $code = EmailVerification::generateCode();
        EmailVerification::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
            'ip' => $request->ip(),
        ]);

        Notification::route('mail', $email)
            ->notify(new VerificationCodeNotification($code, self::CODE_TTL_MINUTES));

        return response()->json(['message' => 'Verification code resent.'], 200);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAuthChallenge;
use App\Notifications\Auth\LoginChallengeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\URL;

class SessionController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'handle' => ['required', 'string', 'min:3', 'max:32', 'regex:/^[A-Za-z0-9_.-]+$/', 'unique:users,handle'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'public_bio' => ['nullable', 'string', 'max:280'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()],
        ], [
            'name.required' => 'Please enter your public name.',
            'name.min' => 'Your public name must be at least 2 characters long.',
            'name.max' => 'Your public name must be 120 characters or fewer.',
            'handle.required' => 'Please choose a public handle.',
            'handle.min' => 'Your public handle must be at least 3 characters long.',
            'handle.max' => 'Your public handle must be 32 characters or fewer.',
            'handle.regex' => 'Use only letters, numbers, dots, hyphens, and underscores in your handle.',
            'handle.unique' => 'That public handle is already in use.',
            'email.required' => 'Please enter an email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'That email address is already in use.',
            'public_bio.max' => 'Your public bio must be 280 characters or fewer.',
            'password.required' => 'Please enter a password.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'Your password must be at least 12 characters long.',
        ]);

        $user = new User();
        $user->forceFill([
            'name' => trim($data['name']),
            'display_name' => trim($data['name']),
            'handle' => Str::lower(trim($data['handle'])),
            'email' => Str::lower(trim($data['email'])),
            'public_bio' => $data['public_bio'] ?? null,
            'account_state' => 'pending',
            'verification_level' => 'unverified',
            'trust_level' => 'untrusted',
            'is_super_admin' => false,
            'two_factor_mode' => 'email_code',
            'password' => $data['password'],
            'email_verified_at' => null,
            'last_seen_at' => Carbon::now(),
        ]);
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();
        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Your account has been created. Please verify your email address to continue.',
            'email_verification_required' => true,
            'user' => $this->userPayload($user->refresh()),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Please enter your password.',
        ]);

        $user = User::query()
            ->whereRaw('lower(email) = ?', [Str::lower(trim($data['email']))])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['We could not sign you in with those details. Check your email address and password.'],
                'password' => ['We could not sign you in with those details. Check your email address and password.'],
            ]);
        }

        if ($user->account_state === 'suspended') {
            throw ValidationException::withMessages([
                'email' => ['This account has been suspended. Please contact support if you think this is a mistake.'],
            ]);
        }

        if ($user->two_factor_mode !== 'off') {
            $challenge = $this->issueChallenge($user, $request);

            return response()->json([
                'message' => 'We have sent an extra sign-in check to your email address.',
                'requires_two_factor' => true,
                'challenge' => $this->challengePayload($challenge),
                'user' => $this->userPayload($user),
            ], 202);
        }

        Auth::login($user, (bool) ($data['remember'] ?? false));
        $request->session()->regenerate();

        $user->forceFill([
            'last_seen_at' => Carbon::now(),
        ])->save();

        return response()->json([
            'message' => 'Signed in successfully.',
            'requires_two_factor' => false,
            'user' => $this->userPayload($user->refresh()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Signed out successfully.',
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'handle' => ['required', 'string', 'min:3', 'max:32', 'regex:/^[A-Za-z0-9_.-]+$/', Rule::unique('users', 'handle')->ignore($user->getKey())],
            'public_bio' => ['nullable', 'string', 'max:280'],
        ], [
            'name.required' => 'Please enter your public name.',
            'name.min' => 'Your public name must be at least 2 characters long.',
            'name.max' => 'Your public name must be 120 characters or fewer.',
            'handle.required' => 'Please choose a public handle.',
            'handle.min' => 'Your public handle must be at least 3 characters long.',
            'handle.max' => 'Your public handle must be 32 characters or fewer.',
            'handle.regex' => 'Use only letters, numbers, dots, hyphens, and underscores in your handle.',
            'handle.unique' => 'That public handle is already in use.',
            'public_bio.max' => 'Your public bio must be 280 characters or fewer.',
        ]);

        $user->forceFill([
            'name' => trim($data['name']),
            'display_name' => trim($data['name']),
            'handle' => Str::lower(trim($data['handle'])),
            'public_bio' => $data['public_bio'] ?? null,
        ])->save();

        return response()->json([
            'message' => 'Your profile has been updated.',
            'user' => $this->userPayload($user->refresh()),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()],
        ], [
            'current_password.required' => 'Please enter your current password.',
            'current_password.current_password' => 'The current password is not correct.',
            'password.required' => 'Please enter a new password.',
            'password.confirmed' => 'The new password confirmation does not match.',
            'password.min' => 'Your password must be at least 12 characters long.',
        ]);

        $user = $request->user();
        $user->forceFill([
            'password' => $data['password'],
        ])->save();

        return response()->json([
            'message' => 'Your password has been updated.',
            'user' => $this->userPayload($user->refresh()),
        ]);
    }

    public function sendVerificationNotification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Your email address is already verified.',
                'user' => $this->userPayload($user),
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'We have sent a fresh verification email.',
            'user' => $this->userPayload($user),
        ]);
    }

    public function csrfCookie(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }

    public function challengePayload(UserAuthChallenge $challenge): array
    {
        return [
            'id' => $challenge->getKey(),
            'challenge_type' => $challenge->challenge_type,
            'delivery_mode' => $challenge->delivery_mode,
            'expires_at' => $challenge->expires_at?->toIso8601String(),
            'last_sent_at' => $challenge->last_sent_at?->toIso8601String(),
        ];
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'display_name' => $user->display_name,
            'handle' => $user->handle,
            'email' => $user->email,
            'public_bio' => $user->public_bio,
            'account_state' => $user->account_state,
            'verification_level' => $user->verification_level,
            'trust_level' => $user->trust_level,
            'is_super_admin' => $user->isSuperAdmin(),
            'two_factor_mode' => $user->two_factor_mode,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'last_seen_at' => $user->last_seen_at?->toIso8601String(),
            'is_email_verified' => $user->hasVerifiedEmail(),
        ];
    }

    private function issueChallenge(User $user, Request $request): UserAuthChallenge
    {
        $challenge = UserAuthChallenge::create([
            'user_id' => $user->getKey(),
            'purpose' => 'login',
            'challenge_type' => 'login',
            'delivery_mode' => 'email_code',
            'code_hash' => null,
            'magic_token_hash' => null,
            'code_sent_to' => $user->email,
            'expires_at' => Carbon::now()->addMinutes(15),
            'last_sent_at' => Carbon::now(),
            'resend_count' => 0,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500),
            'meta' => [
                'remember' => (bool) $request->boolean('remember'),
            ],
        ]);

        $code = (string) random_int(100000, 999999);
        $challenge->forceFill(['code_hash' => Hash::make($code)]);

        $challenge->save();

        $magicToken = Str::random(64);
        $challenge->forceFill([
            'magic_token_hash' => Hash::make($magicToken),
        ])->save();

        $magicLink = URL::temporarySignedRoute(
            'auth.two-factor.magic-link',
            Carbon::now()->addMinutes(15),
            [
                'challenge' => $challenge->getKey(),
                'token' => $magicToken,
            ]
        );

        $user->notify(new LoginChallengeNotification($challenge, $code, $magicLink));

        return $challenge;
    }
}

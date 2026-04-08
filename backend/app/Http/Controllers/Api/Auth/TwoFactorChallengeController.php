<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAuthChallenge;
use App\Notifications\Auth\LoginChallengeNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TwoFactorChallengeController extends Controller
{
    public function confirm(Request $request): JsonResponse
    {
        $data = $request->validate([
            'challenge_id' => ['required', 'string', 'exists:auth_challenges,id'],
            'code' => ['required', 'string'],
        ]);

        $challenge = UserAuthChallenge::query()->with('user')->findOrFail($data['challenge_id']);
        $this->assertChallengeCanBeUsed($challenge);

        if (! $challenge->code_hash || ! Hash::check($data['code'], $challenge->code_hash)) {
            throw ValidationException::withMessages([
                'code' => ['That code is not valid.'],
            ]);
        }

        return $this->completeLogin($request, $challenge);
    }

    public function magicLink(Request $request, UserAuthChallenge $challenge, string $token): RedirectResponse|JsonResponse
    {
        $this->assertChallengeCanBeUsed($challenge);

        if (! $challenge->magic_token_hash || ! Hash::check($token, $challenge->magic_token_hash)) {
            abort(403);
        }

        $this->completeLogin($request, $challenge);

        return redirect()->away(rtrim((string) config('knowmycouncil.frontend_url'), '/') . '/profile?signed_in=1');
    }

    public function resend(Request $request): JsonResponse
    {
        $data = $request->validate([
            'challenge_id' => ['required', 'string', 'exists:auth_challenges,id'],
            'delivery_mode' => ['nullable', 'string', 'in:email_code,magic_link'],
        ]);

        $challenge = UserAuthChallenge::query()->with('user')->findOrFail($data['challenge_id']);
        $this->assertChallengeCanBeUsed($challenge);

        $this->refreshChallenge($challenge, $request, $data['delivery_mode'] ?? null);

        return response()->json([
            'message' => 'We have sent a fresh sign-in check.',
            'challenge' => [
                'id' => $challenge->getKey(),
                'challenge_type' => $challenge->challenge_type,
                'delivery_mode' => $challenge->delivery_mode,
                'expires_at' => $challenge->expires_at?->toIso8601String(),
                'last_sent_at' => $challenge->last_sent_at?->toIso8601String(),
            ],
        ]);
    }

    private function completeLogin(Request $request, UserAuthChallenge $challenge): JsonResponse
    {
        $user = $challenge->user;

        Auth::login($user, (bool) ($challenge->meta['remember'] ?? false));
        $request->session()->regenerate();

        $user->forceFill([
            'last_seen_at' => Carbon::now(),
        ])->save();

        $challenge->forceFill([
            'consumed_at' => Carbon::now(),
        ])->save();

        return response()->json([
            'message' => 'Signed in successfully.',
            'requires_two_factor' => false,
            'user' => [
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
            ],
        ]);
    }

    private function assertChallengeCanBeUsed(UserAuthChallenge $challenge): void
    {
        if ($challenge->isConsumed() || $challenge->isExpired()) {
            abort(422, 'That sign-in check has expired.');
        }
    }

    private function refreshChallenge(UserAuthChallenge $challenge, Request $request, ?string $deliveryMode = null): void
    {
        $deliveryMode ??= $challenge->delivery_mode;
        $code = null;
        $magicToken = null;

        if ($deliveryMode === 'email_code') {
            $code = (string) random_int(100000, 999999);
            $challenge->forceFill(['code_hash' => Hash::make($code)]);
        }

        if ($deliveryMode === 'magic_link') {
            $magicToken = Str::random(64);
            $challenge->forceFill(['magic_token_hash' => Hash::make($magicToken)]);
        }

        $challenge->forceFill([
            'delivery_mode' => $deliveryMode,
            'last_sent_at' => Carbon::now(),
            'resend_count' => $challenge->resend_count + 1,
            'expires_at' => Carbon::now()->addMinutes(15),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500),
        ])->save();

        $magicLink = $magicToken !== null
            ? URL::temporarySignedRoute(
                'auth.two-factor.magic-link',
                Carbon::now()->addMinutes(15),
                [
                    'challenge' => $challenge->getKey(),
                    'token' => $magicToken,
                ]
            )
            : null;

        $challenge->user->notify(new LoginChallengeNotification($challenge, $code, $magicLink));
    }
}

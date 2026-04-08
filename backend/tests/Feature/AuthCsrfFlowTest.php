<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\Auth\LoginChallengeNotification;
use App\Notifications\Auth\VerifyEmailLinkNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class AuthCsrfFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_csrf_cookie_bootstrap_sets_the_xsrf_token_cookie(): void
    {
        $response = $this->get('/api/auth/csrf-cookie');

        $response->assertOk()->assertCookie('XSRF-TOKEN');
    }

    public function test_registration_can_use_the_csrf_cookie_bootstrap(): void
    {
        Notification::fake();

        $xsrfToken = $this->bootstrapXsrfToken();

        $this->withHeader('X-XSRF-TOKEN', $xsrfToken)
            ->postJson('/api/auth/register', [
                'name' => 'Ada Lovelace',
                'handle' => 'ada-lovelace',
                'email' => 'ada@example.com',
                'public_bio' => 'Council data researcher.',
                'password' => 'Password12345',
                'password_confirmation' => 'Password12345',
            ])
            ->assertCreated()
            ->assertJsonPath('email_verification_required', true);

        $this->assertDatabaseHas('users', [
            'email' => 'ada@example.com',
            'handle' => 'ada-lovelace',
            'two_factor_mode' => 'email_code',
        ]);

        $user = User::query()->where('email', 'ada@example.com')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmailLinkNotification::class);
    }

    public function test_registration_rejects_invalid_handles_with_helpful_feedback(): void
    {
        $xsrfToken = $this->bootstrapXsrfToken();

        $this->withHeader('X-XSRF-TOKEN', $xsrfToken)
            ->postJson('/api/auth/register', [
                'name' => 'Ada Lovelace',
                'handle' => 'ada lovelace',
                'email' => 'ada-invalid-handle@example.com',
                'public_bio' => 'Council data researcher.',
                'password' => 'Password12345',
                'password_confirmation' => 'Password12345',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['handle'])
            ->assertJsonPath('errors.handle.0', 'Use only letters, numbers, dots, hyphens, and underscores in your handle.');
    }

    public function test_login_two_factor_and_magic_link_sign_in_can_use_the_csrf_cookie_bootstrap(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'becky@example.com',
            'password' => Hash::make('Password12345'),
            'two_factor_mode' => 'email_code',
            'account_state' => 'active',
            'verification_level' => 'verified',
        ]);

        $xsrfToken = $this->bootstrapXsrfToken();

        $response = $this->withHeader('X-XSRF-TOKEN', $xsrfToken)
            ->postJson('/api/auth/login', [
                'email' => $user->email,
                'password' => 'Password12345',
                'remember' => true,
            ]);

        $response
            ->assertAccepted()
            ->assertJsonPath('requires_two_factor', true)
            ->assertJsonPath('challenge.delivery_mode', 'email_code');

        $challenge = \App\Models\UserAuthChallenge::query()->where('user_id', $user->id)->firstOrFail();

        $code = null;
        Notification::assertSentTo($user, LoginChallengeNotification::class, function (LoginChallengeNotification $notification) use ($challenge, &$code): bool {
            $this->assertSame($challenge->getKey(), $notification->challenge->getKey());
            $code = $notification->code;
            $this->assertNotNull($notification->magicLink);

            return true;
        });

        $this->withHeader('X-XSRF-TOKEN', $xsrfToken)
            ->postJson('/api/auth/two-factor/confirm', [
                'challenge_id' => $challenge->getKey(),
                'code' => $code,
            ])
            ->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_feedback_points_to_the_email_and_password_fields(): void
    {
        $user = User::factory()->create([
            'email' => 'ellen@example.com',
            'password' => Hash::make('Password12345'),
            'two_factor_mode' => 'off',
            'account_state' => 'active',
            'verification_level' => 'verified',
        ]);

        $xsrfToken = $this->bootstrapXsrfToken();

        $this->withHeader('X-XSRF-TOKEN', $xsrfToken)
            ->postJson('/api/auth/login', [
                'email' => $user->email,
                'password' => 'incorrect-password',
                'remember' => false,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password'])
            ->assertJsonPath('errors.email.0', 'We could not sign you in with those details. Check your email address and password.')
            ->assertJsonPath('errors.password.0', 'We could not sign you in with those details. Check your email address and password.');
    }

    public function test_password_reset_can_use_the_csrf_cookie_bootstrap(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'handle' => 'dana-civic',
            'email' => 'dana@example.com',
            'password' => Hash::make('Password12345'),
        ]);

        $xsrfToken = $this->bootstrapXsrfToken();

        $this->withHeader('X-XSRF-TOKEN', $xsrfToken)
            ->postJson('/api/auth/forgot-password', [
                'email' => $user->email,
            ])
            ->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::broker()->createToken($user);

        $this->withHeader('X-XSRF-TOKEN', $xsrfToken)
            ->postJson('/api/auth/reset-password', [
                'token' => $token,
                'email' => $user->email,
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ])
            ->assertOk();

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
    }

    public function test_email_verification_notification_can_use_the_csrf_cookie_bootstrap(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create([
            'handle' => 'charlie-civic',
            'email' => 'charlie@example.com',
            'password' => Hash::make('Password12345'),
            'account_state' => 'pending',
            'verification_level' => 'unverified',
        ]);

        $xsrfToken = $this->bootstrapXsrfToken();

        $this->actingAs($user)
            ->withHeader('X-XSRF-TOKEN', $xsrfToken)
            ->postJson('/api/auth/email-verification-notification')
            ->assertOk()
            ->assertJsonPath('message', 'We have sent a fresh verification email.');

        Notification::assertSentTo($user, VerifyEmailLinkNotification::class);
    }

    private function bootstrapXsrfToken(): string
    {
        $response = $this->get('/api/auth/csrf-cookie');
        $response->assertOk()->assertCookie('XSRF-TOKEN');

        $cookie = collect($response->headers->getCookies())
            ->first(fn (Cookie $cookie): bool => $cookie->getName() === 'XSRF-TOKEN');

        $this->assertInstanceOf(Cookie::class, $cookie);

        return rawurldecode($cookie->getValue());
    }
}

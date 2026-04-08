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
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_pending_account_and_sends_verification_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Ada Lovelace',
            'handle' => 'ada-lovelace',
            'email' => 'ada@example.com',
            'public_bio' => 'Council data researcher.',
            'password' => 'Password12345',
            'password_confirmation' => 'Password12345',
            'two_factor_mode' => 'off',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('email_verification_required', true)
            ->assertJsonPath('user.handle', 'ada-lovelace');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'ada@example.com',
            'handle' => 'ada-lovelace',
            'account_state' => 'pending',
            'verification_level' => 'unverified',
        ]);

        $user = User::query()->where('email', 'ada@example.com')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmailLinkNotification::class);
    }

    public function test_login_with_two_factor_email_code_requires_challenge_and_can_be_completed(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'becky@example.com',
            'password' => Hash::make('Password12345'),
            'two_factor_mode' => 'email_code',
            'account_state' => 'active',
            'verification_level' => 'verified',
        ]);

        $response = $this->postJson('/api/auth/login', [
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
            return true;
        });

        $this->postJson('/api/auth/two-factor/confirm', [
            'challenge_id' => $challenge->getKey(),
            'code' => $code,
        ])->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    public function test_email_verification_link_marks_the_account_verified(): void
    {
        $user = User::factory()->unverified()->create([
            'handle' => 'charlie-civic',
            'email' => 'charlie@example.com',
            'password' => Hash::make('Password12345'),
            'account_state' => 'pending',
            'verification_level' => 'unverified',
        ]);

        $url = URL::temporarySignedRoute(
            'auth.email.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->get($url);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('/verify-email?verified=1', (string) $response->headers->get('Location'));

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('verified', $user->verification_level);
        $this->assertSame('active', $user->account_state);
    }

    public function test_password_reset_flow_updates_the_password_and_logs_the_user_in(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'handle' => 'dana-civic',
            'email' => 'dana@example.com',
            'password' => Hash::make('Password12345'),
        ]);

        $this->postJson('/api/auth/forgot-password', [
            'email' => $user->email,
        ])->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);

        $token = Password::broker()->createToken($user);

        $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertOk();

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertTrue(Hash::check('NewPassword123', $user->fresh()->password));
    }

    public function test_profile_update_and_logout_work_for_authenticated_users(): void
    {
        $user = User::factory()->create([
            'handle' => 'eve-civic',
            'email' => 'eve@example.com',
            'password' => Hash::make('Password12345'),
            'public_bio' => 'Initial bio.',
        ]);

        $this->actingAs($user);

        $this->patchJson('/api/auth/profile', [
            'name' => 'Eve Example',
            'handle' => 'eve-example',
            'public_bio' => 'Updated bio.',
            'two_factor_mode' => 'both',
        ])->assertOk()
            ->assertJsonPath('user.handle', 'eve-example')
            ->assertJsonPath('user.two_factor_mode', 'both');

        $this->postJson('/api/auth/logout')->assertOk();
        $this->assertGuest();
    }
}

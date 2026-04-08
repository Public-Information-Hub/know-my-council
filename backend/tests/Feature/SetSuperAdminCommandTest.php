<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetSuperAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_grants_and_revokes_superadmin_access_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'is_super_admin' => false,
        ]);

        $this->artisan('kmc:user:superadmin', [
            '--email' => 'admin@example.com',
        ])->expectsOutputToContain('is now a superadmin')
            ->assertExitCode(0);

        $this->assertTrue($user->fresh()->isSuperAdmin());

        $this->artisan('kmc:user:superadmin', [
            '--email' => 'admin@example.com',
            '--remove' => true,
        ])->expectsOutputToContain('is now not a superadmin')
            ->assertExitCode(0);

        $this->assertFalse($user->fresh()->isSuperAdmin());
    }
}

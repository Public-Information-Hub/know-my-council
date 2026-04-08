<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_summary(): void
    {
        $this->getJson('/api/admin/ingestion-summary')
            ->assertUnauthorized();
    }

    public function test_non_superadmin_cannot_access_admin_summary(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => false,
        ]);

        $this->actingAs($user)
            ->getJson('/api/admin/ingestion-summary')
            ->assertForbidden();
    }

    public function test_superadmin_can_access_admin_summary(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
        ]);

        $this->actingAs($user)
            ->getJson('/api/admin/ingestion-summary')
            ->assertOk()
            ->assertJsonPath('counts.councils', 0);
    }
}

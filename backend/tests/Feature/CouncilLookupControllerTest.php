<?php

namespace Tests\Feature;

use App\Models\Council;
use App\Models\CouncilVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouncilLookupControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_a_bootstrapped_council_record(): void
    {
        $council = Council::query()->create([
            'canonical_slug' => 'westminster',
            'jurisdiction_code' => 'E09000033',
            'country_code' => 'GB',
            'authority_kind' => 'english_local_authority',
        ]);

        CouncilVersion::query()->create([
            'council_id' => $council->id,
            'display_name' => 'City of Westminster',
            'short_name' => null,
            'status' => 'active',
            'valid_from' => '2025-04-01',
            'valid_to' => null,
            'public_state' => 'published',
        ]);

        $this->getJson('/api/councils/westminster')
            ->assertOk()
            ->assertJsonPath('local_authority.name', 'City of Westminster')
            ->assertJsonPath('local_authority.slug', 'westminster')
            ->assertJsonPath('local_authority.authority_kind', 'english_local_authority')
            ->assertJsonPath('local_authority.tier', null);
    }
}

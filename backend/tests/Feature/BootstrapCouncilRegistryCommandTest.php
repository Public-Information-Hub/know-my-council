<?php

namespace Tests\Feature;

use App\Models\Council;
use App\Models\CouncilVersion;
use App\Models\Dataset;
use App\Models\Import;
use App\Models\ImportRun;
use App\Models\IngestionSource;
use App\Models\SourceFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BootstrapCouncilRegistryCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_bootstraps_councils_from_the_ons_feature_service(): void
    {
        Storage::fake('local');
        config(['filesystems.default' => 'local']);

        Http::fake([
            'https://services1.arcgis.com/*' => Http::response([
                'features' => [
                    ['attributes' => [
                        'LAD25CD' => 'E06000001',
                        'LAD25NM' => 'Hartlepool',
                        'LAD25NMW' => null,
                    ]],
                    ['attributes' => [
                        'LAD25CD' => 'E06000002',
                        'LAD25NM' => 'Middlesbrough',
                        'LAD25NMW' => null,
                    ]],
                ],
            ], 200, [
                'Content-Type' => 'application/json',
            ]),
        ]);

        $this->artisan('kmc:councils:bootstrap-ons')
            ->expectsOutputToContain('Council registry bootstrap summary:')
            ->assertExitCode(0);

        $this->assertSame(2, Council::query()->count());
        $this->assertSame(2, CouncilVersion::query()->count());
        $this->assertSame(1, Dataset::query()->count());
        $this->assertSame(1, Import::query()->count());
        $this->assertSame(1, ImportRun::query()->count());
        $this->assertSame(1, IngestionSource::query()->count());
        $this->assertSame(1, SourceFile::query()->count());

        $this->assertDatabaseHas('councils', [
            'canonical_slug' => 'hartlepool',
            'jurisdiction_code' => 'E06000001',
        ]);

        $this->assertDatabaseHas('councils', [
            'canonical_slug' => 'middlesbrough',
            'jurisdiction_code' => 'E06000002',
        ]);

        $this->assertDatabaseHas('council_versions', [
            'display_name' => 'Hartlepool',
            'status' => 'active',
            'public_state' => 'published',
        ]);
    }
}

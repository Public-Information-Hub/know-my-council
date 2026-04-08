<?php

namespace Tests\Feature;

use App\Models\Council;
use App\Models\CouncilVersion;
use App\Models\Dataset;
use App\Models\DatasetVersion;
use App\Models\Import;
use App\Models\ImportRun;
use App\Models\IngestionSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngestionDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_an_ingestion_summary_for_the_admin_area(): void
    {
        $council = Council::query()->create([
            'canonical_slug' => 'example-council',
            'jurisdiction_code' => 'E06000000',
            'country_code' => 'GB',
            'authority_kind' => 'unitary_authority',
        ]);

        CouncilVersion::query()->create([
            'council_id' => $council->id,
            'display_name' => 'Example Council',
            'short_name' => null,
            'status' => 'active',
            'valid_from' => null,
            'valid_to' => null,
            'public_state' => 'published',
        ]);

        $dataset = Dataset::query()->create([
            'dataset_key' => 'council:example-council:spend_over_500_csv',
            'publisher_name' => 'Example Council',
            'publisher_kind' => 'local_authority',
            'dataset_family' => 'council_spend_over_500',
            'jurisdiction_scope' => 'GB',
            'default_council_id' => $council->id,
        ]);

        $import = Import::query()->create([
            'dataset_id' => $dataset->id,
            'import_key' => 'spend_over_500_csv:example-council',
            'import_type' => 'council_spend_over_500_csv',
            'connector_key' => 'manual',
            'parser_version' => '1',
            'normalisation_profile' => 'spend_over_500_csv_v1',
            'is_active' => true,
        ]);

        $datasetVersion = DatasetVersion::query()->create([
            'dataset_id' => $dataset->id,
            'version_label' => 'Example spend file',
            'edition_date' => now()->toDateString(),
            'published_at' => now(),
            'captured_at' => now(),
            'code_scheme' => 'E06000000',
            'geography_basis_type' => 'local_authority',
            'mapping_confidence' => 'high',
            'freshness_state' => 'current',
            'public_state' => 'published',
        ]);

        $run = ImportRun::query()->create([
            'import_id' => $import->id,
            'dataset_version_id' => $datasetVersion->id,
            'run_state' => 'succeeded',
            'started_at' => now()->subHour(),
            'finished_at' => now()->subMinutes(50),
            'rows_seen' => 12,
            'rows_inserted' => 11,
            'rows_updated' => 1,
            'warning_count' => 0,
        ]);

        IngestionSource::query()->create([
            'dataset_id' => $dataset->id,
            'import_id' => $import->id,
            'council_id' => $council->id,
            'source_key' => 'example-council:spend',
            'source_kind' => 'csv',
            'source_name' => 'Example Council spend',
            'source_url' => 'https://example.org/spend.csv',
            'adapter_key' => 'council_spend_csv_download',
            'refresh_mode' => 'scheduled',
            'expected_refresh_cadence' => 'daily',
            'last_checked_at' => now()->subDay(),
            'last_success_at' => now()->subDay(),
            'is_active' => true,
        ]);

        $this->getJson('/api/admin/ingestion-summary')
            ->assertOk()
            ->assertJsonPath('counts.councils', 1)
            ->assertJsonPath('counts.import_runs', 1)
            ->assertJsonPath('council_registry.import_key', 'uk:councils:ons-local-authority-districts-apr-2025')
            ->assertJsonPath('recent_import_runs.0.id', $run->id)
            ->assertJsonPath('recent_ingestion_sources.0.source_key', 'example-council:spend')
            ->assertJsonPath('suggested_actions.0.command', 'kmc:councils:bootstrap-ons');
    }
}

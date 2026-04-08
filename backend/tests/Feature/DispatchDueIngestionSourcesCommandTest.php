<?php

namespace Tests\Feature;

use App\Jobs\RefreshIngestionSourceJob;
use App\Models\Council;
use App\Models\CouncilVersion;
use App\Models\Dataset;
use App\Models\Import;
use App\Models\IngestionSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DispatchDueIngestionSourcesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_due_csv_sources_only(): void
    {
        Bus::fake();

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
            'jurisdiction_scope' => 'E06000000',
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

        $dueSource = IngestionSource::query()->create([
            'dataset_id' => $dataset->id,
            'import_id' => $import->id,
            'council_id' => $council->id,
            'source_key' => 'example-council:spend-due',
            'source_kind' => 'csv',
            'source_name' => 'Example Council spend',
            'source_url' => 'https://example.org/spend.csv',
            'adapter_key' => 'council_spend_csv_download',
            'refresh_mode' => 'scheduled',
            'expected_refresh_cadence' => 'hourly',
            'last_checked_at' => null,
            'is_active' => true,
        ]);

        IngestionSource::query()->create([
            'dataset_id' => $dataset->id,
            'import_id' => $import->id,
            'council_id' => $council->id,
            'source_key' => 'example-council:spend-not-due',
            'source_kind' => 'csv',
            'source_name' => 'Example Council spend later',
            'source_url' => 'https://example.org/spend-later.csv',
            'adapter_key' => 'council_spend_csv_download',
            'refresh_mode' => 'scheduled',
            'expected_refresh_cadence' => 'daily',
            'last_checked_at' => now(),
            'is_active' => true,
        ]);

        $this->artisan('kmc:ingestion-source:dispatch-due')
            ->expectsOutputToContain('Dispatching example-council:spend-due (csv)')
            ->assertExitCode(0);

        Bus::assertDispatched(RefreshIngestionSourceJob::class, function (RefreshIngestionSourceJob $job) use ($dueSource): bool {
            return $job->ingestionSourceId === $dueSource->id;
        });
    }
}

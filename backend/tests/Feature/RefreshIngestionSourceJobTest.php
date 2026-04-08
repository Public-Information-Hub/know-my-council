<?php

namespace Tests\Feature;

use App\Jobs\RefreshIngestionSourceJob;
use App\Models\Council;
use App\Models\CouncilVersion;
use App\Models\Dataset;
use App\Models\Import;
use App\Models\IngestionSource;
use App\Models\ImportRun;
use App\Models\SourceFile;
use App\Models\SpendRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RefreshIngestionSourceJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_downloads_and_ingests_a_registered_csv_source(): void
    {
        Storage::fake('local');
        config(['filesystems.default' => 'local']);

        Http::fake([
            'https://example.org/spend.csv' => Http::response("Payment Date,Supplier,Description,Amount\n01/04/2026,Example Ltd,Stationery,123.45\n", 200, [
                'Content-Type' => 'text/csv',
            ]),
        ]);

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

        $source = IngestionSource::query()->create([
            'dataset_id' => $dataset->id,
            'import_id' => $import->id,
            'council_id' => $council->id,
            'source_key' => 'example-council:spend',
            'source_kind' => 'csv',
            'source_name' => 'Example Council spend',
            'source_url' => 'https://example.org/spend.csv',
            'adapter_key' => 'council_spend_csv_download',
            'refresh_mode' => 'scheduled',
            'expected_refresh_cadence' => 'hourly',
            'last_checked_at' => null,
            'is_active' => true,
        ]);

        RefreshIngestionSourceJob::dispatchSync($source->id);

        $source->refresh();

        $this->assertNotNull($source->last_checked_at);
        $this->assertNotNull($source->last_success_at);
        $this->assertNull($source->last_error_summary);
        $this->assertSame(1, ImportRun::query()->count());
        $this->assertSame(1, SourceFile::query()->count());
        $this->assertSame(1, SpendRecord::query()->count());
        $this->assertSame($source->id, SourceFile::query()->first()->ingestion_source_id);

        $storedFile = SourceFile::query()->first();
        $this->assertTrue(Storage::disk('local')->exists($storedFile->storage_key));
    }
}

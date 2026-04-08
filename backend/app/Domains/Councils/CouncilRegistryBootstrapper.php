<?php

declare(strict_types=1);

namespace App\Domains\Councils;

use App\Models\Council;
use App\Models\CouncilVersion;
use App\Models\Dataset;
use App\Models\DatasetVersion;
use App\Models\Import;
use App\Models\ImportRun;
use App\Models\IngestionSource;
use App\Models\SourceFile;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CouncilRegistryBootstrapper
{
    private const DEFAULT_FEATURE_SERVICE_QUERY_URL = 'https://services1.arcgis.com/ESMARspQHYMw9BZ9/arcgis/rest/services/LAD_APR_2025_UK_NC_v2/FeatureServer/0/query?where=1%3D1&outFields=LAD25CD,LAD25NM,LAD25NMW&returnGeometry=false&f=json';

    private const DEFAULT_DATASET_KEY = 'uk:councils:ons-local-authority-districts-apr-2025';
    private const DEFAULT_IMPORT_KEY = 'uk:councils:ons-local-authority-districts-apr-2025';
    private const DEFAULT_SOURCE_KEY = 'ons:local-authority-districts-apr-2025';

    /**
     * @param array<string, mixed> $options
     */
    public function bootstrap(array $options = []): CouncilRegistryBootstrapperResult
    {
        $sourceUrl = $this->stringOption($options, 'source_url') ?? self::DEFAULT_FEATURE_SERVICE_QUERY_URL;
        $datasetKey = $this->stringOption($options, 'dataset_key') ?? self::DEFAULT_DATASET_KEY;
        $importKey = $this->stringOption($options, 'import_key') ?? self::DEFAULT_IMPORT_KEY;
        $sourceKey = $this->stringOption($options, 'source_key') ?? self::DEFAULT_SOURCE_KEY;
        $sourceName = $this->stringOption($options, 'source_name') ?? 'ONS local authority districts and unitary authorities';
        $discoveryUrl = $this->stringOption($options, 'discovery_url') ?? 'https://www.data.gov.uk/dataset/b2c91962-58e7-40f1-ad56-7aa2473a93fd/local-authority-districts-april-2025-names-and-codes-in-the-uk-v21';
        $storageDisk = $this->stringOption($options, 'storage_disk') ?? config('filesystems.default');
        $visibility = $this->stringOption($options, 'visibility') ?? 'restricted';
        $refreshMode = $this->stringOption($options, 'refresh_mode') ?? 'scheduled';
        $cadence = $this->stringOption($options, 'expected_refresh_cadence') ?? 'monthly';
        $dryRun = (bool) ($options['dry_run'] ?? false);

        if (!in_array($refreshMode, ['manual', 'scheduled', 'automatic'], true)) {
            return new CouncilRegistryBootstrapperResult(
                status: CouncilRegistryBootstrapperResult::STATUS_FAILED,
                datasetKey: $datasetKey,
                importKey: $importKey,
                importRunId: null,
                sourceFileId: null,
                rowsSeen: 0,
                councilsInserted: 0,
                councilsUpdated: 0,
                versionsInserted: 0,
                warningCount: 0,
                errorSummary: 'refresh_mode must be one of: manual, scheduled, automatic.',
            );
        }

        $response = Http::retry(2, 500)
            ->timeout(120)
            ->acceptJson()
            ->get($sourceUrl);

        $response->throw();

        $payload = $response->json();
        $features = is_array($payload['features'] ?? null) ? $payload['features'] : [];
        $rowsSeen = count($features);

        if ($rowsSeen === 0) {
            return new CouncilRegistryBootstrapperResult(
                status: CouncilRegistryBootstrapperResult::STATUS_FAILED,
                datasetKey: $datasetKey,
                importKey: $importKey,
                importRunId: null,
                sourceFileId: null,
                rowsSeen: 0,
                councilsInserted: 0,
                councilsUpdated: 0,
                versionsInserted: 0,
                warningCount: 0,
                errorSummary: 'No council records were returned by the source.',
            );
        }

        if ($dryRun) {
            $dryRunWarnings = 0;
            foreach ($features as $feature) {
                $attributes = is_array($feature['attributes'] ?? null) ? $feature['attributes'] : [];
                $name = trim((string) ($attributes['LAD25NM'] ?? ''));
                if ($name === '') {
                    $dryRunWarnings++;
                }
            }

            return new CouncilRegistryBootstrapperResult(
                status: CouncilRegistryBootstrapperResult::STATUS_SUCCEEDED,
                datasetKey: $datasetKey,
                importKey: $importKey,
                importRunId: null,
                sourceFileId: null,
                rowsSeen: $rowsSeen,
                councilsInserted: 0,
                councilsUpdated: 0,
                versionsInserted: 0,
                warningCount: $dryRunWarnings,
                errorSummary: null,
            );
        }

        $dataset = Dataset::query()->firstOrCreate(
            ['dataset_key' => $datasetKey],
            [
                'publisher_name' => 'Office for National Statistics',
                'publisher_kind' => 'public_body',
                'dataset_family' => 'council_registry',
                'jurisdiction_scope' => 'GB',
            ],
        );

        $import = Import::query()->firstOrCreate(
            ['import_key' => $importKey],
            [
                'dataset_id' => $dataset->id,
                'import_type' => 'council_registry_arcgis_feature_service',
                'connector_key' => 'arcgis_feature_service',
                'parser_version' => '1',
                'normalisation_profile' => 'council_registry_ons_lad_v1',
                'is_active' => true,
            ],
        );

        $source = IngestionSource::query()->firstOrCreate(
            ['source_key' => $sourceKey],
            [
                'dataset_id' => $dataset->id,
                'import_id' => $import->id,
                'council_id' => null,
                'source_kind' => 'api',
                'source_name' => $sourceName,
                'source_url' => $sourceUrl,
                'discovery_url' => $discoveryUrl,
                'adapter_key' => 'arcgis_feature_service',
                'refresh_mode' => $refreshMode,
                'expected_refresh_cadence' => $cadence,
                'is_active' => true,
            ],
        );

        $source->fill([
            'dataset_id' => $dataset->id,
            'import_id' => $import->id,
            'source_kind' => 'api',
            'source_name' => $sourceName,
            'source_url' => $sourceUrl,
            'discovery_url' => $discoveryUrl,
            'adapter_key' => 'arcgis_feature_service',
            'refresh_mode' => $refreshMode,
            'expected_refresh_cadence' => $cadence,
            'is_active' => true,
        ])->save();

        $datasetVersion = DatasetVersion::query()->create([
            'dataset_id' => $dataset->id,
            'version_label' => 'ONS local authority districts April 2025 bootstrap',
            'edition_date' => CarbonImmutable::parse('2025-04-01')->toDateString(),
            'published_at' => CarbonImmutable::now(),
            'captured_at' => CarbonImmutable::now(),
            'code_scheme' => 'LAD25CD',
            'geography_basis_type' => 'local_authority_district',
            'mapping_confidence' => 'high',
            'freshness_state' => 'current',
            'public_state' => 'published',
        ]);

        $importRun = ImportRun::query()->create([
            'import_id' => $import->id,
            'dataset_version_id' => $datasetVersion->id,
            'run_state' => 'queued',
        ]);

        $importRun->update([
            'run_state' => 'running',
            'started_at' => CarbonImmutable::now(),
        ]);

        $rawBody = $response->body();
        $sourceFileId = null;

        $storageKey = sprintf(
            'council-registry/%s/%s.json',
            Str::slug($dataset->dataset_key) ?: 'council-registry',
            CarbonImmutable::now()->format('YmdHis')
        );

        Storage::disk($storageDisk)->put($storageKey, $rawBody);

        $sourceFile = SourceFile::query()->create([
            'dataset_version_id' => $datasetVersion->id,
            'import_run_id' => $importRun->id,
            'council_id' => null,
            'ingestion_source_id' => $source->id,
            'storage_provider' => $storageDisk,
            'storage_bucket' => $this->storageBucketForDisk($storageDisk),
            'storage_key' => $storageKey,
            'sha256' => hash('sha256', $rawBody),
            'content_type' => 'application/json',
            'byte_size' => strlen($rawBody),
            'capture_method' => 'download',
            'source_url' => $sourceUrl,
            'published_at' => CarbonImmutable::parse('2025-04-01'),
            'captured_at' => CarbonImmutable::now(),
            'is_raw_unmodified' => true,
            'visibility' => $visibility,
        ]);

        $sourceFileId = $sourceFile->id;

        $existingSlugs = Council::query()->pluck('canonical_slug')->all();
        $existingSlugs = array_fill_keys($existingSlugs, true);
        $councilsInserted = 0;
        $councilsUpdated = 0;
        $versionsInserted = 0;
        $warnings = 0;

        foreach ($features as $feature) {
            $attributes = is_array($feature['attributes'] ?? null) ? $feature['attributes'] : [];
            $code = trim((string) ($attributes['LAD25CD'] ?? ''));
            $name = trim((string) ($attributes['LAD25NM'] ?? ''));
            $welshName = trim((string) ($attributes['LAD25NMW'] ?? ''));

            if ($name === '') {
                $warnings++;
                continue;
            }

            $slug = $this->buildUniqueSlug($name, $code, $existingSlugs);
            $existingSlugs[$slug] = true;

            $council = Council::query()->firstOrNew([
                'canonical_slug' => $slug,
            ]);

            $wasRecentlyCreated = !$council->exists;
            $council->fill([
                'jurisdiction_code' => $code !== '' ? $code : $council->jurisdiction_code,
                'country_code' => $council->country_code,
                'authority_kind' => $this->authorityKindForCode($code),
            ]);
            $council->save();

            if ($wasRecentlyCreated) {
                $councilsInserted++;
            } else {
                $councilsUpdated++;
            }

            $version = CouncilVersion::query()->firstOrCreate(
                [
                    'council_id' => $council->id,
                    'display_name' => $name,
                    'public_state' => 'published',
                ],
                [
                    'short_name' => $welshName !== '' ? $welshName : null,
                    'status' => 'active',
                    'valid_from' => CarbonImmutable::parse('2025-04-01')->toDateString(),
                    'valid_to' => null,
                ],
            );

            if ($version->wasRecentlyCreated) {
                $versionsInserted++;
            }
        }

        $now = CarbonImmutable::now();
        $importRun->update([
            'run_state' => 'succeeded',
            'finished_at' => $now,
            'rows_seen' => $rowsSeen,
            'rows_inserted' => $councilsInserted,
            'rows_updated' => $councilsUpdated,
            'warning_count' => $warnings,
        ]);

        $source->update([
            'last_checked_at' => $now,
            'last_success_at' => $now,
            'last_failure_at' => null,
            'last_error_summary' => null,
        ]);

        return new CouncilRegistryBootstrapperResult(
            status: CouncilRegistryBootstrapperResult::STATUS_SUCCEEDED,
            datasetKey: $datasetKey,
            importKey: $importKey,
            importRunId: $importRun->id,
            sourceFileId: $sourceFileId,
            rowsSeen: $rowsSeen,
            councilsInserted: $councilsInserted,
            councilsUpdated: $councilsUpdated,
            versionsInserted: $versionsInserted,
            warningCount: $warnings,
            errorSummary: null,
        );
    }

    /**
     * @param array<string, bool> $existingSlugs
     */
    private function buildUniqueSlug(string $name, string $code, array $existingSlugs): string
    {
        $baseSlug = Str::slug($name);
        if ($baseSlug === '') {
            $baseSlug = Str::slug($code);
        }

        if ($baseSlug === '') {
            $baseSlug = 'council';
        }

        $slug = $baseSlug;
        if (isset($existingSlugs[$slug])) {
            $codeSuffix = Str::slug($code);
            if ($codeSuffix !== '') {
                $slug = "{$baseSlug}-{$codeSuffix}";
            }
        }

        $counter = 2;
        while (isset($existingSlugs[$slug])) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function authorityKindForCode(string $code): ?string
    {
        $code = strtoupper(trim($code));

        if ($code === '') {
            return null;
        }

        return match (substr($code, 0, 1)) {
            'E' => 'english_local_authority',
            'W' => 'welsh_local_authority',
            'S' => 'scottish_local_authority',
            'N' => 'northern_ireland_local_authority',
            default => null,
        };
    }

    private function storageBucketForDisk(string $disk): string
    {
        return $disk;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function stringOption(array $options, string $key): ?string
    {
        $value = $options[$key] ?? null;
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        return $value !== '' ? $value : null;
    }
}

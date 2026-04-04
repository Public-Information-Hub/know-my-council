<?php

namespace App\Domains\Imports\Spend;

use App\Models\AuditLog;
use App\Models\Council;
use App\Models\CouncilVersion;
use App\Models\Dataset;
use App\Models\DatasetVersion;
use App\Models\Import;
use App\Models\ImportRun;
use App\Models\SourceFile;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CouncilSpendCsvIngestor
{
    public function __construct(
        private readonly OrganisationNameMatcher $organisationNameMatcher = new OrganisationNameMatcher(),
        private readonly SpendCsvRowNormaliser $rowNormaliser = new SpendCsvRowNormaliser(),
    ) {
    }

    /**
     * Ingest a single council spend CSV into Phase 1 tables.
     *
     * This is intentionally a concrete import path, not a generic ingestion framework.
     *
     * @param array<string, mixed> $options
     */
    public function ingest(string $councilSlug, string $localFilePath, array $options = []): CouncilSpendCsvIngestorResult
    {
        $dryRun = (bool) ($options['dry_run'] ?? false);
        $delimiter = (string) ($options['delimiter'] ?? ',');
        $storageDisk = (string) ($options['storage_disk'] ?: config('filesystems.default'));
        $visibility = (string) ($options['visibility'] ?? 'restricted');

        $editionDate = $this->parseDateOnly($options['edition_date'] ?? null) ?? CarbonImmutable::now()->toDateString();
        $capturedAt = $this->parseTimestamp($options['captured_at'] ?? null) ?? CarbonImmutable::now();
        $publishedAt = $this->parseTimestamp($options['published_at'] ?? null);

        $datasetKey = (string) ($options['dataset_key'] ?: "council:{$councilSlug}:spend_over_500_csv");
        $importKey = "spend_over_500_csv:{$councilSlug}";

        if (!is_file($localFilePath)) {
            return new CouncilSpendCsvIngestorResult(
                status: CouncilSpendCsvIngestorResult::STATUS_FAILED,
                councilSlug: $councilSlug,
                datasetKey: $datasetKey,
                importKey: $importKey,
                importRunId: null,
                sourceFileId: null,
                rowsSeen: 0,
                rowsInserted: 0,
                warningCount: 0,
                errorSummary: 'Local file does not exist or is not a file.',
            );
        }

        if ($dryRun) {
            [$rowsSeen, $rowsWouldInsert, $warningCount, $errorSummary] = $this->parseCsvOnly(
                localFilePath: $localFilePath,
                delimiter: $delimiter,
            );

            return new CouncilSpendCsvIngestorResult(
                status: $errorSummary === null ? CouncilSpendCsvIngestorResult::STATUS_SUCCEEDED : CouncilSpendCsvIngestorResult::STATUS_FAILED,
                councilSlug: $councilSlug,
                datasetKey: $datasetKey,
                importKey: $importKey,
                importRunId: null,
                sourceFileId: null,
                rowsSeen: $rowsSeen,
                rowsInserted: $rowsWouldInsert,
                warningCount: $warningCount,
                errorSummary: $errorSummary,
            );
        }

        $council = $this->resolveCouncil(
            slug: $councilSlug,
            createIfMissing: (bool) ($options['create_council'] ?? false),
            councilName: $options['council_name'] ?? null,
            dryRun: false,
        );
        if ($council === null) {
            return new CouncilSpendCsvIngestorResult(
                status: CouncilSpendCsvIngestorResult::STATUS_FAILED,
                councilSlug: $councilSlug,
                datasetKey: $datasetKey,
                importKey: $importKey,
                importRunId: null,
                sourceFileId: null,
                rowsSeen: 0,
                rowsInserted: 0,
                warningCount: 0,
                errorSummary: 'Council not found. Create it first, or use --create-council with --council-name.',
            );
        }

        $councilVersion = $this->resolveCouncilVersion(
            council: $council,
            councilVersionId: $options['council_version_id'] ?? null,
        );
        if ($councilVersion === null) {
            return new CouncilSpendCsvIngestorResult(
                status: CouncilSpendCsvIngestorResult::STATUS_FAILED,
                councilSlug: $councilSlug,
                datasetKey: $datasetKey,
                importKey: $importKey,
                importRunId: null,
                sourceFileId: null,
                rowsSeen: 0,
                rowsInserted: 0,
                warningCount: 0,
                errorSummary: 'Council version not found. Create a council_version first, or provide --council-version-id.',
            );
        }

        $dataset = Dataset::query()->firstOrCreate(
            ['dataset_key' => $datasetKey],
            [
                'publisher_name' => $councilVersion->display_name,
                'publisher_kind' => 'local_authority',
                'dataset_family' => 'council_spend_over_500',
                'jurisdiction_scope' => $council->jurisdiction_code,
                'default_council_id' => $council->id,
            ],
        );

        $datasetVersion = DatasetVersion::query()->create([
            'dataset_id' => $dataset->id,
            'version_label' => $options['version_label'] ?? null,
            'edition_date' => $editionDate,
            'published_at' => $publishedAt,
            'captured_at' => $capturedAt,
            'mapping_confidence' => 'high',
            'freshness_state' => 'unknown',
            'public_state' => 'published',
        ]);

        $import = Import::query()->firstOrCreate(
            ['import_key' => $importKey],
            [
                'dataset_id' => $dataset->id,
                'import_type' => 'council_spend_over_500_csv',
                'connector_key' => 'manual',
                'parser_version' => '1',
                'normalisation_profile' => 'spend_over_500_csv_v1',
                'is_active' => true,
            ],
        );

        $idempotencyKey = $options['idempotency_key'] ?? null;
        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $existing = ImportRun::query()
                ->where('import_id', $import->id)
                ->where('idempotency_key', $idempotencyKey)
                ->where('run_state', 'succeeded')
                ->first();

            if ($existing !== null) {
                return new CouncilSpendCsvIngestorResult(
                    status: CouncilSpendCsvIngestorResult::STATUS_SKIPPED,
                    councilSlug: $council->canonical_slug,
                    datasetKey: $datasetKey,
                    importKey: $importKey,
                    importRunId: $existing->id,
                    sourceFileId: null,
                    rowsSeen: (int) ($existing->rows_seen ?? 0),
                    rowsInserted: (int) ($existing->rows_inserted ?? 0),
                    warningCount: (int) ($existing->warning_count ?? 0),
                    errorSummary: null,
                );
            }
        }

        $importRun = ImportRun::query()->create([
            'import_id' => $import->id,
            'dataset_version_id' => $datasetVersion->id,
            'run_state' => 'queued',
            'idempotency_key' => $idempotencyKey,
        ]);

        $importRun->update([
            'run_state' => 'running',
            'started_at' => CarbonImmutable::now(),
        ]);

        $this->audit(
            actorImportRunId: $importRun->id,
            actionFamily: 'import',
            actionType: 'import_run.started',
            targetType: 'import_runs',
            targetId: $importRun->id,
            after: [
                'run_state' => 'running',
                'dataset_version_id' => $datasetVersion->id,
            ],
        );

        try {
            [$bucket, $key, $sha256, $contentType, $byteSize] = $this->storeRawFile(
                disk: $storageDisk,
                importRunId: $importRun->id,
                localFilePath: $localFilePath,
            );

            $sourceFile = SourceFile::query()->create([
                'dataset_version_id' => $datasetVersion->id,
                'import_run_id' => $importRun->id,
                'council_id' => $council->id,
                'storage_provider' => $storageDisk,
                'storage_bucket' => $bucket,
                'storage_key' => $key,
                'sha256' => $sha256,
                'content_type' => $contentType,
                'byte_size' => $byteSize,
                'capture_method' => 'upload',
                'published_at' => $publishedAt,
                'captured_at' => $capturedAt,
                'is_raw_unmodified' => true,
                'visibility' => $visibility,
            ]);

            [$rowsSeen, $rowsInserted, $warningCount, $errorSummary, $rowErrorSamples] = $this->parseCsvAndInsertSpendRows(
                council: $council,
                councilVersion: $councilVersion,
                datasetVersionId: $datasetVersion->id,
                importRunId: $importRun->id,
                localFilePath: $localFilePath,
                delimiter: $delimiter,
            );

            $finalState = $errorSummary === null ? 'succeeded' : 'failed';

            $importRun->update([
                'run_state' => $finalState,
                'finished_at' => CarbonImmutable::now(),
                'rows_seen' => $rowsSeen,
                'rows_inserted' => $rowsInserted,
                'rows_updated' => 0,
                'warning_count' => $warningCount,
                'error_summary' => $errorSummary,
            ]);

            $this->audit(
                actorImportRunId: $importRun->id,
                actionFamily: 'import',
                actionType: $finalState === 'succeeded' ? 'import_run.succeeded' : 'import_run.failed',
                targetType: 'import_runs',
                targetId: $importRun->id,
                after: [
                    'run_state' => $finalState,
                    'rows_seen' => $rowsSeen,
                    'rows_inserted' => $rowsInserted,
                    'warning_count' => $warningCount,
                    'row_error_samples' => $rowErrorSamples,
                ],
            );

            return new CouncilSpendCsvIngestorResult(
                status: $finalState === 'succeeded' ? CouncilSpendCsvIngestorResult::STATUS_SUCCEEDED : CouncilSpendCsvIngestorResult::STATUS_FAILED,
                councilSlug: $council->canonical_slug,
                datasetKey: $datasetKey,
                importKey: $importKey,
                importRunId: $importRun->id,
                sourceFileId: $sourceFile->id,
                rowsSeen: $rowsSeen,
                rowsInserted: $rowsInserted,
                warningCount: $warningCount,
                errorSummary: $errorSummary,
            );
        } catch (\Throwable $e) {
            $importRun->update([
                'run_state' => 'failed',
                'finished_at' => CarbonImmutable::now(),
                'error_summary' => $e->getMessage(),
            ]);

            $this->audit(
                actorImportRunId: $importRun->id,
                actionFamily: 'import',
                actionType: 'import_run.failed',
                targetType: 'import_runs',
                targetId: $importRun->id,
                after: [
                    'run_state' => 'failed',
                    'error' => $e->getMessage(),
                ],
            );

            return new CouncilSpendCsvIngestorResult(
                status: CouncilSpendCsvIngestorResult::STATUS_FAILED,
                councilSlug: $council->canonical_slug,
                datasetKey: $datasetKey,
                importKey: $importKey,
                importRunId: $importRun->id,
                sourceFileId: null,
                rowsSeen: 0,
                rowsInserted: 0,
                warningCount: 0,
                errorSummary: $e->getMessage(),
            );
        }
    }

    private function resolveCouncil(string $slug, bool $createIfMissing, mixed $councilName, bool $dryRun): ?Council
    {
        $council = Council::query()->where('canonical_slug', $slug)->first();
        if ($council !== null) {
            return $council;
        }

        if (!$createIfMissing) {
            return null;
        }

        $councilName = is_string($councilName) ? trim($councilName) : '';
        if ($councilName === '') {
            return null;
        }

        $council = Council::query()->create([
            'canonical_slug' => $slug,
            'jurisdiction_code' => null,
            'country_code' => 'GB',
            'authority_kind' => null,
        ]);

        CouncilVersion::query()->create([
            'council_id' => $council->id,
            'display_name' => $councilName,
            'short_name' => null,
            'status' => 'active',
            'valid_from' => null,
            'valid_to' => null,
            'public_state' => 'published',
        ]);

        return $council;
    }

    private function resolveCouncilVersion(Council $council, mixed $councilVersionId): ?CouncilVersion
    {
        if (is_string($councilVersionId) && trim($councilVersionId) !== '') {
            return CouncilVersion::query()
                ->where('id', $councilVersionId)
                ->where('council_id', $council->id)
                ->first();
        }

        return CouncilVersion::query()
            ->where('council_id', $council->id)
            ->where('public_state', 'published')
            ->orderByRaw('valid_from desc nulls last')
            ->orderByDesc('created_at')
            ->first();
    }

    private function storeRawFile(string $disk, string $importRunId, string $localFilePath): array
    {
        $bucket = (string) (config("filesystems.disks.{$disk}.bucket") ?: $disk);
        $baseName = basename($localFilePath);
        $key = "raw/import-runs/{$importRunId}/{$baseName}";

        $stream = fopen($localFilePath, 'r');
        if ($stream === false) {
            throw new \RuntimeException('Unable to open local file for reading.');
        }

        try {
            Storage::disk($disk)->put($key, $stream);
        } finally {
            fclose($stream);
        }

        $sha256 = hash_file('sha256', $localFilePath) ?: null;
        $byteSize = filesize($localFilePath) ?: null;
        $contentType = function_exists('mime_content_type') ? (mime_content_type($localFilePath) ?: null) : null;

        return [$bucket, $key, $sha256, $contentType, $byteSize];
    }

    /**
     * Parse and validate CSV only (no database writes).
     *
     * @return array{0:int,1:int,2:int,3:?string}
     */
    private function parseCsvOnly(string $localFilePath, string $delimiter): array
    {
        $rowsSeen = 0;
        $rowsWouldInsert = 0;
        $warningCount = 0;
        $errorSummary = null;

        $handle = fopen($localFilePath, 'r');
        if ($handle === false) {
            return [0, 0, 0, 'Unable to read CSV file.'];
        }

        try {
            $headers = fgetcsv($handle, 0, $delimiter);
            if (!is_array($headers) || count($headers) === 0) {
                return [0, 0, 0, 'CSV header row is missing or unreadable.'];
            }

            $columnMap = SpendCsvColumnMap::fromHeaders($headers);

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowsSeen++;

                if (!is_array($row) || $this->isEmptyRow($row)) {
                    continue;
                }

                $amountRaw = $row[$columnMap->amountIndex] ?? null;
                $dateRaw = $row[$columnMap->transactionDateIndex] ?? null;

                $amount = $this->rowNormaliser->parseAmount(is_string($amountRaw) ? $amountRaw : null);
                $date = $this->rowNormaliser->parseDate(is_string($dateRaw) ? $dateRaw : null);

                if ($amount === null || $date === null) {
                    $warningCount++;
                    continue;
                }

                $rowsWouldInsert++;
            }
        } catch (\Throwable $e) {
            $errorSummary = $e->getMessage();
        } finally {
            fclose($handle);
        }

        return [$rowsSeen, $rowsWouldInsert, $warningCount, $errorSummary];
    }

    /**
     * Parse and insert rows, returning counts and error summaries.
     *
     * @return array{0:int,1:int,2:int,3:?string,4:array<int, array<string, mixed>>}
     */
    private function parseCsvAndInsertSpendRows(
        Council $council,
        CouncilVersion $councilVersion,
        string $datasetVersionId,
        string $importRunId,
        string $localFilePath,
        string $delimiter
    ): array {
        $rowsSeen = 0;
        $rowsInserted = 0;
        $warningCount = 0;
        $errorSummary = null;
        $rowErrorSamples = [];

        $handle = fopen($localFilePath, 'r');
        if ($handle === false) {
            return [0, 0, 0, 'Unable to read CSV file.', []];
        }

        try {
            $headers = fgetcsv($handle, 0, $delimiter);
            if (!is_array($headers) || count($headers) === 0) {
                return [0, 0, 0, 'CSV header row is missing or unreadable.', []];
            }

            $columnMap = SpendCsvColumnMap::fromHeaders($headers);

            $now = CarbonImmutable::now();
            $batch = [];

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowsSeen++;

                if (!is_array($row) || $this->isEmptyRow($row)) {
                    continue;
                }

                $supplierName = $row[$columnMap->supplierNameIndex] ?? null;
                $amountRaw = $row[$columnMap->amountIndex] ?? null;
                $dateRaw = $row[$columnMap->transactionDateIndex] ?? null;
                $description = $columnMap->descriptionIndex !== null ? ($row[$columnMap->descriptionIndex] ?? null) : null;

                $amount = $this->rowNormaliser->parseAmount(is_string($amountRaw) ? $amountRaw : null);
                $date = $this->rowNormaliser->parseDate(is_string($dateRaw) ? $dateRaw : null);

                if ($amount === null || $date === null) {
                    $warningCount++;
                    if (count($rowErrorSamples) < 10) {
                        $rowErrorSamples[] = [
                            'row_number' => $rowsSeen + 1,
                            'error' => 'Missing or invalid amount/date',
                            'amount_raw' => $amountRaw,
                            'date_raw' => $dateRaw,
                        ];
                    }
                    continue;
                }

                $match = $this->organisationNameMatcher->match(is_string($supplierName) ? $supplierName : null);

                $batch[] = [
                    'id' => (string) Str::uuid(),
                    'council_id' => $council->id,
                    'council_version_id' => $councilVersion->id,
                    'dataset_version_id' => $datasetVersionId,
                    'import_run_id' => $importRunId,
                    'organisation_id' => $match->organisationId,
                    'supplier_name_observed' => is_string($supplierName) ? trim($supplierName) : null,
                    'description_observed' => is_string($description) ? trim($description) : null,
                    'transaction_date' => $date->toDateString(),
                    'amount' => $amount,
                    'currency' => 'GBP',
                    'mapping_confidence' => $match->mappingConfidence,
                    'public_state' => 'published',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if (count($batch) >= 500) {
                    \Illuminate\Support\Facades\DB::table('spend_records')->insert($batch);
                    $rowsInserted += count($batch);
                    $batch = [];
                }
            }

            if (count($batch) > 0) {
                \Illuminate\Support\Facades\DB::table('spend_records')->insert($batch);
                $rowsInserted += count($batch);
            }
        } catch (\Throwable $e) {
            $errorSummary = $e->getMessage();
        } finally {
            fclose($handle);
        }

        return [$rowsSeen, $rowsInserted, $warningCount, $errorSummary, $rowErrorSamples];
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $cell) {
            if (is_string($cell) && trim($cell) !== '') {
                return false;
            }
        }
        return true;
    }

    private function audit(
        string $actorImportRunId,
        ?string $actionFamily,
        string $actionType,
        string $targetType,
        string $targetId,
        ?array $before = null,
        ?array $after = null,
        ?array $context = null,
    ): void {
        AuditLog::query()->create([
            'actor_type' => 'import',
            'actor_import_run_id' => $actorImportRunId,
            'action_family' => $actionFamily,
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'before_json' => $before,
            'after_json' => $after,
            'context_json' => $context,
            'correlation_id' => $actorImportRunId,
            'workflow_type' => 'import_run',
            'workflow_id' => $actorImportRunId,
        ]);
    }

    private function parseDateOnly(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseTimestamp(mixed $value): ?CarbonImmutable
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}

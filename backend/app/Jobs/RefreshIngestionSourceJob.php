<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Imports\SourceAdapters\CsvDownloadIngestionAdapter;
use App\Domains\Imports\Spend\CouncilSpendCsvIngestor;
use App\Models\IngestionSource;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshIngestionSourceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $ingestionSourceId,
    ) {
    }

    public function handle(CsvDownloadIngestionAdapter $adapter, CouncilSpendCsvIngestor $ingestor): void
    {
        $source = IngestionSource::query()
            ->with(['dataset', 'import', 'council'])
            ->find($this->ingestionSourceId);

        if ($source === null) {
            return;
        }

        if (!$source->is_active) {
            $source->update([
                'last_checked_at' => CarbonImmutable::now(),
                'last_failure_at' => CarbonImmutable::now(),
                'last_error_summary' => 'Source is inactive.',
            ]);

            return;
        }

        if ($source->source_kind !== 'csv') {
            $this->markFailure($source, 'Unsupported source kind: '.$source->source_kind);
            return;
        }

        if ($source->council === null) {
            $this->markFailure($source, 'Source is not linked to a council.');
            return;
        }

        if ($source->import === null) {
            $this->markFailure($source, 'Source is not linked to an import definition.');
            return;
        }

        if ($source->import->import_type !== 'council_spend_over_500_csv') {
            $this->markFailure($source, 'Unsupported import type: '.$source->import->import_type);
            return;
        }

        $download = null;
        $tempPath = null;

        try {
            $download = $adapter->download($source);
            $tempPath = $download->localFilePath;

            $result = $ingestor->ingest(
                councilSlug: $source->council->canonical_slug,
                localFilePath: $download->localFilePath,
                options: [
                    'council_version_id' => $this->publishedCouncilVersionId($source),
                    'dataset_key' => $source->dataset?->dataset_key,
                    'ingestion_source_id' => $source->id,
                    'source_url' => $download->sourceUrl,
                    'capture_method' => 'download',
                    'captured_at' => CarbonImmutable::now()->toIso8601String(),
                    'storage_disk' => config('filesystems.default'),
                ],
            );

            $now = CarbonImmutable::now();

            $source->update([
                'last_checked_at' => $now,
                'last_success_at' => $result->status === 'succeeded' ? $now : null,
                'last_failure_at' => $result->status === 'succeeded' ? $source->last_failure_at : $now,
                'last_error_summary' => $result->errorSummary,
            ]);
        } catch (\Throwable $e) {
            $this->markFailure($source, $e->getMessage());
        } finally {
            if (is_string($tempPath) && $tempPath !== '' && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    private function publishedCouncilVersionId(IngestionSource $source): ?string
    {
        $version = $source->council?->versions()
            ->where('public_state', 'published')
            ->orderByRaw('CASE WHEN valid_from IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('valid_from')
            ->orderByDesc('created_at')
            ->first();

        return $version?->id;
    }

    private function markFailure(IngestionSource $source, string $message): void
    {
        $now = CarbonImmutable::now();

        $source->update([
            'last_checked_at' => $now,
            'last_failure_at' => $now,
            'last_error_summary' => $message,
        ]);
    }
}

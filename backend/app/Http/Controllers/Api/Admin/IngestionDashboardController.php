<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Council;
use App\Models\CouncilVersion;
use App\Models\Dataset;
use App\Models\Import;
use App\Models\ImportRun;
use App\Models\IngestionSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class IngestionDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $councilRegistryImportKey = 'uk:councils:ons-local-authority-districts-apr-2025';

        $councilRegistryImport = Import::query()
            ->where('import_key', $councilRegistryImportKey)
            ->with(['dataset'])
            ->first();

        $latestCouncilRegistryRun = $councilRegistryImport?->runs()
            ->with(['import.dataset'])
            ->orderByDesc('started_at')
            ->orderByDesc('created_at')
            ->first();

        $latestImportRuns = ImportRun::query()
            ->with(['import.dataset'])
            ->orderByDesc('started_at')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn (ImportRun $run): array => $this->serialiseImportRun($run))
            ->all();

        $latestSources = IngestionSource::query()
            ->with(['council', 'import.dataset'])
            ->orderByDesc('last_checked_at')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (IngestionSource $source): array => $this->serialiseIngestionSource($source))
            ->all();

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'counts' => [
                'councils' => Council::query()->count(),
                'council_versions' => CouncilVersion::query()->count(),
                'datasets' => Dataset::query()->count(),
                'imports' => Import::query()->count(),
                'import_runs' => ImportRun::query()->count(),
                'ingestion_sources' => IngestionSource::query()->count(),
                'active_ingestion_sources' => IngestionSource::query()->where('is_active', true)->count(),
                'failing_ingestion_sources' => IngestionSource::query()->whereNotNull('last_failure_at')->count(),
                'running_import_runs' => ImportRun::query()->where('run_state', 'running')->count(),
            ],
            'council_registry' => [
                'import_key' => $councilRegistryImportKey,
                'dataset_key' => $councilRegistryImport?->dataset?->dataset_key,
                'latest_run' => $latestCouncilRegistryRun === null ? null : $this->serialiseImportRun($latestCouncilRegistryRun),
            ],
            'recent_import_runs' => $latestImportRuns,
            'recent_ingestion_sources' => $latestSources,
            'suggested_actions' => [
                [
                    'label' => 'Bootstrap council registry',
                    'command' => 'kmc:councils:bootstrap-ons',
                    'purpose' => 'Refresh the council registry from the official ONS source and keep the lookup table moving.',
                ],
                [
                    'label' => 'Dispatch due sources',
                    'command' => 'kmc:ingestion-source:dispatch-due',
                    'purpose' => 'Run scheduled council source refreshes and enqueue downloads that are due.',
                ],
                [
                    'label' => 'Upsert a source',
                    'command' => 'kmc:ingestion-source:upsert',
                    'purpose' => 'Register or refresh a council data source before wiring it into automation.',
                ],
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serialiseImportRun(ImportRun $run): array
    {
        return [
            'id' => $run->id,
            'import_key' => $run->import?->import_key,
            'import_type' => $run->import?->import_type,
            'dataset_key' => $run->import?->dataset?->dataset_key,
            'run_state' => $run->run_state,
            'started_at' => $this->formatTimestamp($run->started_at),
            'finished_at' => $this->formatTimestamp($run->finished_at),
            'rows_seen' => $run->rows_seen,
            'rows_inserted' => $run->rows_inserted,
            'rows_updated' => $run->rows_updated,
            'warning_count' => $run->warning_count,
            'error_summary' => $run->error_summary,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serialiseIngestionSource(IngestionSource $source): array
    {
        return [
            'id' => $source->id,
            'source_key' => $source->source_key,
            'source_name' => $source->source_name,
            'source_kind' => $source->source_kind,
            'refresh_mode' => $source->refresh_mode,
            'expected_refresh_cadence' => $source->expected_refresh_cadence,
            'is_active' => $source->is_active,
            'dataset_key' => $source->dataset?->dataset_key,
            'council_slug' => $source->council?->canonical_slug,
            'last_checked_at' => $this->formatTimestamp($source->last_checked_at),
            'last_success_at' => $this->formatTimestamp($source->last_success_at),
            'last_failure_at' => $this->formatTimestamp($source->last_failure_at),
            'last_error_summary' => $source->last_error_summary,
        ];
    }

    private function formatTimestamp(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof Carbon ? $value->toIso8601String() : Carbon::parse($value)->toIso8601String();
    }
}

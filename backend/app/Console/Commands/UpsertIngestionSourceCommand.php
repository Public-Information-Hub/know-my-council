<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Council;
use App\Models\Dataset;
use App\Models\Import;
use App\Models\IngestionSource;
use Illuminate\Console\Command;

class UpsertIngestionSourceCommand extends Command
{
    protected $signature = 'kmc:ingestion-source:upsert
        {source_key : Stable source key, for example "bristol-city-council:spend-over-500"}
        {source_kind : Source kind (api|csv|html|xlsx|pdf|document|other)}
        {--dataset-key= : Optional dataset key to associate}
        {--import-key= : Optional import key to associate}
        {--council-slug= : Optional council slug to associate}
        {--source-name= : Optional human-readable source name}
        {--source-url= : Optional canonical source URL}
        {--discovery-url= : Optional discovery URL for scheduled refreshes}
        {--adapter-key= : Optional adapter key used to ingest this source}
        {--refresh-mode=manual : Refresh mode (manual|scheduled|automatic)}
        {--expected-refresh-cadence= : Optional cadence label, e.g. "daily" or "monthly"}
        {--active : Mark the source active}
        {--inactive : Mark the source inactive}';

    protected $description = 'Create or update a registered ingestion source for a council dataset.';

    public function handle(): int
    {
        $sourceKey = trim((string) $this->argument('source_key'));
        $sourceKind = trim((string) $this->argument('source_kind'));

        if ($sourceKey === '' || $sourceKind === '') {
            $this->error('source_key and source_kind are required.');
            return self::FAILURE;
        }

        $refreshMode = trim((string) $this->option('refresh-mode'));
        if ($refreshMode === '') {
            $refreshMode = 'manual';
        }

        if (!in_array($refreshMode, ['manual', 'scheduled', 'automatic'], true)) {
            $this->error('refresh-mode must be one of: manual, scheduled, automatic.');
            return self::FAILURE;
        }

        if ($this->option('active') && $this->option('inactive')) {
            $this->error('Choose only one of --active or --inactive.');
            return self::FAILURE;
        }

        $dataset = $this->resolveDataset();
        if ($dataset === false) {
            return self::FAILURE;
        }

        $import = $this->resolveImport($dataset);
        if ($import === false) {
            return self::FAILURE;
        }

        $council = $this->resolveCouncil();
        if ($council === false) {
            return self::FAILURE;
        }

        $source = IngestionSource::query()->firstOrNew([
            'source_key' => $sourceKey,
        ]);

        $source->source_key = $sourceKey;
        $source->source_kind = $sourceKind;
        $source->dataset_id = $dataset?->id;
        $source->import_id = $import?->id;
        $source->council_id = $council?->id;
        $source->source_name = $this->nullableOption('source-name') ?? $source->source_name;
        $source->source_url = $this->nullableOption('source-url') ?? $source->source_url;
        $source->discovery_url = $this->nullableOption('discovery-url') ?? $source->discovery_url;
        $source->adapter_key = $this->nullableOption('adapter-key') ?? $source->adapter_key;
        $source->refresh_mode = $refreshMode;
        $source->expected_refresh_cadence = $this->nullableOption('expected-refresh-cadence') ?? $source->expected_refresh_cadence;

        if ($this->option('active')) {
            $source->is_active = true;
        } elseif ($this->option('inactive')) {
            $source->is_active = false;
        }

        $source->save();

        $this->info($source->wasRecentlyCreated ? 'Created ingestion source.' : 'Updated ingestion source.');
        $this->line('Source key: '.$source->source_key);
        $this->line('Source kind: '.$source->source_kind);
        $this->line('Source id: '.$source->id);

        return self::SUCCESS;
    }

    private function resolveDataset(): Dataset|false|null
    {
        $datasetKey = $this->nullableOption('dataset-key');
        if ($datasetKey === null) {
            return null;
        }

        $dataset = Dataset::query()->where('dataset_key', $datasetKey)->first();
        if ($dataset === null) {
            $this->error("Dataset not found for dataset key: {$datasetKey}");
            return false;
        }

        return $dataset;
    }

    private function resolveImport(?Dataset $dataset): Import|false|null
    {
        $importKey = $this->nullableOption('import-key');
        if ($importKey === null) {
            return null;
        }

        $import = Import::query()->where('import_key', $importKey)->first();
        if ($import === null) {
            $this->error("Import not found for import key: {$importKey}");
            return false;
        }

        if ($dataset !== null && $import->dataset_id !== $dataset->id) {
            $this->error('Import key does not belong to the selected dataset.');
            return false;
        }

        return $import;
    }

    private function resolveCouncil(): Council|false|null
    {
        $councilSlug = $this->nullableOption('council-slug');
        if ($councilSlug === null) {
            return null;
        }

        $council = Council::query()->where('canonical_slug', $councilSlug)->first();
        if ($council === null) {
            $this->error("Council not found for council slug: {$councilSlug}");
            return false;
        }

        return $council;
    }

    private function nullableOption(string $name): ?string
    {
        $value = $this->option($name);
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        return $value !== '' ? $value : null;
    }
}

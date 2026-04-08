<?php

namespace App\Console\Commands;

use App\Domains\Imports\Spend\CouncilSpendCsvIngestor;
use App\Domains\Imports\Spend\CouncilSpendCsvIngestorResult;
use Illuminate\Console\Command;

class IngestCouncilSpendCsvCommand extends Command
{
    protected $signature = 'kmc:ingest:council-spend-csv
        {council_slug : The council canonical slug (e.g. "bristol-city-council")}
        {file : Path to a local CSV file}
        {--council-version-id= : Optional council_version UUID to use for the ingest}
        {--create-council : Create the council (and a first council_version) if it does not exist}
        {--council-name= : Required if --create-council is set; used for council_versions.display_name}
        {--dataset-key= : Optional override for datasets.dataset_key}
        {--edition-date= : Optional dataset edition date (YYYY-MM-DD); defaults to today}
        {--version-label= : Optional dataset version label (free text)}
        {--published-at= : Optional dataset published timestamp (ISO 8601); stored on dataset_version and source_file if provided}
        {--captured-at= : Optional capture timestamp (ISO 8601); defaults to now}
        {--idempotency-key= : Optional idempotency key to prevent accidental duplicate runs}
        {--ingestion-source-id= : Optional ingestion_sources UUID to link the raw file to a registered source}
        {--storage-disk= : Filesystem disk to store the raw source file on; defaults to FILESYSTEM_DISK}
        {--visibility=restricted : Source file visibility (public|restricted|private)}
        {--delimiter=, : CSV delimiter}
        {--supplier-header= : Override supplier column header (case-insensitive match)}
        {--amount-header= : Override amount column header (case-insensitive match)}
        {--date-header= : Override transaction date column header (case-insensitive match)}
        {--description-header= : Override description column header (case-insensitive match)}
        {--dry-run : Parse and validate but do not write to the database or object storage}';

    protected $description = 'Ingest a council "spend over £500" style CSV into Phase 1 tables with provenance.';

    public function handle(CouncilSpendCsvIngestor $ingestor): int
    {
        $result = $ingestor->ingest(
            councilSlug: (string) $this->argument('council_slug'),
            localFilePath: (string) $this->argument('file'),
            options: [
                'council_version_id' => $this->option('council-version-id'),
                'create_council' => (bool) $this->option('create-council'),
                'council_name' => $this->option('council-name'),
                'dataset_key' => $this->option('dataset-key'),
                'edition_date' => $this->option('edition-date'),
                'version_label' => $this->option('version-label'),
                'published_at' => $this->option('published-at'),
                'captured_at' => $this->option('captured-at'),
                'idempotency_key' => $this->option('idempotency-key'),
                'ingestion_source_id' => $this->option('ingestion-source-id'),
                'storage_disk' => $this->option('storage-disk'),
                'visibility' => $this->option('visibility'),
                'delimiter' => $this->option('delimiter'),
                'supplier_header' => $this->option('supplier-header'),
                'amount_header' => $this->option('amount-header'),
                'date_header' => $this->option('date-header'),
                'description_header' => $this->option('description-header'),
                'dry_run' => (bool) $this->option('dry-run'),
            ],
        );

        $this->line('');
        $this->line('Ingestion summary:');
        $this->line('- Council: '.$result->councilSlug);
        $this->line('- Dataset key: '.$result->datasetKey);
        $this->line('- Import key: '.$result->importKey);
        $this->line('- Import run: '.($result->importRunId ?? '(none)'));
        $this->line('- Source file: '.($result->sourceFileId ?? '(none)'));
        $this->line('- Rows seen: '.$result->rowsSeen);
        $this->line('- Rows inserted: '.$result->rowsInserted);
        $this->line('- Warnings: '.$result->warningCount);
        if ($result->errorSummary !== null) {
            $this->line('- Error summary: '.$result->errorSummary);
        }

        if ($result->status === CouncilSpendCsvIngestorResult::STATUS_SUCCEEDED) {
            $this->info('Result: succeeded');
            return self::SUCCESS;
        }

        if ($result->status === CouncilSpendCsvIngestorResult::STATUS_SKIPPED) {
            $this->warn('Result: skipped (idempotency key already succeeded)');
            return self::SUCCESS;
        }

        $this->error('Result: failed');
        return self::FAILURE;
    }
}

<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Councils\CouncilRegistryBootstrapper;
use App\Domains\Councils\CouncilRegistryBootstrapperResult;
use Illuminate\Console\Command;

class BootstrapCouncilRegistryCommand extends Command
{
    protected $signature = 'kmc:councils:bootstrap-ons
        {--source-url= : Optional override for the ArcGIS feature service query URL}
        {--dataset-key= : Optional dataset key override}
        {--import-key= : Optional import key override}
        {--source-key= : Optional source key override}
        {--source-name= : Optional source display name override}
        {--discovery-url= : Optional source discovery URL override}
        {--storage-disk= : Filesystem disk for raw source storage}
        {--visibility=restricted : Source file visibility (public|restricted|private)}
        {--refresh-mode=scheduled : Refresh mode for the registered source}
        {--expected-refresh-cadence=monthly : Expected refresh cadence label}
        {--dry-run : Parse and validate but do not write to the database or object storage}';

    protected $description = 'Bootstrap the council registry from the ONS local authority districts feature service.';

    public function handle(CouncilRegistryBootstrapper $bootstrapper): int
    {
        $result = $bootstrapper->bootstrap([
            'source_url' => $this->option('source-url'),
            'dataset_key' => $this->option('dataset-key'),
            'import_key' => $this->option('import-key'),
            'source_key' => $this->option('source-key'),
            'source_name' => $this->option('source-name'),
            'discovery_url' => $this->option('discovery-url'),
            'storage_disk' => $this->option('storage-disk'),
            'visibility' => $this->option('visibility'),
            'refresh_mode' => $this->option('refresh-mode'),
            'expected_refresh_cadence' => $this->option('expected-refresh-cadence'),
            'dry_run' => (bool) $this->option('dry-run'),
        ]);

        $this->line('');
        $this->line('Council registry bootstrap summary:');
        $this->line('- Dataset key: '.$result->datasetKey);
        $this->line('- Import key: '.$result->importKey);
        $this->line('- Import run: '.($result->importRunId ?? '(none)'));
        $this->line('- Source file: '.($result->sourceFileId ?? '(none)'));
        $this->line('- Rows seen: '.$result->rowsSeen);
        $this->line('- Councils inserted: '.$result->councilsInserted);
        $this->line('- Councils updated: '.$result->councilsUpdated);
        $this->line('- Council versions inserted: '.$result->versionsInserted);
        $this->line('- Warnings: '.$result->warningCount);

        if ($result->errorSummary !== null) {
            $this->error($result->errorSummary);
        }

        if ($result->status === CouncilRegistryBootstrapperResult::STATUS_SUCCEEDED) {
            $this->info('Result: succeeded');
            return self::SUCCESS;
        }

        $this->error('Result: failed');
        return self::FAILURE;
    }
}

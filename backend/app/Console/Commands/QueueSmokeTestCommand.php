<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\QueueSmokeTestJob;
use Illuminate\Console\Command;

class QueueSmokeTestCommand extends Command
{
    protected $signature = 'kmc:queue-smoke-test {--message= : Optional message to log when the job runs}';

    protected $description = 'Dispatch a simple job to verify queue configuration (worker must be running to complete it).';

    public function handle(): int
    {
        $message = (string) ($this->option('message') ?: 'Queue smoke test job executed.');

        QueueSmokeTestJob::dispatch($message);

        $this->info('Dispatched QueueSmokeTestJob.');
        $this->line('To process it locally, run: php artisan queue:work');

        return self::SUCCESS;
    }
}


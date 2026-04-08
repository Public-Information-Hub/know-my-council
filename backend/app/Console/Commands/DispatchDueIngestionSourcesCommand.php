<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\RefreshIngestionSourceJob;
use App\Models\IngestionSource;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class DispatchDueIngestionSourcesCommand extends Command
{
    protected $signature = 'kmc:ingestion-source:dispatch-due
        {--limit=50 : Maximum number of sources to dispatch}
        {--dry-run : List sources without dispatching jobs}';

    protected $description = 'Dispatch queued refresh jobs for ingestion sources that are due.';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');
        $now = CarbonImmutable::now();

        $sources = IngestionSource::query()
            ->where('is_active', true)
            ->whereIn('refresh_mode', ['scheduled', 'automatic'])
            ->orderBy('last_checked_at')
            ->orderBy('created_at')
            ->get()
            ->filter(fn (IngestionSource $source): bool => $this->isDue($source, $now))
            ->take($limit);

        if ($sources->isEmpty()) {
            $this->info('No ingestion sources are due.');
            return self::SUCCESS;
        }

        foreach ($sources as $source) {
            $this->line(sprintf(
                '%s %s (%s)',
                $dryRun ? '[dry-run] Would dispatch' : 'Dispatching',
                $source->source_key,
                $source->source_kind,
            ));

            if (!$dryRun) {
                RefreshIngestionSourceJob::dispatch($source->id);
            }
        }

        $this->info(sprintf('Processed %d ingestion source(s).', $sources->count()));

        return self::SUCCESS;
    }

    private function isDue(IngestionSource $source, CarbonImmutable $now): bool
    {
        $lastCheckedAt = $source->last_checked_at;
        if ($lastCheckedAt === null) {
            return true;
        }

        $cadence = strtolower(trim((string) $source->expected_refresh_cadence));
        $threshold = match ($cadence) {
            'hourly' => $now->subHour(),
            'daily' => $now->subDay(),
            'weekly' => $now->subWeek(),
            'monthly' => $now->subMonth(),
            'quarterly' => $now->subMonths(3),
            'yearly', 'annual' => $now->subYear(),
            default => $now->subHour(),
        };

        return $lastCheckedAt->lessThanOrEqualTo($threshold);
    }
}

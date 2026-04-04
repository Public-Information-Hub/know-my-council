<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageSmokeTestCommand extends Command
{
    protected $signature = 'kmc:storage-smoke-test {--disk= : Filesystem disk to test (defaults to FILESYSTEM_DISK)}';

    protected $description = 'Write and delete a small object to verify storage configuration (e.g. MinIO).';

    public function handle(): int
    {
        $disk = (string) ($this->option('disk') ?: config('filesystems.default'));
        $path = 'smoke-tests/'.Str::uuid()->toString().'.txt';
        $contents = 'KnowMyCouncil storage smoke test: '.now()->toIso8601String()."\n";

        $fs = Storage::disk($disk);

        $this->line("Testing disk: {$disk}");

        $fs->put($path, $contents);
        $exists = $fs->exists($path);

        if (! $exists) {
            $this->error('Write succeeded but object does not appear to exist.');
            return self::FAILURE;
        }

        $fs->delete($path);

        $this->info('Storage smoke test passed.');

        return self::SUCCESS;
    }
}


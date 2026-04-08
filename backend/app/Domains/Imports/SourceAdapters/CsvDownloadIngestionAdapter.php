<?php

namespace App\Domains\Imports\SourceAdapters;

use App\Models\IngestionSource;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CsvDownloadIngestionAdapter
{
    public function download(IngestionSource $source): CsvDownloadIngestionAdapterResult
    {
        $sourceUrl = trim((string) $source->source_url);
        if ($sourceUrl === '') {
            throw new \RuntimeException('The source does not have a source_url.');
        }

        $response = Http::retry(2, 250)
            ->timeout(60)
            ->accept('*/*')
            ->get($sourceUrl);

        $response->throw();

        $body = $response->body();
        $sourceKey = Str::slug($source->source_key) ?: 'ingestion-source';
        $tempDir = storage_path("app/tmp/ingestion-sources/{$sourceKey}");
        File::ensureDirectoryExists($tempDir);

        $extension = $this->guessExtension($sourceUrl, $response->header('Content-Type'));
        $fileName = $sourceKey.'-'.now()->format('YmdHis').$extension;
        $localFilePath = $tempDir.'/'.$fileName;
        File::put($localFilePath, $body);

        return new CsvDownloadIngestionAdapterResult(
            localFilePath: $localFilePath,
            sourceUrl: $sourceUrl,
            contentType: $response->header('Content-Type'),
            sha256: hash_file('sha256', $localFilePath) ?: null,
        );
    }

    private function guessExtension(string $sourceUrl, ?string $contentType): string
    {
        $path = (string) parse_url($sourceUrl, PHP_URL_PATH);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (is_string($extension) && $extension !== '') {
            return '.'.ltrim($extension, '.');
        }

        if (is_string($contentType)) {
            $contentType = strtolower(trim(explode(';', $contentType, 2)[0]));
            return match ($contentType) {
                'text/csv', 'application/csv', 'text/plain' => '.csv',
                'application/vnd.ms-excel' => '.csv',
                default => '.csv',
            };
        }

        return '.csv';
    }
}

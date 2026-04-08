<?php

namespace Tests\Unit;

use App\Domains\Imports\SourceAdapters\CsvDownloadIngestionAdapter;
use App\Models\IngestionSource;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CsvDownloadIngestionAdapterTest extends TestCase
{
    public function test_it_downloads_a_csv_source_to_a_temp_file(): void
    {
        Http::fake([
            'https://example.org/spend.csv' => Http::response("Date,Supplier,Amount\n01/04/2026,Example Ltd,123.45\n", 200, [
                'Content-Type' => 'text/csv',
            ]),
        ]);

        $source = new IngestionSource();
        $source->forceFill([
            'source_key' => 'example-council:spend',
            'source_kind' => 'csv',
            'source_url' => 'https://example.org/spend.csv',
        ]);

        $adapter = new CsvDownloadIngestionAdapter();
        $result = $adapter->download($source);

        $this->assertFileExists($result->localFilePath);
        $this->assertStringContainsString('Example Ltd', (string) file_get_contents($result->localFilePath));
        $this->assertSame('https://example.org/spend.csv', $result->sourceUrl);
        $this->assertSame('text/csv', $result->contentType);
        $this->assertNotNull($result->sha256);

        @unlink($result->localFilePath);
    }
}

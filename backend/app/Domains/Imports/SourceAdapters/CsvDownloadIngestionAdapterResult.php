<?php

namespace App\Domains\Imports\SourceAdapters;

class CsvDownloadIngestionAdapterResult
{
    public function __construct(
        public readonly string $localFilePath,
        public readonly string $sourceUrl,
        public readonly ?string $contentType,
        public readonly ?string $sha256,
    ) {
    }
}

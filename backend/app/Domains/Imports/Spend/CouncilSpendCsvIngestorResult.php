<?php

namespace App\Domains\Imports\Spend;

class CouncilSpendCsvIngestorResult
{
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    public function __construct(
        public string $status,
        public string $councilSlug,
        public string $datasetKey,
        public string $importKey,
        public ?string $importRunId,
        public ?string $sourceFileId,
        public int $rowsSeen,
        public int $rowsInserted,
        public int $warningCount,
        public ?string $errorSummary,
    ) {
    }
}


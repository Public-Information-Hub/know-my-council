<?php

declare(strict_types=1);

namespace App\Domains\Councils;

class CouncilRegistryBootstrapperResult
{
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED = 'failed';

    public function __construct(
        public readonly string $status,
        public readonly string $datasetKey,
        public readonly string $importKey,
        public readonly ?string $importRunId,
        public readonly ?string $sourceFileId,
        public readonly int $rowsSeen,
        public readonly int $councilsInserted,
        public readonly int $councilsUpdated,
        public readonly int $versionsInserted,
        public readonly int $warningCount,
        public readonly ?string $errorSummary,
    ) {
    }
}

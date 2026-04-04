<?php

namespace App\Domains\Imports\Spend;

class OrganisationMatchResult
{
    private function __construct(
        public ?string $organisationId,
        public string $mappingConfidence,
    ) {
    }

    public static function none(): self
    {
        return new self(null, 'unknown');
    }

    public static function high(string $organisationId): self
    {
        return new self($organisationId, 'high');
    }

    public static function medium(string $organisationId): self
    {
        return new self($organisationId, 'medium');
    }
}


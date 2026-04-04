<?php

namespace App\Domains\Imports\Spend;

use App\Models\Organisation;
use App\Models\OrganisationAlias;

class OrganisationNameMatcher
{
    /**
     * Phase 1 matching: case-insensitive exact matches against organisations.canonical_name
     * and organisation_aliases.alias.
     *
     * This is intentionally conservative: no fuzzy matching in Phase 1.
     */
    public function match(?string $supplierNameObserved): OrganisationMatchResult
    {
        $supplierNameObserved = $this->clean($supplierNameObserved);
        if ($supplierNameObserved === null) {
            return OrganisationMatchResult::none();
        }

        $needle = mb_strtolower($supplierNameObserved);

        $org = Organisation::query()
            ->whereRaw('lower(canonical_name) = ?', [$needle])
            ->first();
        if ($org !== null) {
            return OrganisationMatchResult::high($org->id);
        }

        $alias = OrganisationAlias::query()
            ->whereRaw('lower(alias) = ?', [$needle])
            ->first();
        if ($alias !== null) {
            return OrganisationMatchResult::medium($alias->organisation_id);
        }

        return OrganisationMatchResult::none();
    }

    private function clean(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;
        if ($value === null || $value === '') {
            return null;
        }
        return $value;
    }
}


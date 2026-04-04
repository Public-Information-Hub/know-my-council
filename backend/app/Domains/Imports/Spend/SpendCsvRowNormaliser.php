<?php

namespace App\Domains\Imports\Spend;

use Carbon\CarbonImmutable;

class SpendCsvRowNormaliser
{
    public function parseDate(?string $value): ?CarbonImmutable
    {
        $value = $this->clean($value);
        if ($value === null) {
            return null;
        }

        // Common council formats include "dd/mm/yyyy" and ISO "yyyy-mm-dd".
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{2,4}$/', $value) === 1) {
            // Day-first is the least risky assumption in the UK context.
            $parts = explode('/', $value);
            if (count($parts) === 3) {
                [$d, $m, $y] = $parts;
                if (strlen($y) === 2) {
                    $y = (int) $y;
                    $y = (string) ($y >= 70 ? 1900 + $y : 2000 + $y);
                }

                return CarbonImmutable::createFromFormat('!d/m/Y', sprintf('%02d/%02d/%04d', (int) $d, (int) $m, (int) $y))
                    ?: null;
            }
        }

        try {
            return CarbonImmutable::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    public function parseAmount(?string $value): ?string
    {
        $value = $this->clean($value);
        if ($value === null) {
            return null;
        }

        // Remove currency symbols and separators while keeping minus and dot.
        $value = str_replace([',', '£', '$', '€'], '', $value);
        $value = trim($value);

        // "(123.45)" style negatives.
        if (preg_match('/^\((.+)\)$/', $value, $m) === 1) {
            $value = '-'.trim($m[1]);
        }

        if (!preg_match('/^-?\d+(\.\d+)?$/', $value)) {
            return null;
        }

        return $value;
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


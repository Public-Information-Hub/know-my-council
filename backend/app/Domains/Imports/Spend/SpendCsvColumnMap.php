<?php

namespace App\Domains\Imports\Spend;

use RuntimeException;

class SpendCsvColumnMap
{
    private function __construct(
        public int $supplierNameIndex,
        public int $amountIndex,
        public int $transactionDateIndex,
        public ?int $descriptionIndex,
    ) {
    }

    /**
     * Map common "spend over £500" CSV headers into canonical fields.
     *
     * This is intentionally lean; contributors can extend the header list as we
     * encounter real council variations.
     *
     * @param array<int, string|null> $headers
     */
    public static function fromHeaders(array $headers): self
    {
        $normalised = [];
        foreach ($headers as $i => $header) {
            $normalised[$i] = self::normaliseHeader($header);
        }

        $supplier = self::firstIndexOf($normalised, [
            'supplier',
            'supplier name',
            'supplier_name',
            'suppliername',
            'payee',
            'payee name',
            'payee_name',
        ]);

        $amount = self::firstIndexOf($normalised, [
            'amount',
            'net amount',
            'net_amount',
            'payment amount',
            'payment_amount',
            'value',
            'amount gbp',
            'amount_gbp',
        ]);

        $date = self::firstIndexOf($normalised, [
            'date',
            'transaction date',
            'transaction_date',
            'payment date',
            'payment_date',
            'invoice date',
            'invoice_date',
        ]);

        $description = self::firstIndexOf($normalised, [
            'description',
            'details',
            'narrative',
            'expense type',
            'expense_type',
        ], required: false);

        if ($supplier === null || $amount === null || $date === null) {
            throw new RuntimeException('CSV is missing required columns. Need supplier, amount, and date.');
        }

        return new self($supplier, $amount, $date, $description);
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, string> $candidates
     */
    private static function firstIndexOf(array $headers, array $candidates, bool $required = true): ?int
    {
        foreach ($headers as $i => $header) {
            foreach ($candidates as $candidate) {
                if ($header === $candidate) {
                    return $i;
                }
            }
        }

        return null;
    }

    private static function normaliseHeader(?string $header): string
    {
        $header = is_string($header) ? trim($header) : '';
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header; // strip UTF-8 BOM
        $header = mb_strtolower($header);

        // Reduce punctuation differences and collapse whitespace.
        $header = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $header) ?? $header;
        $header = preg_replace('/\s+/', ' ', $header) ?? $header;

        return trim($header);
    }
}

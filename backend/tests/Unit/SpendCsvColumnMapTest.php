<?php

namespace Tests\Unit;

use App\Domains\Imports\Spend\SpendCsvColumnMap;
use PHPUnit\Framework\TestCase;

class SpendCsvColumnMapTest extends TestCase
{
    public function test_it_maps_common_headers(): void
    {
        $map = SpendCsvColumnMap::fromHeaders([
            'Payment Date',
            'Supplier',
            'Description',
            'Amount',
        ]);

        $this->assertSame(1, $map->supplierNameIndex);
        $this->assertSame(3, $map->amountIndex);
        $this->assertSame(0, $map->transactionDateIndex);
        $this->assertSame(2, $map->descriptionIndex);
    }

    public function test_it_can_use_header_overrides(): void
    {
        $map = SpendCsvColumnMap::fromHeaders(
            headers: ['Date', 'Vendor', 'Details', 'Value'],
            overrides: [
                'supplier_header' => 'Vendor',
                'amount_header' => 'Value',
                'date_header' => 'Date',
                'description_header' => 'Details',
            ],
        );

        $this->assertSame(1, $map->supplierNameIndex);
        $this->assertSame(3, $map->amountIndex);
        $this->assertSame(0, $map->transactionDateIndex);
        $this->assertSame(2, $map->descriptionIndex);
    }
}

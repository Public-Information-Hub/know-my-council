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
}


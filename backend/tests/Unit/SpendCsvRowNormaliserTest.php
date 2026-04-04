<?php

namespace Tests\Unit;

use App\Domains\Imports\Spend\SpendCsvRowNormaliser;
use PHPUnit\Framework\TestCase;

class SpendCsvRowNormaliserTest extends TestCase
{
    public function test_it_parses_uk_slash_dates_day_first(): void
    {
        $n = new SpendCsvRowNormaliser();
        $date = $n->parseDate('04/01/2026');

        $this->assertNotNull($date);
        $this->assertSame('2026-01-04', $date->toDateString());
    }

    public function test_it_parses_amounts_with_commas_and_currency_symbols(): void
    {
        $n = new SpendCsvRowNormaliser();

        $this->assertSame('1234.50', $n->parseAmount('£1,234.50'));
        $this->assertSame('-99.99', $n->parseAmount('(99.99)'));
        $this->assertNull($n->parseAmount('not-a-number'));
    }
}


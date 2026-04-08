<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class UpsertIngestionSourceCommandTest extends TestCase
{
    public function test_it_registers_the_ingestion_source_upsert_command(): void
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('kmc:ingestion-source:upsert', $commands);
    }
}

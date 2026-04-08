<?php

namespace Tests\Unit;

use App\Models\IngestionSource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class IngestionSourceModelTest extends TestCase
{
    public function test_it_exposes_expected_relations_and_casts(): void
    {
        $source = new IngestionSource();

        $this->assertInstanceOf(BelongsTo::class, $source->dataset());
        $this->assertInstanceOf(BelongsTo::class, $source->import());
        $this->assertInstanceOf(BelongsTo::class, $source->council());
        $this->assertInstanceOf(HasMany::class, $source->sourceFiles());

        $source->forceFill([
            'is_active' => 0,
            'last_checked_at' => '2026-04-08 12:00:00',
        ]);

        $this->assertFalse($source->is_active);
        $this->assertNotNull($source->last_checked_at);
    }
}

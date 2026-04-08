<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'dataset_id',
    'import_id',
    'council_id',
    'source_key',
    'source_kind',
    'source_name',
    'source_url',
    'discovery_url',
    'adapter_key',
    'refresh_mode',
    'expected_refresh_cadence',
    'last_checked_at',
    'last_success_at',
    'last_failure_at',
    'last_error_summary',
    'is_active',
])]
class IngestionSource extends Model
{
    /** @use HasFactory<\Database\Factories\IngestionSourceFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function council(): BelongsTo
    {
        return $this->belongsTo(Council::class);
    }

    public function sourceFiles(): HasMany
    {
        return $this->hasMany(SourceFile::class);
    }

    protected function casts(): array
    {
        return [
            'last_checked_at' => 'datetime',
            'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}

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
    'import_key',
    'import_type',
    'connector_key',
    'parser_version',
    'normalisation_profile',
    'is_active',
])]
class Import extends Model
{
    /** @use HasFactory<\Database\Factories\ImportFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ImportRun::class);
    }

    public function ingestionSources(): HasMany
    {
        return $this->hasMany(IngestionSource::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}

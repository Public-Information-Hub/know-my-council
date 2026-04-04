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
    'version_label',
    'edition_date',
    'published_at',
    'captured_at',
    'geography_basis_type',
    'code_scheme',
    'reporting_period_start',
    'reporting_period_end',
    'mapping_confidence',
    'freshness_state',
    'public_state',
])]
class DatasetVersion extends Model
{
    /** @use HasFactory<\Database\Factories\DatasetVersionFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function importRuns(): HasMany
    {
        return $this->hasMany(ImportRun::class);
    }

    public function sourceFiles(): HasMany
    {
        return $this->hasMany(SourceFile::class);
    }

    protected function casts(): array
    {
        return [
            'edition_date' => 'date',
            'published_at' => 'datetime',
            'captured_at' => 'datetime',
            'reporting_period_start' => 'date',
            'reporting_period_end' => 'date',
        ];
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'import_id',
    'dataset_version_id',
    'started_at',
    'finished_at',
    'run_state',
    'idempotency_key',
    'triggered_by_user_id',
    'parent_import_run_id',
    'rows_seen',
    'rows_inserted',
    'rows_updated',
    'warning_count',
    'error_summary',
])]
class ImportRun extends Model
{
    /** @use HasFactory<\Database\Factories\ImportRunFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function datasetVersion(): BelongsTo
    {
        return $this->belongsTo(DatasetVersion::class);
    }

    public function sourceFiles(): HasMany
    {
        return $this->hasMany(SourceFile::class);
    }

    public function spendRecords(): HasMany
    {
        return $this->hasMany(SpendRecord::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'rows_seen' => 'integer',
            'rows_inserted' => 'integer',
            'rows_updated' => 'integer',
            'warning_count' => 'integer',
        ];
    }
}


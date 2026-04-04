<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'dataset_version_id',
    'import_run_id',
    'council_id',
    'storage_provider',
    'storage_bucket',
    'storage_key',
    'sha256',
    'content_type',
    'byte_size',
    'capture_method',
    'source_url',
    'published_at',
    'captured_at',
    'is_raw_unmodified',
    'visibility',
])]
class SourceFile extends Model
{
    /** @use HasFactory<\Database\Factories\SourceFileFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function datasetVersion(): BelongsTo
    {
        return $this->belongsTo(DatasetVersion::class);
    }

    public function importRun(): BelongsTo
    {
        return $this->belongsTo(ImportRun::class);
    }

    public function council(): BelongsTo
    {
        return $this->belongsTo(Council::class);
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'captured_at' => 'datetime',
            'byte_size' => 'integer',
            'is_raw_unmodified' => 'boolean',
        ];
    }
}


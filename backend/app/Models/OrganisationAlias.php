<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'organisation_id',
    'alias',
    'alias_type',
    'valid_from',
    'valid_to',
    'mapping_confidence',
    'dataset_version_id',
    'source_file_id',
])]
class OrganisationAlias extends Model
{
    /** @use HasFactory<\Database\Factories\OrganisationAliasFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function datasetVersion(): BelongsTo
    {
        return $this->belongsTo(DatasetVersion::class);
    }

    public function sourceFile(): BelongsTo
    {
        return $this->belongsTo(SourceFile::class);
    }

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }
}


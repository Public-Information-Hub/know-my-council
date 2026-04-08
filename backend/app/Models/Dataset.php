<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'dataset_key',
    'publisher_name',
    'publisher_kind',
    'dataset_family',
    'jurisdiction_scope',
    'default_council_id',
])]
class Dataset extends Model
{
    /** @use HasFactory<\Database\Factories\DatasetFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function defaultCouncil(): BelongsTo
    {
        return $this->belongsTo(Council::class, 'default_council_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DatasetVersion::class);
    }

    public function imports(): HasMany
    {
        return $this->hasMany(Import::class);
    }

    public function ingestionSources(): HasMany
    {
        return $this->hasMany(IngestionSource::class);
    }
}

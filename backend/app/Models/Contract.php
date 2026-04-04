<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'council_id',
    'council_version_id',
    'dataset_version_id',
    'import_run_id',
    'title',
    'description_observed',
    'contract_reference',
    'award_date',
    'start_date',
    'end_date',
    'total_value_amount',
    'currency',
    'public_state',
])]
class Contract extends Model
{
    /** @use HasFactory<\Database\Factories\ContractFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function council(): BelongsTo
    {
        return $this->belongsTo(Council::class);
    }

    public function councilVersion(): BelongsTo
    {
        return $this->belongsTo(CouncilVersion::class);
    }

    public function datasetVersion(): BelongsTo
    {
        return $this->belongsTo(DatasetVersion::class);
    }

    public function importRun(): BelongsTo
    {
        return $this->belongsTo(ImportRun::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(ContractSupplier::class);
    }

    protected function casts(): array
    {
        return [
            'award_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'total_value_amount' => 'decimal:2',
        ];
    }
}


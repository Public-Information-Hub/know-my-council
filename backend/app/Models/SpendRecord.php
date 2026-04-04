<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'council_id',
    'council_version_id',
    'dataset_version_id',
    'import_run_id',
    'organisation_id',
    'supplier_name_observed',
    'description_observed',
    'transaction_date',
    'amount',
    'currency',
    'mapping_confidence',
    'public_state',
])]
class SpendRecord extends Model
{
    /** @use HasFactory<\Database\Factories\SpendRecordFactory> */
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

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }
}


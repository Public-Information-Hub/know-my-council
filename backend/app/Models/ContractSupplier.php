<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'contract_id',
    'organisation_id',
    'supplier_name_observed',
    'role_type',
    'share_fraction',
    'mapping_confidence',
])]
class ContractSupplier extends Model
{
    /** @use HasFactory<\Database\Factories\ContractSupplierFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    protected function casts(): array
    {
        return [
            'share_fraction' => 'decimal:8',
        ];
    }
}


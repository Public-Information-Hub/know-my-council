<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'canonical_name',
    'organisation_kind',
    'jurisdiction_code',
])]
class Organisation extends Model
{
    /** @use HasFactory<\Database\Factories\OrganisationFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function aliases(): HasMany
    {
        return $this->hasMany(OrganisationAlias::class);
    }

    public function identifiers(): HasMany
    {
        return $this->hasMany(OrganisationIdentifier::class);
    }

    public function spendRecords(): HasMany
    {
        return $this->hasMany(SpendRecord::class);
    }

    public function contractSuppliers(): HasMany
    {
        return $this->hasMany(ContractSupplier::class);
    }
}


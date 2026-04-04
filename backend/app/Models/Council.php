<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'canonical_slug',
    'jurisdiction_code',
    'country_code',
    'authority_kind',
])]
class Council extends Model
{
    /** @use HasFactory<\Database\Factories\CouncilFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function versions(): HasMany
    {
        return $this->hasMany(CouncilVersion::class);
    }

    public function spendRecords(): HasMany
    {
        return $this->hasMany(SpendRecord::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'council_id',
    'display_name',
    'short_name',
    'status',
    'valid_from',
    'valid_to',
    'public_state',
])]
class CouncilVersion extends Model
{
    /** @use HasFactory<\Database\Factories\CouncilVersionFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function council(): BelongsTo
    {
        return $this->belongsTo(Council::class);
    }

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }
}


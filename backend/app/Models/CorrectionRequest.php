<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'topic',
    'name',
    'email',
    'council_name',
    'council_slug',
    'page_url',
    'source_url',
    'details',
    'status',
    'admin_notes',
    'reviewed_at',
])]
class CorrectionRequest extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory;
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }
}

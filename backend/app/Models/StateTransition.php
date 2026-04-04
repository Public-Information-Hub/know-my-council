<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'target_type',
    'target_id',
    'state_field',
    'from_state',
    'to_state',
    'reason_code',
    'reason_note',
    'actor_type',
    'acted_by_user_id',
    'actor_import_run_id',
    'actor_job_key',
    'workflow_type',
    'workflow_id',
    'changed_at',
])]
class StateTransition extends Model
{
    /** @use HasFactory<\Database\Factories\StateTransitionFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    public function actorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by_user_id');
    }

    public function actorImportRun(): BelongsTo
    {
        return $this->belongsTo(ImportRun::class, 'actor_import_run_id');
    }

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }
}

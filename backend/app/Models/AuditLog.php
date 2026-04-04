<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'actor_type',
    'actor_user_id',
    'actor_system_key',
    'actor_import_run_id',
    'actor_job_key',
    'actor_api_key_id',
    'actor_ai_process_key',
    'action_family',
    'action_type',
    'target_type',
    'target_id',
    'before_json',
    'after_json',
    'context_json',
    'request_id',
    'correlation_id',
    'workflow_type',
    'workflow_id',
])]
class AuditLog extends Model
{
    /** @use HasFactory<\Database\Factories\AuditLogFactory> */
    use HasFactory;
    use HasUuids;

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    public function actorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function actorImportRun(): BelongsTo
    {
        return $this->belongsTo(ImportRun::class, 'actor_import_run_id');
    }

    protected function casts(): array
    {
        return [
            'before_json' => 'array',
            'after_json' => 'array',
            'context_json' => 'array',
            'created_at' => 'datetime',
        ];
    }
}


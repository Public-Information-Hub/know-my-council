<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UserAuthChallenge extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'auth_challenges';

    protected $fillable = [
        'user_id',
        'purpose',
        'challenge_type',
        'delivery_mode',
        'code_hash',
        'magic_token_hash',
        'code_sent_to',
        'expires_at',
        'last_sent_at',
        'consumed_at',
        'resend_count',
        'ip_address',
        'user_agent',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'consumed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at instanceof Carbon && $this->expires_at->isPast();
    }
}

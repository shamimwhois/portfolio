<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'guest_email',
        'guest_phone',
        'title',
        'status',
        'source',
        'last_message_at',
    ];

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->latest('id');
    }

    public function oldestMessages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->oldest('id');
    }

    public function scopeOwnedBy(Builder $query, ?int $userId, ?string $sessionId): Builder
    {
        if ($userId !== null) {
            return $query->where('user_id', $userId);
        }

        return $query->where('user_id', null)->where('session_id', $sessionId);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}

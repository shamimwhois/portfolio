<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FeedLike extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * Supported reaction types (Facebook-style).
     */
    public const TYPES = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];

    protected $fillable = [
        'user_id', 'likeable_type', 'likeable_id', 'reaction_type',
    ];

    protected function casts(): array
    {
        return [
            'reaction_type' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }
}

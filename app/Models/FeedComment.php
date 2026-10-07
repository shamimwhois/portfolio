<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FeedComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'feed_post_id', 'parent_id', 'body',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(FeedPost::class, 'feed_post_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(FeedLike::class, 'likeable');
    }

    public function reactionBy(?int $userId): ?string
    {
        if (! $userId) {
            return null;
        }

        return $this->reactions->firstWhere('user_id', $userId)?->reaction_type;
    }

    /**
     * Reaction type => count, built from the loaded relation.
     *
     * @return array<string, int>
     */
    public function reactionTotals(): array
    {
        return $this->reactions->countBy('reaction_type')->toArray();
    }
}

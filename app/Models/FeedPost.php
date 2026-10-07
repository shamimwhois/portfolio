<?php

namespace App\Models;

use App\Models\Concerns\HasPublishStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FeedPost extends Model
{
    use HasFactory;
    use HasPublishStatus;

    protected $fillable = [
        'user_id', 'body', 'publish_status',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(FeedComment::class)->whereNull('parent_id')->latest();
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(FeedComment::class)->oldest();
    }

    public function media(): HasMany
    {
        return $this->hasMany(FeedMedia::class)->orderBy('position');
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

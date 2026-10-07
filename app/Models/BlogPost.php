<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    use HasFactory;

    public const CATEGORIES = ['Tutorials', 'News', 'Guides', 'Updates', 'Tips'];

    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt', 'body', 'tags', 'category', 'views',
        'cover_image', 'featured', 'published', 'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'category' => 'string',
        'views' => 'integer',
        'featured' => 'boolean',
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function categoryLabel(): string
    {
        return $this->category ?: 'Uncategorized';
    }

    /**
     * Approximate read time in minutes based on ~200 words per minute.
     */
    public function readTime(): int
    {
        $words = str_word_count(strip_tags($this->body));

        return max(1, (int) ceil($words / 200));
    }

    public function formattedViews(): string
    {
        return number_format($this->views);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->whereNull('parent_id')->oldest();
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->oldest();
    }

    public function excerptOrFallback(int $length = 160): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        return Str::limit(strip_tags($this->body), $length);
    }
}

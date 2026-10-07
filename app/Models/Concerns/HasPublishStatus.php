<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasPublishStatus
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_UNPUBLISHED = 'unpublished';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_UNPUBLISHED,
    ];

    public function isPublished(): bool
    {
        return $this->publish_status === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->publish_status === self::STATUS_DRAFT;
    }

    public function isUnpublished(): bool
    {
        return $this->publish_status === self::STATUS_UNPUBLISHED;
    }

    public function statusLabel(): string
    {
        return match ($this->publish_status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_UNPUBLISHED => 'Unpublished',
            default => 'Published',
        };
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('publish_status', self::STATUS_PUBLISHED);
    }
}

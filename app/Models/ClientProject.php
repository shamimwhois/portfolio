<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ClientProject extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_REVIEW = 'review';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_REVIEW => 'In Review',
        self::STATUS_COMPLETED => 'Completed',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    public const SOURCE_ADMIN = 'admin';

    public const SOURCE_CLIENT = 'client';

    public const OFFER_PENDING = 'pending';

    public const OFFER_ACCEPTED = 'accepted';

    public const OFFER_DECLINED = 'declined';

    public const OFFER_STATUSES = [
        self::OFFER_PENDING => 'Pending',
        self::OFFER_ACCEPTED => 'Accepted',
        self::OFFER_DECLINED => 'Declined',
    ];

    public const TIMELINE_PREFERENCES = [
        'asap' => 'As soon as possible',
        'within_2_weeks' => 'Within 2 weeks',
        'within_month' => 'Within a month',
        'flexible' => 'Flexible / open to discuss',
    ];

    protected $fillable = [
        'user_id', 'invoice_id', 'title', 'description', 'status',
        'source', 'submitted_at', 'tech_package_id', 'service_tier_id',
        'start_date', 'deadline', 'remind_at',
        'requirements', 'agreement', 'policy', 'cancel_policy', 'return_policy',
        'example_url', 'demo_url', 'design_notes', 'color_palette',
        'budget_from', 'budget_to', 'timeline_preference', 'preferred_deadline',
        'offer_price', 'offer_note', 'offer_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'preferred_deadline' => 'date',
        'submitted_at' => 'datetime',
        'remind_at' => 'datetime',
        'color_palette' => 'array',
        'budget_from' => 'decimal:2',
        'budget_to' => 'decimal:2',
        'offer_price' => 'decimal:2',
    ];

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class)->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ProjectFile::class)->where('kind', ProjectFile::KIND_ATTACHMENT)->latest();
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(ProjectFile::class)->where('kind', ProjectFile::KIND_DELIVERABLE)->latest();
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProjectMedia::class)->orderBy('position');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectMedia::class)->where('type', ProjectMedia::TYPE_IMAGE)->orderBy('position');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ProjectMedia::class)->where('type', ProjectMedia::TYPE_VIDEO)->orderBy('position');
    }

    protected static function booted(): void
    {
        // Remove uploaded files from disk when the project is deleted.
        static::deleting(function (ClientProject $project) {
            $paths = $project->media()->pluck('path')->merge($project->files()->pluck('path'));

            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
            }
        });
    }

    public function isClientSubmitted(): bool
    {
        return $this->source === self::SOURCE_CLIENT;
    }

    public function isEditableByCustomer(): bool
    {
        return $this->isClientSubmitted() && $this->status === self::STATUS_PENDING;
    }

    public function hasOffer(): bool
    {
        return $this->offer_price !== null;
    }

    public function offerPending(): bool
    {
        return $this->offer_status === self::OFFER_PENDING && $this->hasOffer();
    }

    public function offerAccepted(): bool
    {
        return $this->offer_status === self::OFFER_ACCEPTED;
    }

    public function offerDeclined(): bool
    {
        return $this->offer_status === self::OFFER_DECLINED;
    }

    public function timelinePreferenceLabel(): ?string
    {
        return self::TIMELINE_PREFERENCES[$this->timeline_preference] ?? $this->timeline_preference;
    }

    public function budgetLabel(): ?string
    {
        if ($this->budget_from === null && $this->budget_to === null) {
            return null;
        }

        $from = $this->budget_from !== null ? '$'.number_format((float) $this->budget_from) : null;
        $to = $this->budget_to !== null ? '$'.number_format((float) $this->budget_to) : null;

        if ($from && $to) {
            return $from.' – '.$to;
        }

        return $from ?: $to;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TechPackage::class, 'tech_package_id');
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(ServiceTier::class, 'service_tier_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class)->orderBy('sort_order');
    }

    public function completedTasks(): HasMany
    {
        return $this->tasks()->where('is_complete', true);
    }

    public function features(): HasMany
    {
        return $this->hasMany(ProjectFeature::class)->orderBy('sort_order');
    }

    public function completedFeatures(): HasMany
    {
        return $this->features()->where('is_complete', true);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ProjectUpdate::class)->latest();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function progress(): int
    {
        // Prefer withCount aggregates (tasks_count, features_count,
        // completed_tasks_count, completed_features_count) when the query
        // loaded them, falling back to the loaded collections so list views
        // never trigger extra per-project queries.
        if (isset($this->tasks_count, $this->features_count)) {
            $total = $this->tasks_count + $this->features_count;
            $done = ($this->completed_tasks_count ?? 0) + ($this->completed_features_count ?? 0);
        } else {
            $total = ($this->relationLoaded('tasks') ? $this->tasks->count() : $this->tasks()->count())
                + ($this->relationLoaded('features') ? $this->features->count() : $this->features()->count());
            $done = ($this->relationLoaded('features') ? $this->features->filter(fn ($f) => $f->is_complete)->count() : $this->completedFeatures()->count())
                + ($this->relationLoaded('tasks') ? $this->tasks->filter(fn ($t) => $t->is_complete)->count() : $this->completedTasks()->count());
        }

        if ($total === 0) {
            return $this->status === self::STATUS_COMPLETED ? 100 : 0;
        }

        return (int) round(($done / $total) * 100);
    }

    public function daysRemaining(): ?int
    {
        return $this->deadline ? now()->startOfDay()->diffInDays($this->deadline, false) : null;
    }

    public function isOverdue(): bool
    {
        return $this->deadline && $this->deadline->isPast() && ! in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true);
    }

    public function isActive(): bool
    {
        return ! in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true);
    }

    public function reminderDue(): bool
    {
        return $this->remind_at && $this->remind_at->isPast() && $this->isActive();
    }
}

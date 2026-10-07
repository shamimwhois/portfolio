<?php

namespace App\Models;

use App\Models\Concerns\HasPublishStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTier extends Model
{
    use HasFactory;
    use HasPublishStatus;

    protected $fillable = [
        'service_category_id', 'name', 'price', 'billing', 'features', 'popular', 'cta', 'sort_order', 'publish_status',
    ];

    protected $casts = [
        'features' => 'array',
        'popular' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function billingLabel(): string
    {
        return $this->billing === 'month' ? 'per month' : 'one-time';
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\HasPublishStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use HasFactory;
    use HasPublishStatus;

    protected $fillable = ['name', 'slug', 'sort_order', 'publish_status'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function tiers(): HasMany
    {
        return $this->hasMany(ServiceTier::class)->orderBy('sort_order');
    }
}

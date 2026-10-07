<?php

namespace App\Models;

use App\Models\Concerns\HasPublishStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupPackage extends Model
{
    use HasFactory;
    use HasPublishStatus;

    public const KIND_COMBO = 'combo';

    public const KIND_TOOL = 'tool';

    public const KINDS = [self::KIND_COMBO, self::KIND_TOOL];

    public const BILLINGS = ['month', 'one-time'];

    protected $fillable = [
        'kind', 'title', 'icon', 'description', 'features',
        'price', 'billing', 'publish_status', 'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function isCombo(): bool
    {
        return $this->kind === self::KIND_COMBO;
    }

    public function isTool(): bool
    {
        return $this->kind === self::KIND_TOOL;
    }

    public function kindLabel(): string
    {
        return $this->isCombo() ? 'Combo Package' : 'Single Tool';
    }

    public function isSellable(): bool
    {
        return $this->price !== null;
    }

    public function billingLabel(): string
    {
        return $this->billing === 'month' ? 'per month' : 'one-time';
    }

    public function displayPrice(): string
    {
        return '৳'.number_format((float) $this->price, 0);
    }

    public function icon(): string
    {
        return $this->icon ?: strtoupper(substr($this->title, 0, 2));
    }
}

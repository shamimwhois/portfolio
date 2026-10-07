<?php

namespace App\Models;

use App\Models\Concerns\HasPublishStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechPackage extends Model
{
    use HasFactory;
    use HasPublishStatus;

    public const CATEGORIES = ['javascript', 'php', 'python', 'managers'];

    public const VALIDITIES = ['14 Days', '30 Days', '2 Months', '6 Months', '1 Year'];

    protected $fillable = [
        'category', 'icon', 'title', 'version', 'type', 'description',
        'price', 'billing', 'validity', 'command', 'features', 'install_commands', 'repo_url', 'docs_url', 'sort_order', 'publish_status',
    ];

    protected $casts = [
        'features' => 'array',
        'install_commands' => 'array',
        'price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public const BILLINGS = ['one-time', 'month'];

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'javascript' => 'JavaScript',
            'php' => 'PHP',
            'python' => 'Python',
            'managers' => 'Package Managers',
            default => ucfirst($this->category),
        };
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
        return '$'.number_format((float) $this->price, 2);
    }
}

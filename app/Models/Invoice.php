<?php

namespace App\Models;

use App\Support\Payments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'service_tier_id', 'tech_package_id', 'group_package_id', 'invoice_no', 'amount', 'currency', 'status', 'payment_method', 'gateway', 'gateway_reference', 'paid_at', 'purchased_at', 'notes', 'terms_accepted',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'purchased_at' => 'datetime',
        'paid_at' => 'datetime',
        'terms_accepted' => 'boolean',
    ];

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clientProject(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ClientProject::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(ServiceTier::class, 'service_tier_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(TechPackage::class, 'tech_package_id');
    }

    public function groupPackage(): BelongsTo
    {
        return $this->belongsTo(GroupPackage::class, 'group_package_id');
    }

    public function itemLabel(): string
    {
        if ($this->tier) {
            return trim(($this->tier->category?->name ?? '').' // '.$this->tier->name, ' /');
        }

        if ($this->package) {
            return $this->package->categoryLabel().' // '.$this->package->title;
        }

        if ($this->groupPackage) {
            return 'Group Buy // '.$this->groupPackage->title;
        }

        return 'Custom order';
    }

    public function paymentMethodInfo(): ?array
    {
        return $this->payment_method ? Payments::method($this->payment_method) : null;
    }

    public function paymentMethodLabel(): string
    {
        return $this->paymentMethodInfo()['label'] ?? ($this->payment_method ? ucfirst($this->payment_method) : 'Unset');
    }

    public function paymentMethodAccount(): ?string
    {
        return $this->paymentMethodInfo()['account'] ?? null;
    }
}

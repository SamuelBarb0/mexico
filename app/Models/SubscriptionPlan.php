<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'currency',
        'stripe_price_id_monthly',
        'stripe_price_id_yearly',
        'stripe_product_id',
        'has_trial',
        'trial_days',
        'max_users',
        'max_contacts',
        'max_campaigns',
        'max_waba_accounts',
        'max_messages_per_month',
        'max_storage_mb',
        'features',
        'restrictions',
        'is_active',
        'is_visible',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'has_trial' => 'boolean',
        'trial_days' => 'integer',
        'max_users' => 'integer',
        'max_contacts' => 'integer',
        'max_campaigns' => 'integer',
        'max_waba_accounts' => 'integer',
        'max_messages_per_month' => 'integer',
        'max_storage_mb' => 'integer',
        'features' => 'array',
        'restrictions' => 'array',
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    // Helper Methods
    public function isFree(): bool
    {
        return $this->price_monthly == 0 && $this->price_yearly == 0;
    }

    public function hasUnlimitedTrial(): bool
    {
        return $this->has_trial && $this->trial_days == -1;
    }

    public function getMonthlyPrice(): float
    {
        return (float) $this->price_monthly;
    }

    public function getYearlyPrice(): float
    {
        return (float) $this->price_yearly;
    }

    public function getYearlyMonthlyCost(): float
    {
        return $this->price_yearly > 0 ? $this->price_yearly / 12 : 0;
    }

    public function getYearlySavings(): float
    {
        $monthlyTotal = $this->price_monthly * 12;
        return $monthlyTotal - $this->price_yearly;
    }

    public function getYearlySavingsPercentage(): int
    {
        $monthlyTotal = $this->price_monthly * 12;
        if ($monthlyTotal == 0) {
            return 0;
        }
        return (int) round((($monthlyTotal - $this->price_yearly) / $monthlyTotal) * 100);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}

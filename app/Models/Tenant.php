<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'stripe_customer_id',
        'billing_email',
        'billing_name',
        'billing_address',
        'tax_id',
        'domain',
        'database',
        'logo',
        'status',
        'settings',
        'trial_ends_at',
        'subscription_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'billing_address' => 'array',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function limits(): HasOne
    {
        return $this->hasOne(TenantLimit::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function wabaAccounts(): HasMany
    {
        return $this->hasMany(WabaAccount::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Subscription Methods
    public function currentSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->whereIn('status', ['trial', 'active'])
            ->latest()
            ->first();
    }

    public function hasActiveSubscription(): bool
    {
        $subscription = $this->currentSubscription();
        return $subscription && $subscription->isValid();
    }

    public function isOnTrial(): bool
    {
        $subscription = $this->currentSubscription();
        return $subscription && $subscription->isOnTrial();
    }

    public function daysRemainingOnTrial(): int
    {
        $subscription = $this->currentSubscription();
        return $subscription ? $subscription->trialDaysRemaining() : 0;
    }

    public function canUseFeature(string $feature): bool
    {
        $subscription = $this->currentSubscription();

        if (!$subscription || !$subscription->isValid()) {
            return false;
        }

        $plan = $subscription->plan;
        $features = $plan->features ?? [];

        return in_array($feature, $features);
    }

    public function hasReachedLimit(string $resource): bool
    {
        $subscription = $this->currentSubscription();

        if (!$subscription) {
            return true; // No subscription = no access
        }

        return $subscription->hasReachedLimit($resource);
    }

    public function getRemainingLimit(string $resource): int
    {
        $subscription = $this->currentSubscription();

        if (!$subscription) {
            return 0;
        }

        return $subscription->getRemainingLimit($resource);
    }

    // Status Methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    // Billing Methods
    public function defaultPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethods()->where('is_default', true)->first();
    }

    public function hasPaymentMethod(): bool
    {
        return $this->paymentMethods()->active()->exists();
    }
}

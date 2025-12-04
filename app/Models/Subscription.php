<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'subscription_plan_id',
        'billing_cycle',
        'stripe_subscription_id',
        'stripe_customer_id',
        'stripe_status',
        'status',
        'trial_starts_at',
        'trial_ends_at',
        'starts_at',
        'current_period_start',
        'current_period_end',
        'ends_at',
        'canceled_at',
        'paused_at',
        'auto_renew',
        'metadata',
    ];

    protected $casts = [
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'paused_at' => 'datetime',
        'auto_renew' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Status Checks
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'trial' => 'En Prueba',
            'active' => 'Activa',
            'canceled' => 'Cancelada',
            'past_due' => 'Vencida',
            'unpaid' => 'Impaga',
            'incomplete' => 'Incompleta',
            'incomplete_expired' => 'Expirada',
            'paused' => 'Pausada',
            default => ucfirst($this->status),
        };
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnTrial(): bool
    {
        if ($this->status !== 'trial') {
            return false;
        }

        if (!$this->trial_ends_at) {
            return true; // Unlimited trial
        }

        return $this->trial_ends_at->isFuture();
    }

    public function hasExpiredTrial(): bool
    {
        if (!$this->trial_ends_at) {
            return false;
        }

        return $this->trial_ends_at->isPast();
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'unpaid';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isValid(): bool
    {
        return in_array($this->status, ['trial', 'active']);
    }

    // Trial Methods
    public function trialDaysRemaining(): int
    {
        if (!$this->trial_ends_at) {
            return -1; // Unlimited
        }

        if ($this->trial_ends_at->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($this->trial_ends_at, false);
    }

    public function trialEndsIn(): string
    {
        $days = $this->trialDaysRemaining();

        if ($days === -1) {
            return 'Trial ilimitado';
        }

        if ($days === 0) {
            return 'Trial expirado';
        }

        if ($days === 1) {
            return '1 día';
        }

        return "{$days} días";
    }

    // Billing Methods
    public function isMonthly(): bool
    {
        return $this->billing_cycle === 'monthly';
    }

    public function isYearly(): bool
    {
        return $this->billing_cycle === 'yearly';
    }

    public function getCurrentPrice(): float
    {
        if ($this->isMonthly()) {
            return (float) $this->plan->price_monthly;
        }

        return (float) $this->plan->price_yearly;
    }

    public function getNextBillingDate(): ?Carbon
    {
        return $this->current_period_end;
    }

    public function daysUntilRenewal(): int
    {
        if (!$this->current_period_end) {
            return 0;
        }

        return (int) now()->diffInDays($this->current_period_end, false);
    }

    // Cancellation Methods
    public function canCancel(): bool
    {
        return $this->isValid() && !$this->isCanceled();
    }

    public function canResume(): bool
    {
        return $this->isCanceled() && $this->ends_at && $this->ends_at->isFuture();
    }

    public function cancel(): bool
    {
        if (!$this->canCancel()) {
            return false;
        }

        $this->update([
            'status' => 'canceled',
            'canceled_at' => now(),
            'ends_at' => $this->current_period_end ?? now(),
        ]);

        return true;
    }

    public function resume(): bool
    {
        if (!$this->canResume()) {
            return false;
        }

        $this->update([
            'status' => 'active',
            'canceled_at' => null,
            'ends_at' => null,
        ]);

        return true;
    }

    // Limits Checking
    public function hasReachedLimit(string $resource): bool
    {
        $limits = $this->tenant->limits;
        if (!$limits) {
            return false;
        }

        $mapping = [
            'users' => ['current' => 'current_users', 'max' => $this->plan->max_users],
            'contacts' => ['current' => 'current_contacts', 'max' => $this->plan->max_contacts],
            'campaigns' => ['current' => 'current_campaigns', 'max' => $this->plan->max_campaigns],
            'waba_accounts' => ['current' => 'current_waba_accounts', 'max' => $this->plan->max_waba_accounts],
            'messages' => ['current' => 'current_messages_this_month', 'max' => $this->plan->max_messages_per_month],
        ];

        if (!isset($mapping[$resource])) {
            return false;
        }

        $current = $limits->{$mapping[$resource]['current']} ?? 0;
        $max = $mapping[$resource]['max'];

        return $current >= $max;
    }

    public function getRemainingLimit(string $resource): int
    {
        $limits = $this->tenant->limits;
        if (!$limits) {
            return 0;
        }

        $mapping = [
            'users' => ['current' => 'current_users', 'max' => $this->plan->max_users],
            'contacts' => ['current' => 'current_contacts', 'max' => $this->plan->max_contacts],
            'campaigns' => ['current' => 'current_campaigns', 'max' => $this->plan->max_campaigns],
            'waba_accounts' => ['current' => 'current_waba_accounts', 'max' => $this->plan->max_waba_accounts],
            'messages' => ['current' => 'current_messages_this_month', 'max' => $this->plan->max_messages_per_month],
        ];

        if (!isset($mapping[$resource])) {
            return 0;
        }

        $current = $limits->{$mapping[$resource]['current']} ?? 0;
        $max = $mapping[$resource]['max'];

        return max(0, $max - $current);
    }

    public function getLimitPercentage(string $resource): int
    {
        $limits = $this->tenant->limits;
        if (!$limits) {
            return 0;
        }

        $mapping = [
            'users' => ['current' => 'current_users', 'max' => $this->plan->max_users],
            'contacts' => ['current' => 'current_contacts', 'max' => $this->plan->max_contacts],
            'campaigns' => ['current' => 'current_campaigns', 'max' => $this->plan->max_campaigns],
            'waba_accounts' => ['current' => 'current_waba_accounts', 'max' => $this->plan->max_waba_accounts],
            'messages' => ['current' => 'current_messages_this_month', 'max' => $this->plan->max_messages_per_month],
        ];

        if (!isset($mapping[$resource])) {
            return 0;
        }

        $current = $limits->{$mapping[$resource]['current']} ?? 0;
        $max = $mapping[$resource]['max'];

        if ($max == 0) {
            return 0;
        }

        return (int) min(100, ($current / $max) * 100);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOnTrial($query)
    {
        return $query->where('status', 'trial');
    }

    public function scopeValid($query)
    {
        return $query->whereIn('status', ['trial', 'active']);
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    public function scopePastDue($query)
    {
        return $query->where('status', 'past_due');
    }
}

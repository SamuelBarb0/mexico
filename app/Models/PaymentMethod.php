<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'stripe_payment_method_id',
        'stripe_customer_id',
        'type',
        'brand',
        'last4',
        'exp_month',
        'exp_year',
        'country',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'exp_month' => 'integer',
        'exp_year' => 'integer',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Helper Methods
    public function isExpired(): bool
    {
        if (!$this->exp_month || !$this->exp_year) {
            return false;
        }

        $expirationDate = now()->setYear($this->exp_year)->setMonth($this->exp_month)->endOfMonth();

        return $expirationDate->isPast();
    }

    public function isExpiringSoon(): bool
    {
        if (!$this->exp_month || !$this->exp_year) {
            return false;
        }

        $expirationDate = now()->setYear($this->exp_year)->setMonth($this->exp_month)->endOfMonth();

        return $expirationDate->isPast() === false && $expirationDate->diffInDays() <= 30;
    }

    public function getDisplayName(): string
    {
        $brand = ucfirst($this->brand ?? $this->type);
        return "{$brand} •••• {$this->last4}";
    }

    public function getExpirationDisplay(): string
    {
        return str_pad($this->exp_month, 2, '0', STR_PAD_LEFT) . '/' . substr($this->exp_year, -2);
    }

    public function getBrandIcon(): string
    {
        $icons = [
            'visa' => 'cc-visa',
            'mastercard' => 'cc-mastercard',
            'amex' => 'cc-amex',
            'discover' => 'cc-discover',
            'diners' => 'cc-diners-club',
            'jcb' => 'cc-jcb',
        ];

        return $icons[strtolower($this->brand ?? '')] ?? 'credit-card';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeNotExpired($query)
    {
        $currentMonth = now()->format('Y-m');

        return $query->where(function ($q) use ($currentMonth) {
            $q->whereRaw("CONCAT(exp_year, '-', LPAD(exp_month, 2, '0')) >= ?", [$currentMonth])
              ->orWhereNull('exp_month')
              ->orWhereNull('exp_year');
        });
    }
}

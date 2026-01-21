<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ScopedBy(TenantScope::class)]
class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'stripe_invoice_id',
        'stripe_customer_id',
        'invoice_number',
        'subtotal',
        'tax',
        'total',
        'currency',
        'status',
        'invoice_date',
        'due_date',
        'paid_at',
        'stripe_payment_intent_id',
        'payment_method',
        'stripe_hosted_invoice_url',
        'stripe_invoice_pdf',
        'line_items',
        'metadata',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'invoice_date' => 'datetime',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'line_items' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    // Status Checks
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    public function isUncollectible(): bool
    {
        return $this->status === 'uncollectible';
    }

    public function isOverdue(): bool
    {
        if ($this->isPaid() || !$this->due_date) {
            return false;
        }

        return $this->due_date->isPast();
    }

    // Helper Methods
    public function getFormattedTotal(): string
    {
        return $this->formatMoney($this->total);
    }

    public function getFormattedSubtotal(): string
    {
        return $this->formatMoney($this->subtotal);
    }

    public function getFormattedTax(): string
    {
        return $this->formatMoney($this->tax);
    }

    protected function formatMoney(float $amount): string
    {
        $symbols = [
            'usd' => '$',
            'mxn' => '$',
            'eur' => '€',
        ];

        $symbol = $symbols[strtolower($this->currency)] ?? '$';

        return $symbol . number_format($amount, 2);
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'paid' => 'bg-green-100 text-green-800',
            'open' => 'bg-blue-100 text-blue-800',
            'void' => 'bg-gray-100 text-gray-800',
            'uncollectible' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'paid' => 'Pagada',
            'open' => 'Pendiente',
            'void' => 'Anulada',
            'uncollectible' => 'Incobrable',
            'draft' => 'Borrador',
            default => ucfirst($this->status),
        };
    }

    public function canDownload(): bool
    {
        return $this->isPaid() && !empty($this->stripe_invoice_pdf);
    }

    public function canView(): bool
    {
        return !empty($this->stripe_hosted_invoice_url);
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'open')
                     ->where('due_date', '<', now());
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('invoice_date', 'desc');
    }
}

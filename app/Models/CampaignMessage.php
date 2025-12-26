<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignMessage extends Model
{
    protected $fillable = [
        'campaign_id',
        'contact_id',
        'whatsapp_message_id',
        'phone_number',
        'message_body',
        'status',
        'error_message',
        'error_code',
        'queued_at',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
        'template_variables',
        'retry_count',
    ];

    protected $casts = [
        'template_variables' => 'array',
        'retry_count' => 'integer',
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    // Relationships
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeQueued($query)
    {
        return $query->where('status', 'QUEUED');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'SENT');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'DELIVERED');
    }

    public function scopeRead($query)
    {
        return $query->where('status', 'READ');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'FAILED');
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isQueued(): bool
    {
        return $this->status === 'QUEUED';
    }

    public function isSent(): bool
    {
        return $this->status === 'SENT';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'DELIVERED';
    }

    public function isRead(): bool
    {
        return $this->status === 'READ';
    }

    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    public function markAsQueued(): void
    {
        $this->update([
            'status' => 'QUEUED',
            'queued_at' => now(),
        ]);
    }

    public function markAsSent(string $whatsappMessageId): void
    {
        $this->update([
            'status' => 'SENT',
            'whatsapp_message_id' => $whatsappMessageId,
            'sent_at' => now(),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'DELIVERED',
            'delivered_at' => now(),
        ]);
    }

    public function markAsRead(): void
    {
        $this->update([
            'status' => 'READ',
            'read_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage, ?string $errorCode = null): void
    {
        $this->update([
            'status' => 'FAILED',
            'error_message' => $errorMessage,
            'error_code' => $errorCode,
            'failed_at' => now(),
        ]);
    }

    public function canRetry(): bool
    {
        return $this->isFailed() && $this->retry_count < 3;
    }

    public function incrementRetry(): void
    {
        $this->increment('retry_count');
    }
}

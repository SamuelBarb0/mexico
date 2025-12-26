<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'waba_account_id',
        'name',
        'language',
        'category',
        'status',
        'meta_template_id',
        'meta_status',
        'components',
        'variables',
        'rejection_reason',
        'quality_score',
        'description',
        'tags',
        'usage_count',
    ];

    protected $casts = [
        'components' => 'array',
        'variables' => 'array',
        'tags' => 'array',
        'usage_count' => 'integer',
    ];

    protected $attributes = [
        'status' => 'DRAFT',
        'category' => 'MARKETING',
        'language' => 'es',
        'quality_score' => 'UNKNOWN',
        'usage_count' => 0,
    ];

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function wabaAccount(): BelongsTo
    {
        return $this->belongsTo(WabaAccount::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    // Helpers
    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }

    public function isDraft(): bool
    {
        return $this->status === 'DRAFT';
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'APPROVED' => 'bg-success-100 text-success-800',
            'PENDING' => 'bg-warning-100 text-warning-800',
            'REJECTED' => 'bg-danger-100 text-danger-800',
            'DRAFT' => 'bg-neutral-100 text-neutral-800',
            'DISABLED' => 'bg-neutral-200 text-neutral-600',
            default => 'bg-neutral-100 text-neutral-800',
        };
    }

    public function getCategoryBadgeClass(): string
    {
        return match($this->category) {
            'MARKETING' => 'bg-primary-100 text-primary-800',
            'UTILITY' => 'bg-secondary-100 text-secondary-800',
            'AUTHENTICATION' => 'bg-accent-100 text-accent-800',
            default => 'bg-neutral-100 text-neutral-800',
        };
    }

    public function getQualityBadgeClass(): string
    {
        return match($this->quality_score) {
            'GREEN' => 'bg-success-100 text-success-800',
            'YELLOW' => 'bg-warning-100 text-warning-800',
            'RED' => 'bg-danger-100 text-danger-800',
            default => 'bg-neutral-100 text-neutral-800',
        };
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    // Extract variable placeholders from components
    public function extractVariables(): array
    {
        $variables = [];

        foreach ($this->components as $component) {
            if (isset($component['text'])) {
                preg_match_all('/\{\{(\d+)\}\}/', $component['text'], $matches);
                foreach ($matches[1] as $index) {
                    $variables[] = [
                        'index' => (int)$index,
                        'component' => $component['type'] ?? 'BODY',
                    ];
                }
            }
        }

        return collect($variables)->unique('index')->sortBy('index')->values()->all();
    }

    // Get preview text for display
    public function getPreviewText(): string
    {
        $bodyComponent = collect($this->components)->firstWhere('type', 'BODY');
        return $bodyComponent['text'] ?? 'Sin contenido';
    }
}

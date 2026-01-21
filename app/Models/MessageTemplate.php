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
        $components = $this->components ?? [];

        // Check if it's Meta array format
        if (isset($components[0]) || (is_array($components) && !empty($components) && isset(array_values($components)[0]['type']))) {
            // Meta array format
            foreach ($components as $component) {
                if (isset($component['text'])) {
                    preg_match_all('/\{\{(\d+)\}\}/', $component['text'], $matches);
                    foreach ($matches[1] as $index) {
                        $variables[] = [
                            'index' => (int)$index,
                            'component' => $component['type'] ?? 'BODY',
                        ];
                    }
                }

                // Also check carousel cards
                if (isset($component['cards'])) {
                    foreach ($component['cards'] as $cardIndex => $card) {
                        foreach ($card['components'] ?? [] as $cardComponent) {
                            if (isset($cardComponent['text'])) {
                                preg_match_all('/\{\{(\d+)\}\}/', $cardComponent['text'], $matches);
                                foreach ($matches[1] as $index) {
                                    $variables[] = [
                                        'index' => (int)$index,
                                        'component' => 'CAROUSEL_CARD_' . ($cardIndex + 1),
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        } else {
            // Object format (local templates)
            foreach (['header', 'body', 'footer'] as $key) {
                if (isset($components[$key]['text'])) {
                    preg_match_all('/\{\{(\d+)\}\}/', $components[$key]['text'], $matches);
                    foreach ($matches[1] as $index) {
                        $variables[] = [
                            'index' => (int)$index,
                            'component' => strtoupper($key),
                        ];
                    }
                }
            }
        }

        return collect($variables)->unique('index')->sortBy('index')->values()->all();
    }

    // Get preview text for display
    public function getPreviewText(): string
    {
        $components = $this->components ?? [];

        // Check if it's Meta array format (has numeric keys or first element has 'type')
        if (isset($components[0]) || (is_array($components) && !empty($components) && isset(array_values($components)[0]['type']))) {
            // Meta array format
            $bodyComponent = collect($components)->firstWhere('type', 'BODY');
            if ($bodyComponent && isset($bodyComponent['text'])) {
                return $bodyComponent['text'];
            }

            // Check for carousel
            $carouselComponent = collect($components)->firstWhere('type', 'CAROUSEL');
            if ($carouselComponent) {
                $cardCount = count($carouselComponent['cards'] ?? []);
                return "[Carrusel con {$cardCount} tarjetas]";
            }

            // Check for catalog
            $catalogComponent = collect($components)->firstWhere('type', 'CATALOG');
            if ($catalogComponent) {
                return "[Catálogo de productos]";
            }

            return 'Sin contenido de texto';
        }

        // Object format (local templates): {header: {...}, body: {...}}
        if (isset($components['body']['text'])) {
            return $components['body']['text'];
        }

        return 'Sin contenido';
    }

    /**
     * Get header type (TEXT, IMAGE, VIDEO, DOCUMENT, LOCATION, or null)
     */
    public function getHeaderType(): ?string
    {
        $components = $this->components ?? [];

        // Check if it's Meta array format
        if (isset($components[0]) || (is_array($components) && !empty($components) && isset(array_values($components)[0]['type']))) {
            $headerComponent = collect($components)->firstWhere('type', 'HEADER');
            if ($headerComponent && isset($headerComponent['format'])) {
                return strtoupper($headerComponent['format']);
            }
            return null;
        }

        // Object format (local templates)
        if (isset($components['header']['format'])) {
            return strtoupper($components['header']['format']);
        }

        return null;
    }

    /**
     * Check if template has media header
     */
    public function hasMediaHeader(): bool
    {
        $headerType = $this->getHeaderType();
        return in_array($headerType, ['IMAGE', 'VIDEO', 'DOCUMENT', 'LOCATION']);
    }

    /**
     * Check if template has text header with variables
     */
    public function hasTextHeader(): bool
    {
        return $this->getHeaderType() === 'TEXT';
    }

    /**
     * Get header media example URL if exists
     */
    public function getHeaderMediaExample(): ?string
    {
        $components = $this->components ?? [];

        // Check if it's Meta array format
        if (isset($components[0]) || (is_array($components) && !empty($components) && isset(array_values($components)[0]['type']))) {
            $headerComponent = collect($components)->firstWhere('type', 'HEADER');
            if ($headerComponent && isset($headerComponent['example']['header_handle'][0])) {
                return $headerComponent['example']['header_handle'][0];
            }
            return null;
        }

        // Object format (local templates)
        if (isset($components['header']['example']['header_handle'][0])) {
            return $components['header']['example']['header_handle'][0];
        }

        return null;
    }

    /**
     * Check if template is a carousel type
     */
    public function isCarousel(): bool
    {
        $components = $this->components ?? [];

        if (isset($components[0]) || (is_array($components) && !empty($components) && isset(array_values($components)[0]['type']))) {
            return collect($components)->contains('type', 'CAROUSEL');
        }

        return false;
    }

    /**
     * Check if template has a catalog component
     */
    public function hasCatalog(): bool
    {
        $components = $this->components ?? [];

        if (isset($components[0]) || (is_array($components) && !empty($components) && isset(array_values($components)[0]['type']))) {
            return collect($components)->contains('type', 'CATALOG');
        }

        return false;
    }

    /**
     * Get all component types present in this template
     */
    public function getComponentTypes(): array
    {
        $components = $this->components ?? [];
        $types = [];

        if (isset($components[0]) || (is_array($components) && !empty($components) && isset(array_values($components)[0]['type']))) {
            // Meta array format
            foreach ($components as $component) {
                if (isset($component['type'])) {
                    $types[] = strtoupper($component['type']);
                }
            }
        } else {
            // Object format
            if (isset($components['header'])) $types[] = 'HEADER';
            if (isset($components['body'])) $types[] = 'BODY';
            if (isset($components['footer'])) $types[] = 'FOOTER';
            if (isset($components['buttons'])) $types[] = 'BUTTONS';
        }

        return array_unique($types);
    }
}

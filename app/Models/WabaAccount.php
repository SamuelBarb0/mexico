<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WabaAccount extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone_number',
        'phone_number_id',
        'business_account_id',
        'waba_id',
        'access_token',
        'status',
        'quality_rating',
        'settings',
        'verified_at',
        'last_sync_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'verified_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
    ];

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(MessageTemplate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function hasGoodQuality(): bool
    {
        return $this->quality_rating === 'green';
    }

    public function updateQualityRating(string $rating): void
    {
        $this->update(['quality_rating' => $rating]);
    }

    public function syncData(): void
    {
        $this->update(['last_sync_at' => now()]);
    }
}

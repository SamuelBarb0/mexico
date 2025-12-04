<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantLimit extends Model
{
    protected $fillable = [
        'tenant_id',
        'max_users',
        'max_contacts',
        'max_campaigns',
        'max_waba_accounts',
        'max_messages_per_month',
        'max_storage_mb',
        'current_users',
        'current_contacts',
        'current_campaigns',
        'current_waba_accounts',
        'current_messages_this_month',
        'current_storage_mb',
    ];

    protected $casts = [
        'max_users' => 'integer',
        'max_contacts' => 'integer',
        'max_campaigns' => 'integer',
        'max_waba_accounts' => 'integer',
        'max_messages_per_month' => 'integer',
        'max_storage_mb' => 'integer',
        'current_users' => 'integer',
        'current_contacts' => 'integer',
        'current_campaigns' => 'integer',
        'current_waba_accounts' => 'integer',
        'current_messages_this_month' => 'integer',
        'current_storage_mb' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function canAddUser(): bool
    {
        return $this->current_users < $this->max_users;
    }

    public function canAddContact(): bool
    {
        return $this->current_contacts < $this->max_contacts;
    }

    public function canAddCampaign(): bool
    {
        return $this->current_campaigns < $this->max_campaigns;
    }

    public function canAddWabaAccount(): bool
    {
        return $this->current_waba_accounts < $this->max_waba_accounts;
    }

    public function canSendMessage(): bool
    {
        return $this->current_messages_this_month < $this->max_messages_per_month;
    }

    public function hasStorageSpace($sizeInMb): bool
    {
        return ($this->current_storage_mb + $sizeInMb) <= $this->max_storage_mb;
    }
}

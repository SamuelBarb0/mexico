<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'company',
        'email',
        'phone',
        'country',
        'address',
        'notes',
        'custom_fields',
        'status',
    ];

    protected $casts = [
        'custom_fields' => 'array',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

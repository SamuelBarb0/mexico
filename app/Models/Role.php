<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'scope',
        'tenant_id',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')->withTimestamps();
    }

    public function givePermissionTo($permission): self
    {
        $permissionModel = is_string($permission)
            ? Permission::where('slug', $permission)->firstOrFail()
            : $permission;

        $this->permissions()->syncWithoutDetaching($permissionModel);

        return $this;
    }

    public function revokePermissionTo($permission): self
    {
        $permissionModel = is_string($permission)
            ? Permission::where('slug', $permission)->firstOrFail()
            : $permission;

        $this->permissions()->detach($permissionModel);

        return $this;
    }

    public function hasPermission($permission): bool
    {
        return $this->permissions->contains('slug', $permission);
    }

    public function isPlatformRole(): bool
    {
        return $this->scope === 'platform';
    }

    public function isTenantRole(): bool
    {
        return $this->scope === 'tenant';
    }
}

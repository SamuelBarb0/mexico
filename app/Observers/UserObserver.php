<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Solo incrementar si el usuario pertenece a un tenant (no platform admins)
        if ($user->tenant_id && $user->tenant && $user->tenant->limits) {
            $user->tenant->limits->increment('current_users');
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        if ($user->tenant_id && $user->tenant && $user->tenant->limits) {
            $user->tenant->limits->decrement('current_users');
        }
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        if ($user->tenant_id && $user->tenant && $user->tenant->limits) {
            $user->tenant->limits->decrement('current_users');
        }
    }
}

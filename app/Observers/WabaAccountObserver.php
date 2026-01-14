<?php

namespace App\Observers;

use App\Models\WabaAccount;

class WabaAccountObserver
{
    /**
     * Handle the WabaAccount "created" event.
     */
    public function created(WabaAccount $wabaAccount): void
    {
        $tenant = $wabaAccount->tenant;

        if ($tenant && $tenant->limits) {
            $tenant->limits->increment('current_waba_accounts');
        }
    }

    /**
     * Handle the WabaAccount "deleted" event.
     */
    public function deleted(WabaAccount $wabaAccount): void
    {
        $tenant = $wabaAccount->tenant;

        if ($tenant && $tenant->limits) {
            $tenant->limits->decrement('current_waba_accounts');
        }
    }

    /**
     * Handle the WabaAccount "force deleted" event.
     */
    public function forceDeleted(WabaAccount $wabaAccount): void
    {
        $tenant = $wabaAccount->tenant;

        if ($tenant && $tenant->limits) {
            $tenant->limits->decrement('current_waba_accounts');
        }
    }
}

<?php

namespace App\Observers;

use App\Models\Campaign;

class CampaignObserver
{
    /**
     * Handle the Campaign "created" event.
     */
    public function created(Campaign $campaign): void
    {
        $tenant = $campaign->tenant;

        if ($tenant && $tenant->limits) {
            $tenant->limits->increment('current_campaigns');
        }
    }

    /**
     * Handle the Campaign "deleted" event.
     */
    public function deleted(Campaign $campaign): void
    {
        $tenant = $campaign->tenant;

        if ($tenant && $tenant->limits) {
            $tenant->limits->decrement('current_campaigns');
        }
    }

    /**
     * Handle the Campaign "force deleted" event.
     */
    public function forceDeleted(Campaign $campaign): void
    {
        $tenant = $campaign->tenant;

        if ($tenant && $tenant->limits) {
            $tenant->limits->decrement('current_campaigns');
        }
    }
}

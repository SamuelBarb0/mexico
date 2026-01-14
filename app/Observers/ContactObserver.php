<?php

namespace App\Observers;

use App\Models\Contact;

class ContactObserver
{
    /**
     * Handle the Contact "created" event.
     */
    public function created(Contact $contact): void
    {
        $tenant = $contact->tenant;

        if ($tenant && $tenant->limits) {
            $tenant->limits->increment('current_contacts');
        }
    }

    /**
     * Handle the Contact "deleted" event.
     */
    public function deleted(Contact $contact): void
    {
        $tenant = $contact->tenant;

        if ($tenant && $tenant->limits) {
            $tenant->limits->decrement('current_contacts');
        }
    }

    /**
     * Handle the Contact "force deleted" event.
     */
    public function forceDeleted(Contact $contact): void
    {
        $tenant = $contact->tenant;

        if ($tenant && $tenant->limits) {
            $tenant->limits->decrement('current_contacts');
        }
    }
}

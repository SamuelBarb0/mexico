<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any invoices.
     */
    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    /**
     * Determine whether the user can view the invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->tenant_id === $invoice->tenant_id;
    }

    /**
     * Determine whether the user can download the invoice.
     */
    public function download(User $user, Invoice $invoice): bool
    {
        return $user->tenant_id === $invoice->tenant_id && $invoice->canDownload();
    }
}

<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy
{
    /**
     * Determine whether the user can view any subscriptions.
     */
    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    /**
     * Determine whether the user can view the subscription.
     */
    public function view(User $user, Subscription $subscription): bool
    {
        return $user->tenant_id === $subscription->tenant_id;
    }

    /**
     * Determine whether the user can create subscriptions.
     */
    public function create(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    /**
     * Determine whether the user can update the subscription.
     */
    public function update(User $user, Subscription $subscription): bool
    {
        return $user->tenant_id === $subscription->tenant_id;
    }

    /**
     * Determine whether the user can delete the subscription.
     */
    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->tenant_id === $subscription->tenant_id;
    }

    /**
     * Determine whether the user can cancel the subscription.
     */
    public function cancel(User $user, Subscription $subscription): bool
    {
        return $user->tenant_id === $subscription->tenant_id && $subscription->canCancel();
    }

    /**
     * Determine whether the user can resume the subscription.
     */
    public function resume(User $user, Subscription $subscription): bool
    {
        return $user->tenant_id === $subscription->tenant_id && $subscription->canResume();
    }
}

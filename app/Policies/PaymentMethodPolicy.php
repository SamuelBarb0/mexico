<?php

namespace App\Policies;

use App\Models\PaymentMethod;
use App\Models\User;

class PaymentMethodPolicy
{
    /**
     * Determine whether the user can view any payment methods.
     */
    public function viewAny(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    /**
     * Determine whether the user can view the payment method.
     */
    public function view(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->tenant_id === $paymentMethod->tenant_id;
    }

    /**
     * Determine whether the user can create payment methods.
     */
    public function create(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    /**
     * Determine whether the user can update the payment method.
     */
    public function update(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->tenant_id === $paymentMethod->tenant_id;
    }

    /**
     * Determine whether the user can delete the payment method.
     */
    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        return $user->tenant_id === $paymentMethod->tenant_id;
    }
}

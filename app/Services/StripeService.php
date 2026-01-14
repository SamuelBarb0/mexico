<?php

namespace App\Services;

use App\Models\{Tenant, Subscription, SubscriptionPlan, PaymentMethod, Invoice};
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription as StripeSubscription;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\Invoice as StripeInvoice;
use Exception;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create or retrieve a Stripe customer for a tenant
     */
    public function createOrGetCustomer(Tenant $tenant): Customer
    {
        // If tenant already has a Stripe customer ID, retrieve it
        if ($tenant->stripe_customer_id) {
            try {
                return Customer::retrieve($tenant->stripe_customer_id);
            } catch (Exception $e) {
                Log::error('Failed to retrieve Stripe customer', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage()
                ]);
                // Continue to create a new one
            }
        }

        // Create new customer
        try {
            $customer = Customer::create([
                'name' => $tenant->billing_name ?? $tenant->name,
                'email' => $tenant->billing_email ?? $tenant->users()->first()?->email,
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                ],
            ]);

            $tenant->update(['stripe_customer_id' => $customer->id]);

            return $customer;
        } catch (Exception $e) {
            Log::error('Failed to create Stripe customer', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create a subscription in Stripe
     */
    public function createSubscription(
        Tenant $tenant,
        SubscriptionPlan $plan,
        string $billingCycle = 'monthly',
        ?string $paymentMethodId = null
    ): Subscription {
        try {
            $customer = $this->createOrGetCustomer($tenant);

            // Attach payment method if provided
            if ($paymentMethodId) {
                $this->attachPaymentMethod($tenant, $paymentMethodId);
            }

            // Determine which price to use
            $priceId = $billingCycle === 'yearly'
                ? $plan->stripe_price_id_yearly
                : $plan->stripe_price_id_monthly;

            if (!$priceId) {
                throw new Exception("Plan {$plan->name} doesn't have a Stripe price ID for {$billingCycle} billing");
            }

            // Create subscription in Stripe
            $subscriptionParams = [
                'customer' => $customer->id,
                'items' => [['price' => $priceId]],
                'metadata' => [
                    'tenant_id' => $tenant->id,
                    'plan_slug' => $plan->slug,
                ],
            ];

            // Add trial period if applicable
            if ($plan->has_trial && $plan->trial_days > 0) {
                $subscriptionParams['trial_period_days'] = $plan->trial_days;
            }

            $stripeSubscription = StripeSubscription::create($subscriptionParams);

            // Log subscription details for debugging
            Log::info('Stripe Subscription Created', [
                'stripe_subscription_id' => $stripeSubscription->id,
                'status' => $stripeSubscription->status,
                'trial_start' => $stripeSubscription->trial_start,
                'trial_end' => $stripeSubscription->trial_end,
                'created' => $stripeSubscription->created,
                'current_period_start' => $stripeSubscription->current_period_start,
                'current_period_end' => $stripeSubscription->current_period_end,
            ]);

            // Create local subscription record
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'subscription_plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'stripe_subscription_id' => $stripeSubscription->id,
                'stripe_customer_id' => $customer->id,
                'stripe_status' => $stripeSubscription->status,
                'status' => $this->mapStripeStatus($stripeSubscription->status),
                'trial_starts_at' => $stripeSubscription->trial_start ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->trial_start) : null,
                'trial_ends_at' => $stripeSubscription->trial_end ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->trial_end) : null,
                'starts_at' => $stripeSubscription->created ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->created) : now(),
                'current_period_start' => $stripeSubscription->current_period_start ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start) : now(),
                'current_period_end' => $stripeSubscription->current_period_end ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end) : now()->addMonth(),
            ]);

            return $subscription;
        } catch (Exception $e) {
            Log::error('Failed to create subscription', [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(Subscription $subscription, bool $immediately = false): void
    {
        try {
            if (!$subscription->stripe_subscription_id) {
                throw new Exception('Subscription does not have a Stripe subscription ID');
            }

            $stripeSubscription = StripeSubscription::retrieve($subscription->stripe_subscription_id);

            if ($immediately) {
                $stripeSubscription->cancel();
                $subscription->update([
                    'status' => 'canceled',
                    'canceled_at' => now(),
                    'ends_at' => now(),
                ]);
            } else {
                // Cancel at period end
                StripeSubscription::update($stripeSubscription->id, [
                    'cancel_at_period_end' => true,
                ]);
                $subscription->update([
                    'status' => 'canceled',
                    'canceled_at' => now(),
                    'ends_at' => $subscription->current_period_end,
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to cancel subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Resume a canceled subscription
     */
    public function resumeSubscription(Subscription $subscription): void
    {
        try {
            if (!$subscription->stripe_subscription_id) {
                throw new Exception('Subscription does not have a Stripe subscription ID');
            }

            $stripeSubscription = StripeSubscription::retrieve($subscription->stripe_subscription_id);

            // Resume by removing the cancellation
            $stripeSubscription->update([
                'cancel_at_period_end' => false,
            ]);

            $subscription->update([
                'status' => 'active',
                'canceled_at' => null,
                'ends_at' => null,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to resume subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update subscription to a different plan
     */
    public function updateSubscriptionPlan(
        Subscription $subscription,
        SubscriptionPlan $newPlan,
        string $billingCycle = 'monthly'
    ): Subscription {
        try {
            if (!$subscription->stripe_subscription_id) {
                throw new Exception('Subscription does not have a Stripe subscription ID');
            }

            $priceId = $billingCycle === 'yearly'
                ? $newPlan->stripe_price_id_yearly
                : $newPlan->stripe_price_id_monthly;

            if (!$priceId) {
                throw new Exception("Plan {$newPlan->name} doesn't have a Stripe price ID for {$billingCycle} billing");
            }

            $stripeSubscription = StripeSubscription::retrieve($subscription->stripe_subscription_id);

            // Update the subscription
            $stripeSubscription->update([
                'items' => [
                    [
                        'id' => $stripeSubscription->items->data[0]->id,
                        'price' => $priceId,
                    ],
                ],
                'proration_behavior' => 'create_prorations', // Prorate charges
            ]);

            $subscription->update([
                'subscription_plan_id' => $newPlan->id,
                'billing_cycle' => $billingCycle,
            ]);

            return $subscription;
        } catch (Exception $e) {
            Log::error('Failed to update subscription plan', [
                'subscription_id' => $subscription->id,
                'new_plan_id' => $newPlan->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Attach a payment method to a customer
     */
    public function attachPaymentMethod(Tenant $tenant, string $paymentMethodId): PaymentMethod
    {
        try {
            $customer = $this->createOrGetCustomer($tenant);

            // Retrieve payment method details from Stripe
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethodId);

            // Check if payment method ID already exists
            $existingPaymentMethod = PaymentMethod::where('tenant_id', $tenant->id)
                ->where('stripe_payment_method_id', $paymentMethodId)
                ->first();

            if ($existingPaymentMethod) {
                // Payment method already exists, just set it as default if needed
                Customer::update($customer->id, [
                    'invoice_settings' => [
                        'default_payment_method' => $paymentMethodId,
                    ],
                ]);

                // Set as default and activate other payment methods as non-default
                PaymentMethod::where('tenant_id', $tenant->id)
                    ->where('id', '!=', $existingPaymentMethod->id)
                    ->update(['is_default' => false]);

                $existingPaymentMethod->update(['is_default' => true, 'is_active' => true]);

                return $existingPaymentMethod;
            }

            // Check if the same card already exists (by card details, not just Stripe ID)
            // This prevents adding the same physical card multiple times
            if ($stripePaymentMethod->type === 'card' && isset($stripePaymentMethod->card)) {
                $duplicateCard = PaymentMethod::where('tenant_id', $tenant->id)
                    ->where('type', 'card')
                    ->where('brand', $stripePaymentMethod->card->brand)
                    ->where('last4', $stripePaymentMethod->card->last4)
                    ->where('exp_month', $stripePaymentMethod->card->exp_month)
                    ->where('exp_year', $stripePaymentMethod->card->exp_year)
                    ->where('is_active', true)
                    ->first();

                if ($duplicateCard) {
                    throw new Exception(
                        "Esta tarjeta {$stripePaymentMethod->card->brand} ****{$stripePaymentMethod->card->last4} " .
                        "ya está registrada en su cuenta."
                    );
                }
            }

            // Attach payment method to customer in Stripe
            // Only attach if not already attached
            if (!$stripePaymentMethod->customer || $stripePaymentMethod->customer !== $customer->id) {
                $stripePaymentMethod->attach(['customer' => $customer->id]);
            }

            // Set as default payment method
            Customer::update($customer->id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);

            // Store payment method locally
            $paymentMethod = PaymentMethod::create([
                'tenant_id' => $tenant->id,
                'stripe_payment_method_id' => $paymentMethodId,
                'stripe_customer_id' => $customer->id,
                'type' => $stripePaymentMethod->type,
                'brand' => $stripePaymentMethod->card->brand ?? null,
                'last4' => $stripePaymentMethod->card->last4 ?? null,
                'exp_month' => $stripePaymentMethod->card->exp_month ?? null,
                'exp_year' => $stripePaymentMethod->card->exp_year ?? null,
                'country' => $stripePaymentMethod->card->country ?? null,
                'is_default' => true,
            ]);

            // Set all other payment methods as non-default
            PaymentMethod::where('tenant_id', $tenant->id)
                ->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);

            return $paymentMethod;
        } catch (Exception $e) {
            Log::error('Failed to attach payment method', [
                'tenant_id' => $tenant->id,
                'payment_method_id' => $paymentMethodId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Detach a payment method
     */
    public function detachPaymentMethod(PaymentMethod $paymentMethod): void
    {
        try {
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethod->stripe_payment_method_id);
            $stripePaymentMethod->detach();

            $paymentMethod->delete();
        } catch (Exception $e) {
            Log::error('Failed to detach payment method', [
                'payment_method_id' => $paymentMethod->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Sync invoice from Stripe
     */
    public function syncInvoice(string $stripeInvoiceId): ?Invoice
    {
        try {
            $stripeInvoice = StripeInvoice::retrieve($stripeInvoiceId);

            // Find tenant by customer ID
            $tenant = Tenant::where('stripe_customer_id', $stripeInvoice->customer)->first();

            if (!$tenant) {
                Log::warning('Tenant not found for invoice', ['stripe_customer_id' => $stripeInvoice->customer]);
                return null;
            }

            // Find subscription
            $subscription = Subscription::where('stripe_subscription_id', $stripeInvoice->subscription)->first();

            // Create or update invoice
            $invoice = Invoice::updateOrCreate(
                ['stripe_invoice_id' => $stripeInvoiceId],
                [
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $subscription?->id,
                    'stripe_customer_id' => $stripeInvoice->customer,
                    'invoice_number' => $stripeInvoice->number ?? 'DRAFT-' . $stripeInvoice->id,
                    'subtotal' => $stripeInvoice->subtotal / 100,
                    'tax' => $stripeInvoice->tax / 100,
                    'total' => $stripeInvoice->total / 100,
                    'currency' => strtoupper($stripeInvoice->currency),
                    'status' => $stripeInvoice->status,
                    'invoice_date' => \Carbon\Carbon::createFromTimestamp($stripeInvoice->created),
                    'due_date' => $stripeInvoice->due_date ? \Carbon\Carbon::createFromTimestamp($stripeInvoice->due_date) : null,
                    'paid_at' => $stripeInvoice->status_transitions->paid_at
                        ? \Carbon\Carbon::createFromTimestamp($stripeInvoice->status_transitions->paid_at)
                        : null,
                    'stripe_payment_intent_id' => $stripeInvoice->payment_intent,
                    'stripe_hosted_invoice_url' => $stripeInvoice->hosted_invoice_url,
                    'stripe_invoice_pdf' => $stripeInvoice->invoice_pdf,
                    'line_items' => $this->formatLineItems($stripeInvoice->lines->data),
                ]
            );

            return $invoice;
        } catch (Exception $e) {
            Log::error('Failed to sync invoice', [
                'stripe_invoice_id' => $stripeInvoiceId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Map Stripe status to local status
     */
    protected function mapStripeStatus(string $stripeStatus): string
    {
        return match($stripeStatus) {
            'trialing' => 'trial',
            'active' => 'active',
            'canceled' => 'canceled',
            'past_due' => 'past_due',
            'unpaid' => 'unpaid',
            'incomplete' => 'incomplete',
            'incomplete_expired' => 'incomplete_expired',
            'paused' => 'paused',
            default => 'active',
        };
    }

    /**
     * Format line items from Stripe invoice
     */
    protected function formatLineItems(array $stripeLineItems): array
    {
        return array_map(function ($item) {
            return [
                'description' => $item->description,
                'amount' => $item->amount / 100,
                'quantity' => $item->quantity,
                'period' => [
                    'start' => $item->period->start,
                    'end' => $item->period->end,
                ],
            ];
        }, $stripeLineItems);
    }
}

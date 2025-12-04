<?php

namespace App\Http\Controllers;

use App\Models\{Subscription, Tenant};
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Handle incoming Stripe webhooks
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid webhook payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Invalid webhook signature', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Log the webhook event
        Log::info('Stripe webhook received', [
            'type' => $event->type,
            'id' => $event->id
        ]);

        // Handle the event
        try {
            switch ($event->type) {
                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event->data->object);
                    break;

                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event->data->object);
                    break;

                case 'customer.subscription.trial_will_end':
                    $this->handleTrialWillEnd($event->data->object);
                    break;

                case 'invoice.paid':
                    $this->handleInvoicePaid($event->data->object);
                    break;

                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($event->data->object);
                    break;

                case 'invoice.payment_action_required':
                    $this->handleInvoicePaymentActionRequired($event->data->object);
                    break;

                case 'payment_method.attached':
                    $this->handlePaymentMethodAttached($event->data->object);
                    break;

                case 'payment_method.detached':
                    $this->handlePaymentMethodDetached($event->data->object);
                    break;

                default:
                    Log::info('Unhandled webhook event type', ['type' => $event->type]);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Webhook handler error', [
                'type' => $event->type,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Webhook handler failed'], 500);
        }
    }

    /**
     * Handle subscription created or updated
     */
    protected function handleSubscriptionUpdated($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if (!$subscription) {
            Log::warning('Subscription not found for webhook', [
                'stripe_subscription_id' => $stripeSubscription->id
            ]);
            return;
        }

        $subscription->update([
            'stripe_status' => $stripeSubscription->status,
            'status' => $this->mapStripeStatus($stripeSubscription->status),
            'current_period_start' => now()->timestamp($stripeSubscription->current_period_start),
            'current_period_end' => now()->timestamp($stripeSubscription->current_period_end),
        ]);

        Log::info('Subscription updated from webhook', [
            'subscription_id' => $subscription->id,
            'status' => $subscription->status
        ]);
    }

    /**
     * Handle subscription deleted
     */
    protected function handleSubscriptionDeleted($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status' => 'canceled',
            'stripe_status' => 'canceled',
            'ends_at' => now(),
        ]);

        Log::info('Subscription canceled from webhook', [
            'subscription_id' => $subscription->id
        ]);
    }

    /**
     * Handle trial ending soon
     */
    protected function handleTrialWillEnd($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if (!$subscription) {
            return;
        }

        $tenant = $subscription->tenant;
        $daysRemaining = $subscription->trialDaysRemaining();

        // TODO: Send notification email to tenant
        Log::info('Trial ending soon', [
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'days_remaining' => $daysRemaining
        ]);
    }

    /**
     * Handle successful invoice payment
     */
    protected function handleInvoicePaid($stripeInvoice)
    {
        // Sync invoice to database
        $invoice = $this->stripeService->syncInvoice($stripeInvoice->id);

        if ($invoice) {
            Log::info('Invoice paid and synced', [
                'invoice_id' => $invoice->id,
                'total' => $invoice->total
            ]);

            // TODO: Send receipt email to tenant
        }
    }

    /**
     * Handle failed invoice payment
     */
    protected function handleInvoicePaymentFailed($stripeInvoice)
    {
        // Sync invoice to database
        $invoice = $this->stripeService->syncInvoice($stripeInvoice->id);

        if ($invoice) {
            // Update subscription status
            $subscription = $invoice->subscription;
            if ($subscription) {
                $subscription->update([
                    'status' => 'past_due',
                ]);
            }

            Log::warning('Invoice payment failed', [
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id
            ]);

            // TODO: Send payment failed notification to tenant
        }
    }

    /**
     * Handle invoice requiring payment action
     */
    protected function handleInvoicePaymentActionRequired($stripeInvoice)
    {
        $invoice = $this->stripeService->syncInvoice($stripeInvoice->id);

        if ($invoice) {
            Log::info('Invoice requires payment action', [
                'invoice_id' => $invoice->id,
                'tenant_id' => $invoice->tenant_id
            ]);

            // TODO: Send notification requiring action
        }
    }

    /**
     * Handle payment method attached
     */
    protected function handlePaymentMethodAttached($stripePaymentMethod)
    {
        Log::info('Payment method attached', [
            'payment_method_id' => $stripePaymentMethod->id,
            'customer' => $stripePaymentMethod->customer
        ]);
    }

    /**
     * Handle payment method detached
     */
    protected function handlePaymentMethodDetached($stripePaymentMethod)
    {
        Log::info('Payment method detached', [
            'payment_method_id' => $stripePaymentMethod->id
        ]);
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
}

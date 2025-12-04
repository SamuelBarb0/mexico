<?php

namespace App\Http\Controllers;

use App\Models\{Subscription, SubscriptionPlan};
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class SubscriptionController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Display subscription management page
     */
    public function index()
    {
        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró información del tenant.');
        }

        $currentSubscription = $tenant->currentSubscription();
        $paymentMethod = $tenant->defaultPaymentMethod();
        $invoices = $tenant->invoices()->recent()->limit(10)->get();

        return view('subscriptions.index', compact(
            'tenant',
            'currentSubscription',
            'paymentMethod',
            'invoices'
        ));
    }

    /**
     * Show available subscription plans
     */
    public function plans()
    {
        $plans = SubscriptionPlan::active()
            ->visible()
            ->ordered()
            ->get();

        $tenant = auth()->user()->tenant;
        $currentSubscription = $tenant ? $tenant->currentSubscription() : null;

        return view('subscriptions.plans', compact('plans', 'currentSubscription'));
    }

    /**
     * Show checkout page for a specific plan
     */
    public function checkout(SubscriptionPlan $plan)
    {
        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró información del tenant.');
        }

        $currentSubscription = $tenant->currentSubscription();

        // Prevent subscribing to Free plan if already on paid plan
        if ($plan->isFree() && $currentSubscription && !$currentSubscription->plan->isFree()) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'No puede cambiar a un plan gratuito desde un plan de pago.');
        }

        return view('subscriptions.checkout', compact('plan', 'tenant'));
    }

    /**
     * Process subscription creation
     */
    public function subscribe(Request $request, SubscriptionPlan $plan)
    {
        $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
            'payment_method_id' => 'required_if:plan.price_monthly,>,0|string',
        ]);

        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró información del tenant.');
        }

        try {
            // Cancel existing subscription if any
            $currentSubscription = $tenant->currentSubscription();
            if ($currentSubscription && $currentSubscription->canCancel()) {
                $this->stripeService->cancelSubscription($currentSubscription, true);
            }

            // Create new subscription
            $subscription = $this->stripeService->createSubscription(
                $tenant,
                $plan,
                $request->billing_cycle,
                $request->payment_method_id
            );

            return redirect()->route('subscriptions.index')
                ->with('success', "¡Suscripción al plan {$plan->name} creada exitosamente!");
        } catch (Exception $e) {
            Log::error('Subscription creation failed', [
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al crear la suscripción: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Change subscription plan
     */
    public function changePlan(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró información del tenant.');
        }

        $currentSubscription = $tenant->currentSubscription();

        if (!$currentSubscription) {
            return redirect()->route('subscriptions.plans')
                ->with('error', 'No tiene una suscripción activa.');
        }

        $newPlan = SubscriptionPlan::findOrFail($request->plan_id);

        // Prevent changing to Free plan
        if ($newPlan->isFree()) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'No puede cambiar a un plan gratuito. Por favor cancele su suscripción actual.');
        }

        try {
            $this->stripeService->updateSubscriptionPlan(
                $currentSubscription,
                $newPlan,
                $request->billing_cycle
            );

            return redirect()->route('subscriptions.index')
                ->with('success', "¡Plan actualizado a {$newPlan->name} exitosamente!");
        } catch (Exception $e) {
            Log::error('Plan change failed', [
                'subscription_id' => $currentSubscription->id,
                'new_plan_id' => $newPlan->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al cambiar de plan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró información del tenant.');
        }

        $subscription = $tenant->currentSubscription();

        if (!$subscription || !$subscription->canCancel()) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'No se puede cancelar esta suscripción.');
        }

        $immediately = $request->boolean('immediately', false);

        try {
            $this->stripeService->cancelSubscription($subscription, $immediately);

            $message = $immediately
                ? 'Suscripción cancelada inmediatamente.'
                : 'Suscripción programada para cancelación al final del período actual.';

            return redirect()->route('subscriptions.index')
                ->with('success', $message);
        } catch (Exception $e) {
            Log::error('Subscription cancellation failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al cancelar la suscripción: ' . $e->getMessage());
        }
    }

    /**
     * Resume a canceled subscription
     */
    public function resume()
    {
        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró información del tenant.');
        }

        $subscription = $tenant->currentSubscription();

        if (!$subscription || !$subscription->canResume()) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'No se puede reanudar esta suscripción.');
        }

        try {
            $this->stripeService->resumeSubscription($subscription);

            return redirect()->route('subscriptions.index')
                ->with('success', '¡Suscripción reanudada exitosamente!');
        } catch (Exception $e) {
            Log::error('Subscription resume failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al reanudar la suscripción: ' . $e->getMessage());
        }
    }

    /**
     * Show invoice
     */
    public function invoice($invoiceId)
    {
        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró información del tenant.');
        }

        $invoice = $tenant->invoices()->findOrFail($invoiceId);

        return view('subscriptions.invoice', compact('invoice'));
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoice($invoiceId)
    {
        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró información del tenant.');
        }

        $invoice = $tenant->invoices()->findOrFail($invoiceId);

        if (!$invoice->canDownload()) {
            return redirect()->back()
                ->with('error', 'Esta factura no está disponible para descarga.');
        }

        return redirect($invoice->stripe_invoice_pdf);
    }
}

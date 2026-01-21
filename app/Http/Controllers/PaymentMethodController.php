<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PaymentMethodController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Display payment methods
     */
    public function index()
    {
        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró información del tenant.');
        }

        $paymentMethods = $tenant->paymentMethods()->active()->get();

        return view('payment-methods.index', compact('paymentMethods'));
    }

    /**
     * Store a new payment method
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró información del tenant.');
        }

        try {
            $paymentMethod = $this->stripeService->attachPaymentMethod(
                $tenant,
                $request->payment_method_id
            );

            return redirect()->route('payment-methods.index')
                ->with('success', '¡Método de pago agregado exitosamente!');
        } catch (Exception $e) {
            Log::error('Payment method attachment failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al agregar método de pago: ' . $e->getMessage());
        }
    }

    /**
     * Set payment method as default
     */
    public function setDefault(PaymentMethod $paymentMethod)
    {
        $this->authorize('update', $paymentMethod);

        $tenant = auth()->user()->tenant;

        try {
            // Set all as non-default
            $tenant->paymentMethods()->update(['is_default' => false]);

            // Set this one as default
            $paymentMethod->update(['is_default' => true]);

            // Update in Stripe
            \Stripe\Customer::update($tenant->stripe_customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethod->stripe_payment_method_id,
                ],
            ]);

            return redirect()->route('payment-methods.index')
                ->with('success', 'Método de pago predeterminado actualizado.');
        } catch (Exception $e) {
            Log::error('Failed to set default payment method', [
                'payment_method_id' => $paymentMethod->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al actualizar método de pago predeterminado.');
        }
    }

    /**
     * Remove a payment method
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        $this->authorize('delete', $paymentMethod);

        $tenant = auth()->user()->tenant;

        // Prevent removing default payment method if subscription exists
        if ($paymentMethod->is_default && $tenant->hasActiveSubscription()) {
            return redirect()->back()
                ->with('error', 'No puede eliminar el método de pago predeterminado mientras tenga una suscripción activa. Agregue otro método primero.');
        }

        try {
            $this->stripeService->detachPaymentMethod($paymentMethod);

            return redirect()->route('payment-methods.index')
                ->with('success', 'Método de pago eliminado exitosamente.');
        } catch (Exception $e) {
            Log::error('Payment method deletion failed', [
                'payment_method_id' => $paymentMethod->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error al eliminar método de pago: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;
use Exception;
use Illuminate\Support\Facades\Log;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans',
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'has_trial' => 'boolean',
            'trial_days' => 'nullable|integer',
            'max_users' => 'required|integer|min:1',
            'max_contacts' => 'required|integer|min:0',
            'max_campaigns' => 'required|integer|min:0',
            'max_waba_accounts' => 'required|integer|min:0',
            'max_messages_per_month' => 'required|integer|min:0',
            'max_storage_mb' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'restrictions' => 'nullable|array',
            'is_active' => 'boolean',
            'is_visible' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['has_trial'] = $request->has('has_trial');
        $validated['is_active'] = $request->has('is_active');
        $validated['is_visible'] = $request->has('is_visible');
        $validated['is_default'] = $request->has('is_default');

        // Si este plan es el default, quitar el default de los demás
        if ($validated['is_default']) {
            SubscriptionPlan::where('is_default', true)->update(['is_default' => false]);
        }

        $plan = SubscriptionPlan::create($validated);

        // Sincronizar automáticamente con Stripe si el plan tiene precio
        if ($plan->price_monthly > 0 || $plan->price_yearly > 0) {
            try {
                $this->syncPlanWithStripe($plan);
                return redirect()->route('admin.plans.index')
                    ->with('success', 'Plan de suscripción creado y sincronizado con Stripe exitosamente');
            } catch (Exception $e) {
                Log::error('Error al sincronizar plan con Stripe', [
                    'plan_id' => $plan->id,
                    'error' => $e->getMessage()
                ]);
                return redirect()->route('admin.plans.index')
                    ->with('warning', 'Plan creado pero hubo un error al sincronizar con Stripe: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan de suscripción creado exitosamente');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'has_trial' => 'boolean',
            'trial_days' => 'nullable|integer',
            'max_users' => 'required|integer|min:1',
            'max_contacts' => 'required|integer|min:0',
            'max_campaigns' => 'required|integer|min:0',
            'max_waba_accounts' => 'required|integer|min:0',
            'max_messages_per_month' => 'required|integer|min:0',
            'max_storage_mb' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'restrictions' => 'nullable|array',
            'is_active' => 'boolean',
            'is_visible' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['has_trial'] = $request->has('has_trial');
        $validated['is_active'] = $request->has('is_active');
        $validated['is_visible'] = $request->has('is_visible');
        $validated['is_default'] = $request->has('is_default');

        // Si este plan es el default, quitar el default de los demás
        if ($validated['is_default']) {
            SubscriptionPlan::where('is_default', true)
                ->where('id', '!=', $plan->id)
                ->update(['is_default' => false]);
        }

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan de suscripción actualizado exitosamente');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        // Verificar si hay suscripciones activas con este plan
        if ($plan->subscriptions()->whereIn('status', ['active', 'trialing'])->exists()) {
            return redirect()->route('admin.plans.index')
                ->with('error', 'No se puede eliminar un plan con suscripciones activas');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan de suscripción eliminado exitosamente');
    }

    public function toggle(SubscriptionPlan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Estado del plan actualizado');
    }

    /**
     * Sincroniza un plan con Stripe creando Product y Prices automáticamente
     */
    protected function syncPlanWithStripe(SubscriptionPlan $plan): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // Crear producto en Stripe si no existe
        if (!$plan->stripe_product_id) {
            $product = Product::create([
                'name' => $plan->name,
                'description' => $plan->description,
                'metadata' => [
                    'plan_id' => $plan->id,
                    'slug' => $plan->slug,
                ],
            ]);

            $plan->stripe_product_id = $product->id;
            $plan->save();
        }

        // Crear precio mensual si no existe y el precio es mayor a 0
        if (!$plan->stripe_price_id_monthly && $plan->price_monthly > 0) {
            $priceMonthly = Price::create([
                'product' => $plan->stripe_product_id,
                'unit_amount' => (int)($plan->price_monthly * 100), // Convertir a centavos
                'currency' => $plan->currency,
                'recurring' => [
                    'interval' => 'month',
                ],
                'metadata' => [
                    'plan_id' => $plan->id,
                    'billing_period' => 'monthly',
                ],
            ]);

            $plan->stripe_price_id_monthly = $priceMonthly->id;
            $plan->save();
        }

        // Crear precio anual si no existe y el precio es mayor a 0
        if (!$plan->stripe_price_id_yearly && $plan->price_yearly > 0) {
            $priceYearly = Price::create([
                'product' => $plan->stripe_product_id,
                'unit_amount' => (int)($plan->price_yearly * 100), // Convertir a centavos
                'currency' => $plan->currency,
                'recurring' => [
                    'interval' => 'year',
                ],
                'metadata' => [
                    'plan_id' => $plan->id,
                    'billing_period' => 'yearly',
                ],
            ]);

            $plan->stripe_price_id_yearly = $priceYearly->id;
            $plan->save();
        }
    }
}

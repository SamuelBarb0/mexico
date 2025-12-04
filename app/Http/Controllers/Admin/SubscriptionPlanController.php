<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

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

        SubscriptionPlan::create($validated);

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
}

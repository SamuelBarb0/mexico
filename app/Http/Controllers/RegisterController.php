<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\TenantLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle the registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'company_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear el Tenant (empresa)
            $tenant = Tenant::create([
                'name' => $validated['company_name'],
                'slug' => Str::slug($validated['company_name']) . '-' . Str::random(6),
                'billing_email' => $validated['email'],
                'billing_name' => $validated['name'],
                'status' => 'active',
                'trial_ends_at' => now()->addDays(14), // 14 días de prueba
            ]);

            // 2. Crear el Usuario como Tenant Admin
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'user_type' => 'tenant_admin',
                'is_active' => true,
            ]);

            // 3. Buscar el plan gratuito o de prueba
            $trialPlan = SubscriptionPlan::where('price', 0)
                ->orWhere('name', 'like', '%trial%')
                ->orWhere('name', 'like', '%free%')
                ->first();

            // Si no hay plan gratuito, usar el plan básico
            if (!$trialPlan) {
                $trialPlan = SubscriptionPlan::where('is_active', true)
                    ->orderBy('price')
                    ->first();
            }

            // 4. Crear suscripción de prueba (si hay plan disponible)
            if ($trialPlan) {
                Subscription::create([
                    'tenant_id' => $tenant->id,
                    'subscription_plan_id' => $trialPlan->id,
                    'status' => 'trial',
                    'trial_ends_at' => now()->addDays(14),
                    'current_period_start' => now(),
                    'current_period_end' => now()->addDays(14),
                ]);

                // 5. Inicializar límites del tenant basado en el plan
                TenantLimit::create([
                    'tenant_id' => $tenant->id,
                    'max_users' => $trialPlan->max_users ?? 2,
                    'max_contacts' => $trialPlan->max_contacts ?? 100,
                    'max_campaigns' => $trialPlan->max_campaigns ?? 10,
                    'max_waba_accounts' => $trialPlan->max_waba_accounts ?? 1,
                    'max_messages_per_month' => $trialPlan->max_messages_per_month ?? 1000,
                    'max_storage_mb' => $trialPlan->max_storage_mb ?? 100,
                    'current_users' => 1, // Ya creamos un usuario
                    'current_contacts' => 0,
                    'current_campaigns' => 0,
                    'current_waba_accounts' => 0,
                    'current_messages_this_month' => 0,
                    'current_storage_mb' => 0,
                ]);
            }

            DB::commit();

            // 5. Iniciar sesión automáticamente
            auth()->login($user);

            return redirect()->route('dashboard')
                ->with('success', '¡Bienvenido! Tu cuenta ha sido creada exitosamente. Tienes 14 días de prueba gratis.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Error al crear la cuenta: ' . $e->getMessage());
        }
    }
}

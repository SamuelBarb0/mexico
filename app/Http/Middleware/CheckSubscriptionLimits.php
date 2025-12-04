<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $tenant = $request->user()?->tenant;

        if (!$tenant) {
            return redirect()->route('dashboard')
                ->with('error', 'No se pudo verificar su cuenta de tenant.');
        }

        // Check if tenant has an active subscription
        if (!$tenant->hasActiveSubscription()) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Necesita una suscripción activa para acceder a esta funcionalidad.');
        }

        // Check if the specific resource limit has been reached
        if ($tenant->hasReachedLimit($resource)) {
            $subscription = $tenant->currentSubscription();
            $plan = $subscription->plan;

            $limitMessages = [
                'users' => "Ha alcanzado el límite de usuarios ({$plan->max_users}) de su plan {$plan->name}.",
                'contacts' => "Ha alcanzado el límite de contactos ({$plan->max_contacts}) de su plan {$plan->name}.",
                'campaigns' => "Ha alcanzado el límite de campañas ({$plan->max_campaigns}) de su plan {$plan->name}.",
                'waba_accounts' => "Ha alcanzado el límite de cuentas WABA ({$plan->max_waba_accounts}) de su plan {$plan->name}.",
                'messages' => "Ha alcanzado el límite mensual de mensajes ({$plan->max_messages_per_month}) de su plan {$plan->name}.",
                'storage' => "Ha alcanzado el límite de almacenamiento ({$plan->max_storage_mb}MB) de su plan {$plan->name}.",
            ];

            $message = $limitMessages[$resource] ?? "Ha alcanzado el límite de {$resource} de su plan actual.";
            $message .= " Por favor, actualice su plan para continuar.";

            return redirect()->back()
                ->with('error', $message)
                ->with('upgrade_required', true);
        }

        return $next($request);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Customer;

class CleanStripeIds extends Command
{
    protected $signature = 'stripe:clean-ids
                            {--check : Solo verificar sin hacer cambios}
                            {--tenant= : ID de tenant específico}';

    protected $description = 'Limpia IDs de Stripe inválidos (clientes que no existen)';

    public function handle(): int
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $checkOnly = $this->option('check');
        $tenantId = $this->option('tenant');

        $this->info($checkOnly ? '🔍 Verificando IDs de Stripe...' : '🧹 Limpiando IDs de Stripe inválidos...');
        $this->newLine();

        $query = Tenant::whereNotNull('stripe_customer_id');
        if ($tenantId) {
            $query->where('id', $tenantId);
        }

        $tenants = $query->get();
        $invalidCount = 0;
        $validCount = 0;

        foreach ($tenants as $tenant) {
            $this->info("Verificando Tenant #{$tenant->id}: {$tenant->name}");
            $this->line("  Customer ID: {$tenant->stripe_customer_id}");

            try {
                $customer = Customer::retrieve($tenant->stripe_customer_id);

                if ($customer->deleted ?? false) {
                    $this->warn("  ⚠️  Cliente eliminado en Stripe");
                    $invalidCount++;

                    if (!$checkOnly) {
                        $this->cleanTenantStripeData($tenant);
                    }
                } else {
                    $this->line("  ✅ Cliente válido: {$customer->email}");
                    $validCount++;
                }
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                if (str_contains($e->getMessage(), 'No such customer')) {
                    $this->error("  ❌ Cliente NO existe en Stripe");
                    $invalidCount++;

                    if (!$checkOnly) {
                        $this->cleanTenantStripeData($tenant);
                    }
                } else {
                    $this->error("  ❌ Error: " . $e->getMessage());
                }
            }

            $this->newLine();
        }

        // Resumen
        $this->newLine();
        $this->table(
            ['Estado', 'Cantidad'],
            [
                ['Válidos', $validCount],
                ['Inválidos', $invalidCount],
            ]
        );

        if ($checkOnly && $invalidCount > 0) {
            $this->newLine();
            $this->warn("Ejecuta sin --check para limpiar los IDs inválidos:");
            $this->line("  php artisan stripe:clean-ids");
        }

        if (!$checkOnly && $invalidCount > 0) {
            $this->newLine();
            $this->info("✨ Se limpiaron {$invalidCount} tenant(s) con IDs inválidos.");
            $this->line("Los nuevos clientes se crearán automáticamente cuando agreguen un método de pago.");
        }

        return 0;
    }

    protected function cleanTenantStripeData(Tenant $tenant): void
    {
        $this->line("  🧹 Limpiando datos de Stripe...");

        // Limpiar stripe_customer_id del tenant
        $tenant->update(['stripe_customer_id' => null]);
        $this->line("    - stripe_customer_id limpiado");

        // Cancelar suscripciones locales que tengan stripe IDs inválidos
        $subscriptions = Subscription::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->whereNotNull('stripe_subscription_id')
            ->whereIn('status', ['active', 'trial'])
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'stripe_subscription_id' => null,
                'stripe_customer_id' => null,
            ]);
            $this->line("    - Suscripción #{$subscription->id} marcada como cancelada");
        }

        // Limpiar métodos de pago
        $paymentMethods = $tenant->paymentMethods()->get();
        foreach ($paymentMethods as $pm) {
            $pm->delete();
            $this->line("    - Método de pago eliminado");
        }
    }
}

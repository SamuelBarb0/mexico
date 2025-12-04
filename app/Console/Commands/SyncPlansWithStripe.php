<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPlan;
use Illuminate\Console\Command;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Price;

class SyncPlansWithStripe extends Command
{
    protected $signature = 'stripe:sync-plans';
    protected $description = 'Sincroniza los planes de suscripción con Stripe';

    public function handle()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $this->info('🔄 Sincronizando planes con Stripe...');
        $this->newLine();

        $plans = SubscriptionPlan::all();

        if ($plans->isEmpty()) {
            $this->warn('⚠️  No hay planes para sincronizar.');
            return 0;
        }

        $bar = $this->output->createProgressBar($plans->count());
        $bar->start();

        foreach ($plans as $plan) {
            try {
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

                    $this->newLine();
                    $this->info("✅ Producto creado: {$plan->name} ({$product->id})");
                }

                // Crear precio mensual si no existe
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

                    $this->newLine();
                    $this->info("💳 Precio mensual creado: $" . $plan->price_monthly . "/mes ({$priceMonthly->id})");
                }

                // Crear precio anual si no existe
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

                    $this->newLine();
                    $this->info("💳 Precio anual creado: $" . $plan->price_yearly . "/año ({$priceYearly->id})");
                }

                $bar->advance();

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ Error con plan {$plan->name}: " . $e->getMessage());
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✨ Sincronización completada!');

        return 0;
    }
}

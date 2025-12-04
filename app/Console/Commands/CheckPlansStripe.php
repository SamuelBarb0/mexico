<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPlan;
use Illuminate\Console\Command;

class CheckPlansStripe extends Command
{
    protected $signature = 'stripe:check-plans';
    protected $description = 'Verifica los IDs de Stripe de los planes';

    public function handle()
    {
        $plans = SubscriptionPlan::all();

        $this->info('=== ESTADO DE LOS PLANES ===');
        $this->newLine();

        foreach ($plans as $plan) {
            $this->info("📦 {$plan->name}");
            $this->line("   Producto: " . ($plan->stripe_product_id ?: '❌ NULL'));
            $this->line("   Precio Mensual: " . ($plan->stripe_price_id_monthly ?: '❌ NULL'));
            $this->line("   Precio Anual: " . ($plan->stripe_price_id_yearly ?: '❌ NULL'));
            $this->newLine();
        }

        return 0;
    }
}

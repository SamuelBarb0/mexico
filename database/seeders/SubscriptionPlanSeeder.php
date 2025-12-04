<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Plan gratuito para comenzar. Ideal para probar la plataforma.',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'currency' => 'usd',
                'has_trial' => true,
                'trial_days' => -1, // Unlimited trial
                'max_users' => 1,
                'max_contacts' => 100,
                'max_campaigns' => 3,
                'max_waba_accounts' => 1,
                'max_messages_per_month' => 500,
                'max_storage_mb' => 50,
                'features' => [
                    'Campañas básicas',
                    '1 cuenta WABA',
                    'Soporte por email',
                    'Dashboard básico',
                ],
                'restrictions' => [
                    'no_api_access',
                    'no_webhooks',
                    'no_advanced_analytics',
                ],
                'is_active' => true,
                'is_visible' => true,
                'is_default' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfecto para pequeñas empresas que están creciendo.',
                'price_monthly' => 29.00,
                'price_yearly' => 290.00, // 16% discount
                'currency' => 'usd',
                'has_trial' => true,
                'trial_days' => 14,
                'max_users' => 3,
                'max_contacts' => 1000,
                'max_campaigns' => 10,
                'max_waba_accounts' => 2,
                'max_messages_per_month' => 5000,
                'max_storage_mb' => 500,
                'features' => [
                    'Todo lo del plan Free',
                    'Campañas programadas',
                    'Múltiples usuarios',
                    '2 cuentas WABA',
                    'Analíticas básicas',
                    'Soporte prioritario',
                    'Plantillas personalizadas',
                ],
                'restrictions' => [],
                'is_active' => true,
                'is_visible' => true,
                'is_default' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Para empresas en crecimiento que necesitan funcionalidades avanzadas.',
                'price_monthly' => 79.00,
                'price_yearly' => 790.00, // 16% discount
                'currency' => 'usd',
                'has_trial' => true,
                'trial_days' => 14,
                'max_users' => 10,
                'max_contacts' => 10000,
                'max_campaigns' => 50,
                'max_waba_accounts' => 5,
                'max_messages_per_month' => 50000,
                'max_storage_mb' => 5000,
                'features' => [
                    'Todo lo del plan Starter',
                    'Campañas drip automatizadas',
                    'API access',
                    'Webhooks',
                    'Analíticas avanzadas',
                    'Segmentación avanzada',
                    'A/B testing',
                    'Integraciones premium',
                    'Soporte 24/7',
                ],
                'restrictions' => [],
                'is_active' => true,
                'is_visible' => true,
                'is_default' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Solución completa para grandes empresas con necesidades ilimitadas.',
                'price_monthly' => 199.00,
                'price_yearly' => 1990.00, // 16% discount
                'currency' => 'usd',
                'has_trial' => true,
                'trial_days' => 30,
                'max_users' => 50,
                'max_contacts' => 100000,
                'max_campaigns' => 200,
                'max_waba_accounts' => 20,
                'max_messages_per_month' => 500000,
                'max_storage_mb' => 50000,
                'features' => [
                    'Todo lo del plan Professional',
                    'Usuarios ilimitados',
                    'WhatsApp Business API dedicada',
                    'Integraciones personalizadas',
                    'Onboarding dedicado',
                    'Account manager',
                    'SLA garantizado',
                    'Soporte técnico prioritario',
                    'Reportes personalizados',
                    'Backup diario',
                    'Acceso a Beta features',
                ],
                'restrictions' => [],
                'is_active' => true,
                'is_visible' => true,
                'is_default' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }

        $this->command->info('✅ Subscription plans created successfully!');
    }
}

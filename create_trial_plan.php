<?php

/**
 * Script para crear un plan de prueba gratuito
 * Ejecutar con: php create_trial_plan.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SubscriptionPlan;

try {
    // Verificar si ya existe un plan gratuito
    $existingPlan = SubscriptionPlan::where('price_monthly', 0)
        ->where('price_yearly', 0)
        ->orWhere('name', 'like', '%free%')
        ->orWhere('name', 'like', '%trial%')
        ->first();

    if ($existingPlan) {
        echo "✓ Ya existe un plan gratuito/trial:\n";
        echo "  ID: {$existingPlan->id}\n";
        echo "  Nombre: {$existingPlan->name}\n";
        echo "  Precio mensual: \${$existingPlan->price_monthly}\n";
        echo "  Precio anual: \${$existingPlan->price_yearly}\n";
        echo "  Límites:\n";
        echo "    - Usuarios: {$existingPlan->max_users}\n";
        echo "    - Contactos: {$existingPlan->max_contacts}\n";
        echo "    - Campañas: {$existingPlan->max_campaigns}\n";
        echo "    - Cuentas WABA: {$existingPlan->max_waba_accounts}\n";
        echo "    - Mensajes/mes: {$existingPlan->max_messages_per_month}\n";
        exit(0);
    }

    // Crear plan de prueba gratuito
    $plan = SubscriptionPlan::create([
        'name' => 'Plan de Prueba',
        'slug' => 'trial-plan',
        'description' => 'Plan gratuito de 14 días para probar todas las funcionalidades',
        'price_monthly' => 0.00,
        'price_yearly' => 0.00,
        'currency' => 'USD',
        'has_trial' => true,
        'trial_days' => 14,
        'max_users' => 2,
        'max_contacts' => 100,
        'max_campaigns' => 10,
        'max_waba_accounts' => 1,
        'max_messages_per_month' => 1000,
        'max_storage_mb' => 100,
        'features' => [
            'whatsapp_integration' => true,
            'campaign_management' => true,
            'contact_import' => true,
            'basic_analytics' => true,
            'email_support' => true,
        ],
        'is_active' => true,
        'is_visible' => true,
        'is_default' => true,
        'sort_order' => 1,
    ]);

    echo "✓ Plan de prueba creado exitosamente!\n";
    echo "  ID: {$plan->id}\n";
    echo "  Nombre: {$plan->name}\n";
    echo "  Precio mensual: \${$plan->price_monthly}\n";
    echo "  Precio anual: \${$plan->price_yearly}\n";
    echo "\n";
    echo "  Límites:\n";
    echo "    - Usuarios: {$plan->max_users}\n";
    echo "    - Contactos: {$plan->max_contacts}\n";
    echo "    - Campañas: {$plan->max_campaigns}\n";
    echo "    - Cuentas WABA: {$plan->max_waba_accounts}\n";
    echo "    - Mensajes por mes: {$plan->max_messages_per_month}\n";
    echo "    - Almacenamiento: {$plan->max_storage_mb}MB\n";
    echo "\n";
    echo "  Características:\n";
    foreach ($plan->features as $key => $value) {
        $status = $value ? '✓' : '✗';
        echo "    {$status} {$key}\n";
    }

} catch (\Exception $e) {
    echo "✗ Error al crear el plan: {$e->getMessage()}\n";
    exit(1);
}

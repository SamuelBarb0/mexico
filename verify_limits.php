<?php

/**
 * Script para verificar el sistema de límites
 * Ejecutar con: php verify_limits.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tenant;

try {
    echo "========================================\n";
    echo "VERIFICACIÓN DEL SISTEMA DE LÍMITES\n";
    echo "========================================\n\n";

    $tenants = Tenant::with(['limits', 'subscriptions' => function($q) {
        $q->whereIn('status', ['trial', 'active'])->latest()->limit(1);
    }, 'subscriptions.plan'])->get();

    if ($tenants->isEmpty()) {
        echo "❌ No hay tenants en el sistema\n";
        exit(1);
    }

    foreach ($tenants as $tenant) {
        echo "Tenant: {$tenant->name} (ID: {$tenant->id})\n";
        echo str_repeat('-', 50) . "\n";

        $subscription = $tenant->currentSubscription();

        if ($subscription) {
            echo "📋 Suscripción: {$subscription->plan->name}\n";
            echo "   Estado: {$subscription->status}\n";

            if ($subscription->isOnTrial()) {
                echo "   🎉 En período de prueba - {$subscription->trialDaysRemaining()} días restantes\n";
            }
        } else {
            echo "⚠️  Sin suscripción activa\n";
        }

        echo "\n";

        if ($tenant->limits) {
            $limits = $tenant->limits;

            echo "📊 USO DE RECURSOS:\n\n";

            // Contactos
            $contactsPercent = $limits->max_contacts > 0
                ? round(($limits->current_contacts / $limits->max_contacts) * 100)
                : 0;
            echo "  📇 Contactos:     {$limits->current_contacts} / {$limits->max_contacts} ({$contactsPercent}%)\n";

            // Campañas
            $campaignsPercent = $limits->max_campaigns > 0
                ? round(($limits->current_campaigns / $limits->max_campaigns) * 100)
                : 0;
            echo "  📢 Campañas:      {$limits->current_campaigns} / {$limits->max_campaigns} ({$campaignsPercent}%)\n";

            // Usuarios
            $usersPercent = $limits->max_users > 0
                ? round(($limits->current_users / $limits->max_users) * 100)
                : 0;
            echo "  👥 Usuarios:      {$limits->current_users} / {$limits->max_users} ({$usersPercent}%)\n";

            // Cuentas WABA
            $wabaPercent = $limits->max_waba_accounts > 0
                ? round(($limits->current_waba_accounts / $limits->max_waba_accounts) * 100)
                : 0;
            echo "  📱 Cuentas WABA:  {$limits->current_waba_accounts} / {$limits->max_waba_accounts} ({$wabaPercent}%)\n";

            // Mensajes
            $messagesPercent = $limits->max_messages_per_month > 0
                ? round(($limits->current_messages_this_month / $limits->max_messages_per_month) * 100)
                : 0;
            echo "  💬 Mensajes/mes:  {$limits->current_messages_this_month} / {$limits->max_messages_per_month} ({$messagesPercent}%)\n";

            echo "\n";

            // Verificar si puede agregar recursos
            echo "✅ PERMISOS:\n\n";
            echo "  " . ($limits->canAddContact() ? "✓" : "✗") . " Puede agregar contactos\n";
            echo "  " . ($limits->canAddCampaign() ? "✓" : "✗") . " Puede crear campañas\n";
            echo "  " . ($limits->canAddUser() ? "✓" : "✗") . " Puede agregar usuarios\n";
            echo "  " . ($limits->canAddWabaAccount() ? "✓" : "✗") . " Puede agregar cuentas WABA\n";
            echo "  " . ($limits->canSendMessage() ? "✓" : "✗") . " Puede enviar mensajes\n";

        } else {
            echo "❌ NO TIENE LÍMITES CONFIGURADOS\n";
        }

        echo "\n" . str_repeat('=', 50) . "\n\n";
    }

    echo "✓ Verificación completada\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}

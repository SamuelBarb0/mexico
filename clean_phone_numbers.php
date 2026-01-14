<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Contact;

echo "=== Limpiando números de teléfono ===\n\n";

// Get all contacts with + in phone number
$contactsWithPlus = Contact::where('phone', 'like', '+%')->get();

echo "Contactos con '+' encontrados: " . $contactsWithPlus->count() . "\n\n";

$updated = 0;
$skipped = 0;

foreach ($contactsWithPlus as $contact) {
    $cleanPhone = ltrim($contact->phone, '+');

    // Check if a contact with the clean phone already exists
    $existing = Contact::where('tenant_id', $contact->tenant_id)
        ->where('phone', $cleanPhone)
        ->where('id', '!=', $contact->id)
        ->first();

    if ($existing) {
        echo "⚠️ Duplicado encontrado - Eliminando contacto ID {$contact->id} ({$contact->phone})\n";
        echo "   Ya existe: ID {$existing->id} ({$existing->phone})\n";
        $contact->delete();
        $skipped++;
    } else {
        echo "✅ Actualizando: {$contact->phone} → {$cleanPhone}\n";
        $contact->phone = $cleanPhone;
        $contact->save();
        $updated++;
    }
}

echo "\n=== Resumen ===\n";
echo "Actualizados: {$updated}\n";
echo "Eliminados (duplicados): {$skipped}\n";
echo "Total procesados: " . ($updated + $skipped) . "\n";

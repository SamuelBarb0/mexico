<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Contact;
use App\Models\Tenant;
use App\Jobs\ImportContactsFromCsvJob;
use Illuminate\Support\Facades\Storage;

echo "=== Test Import de Contactos ===\n\n";

// Get first tenant
$tenant = Tenant::first();
if (!$tenant) {
    echo "❌ No hay tenant disponible\n";
    exit(1);
}

echo "✅ Tenant encontrado: {$tenant->name} (ID: {$tenant->id})\n\n";

// Copy example CSV to storage
$sourceFile = __DIR__ . '/ejemplo_contactos_simple.csv';
$destinationPath = 'imports/test_' . time() . '.csv';

if (!file_exists($sourceFile)) {
    echo "❌ Archivo de ejemplo no encontrado: {$sourceFile}\n";
    exit(1);
}

Storage::disk('local')->put($destinationPath, file_get_contents($sourceFile));
echo "✅ Archivo CSV copiado a: {$destinationPath}\n\n";

// Create mapping
$mapping = [
    'telefono' => 'phone',
    'nombre' => 'name',
];

echo "Mapeo configurado:\n";
foreach ($mapping as $csvCol => $field) {
    echo "  - {$csvCol} → {$field}\n";
}
echo "\n";

// Count contacts before
$contactsBefore = Contact::where('tenant_id', $tenant->id)->count();
echo "📊 Contactos antes de importar: {$contactsBefore}\n\n";

// Dispatch job
echo "🚀 Ejecutando job de importación...\n";
ImportContactsFromCsvJob::dispatchSync($tenant, $destinationPath, $mapping, null);

// Count contacts after
$contactsAfter = Contact::where('tenant_id', $tenant->id)->count();
echo "\n📊 Contactos después de importar: {$contactsAfter}\n";
echo "✅ Contactos nuevos creados: " . ($contactsAfter - $contactsBefore) . "\n\n";

// Show last 5 contacts
echo "=== Últimos 5 contactos ===\n";
$lastContacts = Contact::where('tenant_id', $tenant->id)
    ->latest()
    ->take(5)
    ->get();

foreach ($lastContacts as $contact) {
    echo sprintf(
        "- %s (%s) - Creado: %s\n",
        $contact->name ?? 'Sin nombre',
        $contact->phone,
        $contact->created_at->format('Y-m-d H:i:s')
    );
}

echo "\n✅ Test completado exitosamente!\n";

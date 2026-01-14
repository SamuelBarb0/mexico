<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Verificando Contactos ===\n\n";

$totalContacts = App\Models\Contact::count();
echo "Total de contactos: {$totalContacts}\n\n";

echo "=== Todos los contactos ===\n";
$contacts = App\Models\Contact::orderBy('created_at', 'desc')->get();

foreach ($contacts as $contact) {
    echo sprintf(
        "ID: %d | Nombre: %s | Teléfono: %s | Email: %s | Creado: %s\n",
        $contact->id,
        $contact->name ?? 'Sin nombre',
        $contact->phone,
        $contact->email ?? 'Sin email',
        $contact->created_at->format('Y-m-d H:i:s')
    );
}

echo "\n=== Contactos por tenant ===\n";
$tenants = App\Models\Tenant::with('contacts')->get();
foreach ($tenants as $tenant) {
    echo sprintf(
        "- %s: %d contactos\n",
        $tenant->name,
        $tenant->contacts->count()
    );
}

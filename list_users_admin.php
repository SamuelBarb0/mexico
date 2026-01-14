<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Lista de Usuarios ===\n\n";

$users = User::with('tenant')->get();

echo "ID | Nombre | Email | Tipo | Tenant\n";
echo str_repeat("-", 80) . "\n";

foreach ($users as $user) {
    $tenantName = $user->tenant ? $user->tenant->name : 'Ninguno';
    echo sprintf(
        "%d | %s | %s | %s | %s\n",
        $user->id,
        $user->name,
        $user->email,
        $user->user_type,
        $tenantName
    );
}

echo "\n=== Instrucciones ===\n";
echo "Para convertir un usuario en Platform Admin, ejecuta en MySQL:\n\n";
echo "UPDATE users SET user_type = 'platform_admin', tenant_id = NULL WHERE id = [ID_DEL_USUARIO];\n";
echo "\nEjemplo: UPDATE users SET user_type = 'platform_admin', tenant_id = NULL WHERE id = 1;\n";

<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Convertir Usuario a Platform Admin ===\n\n";

// Obtener el email del usuario
echo "Ingresa el email del usuario a convertir en Platform Admin:\n";
$email = trim(fgets(STDIN));

if (empty($email)) {
    echo "❌ Error: Debes ingresar un email\n";
    exit(1);
}

// Buscar el usuario
$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ Error: Usuario con email '{$email}' no encontrado\n";
    exit(1);
}

echo "\n📋 Usuario encontrado:\n";
echo "   ID: {$user->id}\n";
echo "   Nombre: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   Tipo actual: {$user->user_type}\n";
echo "   Tenant: " . ($user->tenant ? $user->tenant->name : 'Ninguno') . "\n";

if ($user->user_type === 'platform_admin') {
    echo "\n✅ Este usuario ya es Platform Admin\n";
    exit(0);
}

echo "\n⚠️  ¿Estás seguro de convertir este usuario en Platform Admin? (s/n): ";
$confirm = trim(fgets(STDIN));

if (strtolower($confirm) !== 's') {
    echo "❌ Operación cancelada\n";
    exit(0);
}

// Convertir a Platform Admin
$user->user_type = 'platform_admin';
$user->tenant_id = null; // Los Platform Admins no pertenecen a ningún tenant
$user->save();

echo "\n✅ ¡Usuario convertido exitosamente a Platform Admin!\n";
echo "\n📋 Detalles actualizados:\n";
echo "   Tipo: {$user->user_type}\n";
echo "   Tenant: Ninguno (Platform Admin)\n";
echo "\n🎉 Ahora puedes acceder al panel de administración en: /admin/tenants\n";

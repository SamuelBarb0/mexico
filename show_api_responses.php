<?php
/**
 * Script para mostrar ejemplos de respuestas de la API
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = env('APP_URL', 'http://localhost') . '/api/v1';

// Obtener usuario y crear token
$user = App\Models\User::first();
if (!$user) {
    echo "No hay usuarios\n";
    exit(1);
}

$token = $user->createToken('show-api')->plainTextToken;

$headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
];

echo "\n=== EJEMPLOS DE RESPUESTAS DE LA API ===\n\n";

// 1. Auth/me
echo "1. GET /api/v1/auth/me\n";
echo str_repeat("-", 50) . "\n";
$response = Http::withHeaders($headers)->get($baseUrl . '/auth/me');
echo json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// 2. Contacts
echo "2. GET /api/v1/contacts\n";
echo str_repeat("-", 50) . "\n";
$response = Http::withHeaders($headers)->get($baseUrl . '/contacts?per_page=2');
echo json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// 3. Templates
echo "3. GET /api/v1/templates\n";
echo str_repeat("-", 50) . "\n";
$response = Http::withHeaders($headers)->get($baseUrl . '/templates?per_page=2');
echo json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// 4. Campaigns
echo "4. GET /api/v1/campaigns\n";
echo str_repeat("-", 50) . "\n";
$response = Http::withHeaders($headers)->get($baseUrl . '/campaigns?per_page=2');
echo json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// 5. Messages
echo "5. GET /api/v1/messages\n";
echo str_repeat("-", 50) . "\n";
$response = Http::withHeaders($headers)->get($baseUrl . '/messages?per_page=2');
echo json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// 6. WABA Accounts
echo "6. GET /api/v1/waba-accounts\n";
echo str_repeat("-", 50) . "\n";
$response = Http::withHeaders($headers)->get($baseUrl . '/waba-accounts');
echo json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Limpiar token
$user->tokens()->where('name', 'show-api')->delete();

echo "=== FIN DE EJEMPLOS ===\n";
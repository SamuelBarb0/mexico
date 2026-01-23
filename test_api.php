<?php
/**
 * Script de prueba para verificar todos los endpoints de la API
 * Ejecutar: php test_api.php
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

// Configuración
$baseUrl = env('APP_URL', 'http://localhost') . '/api/v1';

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         PRUEBA COMPLETA DE API - WHATSAPP SAAS              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Primero necesitamos obtener un token
echo "1. AUTENTICACIÓN\n";
echo str_repeat("─", 60) . "\n";

// Buscar un usuario para hacer login
$user = App\Models\User::first();
if (!$user) {
    echo "❌ No hay usuarios en la base de datos\n";
    exit(1);
}

echo "   Usuario encontrado: {$user->email}\n";

// Crear token directamente para pruebas
$token = $user->createToken('test-api')->plainTextToken;
echo "   ✅ Token generado exitosamente\n\n";

$headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
    'Content-Type' => 'application/json',
];

$results = [];
$passed = 0;
$failed = 0;

// Función helper para hacer requests
function testEndpoint($method, $url, $headers, $body = null, $description = '') {
    global $baseUrl;
    $fullUrl = $baseUrl . $url;

    echo "   Testing: {$method} {$url}\n";
    echo "   Descripción: {$description}\n";

    try {
        $response = match(strtoupper($method)) {
            'GET' => Http::withHeaders($headers)->get($fullUrl),
            'POST' => Http::withHeaders($headers)->post($fullUrl, $body ?? []),
            'PUT' => Http::withHeaders($headers)->put($fullUrl, $body ?? []),
            'DELETE' => Http::withHeaders($headers)->delete($fullUrl),
            default => throw new Exception("Método no soportado: {$method}")
        };

        $status = $response->status();
        $data = $response->json();

        $isSuccess = $status >= 200 && $status < 300;
        $icon = $isSuccess ? '✅' : '❌';

        echo "   {$icon} Status: {$status}\n";

        if (!$isSuccess && isset($data['message'])) {
            echo "   Error: {$data['message']}\n";
        }

        return [
            'success' => $isSuccess,
            'status' => $status,
            'data' => $data,
        ];
    } catch (Exception $e) {
        echo "   ❌ Exception: {$e->getMessage()}\n";
        return [
            'success' => false,
            'status' => 0,
            'error' => $e->getMessage(),
        ];
    }
}

// ═══════════════════════════════════════════════════════════════
// 2. PRUEBAS DE AUTENTICACIÓN
// ═══════════════════════════════════════════════════════════════
echo "\n2. ENDPOINTS DE AUTENTICACIÓN\n";
echo str_repeat("─", 60) . "\n";

// GET /auth/me
$result = testEndpoint('GET', '/auth/me', $headers, null, 'Obtener usuario actual');
if ($result['success']) $passed++; else $failed++;
echo "\n";

// ═══════════════════════════════════════════════════════════════
// 3. PRUEBAS DE CONTACTOS
// ═══════════════════════════════════════════════════════════════
echo "\n3. ENDPOINTS DE CONTACTOS\n";
echo str_repeat("─", 60) . "\n";

// GET /contacts
$result = testEndpoint('GET', '/contacts', $headers, null, 'Listar contactos');
if ($result['success']) $passed++; else $failed++;
echo "\n";

// POST /contacts (crear)
$newContact = [
    'name' => 'Test API Contact',
    'phone' => '+52' . rand(1000000000, 9999999999),
    'email' => 'test' . rand(1000, 9999) . '@example.com',
];
$result = testEndpoint('POST', '/contacts', $headers, $newContact, 'Crear contacto');
if ($result['success']) {
    $passed++;
    $contactId = $result['data']['data']['id'] ?? null;
    echo "   Contacto creado con ID: {$contactId}\n";
} else {
    $failed++;
    $contactId = null;
}
echo "\n";

// GET /contacts/{id}
if ($contactId) {
    $result = testEndpoint('GET', "/contacts/{$contactId}", $headers, null, 'Obtener contacto por ID');
    if ($result['success']) $passed++; else $failed++;
    echo "\n";
}

// ═══════════════════════════════════════════════════════════════
// 4. PRUEBAS DE PLANTILLAS
// ═══════════════════════════════════════════════════════════════
echo "\n4. ENDPOINTS DE PLANTILLAS\n";
echo str_repeat("─", 60) . "\n";

// GET /templates
$result = testEndpoint('GET', '/templates', $headers, null, 'Listar plantillas');
if ($result['success']) {
    $passed++;
    $templates = $result['data']['data'] ?? [];
    $templateId = $templates[0]['id'] ?? null;
} else {
    $failed++;
    $templateId = null;
}
echo "\n";

// GET /templates/{id}
if ($templateId) {
    $result = testEndpoint('GET', "/templates/{$templateId}", $headers, null, 'Obtener plantilla por ID');
    if ($result['success']) $passed++; else $failed++;
    echo "\n";
}

// ═══════════════════════════════════════════════════════════════
// 5. PRUEBAS DE CAMPAÑAS
// ═══════════════════════════════════════════════════════════════
echo "\n5. ENDPOINTS DE CAMPAÑAS\n";
echo str_repeat("─", 60) . "\n";

// GET /campaigns
$result = testEndpoint('GET', '/campaigns', $headers, null, 'Listar campañas');
if ($result['success']) {
    $passed++;
    $campaigns = $result['data']['data'] ?? [];
    $campaignId = $campaigns[0]['id'] ?? null;
} else {
    $failed++;
    $campaignId = null;
}
echo "\n";

// GET /campaigns/{id}
if ($campaignId) {
    $result = testEndpoint('GET', "/campaigns/{$campaignId}", $headers, null, 'Obtener campaña por ID');
    if ($result['success']) $passed++; else $failed++;
    echo "\n";

    // GET /campaigns/{id}/stats
    $result = testEndpoint('GET', "/campaigns/{$campaignId}/stats", $headers, null, 'Estadísticas de campaña');
    if ($result['success']) $passed++; else $failed++;
    echo "\n";
}

// ═══════════════════════════════════════════════════════════════
// 6. PRUEBAS DE MENSAJES
// ═══════════════════════════════════════════════════════════════
echo "\n6. ENDPOINTS DE MENSAJES\n";
echo str_repeat("─", 60) . "\n";

// GET /messages
$result = testEndpoint('GET', '/messages', $headers, null, 'Listar mensajes');
if ($result['success']) $passed++; else $failed++;
echo "\n";

// ═══════════════════════════════════════════════════════════════
// 7. PRUEBAS DE CUENTAS WABA
// ═══════════════════════════════════════════════════════════════
echo "\n7. ENDPOINTS DE CUENTAS WABA\n";
echo str_repeat("─", 60) . "\n";

// GET /waba-accounts
$result = testEndpoint('GET', '/waba-accounts', $headers, null, 'Listar cuentas WABA');
if ($result['success']) $passed++; else $failed++;
echo "\n";

// ═══════════════════════════════════════════════════════════════
// 8. LIMPIEZA
// ═══════════════════════════════════════════════════════════════
echo "\n8. LIMPIEZA\n";
echo str_repeat("─", 60) . "\n";

// Eliminar contacto de prueba
if ($contactId) {
    $result = testEndpoint('DELETE', "/contacts/{$contactId}", $headers, null, 'Eliminar contacto de prueba');
    if ($result['success']) $passed++; else $failed++;
    echo "\n";
}

// Revocar token de prueba
$user->tokens()->where('name', 'test-api')->delete();
echo "   ✅ Token de prueba revocado\n\n";

// ═══════════════════════════════════════════════════════════════
// RESUMEN
// ═══════════════════════════════════════════════════════════════
echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║                      RESUMEN DE PRUEBAS                      ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

echo "   Total de pruebas: {$total}\n";
echo "   ✅ Exitosas: {$passed}\n";
echo "   ❌ Fallidas: {$failed}\n";
echo "   Porcentaje de éxito: {$percentage}%\n\n";

if ($failed === 0) {
    echo "   🎉 ¡TODAS LAS PRUEBAS PASARON EXITOSAMENTE!\n\n";
} else {
    echo "   ⚠️  Hay {$failed} prueba(s) que necesitan atención.\n\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
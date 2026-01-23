<?php
/**
 * Test detallado de API con request y response completos
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = env('APP_URL', 'http://localhost') . '/api/v1';

$user = App\Models\User::first();
$token = $user->createToken('detailed-test')->plainTextToken;

$headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
    'Content-Type' => 'application/json',
];

function testEndpoint($method, $url, $headers, $body = null, $desc = '') {
    global $baseUrl;
    $fullUrl = $baseUrl . $url;

    echo "\n" . str_repeat("═", 70) . "\n";
    echo "📌 {$desc}\n";
    echo str_repeat("─", 70) . "\n";
    echo "📤 REQUEST: {$method} {$url}\n";

    if ($body) {
        echo "📦 BODY:\n";
        echo json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }

    try {
        $response = match(strtoupper($method)) {
            'GET' => Http::withHeaders($headers)->get($fullUrl),
            'POST' => Http::withHeaders($headers)->post($fullUrl, $body ?? []),
            'PUT' => Http::withHeaders($headers)->put($fullUrl, $body ?? []),
            'DELETE' => Http::withHeaders($headers)->delete($fullUrl),
        };

        $status = $response->status();
        $ok = $status >= 200 && $status < 300;
        $icon = $ok ? '✅' : '❌';

        echo "\n📥 RESPONSE: {$icon} Status {$status}\n";
        echo json_encode($response->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

        return ['ok' => $ok, 'status' => $status, 'data' => $response->json()];
    } catch (Exception $e) {
        echo "\n❌ ERROR: {$e->getMessage()}\n";
        return ['ok' => false];
    }
}

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║        TEST DETALLADO DE API - REQUEST Y RESPONSE COMPLETOS         ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n";

// ═══════════════════════════════════════════════════════════════
// 1. AUTH - Login (sin autenticación)
// ═══════════════════════════════════════════════════════════════
echo "\n\n▶▶▶ SECCIÓN: AUTENTICACIÓN ◀◀◀\n";

$loginData = [
    'email' => 'demo@admin.com',
    'password' => 'password',
    'device_name' => 'api-test'
];
testEndpoint('POST', '/auth/login', ['Accept' => 'application/json', 'Content-Type' => 'application/json'], $loginData, 'POST /auth/login - Iniciar sesión');

testEndpoint('GET', '/auth/me', $headers, null, 'GET /auth/me - Usuario actual');

// ═══════════════════════════════════════════════════════════════
// 2. CONTACTS
// ═══════════════════════════════════════════════════════════════
echo "\n\n▶▶▶ SECCIÓN: CONTACTOS ◀◀◀\n";

testEndpoint('GET', '/contacts?per_page=3', $headers, null, 'GET /contacts - Listar contactos (3 por página)');

testEndpoint('GET', '/contacts?search=Alejandro&status=active', $headers, null, 'GET /contacts - Buscar con filtros');

$newContact = [
    'name' => 'Juan Pérez Test API',
    'phone' => '+5215512345678',
    'email' => 'juan.perez@test.com',
    'status' => 'active'
];
$r = testEndpoint('POST', '/contacts', $headers, $newContact, 'POST /contacts - Crear nuevo contacto');
$contactId = $r['data']['data']['id'] ?? null;

if ($contactId) {
    testEndpoint('GET', "/contacts/{$contactId}", $headers, null, "GET /contacts/{$contactId} - Obtener contacto por ID");

    $updateData = [
        'name' => 'Juan Pérez Actualizado',
        'email' => 'juan.actualizado@test.com'
    ];
    testEndpoint('PUT', "/contacts/{$contactId}", $headers, $updateData, "PUT /contacts/{$contactId} - Actualizar contacto");
}

// ═══════════════════════════════════════════════════════════════
// 3. CAMPAIGNS
// ═══════════════════════════════════════════════════════════════
echo "\n\n▶▶▶ SECCIÓN: CAMPAÑAS ◀◀◀\n";

$r = testEndpoint('GET', '/campaigns?per_page=2', $headers, null, 'GET /campaigns - Listar campañas');

testEndpoint('GET', '/campaigns?status=draft&per_page=2', $headers, null, 'GET /campaigns - Filtrar por estado draft');

$campaigns = $r['data']['data'] ?? [];
$campaignId = $campaigns[0]['id'] ?? null;

if ($campaignId) {
    testEndpoint('GET', "/campaigns/{$campaignId}", $headers, null, "GET /campaigns/{$campaignId} - Obtener campaña con detalles");
    testEndpoint('GET', "/campaigns/{$campaignId}/stats", $headers, null, "GET /campaigns/{$campaignId}/stats - Estadísticas de campaña");
}

// ═══════════════════════════════════════════════════════════════
// 4. MESSAGES
// ═══════════════════════════════════════════════════════════════
echo "\n\n▶▶▶ SECCIÓN: MENSAJES ◀◀◀\n";

$r = testEndpoint('GET', '/messages?per_page=2', $headers, null, 'GET /messages - Listar mensajes');

testEndpoint('GET', '/messages?direction=outbound&status=failed&per_page=2', $headers, null, 'GET /messages - Filtrar outbound fallidos');

$messages = $r['data']['data'] ?? [];
$messageId = $messages[0]['id'] ?? null;

if ($messageId) {
    testEndpoint('GET', "/messages/{$messageId}/status", $headers, null, "GET /messages/{$messageId}/status - Estado del mensaje");
}

// Conversación
$contact = App\Models\Contact::where('tenant_id', $user->tenant_id)->first();
if ($contact) {
    testEndpoint('GET', "/messages/conversation/{$contact->id}?per_page=5", $headers, null, "GET /messages/conversation/{$contact->id} - Conversación con contacto");
}

// ═══════════════════════════════════════════════════════════════
// 5. TEMPLATES
// ═══════════════════════════════════════════════════════════════
echo "\n\n▶▶▶ SECCIÓN: PLANTILLAS ◀◀◀\n";

$r = testEndpoint('GET', '/templates?per_page=2', $headers, null, 'GET /templates - Listar plantillas');

testEndpoint('GET', '/templates?status=APPROVED&category=UTILITY', $headers, null, 'GET /templates - Filtrar APPROVED y UTILITY');

$templates = $r['data']['data'] ?? [];
$templateId = $templates[0]['id'] ?? null;

if ($templateId) {
    testEndpoint('GET', "/templates/{$templateId}", $headers, null, "GET /templates/{$templateId} - Obtener plantilla completa");
}

$waba = App\Models\WabaAccount::where('tenant_id', $user->tenant_id)->first();
if ($waba) {
    testEndpoint('GET', "/templates/waba/{$waba->id}", $headers, null, "GET /templates/waba/{$waba->id} - Plantillas aprobadas de WABA");
}

// ═══════════════════════════════════════════════════════════════
// 6. WABA ACCOUNTS
// ═══════════════════════════════════════════════════════════════
echo "\n\n▶▶▶ SECCIÓN: CUENTAS WABA ◀◀◀\n";

$r = testEndpoint('GET', '/waba-accounts', $headers, null, 'GET /waba-accounts - Listar cuentas WABA');

$accounts = $r['data']['data'] ?? [];
$wabaId = $accounts[0]['id'] ?? null;

if ($wabaId) {
    testEndpoint('GET', "/waba-accounts/{$wabaId}", $headers, null, "GET /waba-accounts/{$wabaId} - Detalle de cuenta WABA");
    testEndpoint('GET', "/waba-accounts/{$wabaId}/stats", $headers, null, "GET /waba-accounts/{$wabaId}/stats - Estadísticas WABA");
}

// ═══════════════════════════════════════════════════════════════
// CLEANUP
// ═══════════════════════════════════════════════════════════════
echo "\n\n▶▶▶ LIMPIEZA ◀◀◀\n";

if ($contactId) {
    testEndpoint('DELETE', "/contacts/{$contactId}", $headers, null, "DELETE /contacts/{$contactId} - Eliminar contacto de prueba");
}

// Logout
testEndpoint('POST', '/auth/logout', $headers, null, 'POST /auth/logout - Cerrar sesión');

// Limpiar tokens de prueba
$user->tokens()->where('name', 'detailed-test')->delete();

echo "\n" . str_repeat("═", 70) . "\n";
echo "✅ TEST COMPLETADO\n";
echo str_repeat("═", 70) . "\n\n";
<?php
/**
 * Test completo de TODOS los endpoints de la API
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = env('APP_URL', 'http://localhost') . '/api/v1';

$user = App\Models\User::first();
$token = $user->createToken('full-test')->plainTextToken;

$headers = [
    'Authorization' => 'Bearer ' . $token,
    'Accept' => 'application/json',
    'Content-Type' => 'application/json',
];

$results = [];

function test($method, $url, $headers, $body = null, $desc = '') {
    global $baseUrl, $results;
    $fullUrl = $baseUrl . $url;

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

        $results[] = ['ok' => $ok, 'endpoint' => "$method $url"];
        echo "{$icon} {$method} {$url} => {$status} ({$desc})\n";

        return ['ok' => $ok, 'status' => $status, 'data' => $response->json()];
    } catch (Exception $e) {
        $results[] = ['ok' => false, 'endpoint' => "$method $url"];
        echo "❌ {$method} {$url} => ERROR: {$e->getMessage()}\n";
        return ['ok' => false];
    }
}

echo "\n══════════════════════════════════════════════════════════════\n";
echo "           TEST COMPLETO DE TODOS LOS ENDPOINTS API\n";
echo "══════════════════════════════════════════════════════════════\n\n";

// ═══════════════════════════════════════════════════════════════
// AUTH
// ═══════════════════════════════════════════════════════════════
echo "▶ AUTH\n";
echo "──────────────────────────────────────────────────────────────\n";
test('GET', '/auth/me', $headers, null, 'Usuario actual');

// ═══════════════════════════════════════════════════════════════
// CONTACTS
// ═══════════════════════════════════════════════════════════════
echo "\n▶ CONTACTS\n";
echo "──────────────────────────────────────────────────────────────\n";
test('GET', '/contacts', $headers, null, 'Listar contactos');
test('GET', '/contacts?search=test', $headers, null, 'Buscar contactos');
test('GET', '/contacts?status=active', $headers, null, 'Filtrar por estado');

// Crear contacto
$newContact = [
    'name' => 'API Test ' . time(),
    'phone' => '+52' . rand(1000000000, 9999999999),
];
$r = test('POST', '/contacts', $headers, $newContact, 'Crear contacto');
$contactId = $r['data']['data']['id'] ?? null;

if ($contactId) {
    test('GET', "/contacts/{$contactId}", $headers, null, 'Obtener contacto');
    test('PUT', "/contacts/{$contactId}", $headers, ['name' => 'Updated Name'], 'Actualizar contacto');
}

// ═══════════════════════════════════════════════════════════════
// CAMPAIGNS
// ═══════════════════════════════════════════════════════════════
echo "\n▶ CAMPAIGNS\n";
echo "──────────────────────────────────────────────────────────────\n";
$r = test('GET', '/campaigns', $headers, null, 'Listar campañas');
test('GET', '/campaigns?status=draft', $headers, null, 'Filtrar por estado');

$campaigns = $r['data']['data'] ?? [];
$campaignId = $campaigns[0]['id'] ?? null;

if ($campaignId) {
    test('GET', "/campaigns/{$campaignId}", $headers, null, 'Obtener campaña');
    test('GET', "/campaigns/{$campaignId}/stats", $headers, null, 'Estadísticas de campaña');
}

// ═══════════════════════════════════════════════════════════════
// MESSAGES
// ═══════════════════════════════════════════════════════════════
echo "\n▶ MESSAGES\n";
echo "──────────────────────────────────────────────────────────────\n";
$r = test('GET', '/messages', $headers, null, 'Listar mensajes');
test('GET', '/messages?direction=outbound', $headers, null, 'Filtrar por dirección');
test('GET', '/messages?status=sent', $headers, null, 'Filtrar por estado');

$messages = $r['data']['data'] ?? [];
$messageId = $messages[0]['id'] ?? null;

if ($messageId) {
    test('GET', "/messages/{$messageId}/status", $headers, null, 'Estado del mensaje');
}

// Conversación (si hay contacto)
$contact = App\Models\Contact::where('tenant_id', $user->tenant_id)->first();
if ($contact) {
    test('GET', "/messages/conversation/{$contact->id}", $headers, null, 'Conversación con contacto');
}

// ═══════════════════════════════════════════════════════════════
// TEMPLATES
// ═══════════════════════════════════════════════════════════════
echo "\n▶ TEMPLATES\n";
echo "──────────────────────────────────────────────────────────────\n";
$r = test('GET', '/templates', $headers, null, 'Listar plantillas');
test('GET', '/templates?status=APPROVED', $headers, null, 'Filtrar por estado');
test('GET', '/templates?category=MARKETING', $headers, null, 'Filtrar por categoría');
test('GET', '/templates?search=hello', $headers, null, 'Buscar plantillas');

$templates = $r['data']['data'] ?? [];
$templateId = $templates[0]['id'] ?? null;

if ($templateId) {
    test('GET', "/templates/{$templateId}", $headers, null, 'Obtener plantilla');
}

// Templates por WABA
$waba = App\Models\WabaAccount::where('tenant_id', $user->tenant_id)->first();
if ($waba) {
    test('GET', "/templates/waba/{$waba->id}", $headers, null, 'Plantillas por WABA');
}

// ═══════════════════════════════════════════════════════════════
// WABA ACCOUNTS
// ═══════════════════════════════════════════════════════════════
echo "\n▶ WABA ACCOUNTS\n";
echo "──────────────────────────────────────────────────────────────\n";
$r = test('GET', '/waba-accounts', $headers, null, 'Listar cuentas WABA');
test('GET', '/waba-accounts?status=active', $headers, null, 'Filtrar por estado');

$accounts = $r['data']['data'] ?? [];
$wabaId = $accounts[0]['id'] ?? null;

if ($wabaId) {
    test('GET', "/waba-accounts/{$wabaId}", $headers, null, 'Obtener cuenta WABA');
    test('GET', "/waba-accounts/{$wabaId}/stats", $headers, null, 'Estadísticas WABA');
}

// ═══════════════════════════════════════════════════════════════
// CLEANUP
// ═══════════════════════════════════════════════════════════════
echo "\n▶ CLEANUP\n";
echo "──────────────────────────────────────────────────────────────\n";
if ($contactId) {
    test('DELETE', "/contacts/{$contactId}", $headers, null, 'Eliminar contacto test');
}

// Limpiar token
$user->tokens()->where('name', 'full-test')->delete();
echo "✅ Token revocado\n";

// ═══════════════════════════════════════════════════════════════
// RESUMEN
// ═══════════════════════════════════════════════════════════════
echo "\n══════════════════════════════════════════════════════════════\n";
echo "                        RESUMEN\n";
echo "══════════════════════════════════════════════════════════════\n\n";

$passed = count(array_filter($results, fn($r) => $r['ok']));
$failed = count(array_filter($results, fn($r) => !$r['ok']));
$total = count($results);
$pct = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

echo "Total: {$total} | ✅ Pasaron: {$passed} | ❌ Fallaron: {$failed} | {$pct}%\n\n";

if ($failed > 0) {
    echo "Endpoints fallidos:\n";
    foreach ($results as $r) {
        if (!$r['ok']) {
            echo "  - {$r['endpoint']}\n";
        }
    }
}
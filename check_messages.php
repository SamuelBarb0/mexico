<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Verificando Mensajes ===\n\n";

$totalMessages = App\Models\Message::count();
echo "Total de mensajes: {$totalMessages}\n";

$inboundMessages = App\Models\Message::where('direction', 'inbound')->count();
echo "Mensajes inbound: {$inboundMessages}\n";

$outboundMessages = App\Models\Message::where('direction', 'outbound')->count();
echo "Mensajes outbound: {$outboundMessages}\n\n";

echo "=== Últimos 5 mensajes ===\n";
$messages = App\Models\Message::latest()->take(5)->get();

foreach ($messages as $message) {
    echo sprintf(
        "ID: %d | Dir: %s | Status: %s | Content: %s | Fecha: %s\n",
        $message->id,
        $message->direction,
        $message->status,
        substr($message->content ?? 'Template', 0, 50),
        $message->created_at->format('Y-m-d H:i:s')
    );
}

echo "\n=== Contactos con mensajes ===\n";
$contacts = App\Models\Contact::whereHas('messages')->with('messages')->get();
echo "Total de contactos con mensajes: {$contacts->count()}\n";

foreach ($contacts as $contact) {
    echo sprintf(
        "- %s (%s): %d mensajes\n",
        $contact->name ?? 'Sin nombre',
        $contact->phone,
        $contact->messages->count()
    );
}

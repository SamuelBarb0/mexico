<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Message;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InboxController extends Controller
{
    /**
     * Display inbox with conversations list
     */
    public function index(Request $request)
    {
        $tenant = auth()->user()->tenant;

        // Check if tenant exists
        if (!$tenant) {
            $conversations = collect();
            return view('inbox.index', compact('conversations'));
        }

        // Base query
        $query = Contact::where('contacts.tenant_id', $tenant->id)
            ->whereHas('messages'); // Only contacts with messages

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Campaign filter
        if ($request->filled('campaign_filter')) {
            if ($request->campaign_filter === 'campaign') {
                // Solo mostrar contactos que tienen mensajes de campañas
                $query->whereHas('messages', function($q) {
                    $q->whereNotNull('campaign_id');
                });
            } elseif ($request->campaign_filter === 'direct') {
                // Solo mostrar contactos que NO tienen mensajes de campañas
                $query->whereHas('messages', function($q) {
                    $q->whereNull('campaign_id');
                })->whereDoesntHave('messages', function($q) {
                    $q->whereNotNull('campaign_id');
                });
            }
        }

        // Get contacts with their last message, ordered by most recent
        $conversations = $query
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1); // Get last message
            }])
            ->withCount(['messages as unread_count' => function ($query) {
                $query->where('direction', 'inbound')
                      ->where('status', '!=', 'read');
            }])
            ->select('contacts.*')
            ->selectSub(function ($query) {
                $query->selectRaw('MAX(created_at)')
                    ->from('messages')
                    ->whereColumn('messages.contact_id', 'contacts.id');
            }, 'last_message_at')
            ->orderByDesc('last_message_at')
            ->paginate(20)
            ->withQueryString(); // Mantener parámetros de búsqueda y filtros

        return view('inbox.index', compact('conversations'));
    }

    /**
     * Show conversation with a specific contact
     */
    public function show(Contact $contact)
    {
        // Verify contact belongs to current tenant
        if ($contact->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this conversation');
        }

        // Get all messages for this contact
        $messages = Message::where('contact_id', $contact->id)
            ->with(['messageTemplate', 'campaign'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark all inbound messages as read
        Message::where('contact_id', $contact->id)
            ->where('direction', 'inbound')
            ->where('status', '!=', 'read')
            ->update(['status' => 'read', 'read_at' => now()]);

        // Get all conversations for sidebar
        $conversations = Contact::where('contacts.tenant_id', auth()->user()->tenant_id)
            ->whereHas('messages')
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->withCount(['messages as unread_count' => function ($query) {
                $query->where('direction', 'inbound')
                      ->where('status', '!=', 'read');
            }])
            ->select('contacts.*')
            ->selectSub(function ($query) {
                $query->selectRaw('MAX(created_at)')
                    ->from('messages')
                    ->whereColumn('messages.contact_id', 'contacts.id');
            }, 'last_message_at')
            ->orderByDesc('last_message_at')
            ->limit(20)
            ->get();

        return view('inbox.show', compact('contact', 'messages', 'conversations'));
    }

    /**
     * Get conversation statistics
     */
    public function stats()
    {
        $tenant = auth()->user()->tenant;

        // Check if tenant exists
        if (!$tenant) {
            return response()->json([
                'total_conversations' => 0,
                'unread_messages' => 0,
                'total_messages_sent' => 0,
                'total_messages_received' => 0,
            ]);
        }

        $stats = [
            'total_conversations' => Contact::where('tenant_id', $tenant->id)
                ->whereHas('messages')
                ->count(),

            'unread_messages' => Message::where('tenant_id', $tenant->id)
                ->where('direction', 'inbound')
                ->where('status', '!=', 'read')
                ->count(),

            'total_messages_sent' => Message::where('tenant_id', $tenant->id)
                ->where('direction', 'outbound')
                ->count(),

            'total_messages_received' => Message::where('tenant_id', $tenant->id)
                ->where('direction', 'inbound')
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Send a message to a contact
     */
    public function sendMessage(Request $request, Contact $contact)
    {
        // Verify contact belongs to current tenant
        if ($contact->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this conversation');
        }

        $request->validate([
            'message' => 'required|string|max:4096',
        ]);

        $tenant = auth()->user()->tenant;

        // Get the active WABA account
        $wabaAccount = $tenant->wabaAccounts()
            ->where('status', 'active')
            ->first();

        if (!$wabaAccount) {
            return back()->with('error', 'No hay cuenta de WhatsApp configurada');
        }

        // Send message via WhatsApp API
        $whatsappService = app(WhatsAppService::class);
        $result = $whatsappService->sendTextMessage(
            $wabaAccount,
            $contact->phone,
            $request->message
        );

        if ($result['success']) {
            // Save message to database
            $message = Message::create([
                'tenant_id' => $tenant->id,
                'contact_id' => $contact->id,
                'waba_account_id' => $wabaAccount->id,
                'direction' => 'outbound',
                'message_type' => 'text',
                'content' => $request->message,
                'whatsapp_message_id' => $result['message_id'],
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return back()->with('success', 'Mensaje enviado correctamente');
        }

        return back()->with('error', 'Error al enviar mensaje: ' . $result['error_message']);
    }
}

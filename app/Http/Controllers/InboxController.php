<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Message;
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

        // Get contacts with their last message, ordered by most recent
        $conversations = Contact::where('tenant_id', $tenant->id)
            ->whereHas('messages') // Only contacts with messages
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1); // Get last message
            }])
            ->withCount(['messages as unread_count' => function ($query) {
                $query->where('direction', 'inbound')
                      ->where('status', '!=', 'read');
            }])
            ->orderByDesc('last_message_at')
            ->paginate(20);

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
        $conversations = Contact::where('tenant_id', auth()->user()->tenant_id)
            ->whereHas('messages')
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->withCount(['messages as unread_count' => function ($query) {
                $query->where('direction', 'inbound')
                      ->where('status', '!=', 'read');
            }])
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
}

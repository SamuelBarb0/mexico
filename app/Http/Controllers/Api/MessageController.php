<?php

namespace App\Http\Controllers\Api;

use App\Models\Contact;
use App\Models\Message;
use App\Models\WabaAccount;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MessageController extends BaseApiController
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * List messages for a contact
     */
    public function index(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $query = Message::where('tenant_id', $tenant->id)
            ->with(['contact:id,name,phone']);

        // Filter by contact
        if ($request->filled('contact_id')) {
            $query->where('contact_id', $request->contact_id);
        }

        // Filter by direction
        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $perPage = min($request->get('per_page', 50), 100);
        $messages = $query->latest()->paginate($perPage);

        return $this->paginated($messages);
    }

    /**
     * Get conversation with a contact
     */
    public function conversation(Request $request, int $contactId): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $contact = Contact::where('tenant_id', $tenant->id)
            ->where('id', $contactId)
            ->first();

        if (!$contact) {
            return $this->error('Contacto no encontrado', 404);
        }

        $perPage = min($request->get('per_page', 50), 100);

        $messages = Message::where('tenant_id', $tenant->id)
            ->where('contact_id', $contactId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->paginated($messages);
    }

    /**
     * Send a text message
     */
    public function sendText(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        // Check message limits
        if ($tenant->hasReachedLimit('messages')) {
            return $this->error(
                'Ha alcanzado el límite de mensajes de su plan.',
                403
            );
        }

        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'message' => 'required|string|max:4096',
            'waba_account_id' => 'nullable|exists:waba_accounts,id',
        ]);

        // Verify contact belongs to tenant
        $contact = Contact::where('tenant_id', $tenant->id)
            ->where('id', $validated['contact_id'])
            ->first();

        if (!$contact) {
            return $this->error('Contacto no encontrado', 404);
        }

        // Get WABA account
        $wabaAccount = null;
        if (!empty($validated['waba_account_id'])) {
            $wabaAccount = WabaAccount::where('tenant_id', $tenant->id)
                ->where('id', $validated['waba_account_id'])
                ->where('status', 'active')
                ->first();
        } else {
            $wabaAccount = WabaAccount::where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->first();
        }

        if (!$wabaAccount) {
            return $this->error('No hay cuenta de WhatsApp activa', 400);
        }

        // Send message
        $result = $this->whatsAppService->sendTextMessage(
            $wabaAccount,
            $contact->phone,
            $validated['message']
        );

        if (!$result['success']) {
            return $this->error('Error al enviar mensaje: ' . $result['error_message'], 500);
        }

        // Save message to database
        $message = Message::create([
            'tenant_id' => $tenant->id,
            'contact_id' => $contact->id,
            'waba_account_id' => $wabaAccount->id,
            'direction' => 'outbound',
            'message_type' => 'text',
            'content' => $validated['message'],
            'whatsapp_message_id' => $result['message_id'],
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return $this->success([
            'message_id' => $message->id,
            'whatsapp_message_id' => $result['message_id'],
            'status' => 'sent',
        ], 'Mensaje enviado exitosamente', 201);
    }

    /**
     * Send a template message
     */
    public function sendTemplate(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        // Check message limits
        if ($tenant->hasReachedLimit('messages')) {
            return $this->error(
                'Ha alcanzado el límite de mensajes de su plan.',
                403
            );
        }

        $validated = $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'template_id' => 'required|exists:message_templates,id',
            'waba_account_id' => 'nullable|exists:waba_accounts,id',
            'variables' => 'nullable|array',
        ]);

        // Implementation would use the template to send
        // For now, return a placeholder response

        return $this->success([
            'message' => 'Template message sending not fully implemented yet',
        ], 'Funcionalidad en desarrollo', 501);
    }

    /**
     * Get message status
     */
    public function status(Request $request, int $id): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $message = Message::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->first();

        if (!$message) {
            return $this->error('Mensaje no encontrado', 404);
        }

        return $this->success([
            'id' => $message->id,
            'whatsapp_message_id' => $message->whatsapp_message_id,
            'status' => $message->status,
            'sent_at' => $message->sent_at,
            'delivered_at' => $message->delivered_at,
            'read_at' => $message->read_at,
            'failed_at' => $message->failed_at,
            'error_message' => $message->error_message,
        ]);
    }
}

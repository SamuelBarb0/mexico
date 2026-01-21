<?php

namespace App\Http\Controllers\Api;

use App\Models\WabaAccount;
use App\Models\Contact;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends BaseApiController
{
    /**
     * Verify webhook (GET request from Meta)
     */
    public function verify(Request $request): mixed
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = config('services.meta.webhook_verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('Webhook verified successfully');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('Webhook verification failed', [
            'mode' => $mode,
            'token' => $token,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming webhook (POST request from Meta)
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('WhatsApp Webhook received', ['payload' => $payload]);

        try {
            // Validate structure
            if (!isset($payload['entry']) || !is_array($payload['entry'])) {
                return response()->json(['status' => 'ok']);
            }

            foreach ($payload['entry'] as $entry) {
                if (!isset($entry['changes']) || !is_array($entry['changes'])) {
                    continue;
                }

                foreach ($entry['changes'] as $change) {
                    if ($change['field'] !== 'messages') {
                        continue;
                    }

                    $value = $change['value'] ?? [];
                    $this->processWebhookValue($value);
                }
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Always return 200 to prevent Meta from retrying
            return response()->json(['status' => 'ok']);
        }
    }

    /**
     * Process webhook value
     */
    protected function processWebhookValue(array $value): void
    {
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;

        if (!$phoneNumberId) {
            return;
        }

        // Find WABA account
        $wabaAccount = WabaAccount::where('phone_number_id', $phoneNumberId)->first();

        if (!$wabaAccount) {
            Log::warning('WABA account not found for phone_number_id', ['phone_number_id' => $phoneNumberId]);
            return;
        }

        // Process incoming messages
        if (isset($value['messages']) && is_array($value['messages'])) {
            foreach ($value['messages'] as $messageData) {
                $this->processIncomingMessage($wabaAccount, $messageData, $value['contacts'] ?? []);
            }
        }

        // Process status updates
        if (isset($value['statuses']) && is_array($value['statuses'])) {
            foreach ($value['statuses'] as $statusData) {
                $this->processStatusUpdate($statusData);
            }
        }
    }

    /**
     * Process incoming message
     */
    protected function processIncomingMessage(WabaAccount $wabaAccount, array $messageData, array $contacts): void
    {
        $from = $messageData['from'] ?? null;
        $messageId = $messageData['id'] ?? null;
        $messageType = $messageData['type'] ?? 'unknown';

        if (!$from || !$messageId) {
            return;
        }

        // Find or create contact
        $contactName = null;
        foreach ($contacts as $contact) {
            if (($contact['wa_id'] ?? null) === $from) {
                $contactName = $contact['profile']['name'] ?? null;
                break;
            }
        }

        $contact = Contact::firstOrCreate(
            [
                'tenant_id' => $wabaAccount->tenant_id,
                'phone' => $from,
            ],
            [
                'name' => $contactName ?? 'WhatsApp User',
                'status' => 'active',
            ]
        );

        // Update contact name if provided
        if ($contactName && $contact->name === 'WhatsApp User') {
            $contact->update(['name' => $contactName]);
        }

        // Extract message content
        $content = $this->extractMessageContent($messageData);

        // Check for duplicate
        $existingMessage = Message::where('whatsapp_message_id', $messageId)->first();
        if ($existingMessage) {
            return;
        }

        // Save message
        Message::create([
            'tenant_id' => $wabaAccount->tenant_id,
            'contact_id' => $contact->id,
            'waba_account_id' => $wabaAccount->id,
            'whatsapp_message_id' => $messageId,
            'direction' => 'inbound',
            'message_type' => $messageType,
            'content' => $content,
            'status' => 'received',
            'received_at' => now(),
            'metadata' => $messageData,
        ]);

        // Update contact last message
        $contact->update(['last_message_at' => now()]);

        Log::info('Incoming message saved', [
            'contact_id' => $contact->id,
            'message_id' => $messageId,
        ]);
    }

    /**
     * Extract message content based on type
     */
    protected function extractMessageContent(array $messageData): string
    {
        $type = $messageData['type'] ?? 'unknown';

        return match ($type) {
            'text' => $messageData['text']['body'] ?? '',
            'image' => '[Imagen]' . ($messageData['image']['caption'] ?? ''),
            'video' => '[Video]' . ($messageData['video']['caption'] ?? ''),
            'audio' => '[Audio]',
            'document' => '[Documento]' . ($messageData['document']['filename'] ?? ''),
            'sticker' => '[Sticker]',
            'location' => '[Ubicación]',
            'contacts' => '[Contacto compartido]',
            'button' => $messageData['button']['text'] ?? '[Botón]',
            'interactive' => $this->extractInteractiveContent($messageData),
            default => "[{$type}]",
        };
    }

    /**
     * Extract interactive message content
     */
    protected function extractInteractiveContent(array $messageData): string
    {
        $interactive = $messageData['interactive'] ?? [];
        $type = $interactive['type'] ?? 'unknown';

        return match ($type) {
            'button_reply' => $interactive['button_reply']['title'] ?? '[Botón seleccionado]',
            'list_reply' => $interactive['list_reply']['title'] ?? '[Elemento de lista seleccionado]',
            default => '[Mensaje interactivo]',
        };
    }

    /**
     * Process status update
     */
    protected function processStatusUpdate(array $statusData): void
    {
        $messageId = $statusData['id'] ?? null;
        $status = $statusData['status'] ?? null;
        $timestamp = $statusData['timestamp'] ?? null;

        if (!$messageId || !$status) {
            return;
        }

        $message = Message::where('whatsapp_message_id', $messageId)->first();

        if (!$message) {
            return;
        }

        $updates = ['status' => $status];

        if ($timestamp) {
            $dateTime = \Carbon\Carbon::createFromTimestamp($timestamp);

            switch ($status) {
                case 'sent':
                    $updates['sent_at'] = $dateTime;
                    break;
                case 'delivered':
                    $updates['delivered_at'] = $dateTime;
                    break;
                case 'read':
                    $updates['read_at'] = $dateTime;
                    break;
                case 'failed':
                    $updates['failed_at'] = $dateTime;
                    $updates['error_message'] = $statusData['errors'][0]['message'] ?? 'Unknown error';
                    break;
            }
        }

        $message->update($updates);

        Log::info('Message status updated', [
            'message_id' => $message->id,
            'status' => $status,
        ]);
    }
}

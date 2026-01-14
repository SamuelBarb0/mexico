<?php

namespace App\Services\Meta;

use App\Models\Message;
use App\Models\Contact;
use App\Models\WabaAccount;
use Illuminate\Support\Facades\Log;
use Exception;

class WebhookService
{
    /**
     * Process a webhook change event from Meta
     */
    public function processWebhookChange(array $change): void
    {
        try {
            $field = $change['field'] ?? null;
            $value = $change['value'] ?? null;

            if ($field !== 'messages') {
                Log::info('Ignoring non-messages webhook', ['field' => $field]);
                return;
            }

            // Procesar estados de mensajes
            if (isset($value['statuses'])) {
                foreach ($value['statuses'] as $status) {
                    $this->processMessageStatus($status, $value);
                }
            }

            // Procesar mensajes entrantes
            if (isset($value['messages'])) {
                foreach ($value['messages'] as $message) {
                    $this->processIncomingMessage($message, $value);
                }
            }

        } catch (Exception $e) {
            Log::error('Error processing webhook change', [
                'error' => $e->getMessage(),
                'change' => $change,
            ]);
        }
    }

    /**
     * Process message status update (sent, delivered, read, failed)
     */
    protected function processMessageStatus(array $status, array $value): void
    {
        try {
            $messageId = $status['id'] ?? null; // WhatsApp message ID
            $statusType = $status['status'] ?? null;
            $timestamp = $status['timestamp'] ?? null;

            if (!$messageId || !$statusType) {
                Log::warning('Invalid status update - missing ID or status', $status);
                return;
            }

            Log::info('Processing message status', [
                'message_id' => $messageId,
                'status' => $statusType,
            ]);

            // Buscar el mensaje en la base de datos
            $message = Message::where('meta_message_id', $messageId)
                ->orWhere('wamid', $messageId)
                ->first();

            if (!$message) {
                Log::warning('Message not found in database', ['message_id' => $messageId]);
                return;
            }

            // Actualizar el estado del mensaje
            switch ($statusType) {
                case 'sent':
                    $message->markAsSent();
                    break;

                case 'delivered':
                    $message->markAsDelivered();
                    break;

                case 'read':
                    $message->markAsRead();

                    // Actualizar last_message_at del contacto
                    if ($message->contact) {
                        $message->contact->update(['last_message_at' => now()]);
                    }
                    break;

                case 'failed':
                    $errorCode = $status['errors'][0]['code'] ?? null;
                    $errorMessage = $status['errors'][0]['title'] ?? 'Unknown error';
                    $message->markAsFailed($errorCode, $errorMessage);
                    break;
            }

            // Actualizar contadores de la campaña si existe
            if ($message->campaign_id) {
                $this->updateCampaignCounters($message->campaign_id);
            }

            Log::info('Message status updated successfully', [
                'message_id' => $message->id,
                'status' => $statusType,
            ]);

        } catch (Exception $e) {
            Log::error('Error processing message status', [
                'error' => $e->getMessage(),
                'status' => $status,
            ]);
        }
    }

    /**
     * Process incoming message from user
     */
    protected function processIncomingMessage(array $message, array $value): void
    {
        try {
            $from = $message['from'] ?? null;
            $messageId = $message['id'] ?? null;
            $type = $message['type'] ?? 'text';
            $timestamp = $message['timestamp'] ?? null;

            if (!$from || !$messageId) {
                Log::warning('Invalid incoming message - missing from or ID', $message);
                return;
            }

            Log::info('Processing incoming message', [
                'from' => $from,
                'message_id' => $messageId,
                'type' => $type,
            ]);

            // Obtener metadata del webhook
            $metadata = $value['metadata'] ?? [];
            $phoneNumberId = $metadata['phone_number_id'] ?? null;

            // Buscar la cuenta WABA
            $wabaAccount = WabaAccount::where('phone_number_id', $phoneNumberId)->first();

            if (!$wabaAccount) {
                Log::warning('WABA account not found', ['phone_number_id' => $phoneNumberId]);
                return;
            }

            // Obtener nombre del contacto desde el webhook
            $contactProfile = $value['contacts'][0] ?? null;
            $contactName = $contactProfile['profile']['name'] ?? null;

            // Limpiar el número de teléfono (remover el +)
            $cleanPhone = ltrim($from, '+');

            // Buscar o crear el contacto
            $contact = Contact::firstOrCreate(
                [
                    'tenant_id' => $wabaAccount->tenant_id,
                    'phone' => $cleanPhone,
                ],
                [
                    'name' => $contactName,
                    'whatsapp_verified' => true,
                    'last_message_at' => now(),
                ]
            );

            // Extraer el contenido del mensaje
            $content = $this->extractMessageContent($message, $type);

            // Guardar el mensaje en la base de datos
            Message::create([
                'tenant_id' => $wabaAccount->tenant_id,
                'waba_account_id' => $wabaAccount->id,
                'contact_id' => $contact->id,
                'meta_message_id' => $messageId,
                'wamid' => $messageId,
                'direction' => 'inbound',
                'type' => $type,
                'content' => $content,
                'status' => 'delivered', // Los mensajes entrantes ya están entregados
                'delivered_at' => now(),
            ]);

            // Actualizar último mensaje del contacto
            $contact->update(['last_message_at' => now()]);

            Log::info('Incoming message saved successfully', [
                'contact_id' => $contact->id,
                'message_id' => $messageId,
            ]);

        } catch (Exception $e) {
            Log::error('Error processing incoming message', [
                'error' => $e->getMessage(),
                'message' => $message,
            ]);
        }
    }

    /**
     * Extract message content based on type
     */
    protected function extractMessageContent(array $message, string $type): ?string
    {
        switch ($type) {
            case 'text':
                return $message['text']['body'] ?? null;

            case 'image':
            case 'video':
            case 'document':
            case 'audio':
                return $message[$type]['caption'] ?? 'Media message';

            case 'location':
                $lat = $message['location']['latitude'] ?? '';
                $lng = $message['location']['longitude'] ?? '';
                return "Location: {$lat}, {$lng}";

            default:
                return "Message type: {$type}";
        }
    }

    /**
     * Update campaign counters
     */
    protected function updateCampaignCounters(int $campaignId): void
    {
        try {
            $campaign = \App\Models\Campaign::find($campaignId);

            if (!$campaign) {
                return;
            }

            $messages = Message::where('campaign_id', $campaignId)->get();

            $campaign->update([
                'sent_count' => $messages->where('status', '!=', 'queued')->count(),
                'delivered_count' => $messages->whereIn('status', ['delivered', 'read'])->count(),
                'read_count' => $messages->where('status', 'read')->count(),
                'failed_count' => $messages->where('status', 'failed')->count(),
            ]);

        } catch (Exception $e) {
            Log::error('Error updating campaign counters', [
                'campaign_id' => $campaignId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

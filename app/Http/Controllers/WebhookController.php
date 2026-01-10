<?php

namespace App\Http\Controllers;

use App\Services\Meta\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Handle incoming webhooks from Meta WhatsApp
     */
    public function handle(Request $request)
    {
        Log::info('Webhook received from Meta', [
            'method' => $request->method(),
            'body' => $request->all(),
        ]);

        // Verificación inicial de webhook (cuando Meta configura el webhook)
        if ($request->isMethod('GET')) {
            return $this->verifyWebhook($request);
        }

        // Procesar eventos del webhook
        if ($request->isMethod('POST')) {
            return $this->processWebhook($request);
        }

        return response()->json(['error' => 'Method not allowed'], 405);
    }

    /**
     * Verify webhook challenge from Meta
     */
    protected function verifyWebhook(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = config('services.meta.webhook_verify_token', 'mexico_whatsapp_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('Webhook verified successfully');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('Webhook verification failed', [
            'mode' => $mode,
            'token' => $token,
        ]);

        return response()->json(['error' => 'Verification failed'], 403);
    }

    /**
     * Process webhook events
     */
    protected function processWebhook(Request $request)
    {
        try {
            $data = $request->all();

            // Verificar que sea un evento de WhatsApp
            if (!isset($data['entry']) || empty($data['entry'])) {
                Log::warning('Invalid webhook payload - no entry');
                return response()->json(['status' => 'ignored'], 200);
            }

            // Procesar cada entrada del webhook
            foreach ($data['entry'] as $entry) {
                if (!isset($entry['changes'])) {
                    continue;
                }

                foreach ($entry['changes'] as $change) {
                    $this->webhookService->processWebhookChange($change);
                }
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Siempre devolver 200 para que Meta no reintente
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 200);
        }
    }
}

<?php

namespace App\Services\WhatsApp;

use App\Models\MessageTemplate;
use App\Models\WabaAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiVersion = 'v18.0';

    /**
     * Send a template message via WhatsApp Business API
     */
    public function sendTemplateMessage(
        WabaAccount $wabaAccount,
        string $phoneNumber,
        MessageTemplate $template,
        array $variables = []
    ): array {
        $phoneNumberId = $wabaAccount->phone_number_id;
        $accessToken = $wabaAccount->access_token;

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$phoneNumberId}/messages";

        // Build the template message payload
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($phoneNumber),
            'type' => 'template',
            'template' => [
                'name' => $template->name,
                'language' => [
                    'code' => $template->language,
                ],
            ],
        ];

        // Add template components with variables if provided
        if (!empty($variables)) {
            $payload['template']['components'] = $this->buildTemplateComponents($template, $variables);
        }

        try {
            $response = Http::withToken($accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('WhatsApp message sent successfully', [
                    'phone_number' => $phoneNumber,
                    'template' => $template->name,
                    'message_id' => $data['messages'][0]['id'] ?? null,
                ]);

                return [
                    'success' => true,
                    'message_id' => $data['messages'][0]['id'] ?? null,
                    'data' => $data,
                ];
            }

            $error = $response->json();
            Log::error('WhatsApp message send failed', [
                'phone_number' => $phoneNumber,
                'template' => $template->name,
                'error' => $error,
            ]);

            return [
                'success' => false,
                'error_message' => $error['error']['message'] ?? 'Unknown error',
                'error_code' => $error['error']['code'] ?? null,
                'error' => $error,
            ];
        } catch (\Exception $e) {
            Log::error('WhatsApp API exception', [
                'phone_number' => $phoneNumber,
                'template' => $template->name,
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error_message' => $e->getMessage(),
                'error_code' => 'EXCEPTION',
            ];
        }
    }

    /**
     * Build template components with variables replaced
     */
    protected function buildTemplateComponents(MessageTemplate $template, array $variables): array
    {
        $components = [];
        $templateComponents = $template->components;

        // Process HEADER component if it has variables
        if (isset($templateComponents['header'])) {
            $header = $templateComponents['header'];

            if ($header['format'] === 'TEXT' && !empty($header['text'])) {
                $headerVariables = $this->extractVariablesFromText($header['text']);

                if (!empty($headerVariables)) {
                    $parameters = [];
                    foreach ($headerVariables as $varIndex) {
                        if (isset($variables["header_{$varIndex}"])) {
                            $parameters[] = [
                                'type' => 'text',
                                'text' => $variables["header_{$varIndex}"],
                            ];
                        }
                    }

                    if (!empty($parameters)) {
                        $components[] = [
                            'type' => 'header',
                            'parameters' => $parameters,
                        ];
                    }
                }
            } elseif (in_array($header['format'], ['IMAGE', 'VIDEO', 'DOCUMENT'])) {
                // Handle media headers
                $mediaType = strtolower($header['format']);
                if (isset($variables["header_media_url"])) {
                    $components[] = [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => $mediaType,
                                $mediaType => [
                                    'link' => $variables["header_media_url"],
                                ],
                            ],
                        ],
                    ];
                }
            }
        }

        // Process BODY component (required)
        if (isset($templateComponents['body'])) {
            $bodyText = $templateComponents['body']['text'];
            $bodyVariables = $this->extractVariablesFromText($bodyText);

            if (!empty($bodyVariables)) {
                $parameters = [];
                foreach ($bodyVariables as $varIndex) {
                    if (isset($variables["body_{$varIndex}"])) {
                        $parameters[] = [
                            'type' => 'text',
                            'text' => $variables["body_{$varIndex}"],
                        ];
                    }
                }

                if (!empty($parameters)) {
                    $components[] = [
                        'type' => 'body',
                        'parameters' => $parameters,
                    ];
                }
            }
        }

        // Process BUTTONS component if dynamic
        if (isset($templateComponents['buttons'])) {
            foreach ($templateComponents['buttons'] as $index => $button) {
                if ($button['type'] === 'URL' && isset($button['url']) && strpos($button['url'], '{{1}}') !== false) {
                    // Dynamic URL button
                    if (isset($variables["button_{$index}_url"])) {
                        $components[] = [
                            'type' => 'button',
                            'sub_type' => 'url',
                            'index' => $index,
                            'parameters' => [
                                [
                                    'type' => 'text',
                                    'text' => $variables["button_{$index}_url"],
                                ],
                            ],
                        ];
                    }
                }
            }
        }

        return $components;
    }

    /**
     * Extract variable indices from text (e.g., {{1}}, {{2}})
     */
    protected function extractVariablesFromText(string $text): array
    {
        preg_match_all('/\{\{(\d+)\}\}/', $text, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Format phone number to E.164 format
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);

        // Ensure it starts with +
        if (!str_starts_with($cleaned, '+')) {
            $cleaned = '+' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Check if a phone number is valid for WhatsApp
     */
    public function isValidPhoneNumber(string $phoneNumber): bool
    {
        $formatted = $this->formatPhoneNumber($phoneNumber);

        // Basic validation: starts with + and has 10-15 digits
        return preg_match('/^\+\d{10,15}$/', $formatted) === 1;
    }

    /**
     * Get message delivery status from WhatsApp
     */
    public function getMessageStatus(WabaAccount $wabaAccount, string $messageId): ?array
    {
        $accessToken = $wabaAccount->access_token;
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$messageId}";

        try {
            $response = Http::withToken($accessToken)->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Failed to get message status', [
                'message_id' => $messageId,
                'error' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception getting message status', [
                'message_id' => $messageId,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }
}

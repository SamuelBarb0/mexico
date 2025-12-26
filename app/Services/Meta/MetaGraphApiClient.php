<?php

namespace App\Services\Meta;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class MetaGraphApiClient
{
    protected string $baseUrl;
    protected string $apiVersion;

    public function __construct()
    {
        $this->apiVersion = config('services.meta.api_version', 'v18.0');
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}";
    }

    /**
     * Create a new message template
     */
    public function createTemplate(string $wabaId, string $accessToken, array $templateData): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/{$wabaId}/message_templates", $templateData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Meta API: Failed to create template', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
                'error_code' => $response->json()['error']['code'] ?? null,
            ];

        } catch (Exception $e) {
            Log::error('Meta API: Exception creating template', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all templates for a WABA
     */
    public function getTemplates(string $wabaId, string $accessToken, array $filters = []): array
    {
        try {
            $query = array_merge([
                'fields' => 'id,name,language,status,category,components,rejected_reason,quality_score',
                'limit' => 100,
            ], $filters);

            $response = Http::withToken($accessToken)
                ->get("{$this->baseUrl}/{$wabaId}/message_templates", $query);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()['data'] ?? [],
                    'paging' => $response->json()['paging'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];

        } catch (Exception $e) {
            Log::error('Meta API: Exception getting templates', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get a specific template by ID
     */
    public function getTemplate(string $templateId, string $accessToken): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get("{$this->baseUrl}/{$templateId}", [
                    'fields' => 'id,name,language,status,category,components,rejected_reason,quality_score',
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];

        } catch (Exception $e) {
            Log::error('Meta API: Exception getting template', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a template
     */
    public function deleteTemplate(string $wabaId, string $templateName, string $accessToken): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->delete("{$this->baseUrl}/{$wabaId}/message_templates", [
                    'name' => $templateName,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];

        } catch (Exception $e) {
            Log::error('Meta API: Exception deleting template', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sync template status from Meta
     */
    public function syncTemplateStatus(string $templateId, string $accessToken): array
    {
        return $this->getTemplate($templateId, $accessToken);
    }

    /**
     * Get template analytics/insights
     */
    public function getTemplateAnalytics(string $templateId, string $accessToken, array $params = []): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get("{$this->baseUrl}/{$templateId}/analytics", $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

<?php

namespace App\Services\Meta;

use App\Models\MessageTemplate;
use App\Models\WabaAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TemplateService
{
    protected MetaGraphApiClient $apiClient;

    public function __construct(MetaGraphApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Create and submit a template to Meta
     */
    public function createAndSubmitTemplate(array $data, int $tenantId): array
    {
        Log::info('=== TemplateService::createAndSubmitTemplate ===');
        Log::info('Data recibida:', $data);
        Log::info('Tenant ID:', ['tenant_id' => $tenantId]);

        try {
            DB::beginTransaction();
            Log::info('Transacción iniciada');

            // Decode components if it's a JSON string
            $components = $data['components'];
            if (is_string($components)) {
                Log::info('Components es string, decodificando...');
                $components = json_decode($components, true);
                Log::info('Components decodificado:', $components);
            }

            // Create template in database
            $templateData = [
                'tenant_id' => $tenantId,
                'waba_account_id' => $data['waba_account_id'],
                'name' => $data['name'],
                'language' => $data['language'] ?? 'es',
                'category' => $data['category'],
                'status' => 'DRAFT',
                'components' => $components,
                'description' => $data['description'] ?? null,
                'tags' => $data['tags'] ?? null,
            ];

            Log::info('Datos para crear plantilla:', $templateData);

            $template = MessageTemplate::create($templateData);
            Log::info('Plantilla creada en DB:', ['id' => $template->id]);

            // Extract variables
            $template->variables = $template->extractVariables();
            $template->save();
            Log::info('Variables extraídas:', ['variables' => $template->variables]);

            DB::commit();
            Log::info('Transacción committeada exitosamente');

            return [
                'success' => true,
                'template' => $template,
                'message' => 'Plantilla creada como borrador. Envía a Meta para aprobación.',
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating template', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Submit template to Meta for approval
     */
    public function submitToMeta(MessageTemplate $template): array
    {
        try {
            $wabaAccount = $template->wabaAccount;

            if (!$wabaAccount || !$wabaAccount->access_token) {
                return [
                    'success' => false,
                    'error' => 'No se encontró cuenta WABA o token de acceso',
                ];
            }

            // Format template data for Meta API
            $templateData = $this->formatTemplateForMeta($template);

            Log::info('Template data a enviar a Meta:', $templateData);

            // Submit to Meta
            $result = $this->apiClient->createTemplate(
                $wabaAccount->waba_id,
                $wabaAccount->access_token,
                $templateData
            );

            if ($result['success']) {
                // Update template with Meta ID and status
                $template->update([
                    'meta_template_id' => $result['data']['id'] ?? null,
                    'status' => 'PENDING',
                    'meta_status' => $result['data']['status'] ?? 'PENDING',
                ]);

                return [
                    'success' => true,
                    'message' => 'Plantilla enviada a Meta para aprobación',
                    'template' => $template->fresh(),
                ];
            }

            return [
                'success' => false,
                'error' => $result['error'],
            ];

        } catch (Exception $e) {
            Log::error('Error submitting template to Meta', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sync template status with Meta
     */
    public function syncTemplateStatus(MessageTemplate $template): array
    {
        try {
            if (!$template->meta_template_id) {
                return [
                    'success' => false,
                    'error' => 'Plantilla no tiene ID de Meta',
                ];
            }

            $wabaAccount = $template->wabaAccount;
            if (!$wabaAccount || !$wabaAccount->access_token) {
                return [
                    'success' => false,
                    'error' => 'No se encontró cuenta WABA',
                ];
            }

            $result = $this->apiClient->syncTemplateStatus(
                $template->meta_template_id,
                $wabaAccount->access_token
            );

            if ($result['success']) {
                $metaData = $result['data'];

                // Update template with latest status from Meta
                $template->update([
                    'meta_status' => $metaData['status'] ?? $template->meta_status,
                    'status' => $this->mapMetaStatusToLocal($metaData['status'] ?? ''),
                    'rejection_reason' => $metaData['rejected_reason'] ?? null,
                    'quality_score' => $metaData['quality_score']['score'] ?? 'UNKNOWN',
                ]);

                return [
                    'success' => true,
                    'template' => $template->fresh(),
                ];
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Error syncing template status', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sync all templates for a WABA account
     */
    public function syncAllTemplates(WabaAccount $wabaAccount, int $tenantId): array
    {
        try {
            $result = $this->apiClient->getTemplates(
                $wabaAccount->waba_id,
                $wabaAccount->access_token
            );

            if (!$result['success']) {
                return $result;
            }

            $synced = 0;
            $created = 0;
            $deleted = 0;

            // Get list of template names from Meta
            $metaTemplateKeys = [];
            foreach ($result['data'] as $metaTemplate) {
                $key = $metaTemplate['name'] . '_' . $metaTemplate['language'];
                $metaTemplateKeys[$key] = true;
            }

            // Sync templates from Meta
            foreach ($result['data'] as $metaTemplate) {
                // Use updateOrCreate to avoid duplicates
                $template = MessageTemplate::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'name' => $metaTemplate['name'],
                        'language' => $metaTemplate['language'],
                    ],
                    [
                        'waba_account_id' => $wabaAccount->id,
                        'meta_template_id' => $metaTemplate['id'],
                        'category' => $metaTemplate['category'],
                        'status' => $this->mapMetaStatusToLocal($metaTemplate['status']),
                        'meta_status' => $metaTemplate['status'],
                        'components' => $this->formatComponentsFromMeta($metaTemplate['components']),
                        'quality_score' => $metaTemplate['quality_score']['score'] ?? 'UNKNOWN',
                        'rejection_reason' => $metaTemplate['rejected_reason'] ?? null,
                    ]
                );

                if ($template->wasRecentlyCreated) {
                    $created++;
                } else {
                    $synced++;
                }
            }

            // Delete templates that no longer exist in Meta (only those that have meta_template_id)
            $localTemplates = MessageTemplate::where('tenant_id', $tenantId)
                ->where('waba_account_id', $wabaAccount->id)
                ->whereNotNull('meta_template_id')
                ->get();

            foreach ($localTemplates as $localTemplate) {
                $key = $localTemplate->name . '_' . $localTemplate->language;
                if (!isset($metaTemplateKeys[$key])) {
                    Log::info('Deleting template no longer in Meta', [
                        'template_id' => $localTemplate->id,
                        'name' => $localTemplate->name,
                    ]);
                    $localTemplate->delete();
                    $deleted++;
                }
            }

            return [
                'success' => true,
                'synced' => $synced,
                'created' => $created,
                'deleted' => $deleted,
                'total' => count($result['data']),
            ];

        } catch (Exception $e) {
            Log::error('Error syncing all templates', [
                'waba_id' => $wabaAccount->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Error al sincronizar plantillas con Meta',
            ];
        }
    }

    /**
     * Format components from Meta API response to our internal format
     */
    protected function formatComponentsFromMeta(array $metaComponents): array
    {
        $formatted = [];

        foreach ($metaComponents as $component) {
            $type = strtolower($component['type']);

            switch ($type) {
                case 'header':
                    $formatted['header'] = [
                        'format' => $component['format'] ?? 'TEXT',
                        'text' => $component['text'] ?? null,
                        'example' => $component['example'] ?? null,
                    ];
                    break;

                case 'body':
                    $formatted['body'] = [
                        'text' => $component['text'] ?? '',
                        'example' => $component['example'] ?? null,
                    ];
                    break;

                case 'footer':
                    $formatted['footer'] = [
                        'text' => $component['text'] ?? '',
                    ];
                    break;

                case 'buttons':
                    $formatted['buttons'] = $component['buttons'] ?? [];
                    break;
            }
        }

        return $formatted;
    }

    /**
     * Delete a template
     */
    public function deleteTemplate(MessageTemplate $template): array
    {
        try {
            // If template was submitted to Meta, delete from Meta first
            if ($template->meta_template_id) {
                $wabaAccount = $template->wabaAccount;

                if ($wabaAccount && $wabaAccount->access_token) {
                    $result = $this->apiClient->deleteTemplate(
                        $wabaAccount->waba_id,
                        $template->name,
                        $wabaAccount->access_token
                    );

                    if (!$result['success']) {
                        return [
                            'success' => false,
                            'error' => 'Error eliminando de Meta: ' . $result['error'],
                        ];
                    }
                }
            }

            // Delete from local database
            $template->delete();

            return [
                'success' => true,
                'message' => 'Plantilla eliminada correctamente',
            ];

        } catch (Exception $e) {
            Log::error('Error deleting template', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format template data for Meta API
     */
    protected function formatTemplateForMeta(MessageTemplate $template): array
    {
        return [
            'name' => $template->name,
            'language' => $template->language,
            'category' => $template->category,
            'components' => $template->components,
        ];
    }

    /**
     * Map Meta status to local status
     */
    protected function mapMetaStatusToLocal(string $metaStatus): string
    {
        return match(strtoupper($metaStatus)) {
            'APPROVED' => 'APPROVED',
            'PENDING' => 'PENDING',
            'REJECTED' => 'REJECTED',
            'DISABLED' => 'DISABLED',
            default => 'DRAFT',
        };
    }
}

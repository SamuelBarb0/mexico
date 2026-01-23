<?php

namespace App\Http\Controllers\Api;

use App\Models\Campaign;
use App\Models\MessageTemplate;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CampaignController extends BaseApiController
{
    /**
     * List all campaigns
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $tenant = $request->user()->tenant;

            $query = Campaign::where('tenant_id', $tenant->id)
                ->with(['messageTemplate:id,name', 'wabaAccount:id,name,phone_number']);

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Date range filter
            if ($request->filled('from_date')) {
                $query->whereDate('created_at', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('created_at', '<=', $request->to_date);
            }

            $perPage = min($request->get('per_page', 15), 100);
            $campaigns = $query->latest()->paginate($perPage);

            return $this->paginated($campaigns);
        } catch (\Exception $e) {
            Log::error('API Campaign index error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('Error al listar campañas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single campaign with details
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $tenant = $request->user()->tenant;

            $campaign = Campaign::where('tenant_id', $tenant->id)
                ->with(['messageTemplate', 'wabaAccount', 'messages'])
                ->where('id', $id)
                ->first();

            if (!$campaign) {
                return $this->error('Campaña no encontrada', 404);
            }

            return $this->success($campaign);
        } catch (\Exception $e) {
            Log::error('API Campaign show error', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('Error al obtener campaña: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new campaign
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = $request->user()->tenant;

        // Check subscription limits
        if ($tenant->hasReachedLimit('campaigns')) {
            return $this->error(
                'Ha alcanzado el límite de campañas de su plan.',
                403
            );
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'message_template_id' => 'required|exists:message_templates,id',
            'waba_account_id' => 'required|exists:waba_accounts,id',
            'scheduled_at' => 'nullable|date|after:now',
            'contact_ids' => 'nullable|array',
            'contact_ids.*' => 'exists:contacts,id',
            'contact_filters' => 'nullable|array',
            'template_variables' => 'nullable|array',
        ]);

        // Verify template belongs to tenant
        $template = MessageTemplate::where('tenant_id', $tenant->id)
            ->where('id', $validated['message_template_id'])
            ->first();

        if (!$template) {
            return $this->error('Plantilla no encontrada', 404);
        }

        $campaign = Campaign::create([
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'message_template_id' => $validated['message_template_id'],
            'waba_account_id' => $validated['waba_account_id'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => 'draft',
            'settings' => [
                'template_variables' => $validated['template_variables'] ?? [],
                'contact_filters' => $validated['contact_filters'] ?? [],
            ],
        ]);

        // Add recipients if specified
        if (!empty($validated['contact_ids'])) {
            $contacts = Contact::where('tenant_id', $tenant->id)
                ->whereIn('id', $validated['contact_ids'])
                ->get();

            foreach ($contacts as $contact) {
                $campaign->messages()->create([
                    'contact_id' => $contact->id,
                    'phone_number' => $contact->phone,
                    'status' => 'PENDING',
                ]);
            }

            $campaign->update(['total_recipients' => $contacts->count()]);
        }

        $campaign->load(['messageTemplate:id,name', 'wabaAccount:id,name,phone_number']);

        return $this->success($campaign, 'Campaña creada exitosamente', 201);
    }

    /**
     * Update a campaign
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $campaign = Campaign::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->first();

        if (!$campaign) {
            return $this->error('Campaña no encontrada', 404);
        }

        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return $this->error('Solo se pueden editar campañas en borrador o programadas', 400);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'message_template_id' => 'sometimes|required|exists:message_templates,id',
            'waba_account_id' => 'sometimes|required|exists:waba_accounts,id',
            'scheduled_at' => 'nullable|date|after:now',
            'template_variables' => 'nullable|array',
        ]);

        $campaign->update($validated);

        return $this->success($campaign, 'Campaña actualizada exitosamente');
    }

    /**
     * Delete a campaign
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenant = $request->user()->tenant;

        $campaign = Campaign::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->first();

        if (!$campaign) {
            return $this->error('Campaña no encontrada', 404);
        }

        if (in_array($campaign->status, ['sending', 'completed'])) {
            return $this->error('No se pueden eliminar campañas enviadas o en proceso', 400);
        }

        $campaign->delete();

        return $this->success(null, 'Campaña eliminada exitosamente');
    }

    /**
     * Get campaign statistics
     */
    public function stats(Request $request, int $id): JsonResponse
    {
        try {
            $tenant = $request->user()->tenant;

            $campaign = Campaign::where('tenant_id', $tenant->id)
                ->where('id', $id)
                ->first();

            if (!$campaign) {
                return $this->error('Campaña no encontrada', 404);
            }

            $stats = [
                'total_recipients' => $campaign->messages()->count(),
                'sent' => $campaign->messages()->where('status', 'SENT')->count(),
                'delivered' => $campaign->messages()->where('status', 'DELIVERED')->count(),
                'read' => $campaign->messages()->where('status', 'READ')->count(),
                'failed' => $campaign->messages()->where('status', 'FAILED')->count(),
                'pending' => $campaign->messages()->where('status', 'PENDING')->count(),
            ];

            $stats['delivery_rate'] = $stats['total_recipients'] > 0
                ? round(($stats['delivered'] / $stats['total_recipients']) * 100, 2)
                : 0;

            $stats['read_rate'] = $stats['delivered'] > 0
                ? round(($stats['read'] / $stats['delivered']) * 100, 2)
                : 0;

            return $this->success([
                'campaign' => [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'status' => $campaign->status,
                ],
                'statistics' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('API Campaign stats error', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }
}

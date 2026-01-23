<?php

namespace App\Http\Controllers\Api;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TemplateController extends BaseApiController
{
    /**
     * List all templates
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $tenant = $request->user()->tenant;

            $query = MessageTemplate::where('tenant_id', $tenant->id)
                ->with('wabaAccount:id,name,phone_number');

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Category filter
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $perPage = min($request->get('per_page', 15), 100);
            $templates = $query->latest()->paginate($perPage);

            return $this->paginated($templates);
        } catch (\Exception $e) {
            Log::error('API Template index error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('Error al listar plantillas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single template
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $tenant = $request->user()->tenant;

            $template = MessageTemplate::where('tenant_id', $tenant->id)
                ->with('wabaAccount:id,name,phone_number')
                ->where('id', $id)
                ->first();

            if (!$template) {
                return $this->error('Plantilla no encontrada', 404);
            }

            return $this->success($template);
        } catch (\Exception $e) {
            Log::error('API Template show error', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('Error al obtener plantilla: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get templates by WABA account
     */
    public function byWabaAccount(Request $request, int $wabaAccountId): JsonResponse
    {
        try {
            $tenant = $request->user()->tenant;

            $templates = MessageTemplate::where('tenant_id', $tenant->id)
                ->where('waba_account_id', $wabaAccountId)
                ->where('status', 'APPROVED')
                ->select('id', 'name', 'language', 'category', 'status')
                ->orderBy('name')
                ->get();

            return $this->success($templates);
        } catch (\Exception $e) {
            Log::error('API Template byWabaAccount error', [
                'waba_account_id' => $wabaAccountId,
                'message' => $e->getMessage(),
            ]);
            return $this->error('Error al obtener plantillas: ' . $e->getMessage(), 500);
        }
    }
}
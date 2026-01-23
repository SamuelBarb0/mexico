<?php

namespace App\Http\Controllers\Api;

use App\Models\WabaAccount;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WabaAccountController extends BaseApiController
{
    /**
     * List all WABA accounts
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $tenant = $request->user()->tenant;

            $query = WabaAccount::where('tenant_id', $tenant->id);

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $accounts = $query->orderBy('name')->get();

            // Transform to hide sensitive data
            $accounts = $accounts->map(function ($account) {
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'phone_number' => $account->phone_number,
                    'status' => $account->status,
                    'quality_rating' => $account->quality_rating,
                    'verified_at' => $account->verified_at,
                    'last_sync_at' => $account->last_sync_at,
                ];
            });

            return $this->success($accounts);
        } catch (\Exception $e) {
            Log::error('API WabaAccount index error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('Error al listar cuentas WABA: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single WABA account
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $tenant = $request->user()->tenant;

            $account = WabaAccount::where('tenant_id', $tenant->id)
                ->where('id', $id)
                ->first();

            if (!$account) {
                return $this->error('Cuenta WABA no encontrada', 404);
            }

            return $this->success([
                'id' => $account->id,
                'name' => $account->name,
                'phone_number' => $account->phone_number,
                'phone_number_id' => $account->phone_number_id,
                'waba_id' => $account->waba_id,
                'business_account_id' => $account->business_account_id,
                'status' => $account->status,
                'quality_rating' => $account->quality_rating,
                'settings' => $account->settings,
                'verified_at' => $account->verified_at,
                'last_sync_at' => $account->last_sync_at,
            ]);
        } catch (\Exception $e) {
            Log::error('API WabaAccount show error', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('Error al obtener cuenta WABA: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get WABA account statistics
     */
    public function stats(Request $request, int $id): JsonResponse
    {
        try {
            $tenant = $request->user()->tenant;

            $account = WabaAccount::where('tenant_id', $tenant->id)
                ->where('id', $id)
                ->first();

            if (!$account) {
                return $this->error('Cuenta WABA no encontrada', 404);
            }

            $stats = [
                'total_campaigns' => $account->campaigns()->count(),
                'active_campaigns' => $account->campaigns()->where('status', 'active')->count(),
                'total_templates' => $account->templates()->count(),
                'approved_templates' => $account->templates()->where('status', 'APPROVED')->count(),
            ];

            return $this->success([
                'account' => [
                    'id' => $account->id,
                    'name' => $account->name,
                    'phone_number' => $account->phone_number,
                ],
                'statistics' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('API WabaAccount stats error', [
                'id' => $id,
                'message' => $e->getMessage(),
            ]);
            return $this->error('Error al obtener estadísticas: ' . $e->getMessage(), 500);
        }
    }
}
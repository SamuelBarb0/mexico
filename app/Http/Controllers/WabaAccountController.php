<?php

namespace App\Http\Controllers;

use App\Models\WabaAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WabaAccountController extends Controller
{
    public function index()
    {
        $wabaAccounts = WabaAccount::with('campaigns')->paginate(15);
        return view('waba-accounts.index', compact('wabaAccounts'));
    }

    public function create()
    {
        return view('waba-accounts.create');
    }

    public function createManual()
    {
        return view('waba-accounts.create-manual');
    }

    /**
     * Handle Facebook Login callback - fetch user's WABA accounts
     */
    public function facebookCallback(Request $request)
    {
        try {
            $accessToken = $request->input('access_token');
            $userID = $request->input('user_id');

            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibió el access token de Facebook'
                ], 400);
            }

            $apiVersion = config('services.facebook.api_version', 'v21.0');
            $graphUrl = "https://graph.facebook.com/{$apiVersion}";

            Log::info('Facebook callback started', ['user_id' => $userID]);

            // If connecting a specific account (from selector)
            if ($request->input('connect_specific')) {
                return $this->connectSpecificAccount($request, $accessToken, $userID);
            }

            // Fetch all WABA accounts the user has access to
            $wabaAccounts = $this->fetchUserWabaAccounts($accessToken, $graphUrl);

            if (empty($wabaAccounts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron cuentas de WhatsApp Business. Asegúrate de tener una cuenta WABA configurada en Meta Business Suite.',
                    'waba_accounts' => []
                ]);
            }

            // Filter out accounts that are already connected
            $notConnectedAccounts = array_filter($wabaAccounts, function($acc) {
                return !$acc['already_connected'];
            });

            // If all accounts are already connected
            if (empty($notConnectedAccounts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Todas las cuentas de WhatsApp Business ya están conectadas.',
                    'waba_accounts' => $wabaAccounts
                ]);
            }

            // If only one account not connected, connect it directly
            if (count($notConnectedAccounts) === 1) {
                $account = array_values($notConnectedAccounts)[0];
                return $this->createOrUpdateWabaAccount(
                    $account['phone_number_id'],
                    $account['waba_id'],
                    $account['business_id'],
                    $account['name'],
                    $account['phone_number'],
                    $account['quality_rating'] ?? 'unknown',
                    $accessToken,
                    $userID
                );
            }

            // Multiple accounts available - return list for user to select
            // Re-index the array and mark which ones are already connected
            return response()->json([
                'success' => false,
                'message' => 'Se encontraron múltiples cuentas. Selecciona cuál deseas conectar.',
                'waba_accounts' => array_values($wabaAccounts)
            ]);

        } catch (\Exception $e) {
            Log::error('Error in Facebook callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la conexión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch all WABA accounts the user has access to via Facebook Graph API
     */
    private function fetchUserWabaAccounts($accessToken, $graphUrl)
    {
        $wabaAccounts = [];

        // Method 1: Get businesses and their WABA accounts
        $businessesResponse = Http::get("{$graphUrl}/me/businesses", [
            'access_token' => $accessToken,
            'fields' => 'id,name,owned_whatsapp_business_accounts{id,name,phone_numbers{id,display_phone_number,verified_name,quality_rating}}'
        ]);

        Log::info('Businesses response', ['response' => $businessesResponse->json()]);

        if ($businessesResponse->successful()) {
            $businesses = $businessesResponse->json('data', []);

            foreach ($businesses as $business) {
                $wabas = $business['owned_whatsapp_business_accounts']['data'] ?? [];

                foreach ($wabas as $waba) {
                    $phones = $waba['phone_numbers']['data'] ?? [];

                    foreach ($phones as $phone) {
                        // Check if already connected to this tenant
                        $existing = WabaAccount::where('phone_number_id', $phone['id'])
                            ->where('tenant_id', auth()->user()->tenant_id)
                            ->first();

                        $wabaAccounts[] = [
                            'phone_number_id' => $phone['id'],
                            'waba_id' => $waba['id'],
                            'business_id' => $business['id'],
                            'name' => $waba['name'] ?? $business['name'] ?? 'WhatsApp Business',
                            'phone_number' => $phone['display_phone_number'] ?? '',
                            'verified_name' => $phone['verified_name'] ?? null,
                            'quality_rating' => $phone['quality_rating'] ?? 'UNKNOWN',
                            'already_connected' => $existing !== null
                        ];
                    }
                }
            }
        }

        // Method 2: Try client_whatsapp_business_accounts (alternative endpoint)
        if (empty($wabaAccounts)) {
            $altResponse = Http::get("{$graphUrl}/me/client_whatsapp_business_accounts", [
                'access_token' => $accessToken,
                'fields' => 'id,name,phone_numbers{id,display_phone_number,verified_name,quality_rating}'
            ]);

            Log::info('Client WABA response', ['response' => $altResponse->json()]);

            if ($altResponse->successful()) {
                $wabas = $altResponse->json('data', []);

                foreach ($wabas as $waba) {
                    $phones = $waba['phone_numbers']['data'] ?? [];

                    foreach ($phones as $phone) {
                        $existing = WabaAccount::where('phone_number_id', $phone['id'])
                            ->where('tenant_id', auth()->user()->tenant_id)
                            ->first();

                        $wabaAccounts[] = [
                            'phone_number_id' => $phone['id'],
                            'waba_id' => $waba['id'],
                            'business_id' => '',
                            'name' => $waba['name'] ?? 'WhatsApp Business',
                            'phone_number' => $phone['display_phone_number'] ?? '',
                            'verified_name' => $phone['verified_name'] ?? null,
                            'quality_rating' => $phone['quality_rating'] ?? 'UNKNOWN',
                            'already_connected' => $existing !== null
                        ];
                    }
                }
            }
        }

        return $wabaAccounts;
    }

    /**
     * Connect a specific WABA account selected by the user
     */
    private function connectSpecificAccount(Request $request, $accessToken, $userID)
    {
        $phoneNumberId = $request->input('phone_number_id');
        $wabaId = $request->input('waba_id');
        $businessId = $request->input('business_account_id');
        $name = $request->input('name');
        $phoneNumber = $request->input('phone_number');

        if (!$phoneNumberId) {
            return response()->json([
                'success' => false,
                'message' => 'No se proporcionó el ID del número de teléfono'
            ], 400);
        }

        return $this->createOrUpdateWabaAccount(
            $phoneNumberId,
            $wabaId,
            $businessId,
            $name,
            $phoneNumber,
            'unknown',
            $accessToken,
            $userID
        );
    }

    /**
     * Create or update a WABA account
     */
    private function createOrUpdateWabaAccount($phoneNumberId, $wabaId, $businessId, $name, $phoneNumber, $qualityRating, $accessToken, $userID)
    {
        // Check if already exists
        $existingWaba = WabaAccount::where('phone_number_id', $phoneNumberId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if ($existingWaba) {
            $existingWaba->update(['access_token' => $accessToken]);

            Log::info('WABA Account token updated', [
                'waba_account_id' => $existingWaba->id,
                'phone_number_id' => $phoneNumberId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cuenta ya existente, token actualizado',
                'waba_account' => $existingWaba
            ]);
        }

        // Create new WABA account
        $wabaAccount = WabaAccount::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $name ?: 'WhatsApp Business',
            'phone_number' => $phoneNumber ?: '',
            'phone_number_id' => $phoneNumberId,
            'business_account_id' => $businessId ?: '',
            'waba_id' => $wabaId ?: '',
            'access_token' => $accessToken,
            'status' => 'active',
            'quality_rating' => strtolower($qualityRating ?: 'unknown'),
            'settings' => [
                'facebook_user_id' => $userID,
                'connected_via' => 'facebook_login',
                'connected_at' => now()->toIso8601String()
            ]
        ]);

        Log::info('WABA Account created via Facebook Login', [
            'waba_account_id' => $wabaAccount->id,
            'tenant_id' => auth()->user()->tenant_id,
            'phone_number' => $phoneNumber
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cuenta conectada exitosamente',
            'waba_account' => $wabaAccount
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'phone_number_id' => 'required|string|max:255|unique:waba_accounts,phone_number_id',
            'business_account_id' => 'required|string|max:255',
            'waba_id' => 'required|string|max:255',
            'access_token' => 'required|string',
            'status' => 'required|in:pending,active,inactive,suspended',
            'quality_rating' => 'required|in:green,yellow,red,unknown',
            'is_verified' => 'required|boolean',
            'settings' => 'nullable|string',
        ]);

        // Process settings
        if (isset($validated['settings']) && !empty($validated['settings'])) {
            $settings = json_decode($validated['settings'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['settings' => 'El formato JSON no es válido'])->withInput();
            }
            $validated['settings'] = $settings;
        }

        $wabaAccount = WabaAccount::create($validated);

        return redirect()->route('waba-accounts.index')
            ->with('success', 'Cuenta WABA creada exitosamente');
    }

    public function show(WabaAccount $wabaAccount)
    {
        $wabaAccount->load('campaigns');
        return view('waba-accounts.show', compact('wabaAccount'));
    }

    public function edit(WabaAccount $wabaAccount)
    {
        return view('waba-accounts.edit', compact('wabaAccount'));
    }

    public function update(Request $request, WabaAccount $wabaAccount)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'phone_number_id' => 'required|string|max:255|unique:waba_accounts,phone_number_id,' . $wabaAccount->id,
            'business_account_id' => 'required|string|max:255',
            'waba_id' => 'required|string|max:255',
            'access_token' => 'required|string',
            'status' => 'required|in:pending,active,inactive,suspended',
            'quality_rating' => 'required|in:green,yellow,red,unknown',
            'is_verified' => 'required|boolean',
            'settings' => 'nullable|string',
        ]);

        // Process settings
        if (isset($validated['settings']) && !empty($validated['settings'])) {
            $settings = json_decode($validated['settings'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->withErrors(['settings' => 'El formato JSON no es válido'])->withInput();
            }
            $validated['settings'] = $settings;
        }

        $wabaAccount->update($validated);

        return redirect()->route('waba-accounts.show', $wabaAccount)
            ->with('success', 'Cuenta WABA actualizada exitosamente');
    }

    public function destroy(WabaAccount $wabaAccount)
    {
        // Check if there are campaigns using this WABA account
        if ($wabaAccount->campaigns()->count() > 0) {
            return back()->withErrors(['error' => 'No se puede eliminar esta cuenta WABA porque tiene campañas asociadas']);
        }

        $wabaAccount->delete();

        return redirect()->route('waba-accounts.index')
            ->with('success', 'Cuenta WABA eliminada exitosamente');
    }
}

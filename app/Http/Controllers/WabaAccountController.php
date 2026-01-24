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
     * Handle Facebook Embedded Signup callback
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

            // Step 1: Debug token to get permissions and verify
            $debugResponse = Http::get("{$graphUrl}/debug_token", [
                'input_token' => $accessToken,
                'access_token' => config('services.facebook.app_id') . '|' . config('services.facebook.app_secret')
            ]);

            Log::info('Debug token response', ['response' => $debugResponse->json()]);

            // Step 2: Get shared WABA IDs from the embedded signup response
            $sharedWabaIds = $request->input('shared_waba_ids', []);
            $phoneNumberId = $request->input('phone_number_id');

            // If we got phone_number_id directly from embedded signup, use it
            if ($phoneNumberId) {
                return $this->createWabaFromPhoneNumber($phoneNumberId, $accessToken, $userID, $graphUrl);
            }

            // Step 3: Get WhatsApp Business Accounts via Business Manager
            $businessesResponse = Http::get("{$graphUrl}/me/businesses", [
                'access_token' => $accessToken,
                'fields' => 'id,name,owned_whatsapp_business_accounts{id,name,phone_numbers{id,display_phone_number,verified_name,quality_rating}}'
            ]);

            Log::info('Businesses response', ['response' => $businessesResponse->json()]);

            if (!$businessesResponse->successful()) {
                // Try alternative: get WABAs directly
                return $this->getWabasDirectly($accessToken, $userID, $graphUrl);
            }

            $businesses = $businessesResponse->json('data', []);

            if (empty($businesses)) {
                return $this->getWabasDirectly($accessToken, $userID, $graphUrl);
            }

            // Find WABA with phone numbers
            foreach ($businesses as $business) {
                $wabas = $business['owned_whatsapp_business_accounts']['data'] ?? [];

                foreach ($wabas as $waba) {
                    $phones = $waba['phone_numbers']['data'] ?? [];

                    if (!empty($phones)) {
                        $phone = $phones[0];

                        // Check if already exists
                        $existingWaba = WabaAccount::where('phone_number_id', $phone['id'])
                            ->where('tenant_id', auth()->user()->tenant_id)
                            ->first();

                        if ($existingWaba) {
                            // Update access token
                            $existingWaba->update(['access_token' => $accessToken]);
                            return response()->json([
                                'success' => true,
                                'message' => 'Cuenta ya existente, token actualizado',
                                'waba_account' => $existingWaba
                            ]);
                        }

                        // Create new WABA account
                        $wabaAccount = WabaAccount::create([
                            'tenant_id' => auth()->user()->tenant_id,
                            'name' => $waba['name'] ?? $business['name'] ?? 'WhatsApp Business',
                            'phone_number' => $phone['display_phone_number'] ?? '',
                            'phone_number_id' => $phone['id'],
                            'business_account_id' => $business['id'],
                            'waba_id' => $waba['id'],
                            'access_token' => $accessToken,
                            'status' => 'active',
                            'quality_rating' => strtolower($phone['quality_rating'] ?? 'unknown'),
                            'settings' => [
                                'facebook_user_id' => $userID,
                                'verified_name' => $phone['verified_name'] ?? null,
                                'connected_via' => 'facebook_embedded_signup',
                                'connected_at' => now()->toIso8601String()
                            ]
                        ]);

                        Log::info('WABA Account created via Facebook Embedded Signup', [
                            'waba_account_id' => $wabaAccount->id,
                            'tenant_id' => auth()->user()->tenant_id,
                            'phone_number' => $phone['display_phone_number'] ?? ''
                        ]);

                        return response()->json([
                            'success' => true,
                            'message' => 'Cuenta conectada exitosamente',
                            'waba_account' => $wabaAccount
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'No se encontraron números de WhatsApp Business configurados. Asegúrate de completar la configuración en Facebook.'
            ], 404);

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
     * Try to get WABAs directly using the user token
     */
    private function getWabasDirectly($accessToken, $userID, $graphUrl)
    {
        // Try getting WABA via shared_waba_id endpoint (for embedded signup)
        $wabaResponse = Http::get("{$graphUrl}/{$userID}/assigned_business_asset_groups", [
            'access_token' => $accessToken,
            'fields' => 'id,name'
        ]);

        Log::info('Assigned business assets response', ['response' => $wabaResponse->json()]);

        // Alternative: Try to get any accessible WABAs
        $altResponse = Http::get("{$graphUrl}/me", [
            'access_token' => $accessToken,
            'fields' => 'id,name,accounts{whatsapp_business_account}'
        ]);

        Log::info('Me accounts response', ['response' => $altResponse->json()]);

        return response()->json([
            'success' => false,
            'message' => 'No se encontraron cuentas de WhatsApp Business. Por favor usa la conexión manual.',
            'debug' => [
                'user_id' => $userID,
                'has_token' => !empty($accessToken)
            ]
        ], 404);
    }

    /**
     * Create WABA from a phone number ID (direct from embedded signup)
     */
    private function createWabaFromPhoneNumber($phoneNumberId, $accessToken, $userID, $graphUrl)
    {
        // Get phone number details
        $phoneResponse = Http::get("{$graphUrl}/{$phoneNumberId}", [
            'access_token' => $accessToken,
            'fields' => 'id,display_phone_number,verified_name,quality_rating'
        ]);

        if (!$phoneResponse->successful()) {
            Log::error('Phone number fetch error', ['response' => $phoneResponse->json()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del número de teléfono'
            ], 400);
        }

        $phone = $phoneResponse->json();

        // Check if already exists
        $existingWaba = WabaAccount::where('phone_number_id', $phoneNumberId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if ($existingWaba) {
            $existingWaba->update(['access_token' => $accessToken]);
            return response()->json([
                'success' => true,
                'message' => 'Cuenta ya existente, token actualizado',
                'waba_account' => $existingWaba
            ]);
        }

        // Create new WABA
        $wabaAccount = WabaAccount::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $phone['verified_name'] ?? 'WhatsApp Business',
            'phone_number' => $phone['display_phone_number'] ?? '',
            'phone_number_id' => $phoneNumberId,
            'business_account_id' => '',
            'waba_id' => '',
            'access_token' => $accessToken,
            'status' => 'active',
            'quality_rating' => strtolower($phone['quality_rating'] ?? 'unknown'),
            'settings' => [
                'facebook_user_id' => $userID,
                'verified_name' => $phone['verified_name'] ?? null,
                'connected_via' => 'facebook_embedded_signup_direct',
                'connected_at' => now()->toIso8601String()
            ]
        ]);

        Log::info('WABA Account created via direct phone number', [
            'waba_account_id' => $wabaAccount->id,
            'phone_number_id' => $phoneNumberId
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

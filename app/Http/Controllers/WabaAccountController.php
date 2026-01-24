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
            $code = $request->input('code'); // Embedded Signup code

            if (!$accessToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibió el access token de Facebook'
                ], 400);
            }

            $apiVersion = config('services.facebook.api_version', 'v21.0');
            $graphUrl = "https://graph.facebook.com/{$apiVersion}";

            Log::info('Facebook callback started', [
                'user_id' => $userID,
                'has_code' => !empty($code)
            ]);

            // If we have a code from Embedded Signup, exchange it for session info
            if (!empty($code)) {
                $embeddedResult = $this->handleEmbeddedSignupCode($code, $graphUrl);
                if ($embeddedResult) {
                    return $embeddedResult;
                }
            }

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
     * Handle WhatsApp Embedded Signup code exchange
     * This extracts WABA info from the session created during Embedded Signup
     */
    private function handleEmbeddedSignupCode($code, $graphUrl)
    {
        try {
            $appId = config('services.facebook.app_id');
            $appSecret = config('services.facebook.app_secret');

            if (empty($appSecret)) {
                Log::warning('FACEBOOK_APP_SECRET not configured, skipping code exchange');
                return null;
            }

            // Exchange code for access token
            $tokenResponse = Http::get("{$graphUrl}/oauth/access_token", [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'code' => $code
            ]);

            Log::info('Embedded Signup token exchange', ['response' => $tokenResponse->json()]);

            if (!$tokenResponse->successful()) {
                Log::error('Failed to exchange Embedded Signup code', ['response' => $tokenResponse->json()]);
                return null;
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'] ?? null;

            if (!$accessToken) {
                return null;
            }

            // Get debug info about the token to find the WABA
            $debugResponse = Http::get("{$graphUrl}/debug_token", [
                'input_token' => $accessToken,
                'access_token' => "{$appId}|{$appSecret}"
            ]);

            Log::info('Embedded Signup debug token', ['response' => $debugResponse->json()]);

            // The Embedded Signup returns a user token, use it to get WABA info
            // Try to get the shared WABA from the response
            $wabaAccounts = $this->fetchUserWabaAccounts($accessToken, $graphUrl);

            if (!empty($wabaAccounts)) {
                // Get only non-connected accounts
                $newAccounts = array_filter($wabaAccounts, fn($acc) => !$acc['already_connected']);

                if (count($newAccounts) === 1) {
                    $account = array_values($newAccounts)[0];

                    // Use the global System User token for this account
                    return $this->createOrUpdateWabaAccount(
                        $account['phone_number_id'],
                        $account['waba_id'],
                        $account['business_id'],
                        $account['name'],
                        $account['phone_number'],
                        $account['quality_rating'] ?? 'unknown',
                        $accessToken,
                        null
                    );
                }
            }

            // If we couldn't extract directly, return null to continue with standard flow
            return null;

        } catch (\Exception $e) {
            Log::error('Error handling Embedded Signup code', [
                'error' => $e->getMessage()
            ]);
            return null;
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
        // Use the global System User Token if available, otherwise fall back to user token
        $globalToken = config('services.meta.access_token');
        $tokenToUse = !empty($globalToken) ? $globalToken : $accessToken;
        $tokenSource = !empty($globalToken) ? 'system_user' : 'facebook_login';

        // Check if already exists
        $existingWaba = WabaAccount::where('phone_number_id', $phoneNumberId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if ($existingWaba) {
            $existingWaba->update(['access_token' => $tokenToUse]);

            Log::info('WABA Account token updated', [
                'waba_account_id' => $existingWaba->id,
                'phone_number_id' => $phoneNumberId,
                'token_source' => $tokenSource
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cuenta ya existente, token actualizado',
                'waba_account' => $existingWaba
            ]);
        }

        // Create new WABA account with System User Token
        $wabaAccount = WabaAccount::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $name ?: 'WhatsApp Business',
            'phone_number' => $phoneNumber ?: '',
            'phone_number_id' => $phoneNumberId,
            'business_account_id' => $businessId ?: '',
            'waba_id' => $wabaId ?: '',
            'access_token' => $tokenToUse,
            'status' => 'active',
            'quality_rating' => strtolower($qualityRating ?: 'unknown'),
            'settings' => [
                'facebook_user_id' => $userID,
                'connected_via' => 'facebook_login',
                'token_source' => $tokenSource,
                'connected_at' => now()->toIso8601String()
            ]
        ]);

        Log::info('WABA Account created via Facebook Login', [
            'waba_account_id' => $wabaAccount->id,
            'tenant_id' => auth()->user()->tenant_id,
            'phone_number' => $phoneNumber,
            'token_source' => $tokenSource
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

    /**
     * Verify the WABA account status with Meta API
     */
    public function verify(WabaAccount $wabaAccount)
    {
        $apiVersion = config('services.meta.api_version', 'v21.0');
        $accessToken = $wabaAccount->access_token;
        $phoneNumberId = $wabaAccount->phone_number_id;

        $results = [
            'phone_number_id' => $phoneNumberId,
            'checks' => []
        ];

        // Check 1: Verify phone number exists and is registered
        $phoneResponse = Http::withToken($accessToken)
            ->get("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}", [
                'fields' => 'id,display_phone_number,verified_name,quality_rating,platform_type,is_official_business_account,account_mode,status'
            ]);

        Log::info('Phone verification response', [
            'phone_number_id' => $phoneNumberId,
            'status' => $phoneResponse->status(),
            'response' => $phoneResponse->json()
        ]);

        if ($phoneResponse->successful()) {
            $phoneData = $phoneResponse->json();
            $results['checks']['phone_number'] = [
                'status' => 'success',
                'data' => $phoneData
            ];

            // Update the account with fresh data
            $wabaAccount->update([
                'phone_number' => $phoneData['display_phone_number'] ?? $wabaAccount->phone_number,
                'quality_rating' => strtolower($phoneData['quality_rating'] ?? 'unknown'),
                'last_sync_at' => now()
            ]);
        } else {
            $error = $phoneResponse->json();
            $results['checks']['phone_number'] = [
                'status' => 'error',
                'error_code' => $error['error']['code'] ?? null,
                'error_message' => $error['error']['message'] ?? 'Unknown error'
            ];
        }

        // Check 2: Verify we can access the WABA
        if ($wabaAccount->waba_id) {
            $wabaResponse = Http::withToken($accessToken)
                ->get("https://graph.facebook.com/{$apiVersion}/{$wabaAccount->waba_id}", [
                    'fields' => 'id,name,currency,timezone_id,message_template_namespace'
                ]);

            Log::info('WABA verification response', [
                'waba_id' => $wabaAccount->waba_id,
                'status' => $wabaResponse->status(),
                'response' => $wabaResponse->json()
            ]);

            if ($wabaResponse->successful()) {
                $results['checks']['waba'] = [
                    'status' => 'success',
                    'data' => $wabaResponse->json()
                ];
            } else {
                $error = $wabaResponse->json();
                $results['checks']['waba'] = [
                    'status' => 'error',
                    'error_code' => $error['error']['code'] ?? null,
                    'error_message' => $error['error']['message'] ?? 'Unknown error'
                ];
            }
        }

        // Check 3: Test sending capability (dry run)
        $testResponse = Http::withToken($accessToken)
            ->get("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/whatsapp_business_profile", [
                'fields' => 'about,address,description,email,profile_picture_url,websites,vertical'
            ]);

        if ($testResponse->successful()) {
            $results['checks']['messaging_capability'] = [
                'status' => 'success',
                'message' => 'Account can access WhatsApp Business API'
            ];
        } else {
            $error = $testResponse->json();
            $results['checks']['messaging_capability'] = [
                'status' => 'error',
                'error_code' => $error['error']['code'] ?? null,
                'error_message' => $error['error']['message'] ?? 'Unknown error'
            ];
        }

        // Determine overall status
        $hasErrors = collect($results['checks'])->contains(function ($check) {
            return $check['status'] === 'error';
        });

        if ($hasErrors) {
            // Check for specific error codes
            $phoneCheck = $results['checks']['phone_number'] ?? null;
            if ($phoneCheck && isset($phoneCheck['error_code'])) {
                $errorCode = $phoneCheck['error_code'];

                // Error 100 = Invalid parameter (phone not registered)
                // Error 190 = Invalid access token
                // Error 10 = Permission denied
                if ($errorCode == 190) {
                    return back()->with('error', 'El token de acceso ha expirado o es inválido. Por favor vuelve a conectar la cuenta con Facebook.');
                } elseif ($errorCode == 100) {
                    return back()->with('error', 'El número de teléfono no está registrado correctamente en WhatsApp Business. Verifica la configuración en Meta Business Suite.');
                } elseif ($errorCode == 10) {
                    return back()->with('error', 'No tienes permisos suficientes. El token necesita los permisos whatsapp_business_management y whatsapp_business_messaging.');
                }
            }

            return back()->with('warning', 'Se encontraron algunos problemas con la cuenta. Revisa los logs para más detalles.');
        }

        return back()->with('success', 'La cuenta está correctamente configurada y lista para enviar mensajes.');
    }

    /**
     * Register the phone number with WhatsApp Business API
     * This is needed if the account shows "Account not Registered" error
     */
    public function register(WabaAccount $wabaAccount)
    {
        $apiVersion = config('services.meta.api_version', 'v21.0');
        $phoneNumberId = $wabaAccount->phone_number_id;

        // Try with the account's token first, then fall back to the global META_ACCESS_TOKEN
        $tokens = [
            $wabaAccount->access_token,
            config('services.meta.access_token')
        ];

        $lastError = null;

        foreach ($tokens as $accessToken) {
            if (empty($accessToken)) continue;

            // Try to register the phone number
            $response = Http::withToken($accessToken)
                ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/register", [
                    'messaging_product' => 'whatsapp',
                    'pin' => '123456' // Default 6-digit PIN
                ]);

            Log::info('Phone registration attempt', [
                'phone_number_id' => $phoneNumberId,
                'token_prefix' => substr($accessToken, 0, 20) . '...',
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            if ($response->successful()) {
                // If we used the global token, update the account with it
                if ($accessToken === config('services.meta.access_token')) {
                    $wabaAccount->update([
                        'access_token' => $accessToken,
                        'status' => 'active',
                        'verified_at' => now()
                    ]);
                } else {
                    $wabaAccount->update([
                        'status' => 'active',
                        'verified_at' => now()
                    ]);
                }

                return back()->with('success', 'Número registrado exitosamente en WhatsApp Business API.');
            }

            $lastError = $response->json();
        }

        $errorMessage = $lastError['error']['message'] ?? 'Error desconocido';

        // Provide helpful error message
        if (str_contains($errorMessage, 'permission') || str_contains($errorMessage, 'access')) {
            return back()->with('error', "Error: {$errorMessage}. El token no tiene permisos. Necesitas un System User Token de Meta Business Suite.");
        }

        return back()->with('error', "Error al registrar el número: {$errorMessage}");
    }

    /**
     * Update the access token with the global META_ACCESS_TOKEN from .env
     */
    public function useGlobalToken(WabaAccount $wabaAccount)
    {
        $globalToken = config('services.meta.access_token');

        if (empty($globalToken)) {
            return back()->with('error', 'No hay un META_ACCESS_TOKEN configurado en el archivo .env');
        }

        // First verify that the global token has access to this phone_number_id
        $apiVersion = config('services.meta.api_version', 'v21.0');
        $response = Http::withToken($globalToken)
            ->get("https://graph.facebook.com/{$apiVersion}/{$wabaAccount->phone_number_id}", [
                'fields' => 'id,display_phone_number,verified_name'
            ]);

        if (!$response->successful()) {
            $error = $response->json();
            $errorCode = $error['error']['code'] ?? 'unknown';
            $errorMsg = $error['error']['message'] ?? 'Error desconocido';

            Log::warning('Global token does not have access to this phone_number_id', [
                'phone_number_id' => $wabaAccount->phone_number_id,
                'error_code' => $errorCode,
                'error_message' => $errorMsg
            ]);

            return back()->with('error', "El token global no tiene acceso a este número (ID: {$wabaAccount->phone_number_id}). El System User necesita tener asignado este WhatsApp Business Account en Meta Business Suite. Error: {$errorMsg}");
        }

        $wabaAccount->update(['access_token' => $globalToken]);

        return back()->with('success', 'Token actualizado con el META_ACCESS_TOKEN global. La cuenta está verificada y lista para enviar mensajes.');
    }

    /**
     * Lookup phone_number_id for a specific WABA ID
     * This helps users find the correct phone_number_id when they only know the WABA ID
     */
    public function lookupPhoneNumbers(Request $request)
    {
        $wabaId = $request->input('waba_id');
        $globalToken = config('services.meta.access_token');

        if (empty($globalToken)) {
            return response()->json(['error' => 'No META_ACCESS_TOKEN configured'], 400);
        }

        if (empty($wabaId)) {
            return response()->json(['error' => 'waba_id is required'], 400);
        }

        $apiVersion = config('services.meta.api_version', 'v21.0');

        // Query phone numbers for this WABA
        $response = Http::withToken($globalToken)
            ->get("https://graph.facebook.com/{$apiVersion}/{$wabaId}/phone_numbers", [
                'fields' => 'id,display_phone_number,verified_name,quality_rating,code_verification_status,platform_type'
            ]);

        if (!$response->successful()) {
            $error = $response->json();
            return response()->json([
                'error' => 'Failed to fetch phone numbers',
                'details' => $error['error']['message'] ?? 'Unknown error',
                'error_code' => $error['error']['code'] ?? null
            ], 400);
        }

        $phones = $response->json('data', []);

        return response()->json([
            'waba_id' => $wabaId,
            'phone_numbers' => $phones,
            'message' => count($phones) > 0
                ? 'Found ' . count($phones) . ' phone number(s). Use the "id" field as your phone_number_id.'
                : 'No phone numbers found for this WABA ID. Make sure the System User has access to this WABA.'
        ], 200, [], JSON_PRETTY_PRINT);
    }

    /**
     * Debug: List all WABA accounts accessible by the global token
     * This helps identify which accounts the System User has access to
     */
    public function debugGlobalToken()
    {
        $globalToken = config('services.meta.access_token');

        if (empty($globalToken)) {
            return response()->json(['error' => 'No META_ACCESS_TOKEN configured'], 400);
        }

        $apiVersion = config('services.meta.api_version', 'v21.0');
        $results = [
            'token_prefix' => substr($globalToken, 0, 20) . '...',
            'businesses' => [],
            'waba_accounts' => [],
            'phone_numbers' => []
        ];

        // Get businesses
        $bizResponse = Http::withToken($globalToken)
            ->get("https://graph.facebook.com/{$apiVersion}/me/businesses", [
                'fields' => 'id,name,owned_whatsapp_business_accounts{id,name,phone_numbers{id,display_phone_number,verified_name,quality_rating}}'
            ]);

        if ($bizResponse->successful()) {
            $businesses = $bizResponse->json('data', []);
            foreach ($businesses as $biz) {
                $results['businesses'][] = [
                    'id' => $biz['id'],
                    'name' => $biz['name']
                ];

                $wabas = $biz['owned_whatsapp_business_accounts']['data'] ?? [];
                foreach ($wabas as $waba) {
                    $results['waba_accounts'][] = [
                        'id' => $waba['id'],
                        'name' => $waba['name'] ?? 'N/A',
                        'business_id' => $biz['id']
                    ];

                    $phones = $waba['phone_numbers']['data'] ?? [];
                    foreach ($phones as $phone) {
                        $results['phone_numbers'][] = [
                            'phone_number_id' => $phone['id'],
                            'display_phone_number' => $phone['display_phone_number'] ?? 'N/A',
                            'verified_name' => $phone['verified_name'] ?? 'N/A',
                            'waba_id' => $waba['id']
                        ];
                    }
                }
            }
        } else {
            $results['businesses_error'] = $bizResponse->json();
        }

        return response()->json($results, 200, [], JSON_PRETTY_PRINT);
    }
}

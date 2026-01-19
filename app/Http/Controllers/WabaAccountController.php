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

            // Get WhatsApp Business Account details from Facebook Graph API
            $response = Http::get('https://graph.facebook.com/v18.0/me', [
                'access_token' => $accessToken,
                'fields' => 'id,name'
            ]);

            if (!$response->successful()) {
                Log::error('Facebook API error', ['response' => $response->body()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error al verificar el token con Facebook'
                ], 400);
            }

            // Get WhatsApp Business Accounts
            $wabaResponse = Http::get('https://graph.facebook.com/v18.0/' . $userID . '/businesses', [
                'access_token' => $accessToken,
                'fields' => 'id,name'
            ]);

            if (!$wabaResponse->successful()) {
                Log::error('Facebook WABA API error', ['response' => $wabaResponse->body()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error al obtener las cuentas de WhatsApp Business'
                ], 400);
            }

            $businesses = $wabaResponse->json('data', []);

            if (empty($businesses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron cuentas de WhatsApp Business asociadas'
                ], 404);
            }

            // Get the first business (or let user choose in future)
            $business = $businesses[0];
            $businessId = $business['id'];

            // Get WhatsApp Business Phone Numbers
            $phonesResponse = Http::get('https://graph.facebook.com/v18.0/' . $businessId . '/phone_numbers', [
                'access_token' => $accessToken
            ]);

            if (!$phonesResponse->successful()) {
                Log::error('Facebook Phone Numbers API error', ['response' => $phonesResponse->body()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Error al obtener los números de teléfono'
                ], 400);
            }

            $phones = $phonesResponse->json('data', []);

            if (empty($phones)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron números de teléfono configurados'
                ], 404);
            }

            // Get the first phone number
            $phone = $phones[0];

            // Create WABA Account in database
            $wabaAccount = WabaAccount::create([
                'tenant_id' => auth()->user()->tenant_id,
                'name' => $business['name'] ?? 'WABA desde Facebook',
                'phone_number' => $phone['display_phone_number'] ?? 'N/A',
                'phone_number_id' => $phone['id'],
                'business_account_id' => $businessId,
                'waba_id' => $businessId,
                'access_token' => $accessToken,
                'status' => 'active',
                'quality_rating' => $phone['quality_rating'] ?? 'unknown',
                'is_verified' => $phone['is_verified'] ?? false,
                'settings' => [
                    'facebook_user_id' => $userID,
                    'connected_via' => 'facebook_embedded_signup',
                    'connected_at' => now()->toIso8601String()
                ]
            ]);

            Log::info('WABA Account created via Facebook Embedded Signup', [
                'waba_account_id' => $wabaAccount->id,
                'tenant_id' => auth()->user()->tenant_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cuenta conectada exitosamente',
                'waba_account' => $wabaAccount
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

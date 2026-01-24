@extends('layouts.app')

@section('title', 'Editar Cuenta WABA')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Editar Cuenta WABA</h2>
    </div>

    <form action="{{ route('waba-accounts.update', $wabaAccount) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre *</label>
                <input type="text" name="name" id="name" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    value="{{ old('name', $wabaAccount->name) }}">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone_number" class="block text-sm font-medium text-gray-700">Número de Teléfono *</label>
                <input type="text" name="phone_number" id="phone_number" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('phone_number') border-red-500 @enderror"
                    value="{{ old('phone_number', $wabaAccount->phone_number) }}">
                @error('phone_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone_number_id" class="block text-sm font-medium text-gray-700">Phone Number ID *</label>
                <input type="text" name="phone_number_id" id="phone_number_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('phone_number_id') border-red-500 @enderror"
                    value="{{ old('phone_number_id', $wabaAccount->phone_number_id) }}">
                @error('phone_number_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">ID del número de teléfono de WhatsApp Business</p>
            </div>

            <div>
                <label for="business_account_id" class="block text-sm font-medium text-gray-700">Business Account ID *</label>
                <input type="text" name="business_account_id" id="business_account_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('business_account_id') border-red-500 @enderror"
                    value="{{ old('business_account_id', $wabaAccount->business_account_id) }}">
                @error('business_account_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="waba_id" class="block text-sm font-medium text-gray-700">WABA ID *</label>
                <input type="text" name="waba_id" id="waba_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('waba_id') border-red-500 @enderror"
                    value="{{ old('waba_id', $wabaAccount->waba_id) }}">
                @error('waba_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">WhatsApp Business Account ID</p>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                <select name="status" id="status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="pending" {{ old('status', $wabaAccount->status) === 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="active" {{ old('status', $wabaAccount->status) === 'active' ? 'selected' : '' }}>Activa</option>
                    <option value="inactive" {{ old('status', $wabaAccount->status) === 'inactive' ? 'selected' : '' }}>Inactiva</option>
                    <option value="suspended" {{ old('status', $wabaAccount->status) === 'suspended' ? 'selected' : '' }}>Suspendida</option>
                </select>
            </div>

            <div>
                <label for="quality_rating" class="block text-sm font-medium text-gray-700">Calificación de Calidad</label>
                <select name="quality_rating" id="quality_rating"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="unknown" {{ old('quality_rating', $wabaAccount->quality_rating) === 'unknown' ? 'selected' : '' }}>Desconocido</option>
                    <option value="green" {{ old('quality_rating', $wabaAccount->quality_rating) === 'green' ? 'selected' : '' }}>Verde</option>
                    <option value="yellow" {{ old('quality_rating', $wabaAccount->quality_rating) === 'yellow' ? 'selected' : '' }}>Amarillo</option>
                    <option value="red" {{ old('quality_rating', $wabaAccount->quality_rating) === 'red' ? 'selected' : '' }}>Rojo</option>
                </select>
            </div>

            <div>
                <label for="is_verified" class="block text-sm font-medium text-gray-700">Verificado</label>
                <select name="is_verified" id="is_verified"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="0" {{ old('is_verified', $wabaAccount->is_verified ? '1' : '0') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_verified', $wabaAccount->is_verified ? '1' : '0') === '1' ? 'selected' : '' }}>Sí</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="access_token" class="block text-sm font-medium text-gray-700">Access Token *</label>
                <textarea name="access_token" id="access_token" rows="2" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('access_token') border-red-500 @enderror"
                    placeholder="EAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">{{ old('access_token', $wabaAccount->access_token) }}</textarea>
                @error('access_token')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Token de acceso de la API de WhatsApp Business</p>

                <!-- Token Help Box -->
                <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        ¿Ves error "Account not Registered"?
                    </h4>
                    <p class="text-sm text-blue-800 mb-3">
                        Si conectaste tu cuenta con Facebook Login, necesitas reemplazar el token con un <strong>Permanent Access Token</strong> de Meta Business Suite.
                    </p>
                    <div class="text-sm text-blue-800 space-y-2">
                        <p class="font-medium">Pasos para obtener el token correcto:</p>
                        <ol class="list-decimal list-inside space-y-1 ml-2">
                            <li>Ve a <a href="https://business.facebook.com/settings/system-users" target="_blank" class="text-blue-600 hover:underline font-medium">Meta Business Settings → System Users</a></li>
                            <li>Crea un System User (si no tienes uno)</li>
                            <li>Asigna el activo de WhatsApp Business Account</li>
                            <li>Genera un token con permisos: <code class="bg-blue-100 px-1 rounded">whatsapp_business_messaging</code>, <code class="bg-blue-100 px-1 rounded">whatsapp_business_management</code></li>
                            <li>Copia el token y pegalo aqui arriba</li>
                        </ol>
                    </div>
                    <div class="mt-3 pt-3 border-t border-blue-200">
                        <a href="https://developers.facebook.com/docs/whatsapp/business-management-api/get-started#system-user-access-tokens"
                           target="_blank"
                           class="inline-flex items-center text-sm text-blue-700 hover:text-blue-900 font-medium">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Ver documentacion oficial de Meta
                        </a>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <label for="settings" class="block text-sm font-medium text-gray-700">Configuración (JSON)</label>
                <textarea name="settings" id="settings" rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('settings') border-red-500 @enderror"
                    placeholder='{"webhook_url": "https://example.com/webhook", "verify_token": "mi_token_secreto"}'>{{ old('settings', $wabaAccount->settings ? json_encode($wabaAccount->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                @error('settings')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Formato JSON válido. Configuraciones adicionales de la cuenta</p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-3">
            <a href="{{ route('waba-accounts.show', $wabaAccount) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">
                Cancelar
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded cursor-pointer">
                Actualizar Cuenta WABA
            </button>
        </div>
    </form>
</div>
@endsection

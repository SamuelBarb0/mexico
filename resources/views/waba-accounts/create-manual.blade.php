@extends('layouts.app')

@section('title', 'Conexión Manual - Cuenta WABA')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl sm:rounded-2xl shadow-2xl p-4 sm:p-6 lg:p-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex-1">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-1 sm:mb-2">
                    Conexión Manual de Cuenta WABA
                </h1>
                <p class="text-indigo-100 text-sm sm:text-base lg:text-lg">Ingresa los datos de tu cuenta de WhatsApp Business API manualmente</p>
            </div>
            <a href="{{ route('waba-accounts.create') }}" class="bg-white text-indigo-600 px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg sm:rounded-xl font-bold text-sm sm:text-base shadow-lg hover:shadow-xl transition-all cursor-pointer w-full sm:w-auto justify-center flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="truncate">Volver a Conexión Automática</span>
            </a>
        </div>
    </div>

    <!-- Alert Info -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl sm:rounded-2xl p-4 sm:p-6">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <h4 class="font-bold text-amber-900 mb-2 text-sm sm:text-base">Nota Importante</h4>
                <p class="text-sm text-amber-800">Esta opción requiere que ya tengas configurada una cuenta de WhatsApp Business API. Si aún no la tienes, te recomendamos usar la <a href="{{ route('waba-accounts.create') }}" class="font-bold underline hover:text-amber-900">conexión automática con Facebook</a>.</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white/70 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl border border-gray-200 p-6 sm:p-8">
        <form action="{{ route('waba-accounts.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div class="lg:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre de la Cuenta *
                    </label>
                    <input type="text" name="name" id="name" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                        value="{{ old('name') }}" placeholder="Ej: WABA Principal">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Un nombre descriptivo para identificar esta cuenta</p>
                </div>

                <!-- Número de Teléfono -->
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                        Número de Teléfono *
                    </label>
                    <input type="text" name="phone_number" id="phone_number" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('phone_number') border-red-500 @enderror"
                        value="{{ old('phone_number') }}" placeholder="+52 123 456 7890">
                    @error('phone_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Número con código de país</p>
                </div>

                <!-- Phone Number ID -->
                <div>
                    <label for="phone_number_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number ID *
                    </label>
                    <input type="text" name="phone_number_id" id="phone_number_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('phone_number_id') border-red-500 @enderror"
                        value="{{ old('phone_number_id') }}" placeholder="123456789012345">
                    @error('phone_number_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">ID del número en WhatsApp Business API</p>
                </div>

                <!-- Business Account ID -->
                <div>
                    <label for="business_account_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Business Account ID *
                    </label>
                    <input type="text" name="business_account_id" id="business_account_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('business_account_id') border-red-500 @enderror"
                        value="{{ old('business_account_id') }}" placeholder="987654321098765">
                    @error('business_account_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">ID de tu Business Account en Facebook</p>
                </div>

                <!-- WABA ID -->
                <div>
                    <label for="waba_id" class="block text-sm font-medium text-gray-700 mb-2">
                        WABA ID *
                    </label>
                    <input type="text" name="waba_id" id="waba_id" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('waba_id') border-red-500 @enderror"
                        value="{{ old('waba_id') }}" placeholder="111222333444555">
                    @error('waba_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">WhatsApp Business Account ID</p>
                </div>

                <!-- Access Token -->
                <div class="lg:col-span-2">
                    <label for="access_token" class="block text-sm font-medium text-gray-700 mb-2">
                        Access Token *
                    </label>
                    <textarea name="access_token" id="access_token" rows="3" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('access_token') border-red-500 @enderror font-mono text-sm"
                        placeholder="EAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">{{ old('access_token') }}</textarea>
                    @error('access_token')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Token de acceso permanente de la API de WhatsApp Business</p>
                </div>

                <!-- Estado -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select name="status" id="status"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Activa</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactiva</option>
                        <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspendida</option>
                    </select>
                </div>

                <!-- Calificación de Calidad -->
                <div>
                    <label for="quality_rating" class="block text-sm font-medium text-gray-700 mb-2">Calificación de Calidad</label>
                    <select name="quality_rating" id="quality_rating"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="unknown" {{ old('quality_rating', 'unknown') === 'unknown' ? 'selected' : '' }}>Desconocido</option>
                        <option value="green" {{ old('quality_rating') === 'green' ? 'selected' : '' }}>Verde (Buena)</option>
                        <option value="yellow" {{ old('quality_rating') === 'yellow' ? 'selected' : '' }}>Amarillo (Media)</option>
                        <option value="red" {{ old('quality_rating') === 'red' ? 'selected' : '' }}>Rojo (Baja)</option>
                    </select>
                </div>

                <!-- Verificado -->
                <div class="lg:col-span-2">
                    <label for="is_verified" class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_verified" id="is_verified" value="1" {{ old('is_verified') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm font-medium text-gray-700">Cuenta verificada por Facebook</span>
                    </label>
                    <p class="mt-1 ml-6 text-sm text-gray-500">Marca esta casilla si tu cuenta ya ha sido verificada oficialmente</p>
                </div>

                <!-- Configuración Adicional (JSON) -->
                <div class="lg:col-span-2">
                    <label for="settings" class="block text-sm font-medium text-gray-700 mb-2">
                        Configuración Adicional (Opcional)
                    </label>
                    <textarea name="settings" id="settings" rows="4"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('settings') border-red-500 @enderror font-mono text-sm"
                        placeholder='{"webhook_url": "https://example.com/webhook", "verify_token": "mi_token_secreto"}'>{{ old('settings') }}</textarea>
                    @error('settings')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Formato JSON válido. Configuraciones adicionales como webhooks, tokens de verificación, etc.</p>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-end gap-3">
                <a href="{{ route('waba-accounts.index') }}" class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold shadow-md hover:shadow-lg transition cursor-pointer text-center">
                    Cancelar
                </a>
                <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-6 py-3 rounded-lg font-semibold shadow-md hover:shadow-lg transition cursor-pointer">
                    Crear Cuenta WABA
                </button>
            </div>
        </form>
    </div>

    <!-- Ayuda: ¿Dónde encontrar los datos? -->
    <div class="bg-white/70 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl border border-gray-200 p-6 sm:p-8">
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">¿Dónde encontrar estos datos?</h3>

        <div class="space-y-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-600 font-bold">1</span>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-gray-900 mb-1">Accede a Facebook Business Manager</h4>
                    <p class="text-sm text-gray-600">Ve a <a href="https://business.facebook.com" target="_blank" class="text-indigo-600 hover:text-indigo-700 underline">business.facebook.com</a></p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-600 font-bold">2</span>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-gray-900 mb-1">Navega a WhatsApp Manager</h4>
                    <p class="text-sm text-gray-600">En el menú lateral, busca "WhatsApp" o "WhatsApp Business API"</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-600 font-bold">3</span>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-gray-900 mb-1">Copia los IDs</h4>
                    <p class="text-sm text-gray-600">Encontrarás el Phone Number ID, Business Account ID y WABA ID en la configuración de tu cuenta</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-600 font-bold">4</span>
                </div>
                <div class="flex-1">
                    <h4 class="font-bold text-gray-900 mb-1">Genera un Access Token</h4>
                    <p class="text-sm text-gray-600">En "Herramientas del Sistema" > "Acceso a la API" > Genera un token permanente con los permisos necesarios</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
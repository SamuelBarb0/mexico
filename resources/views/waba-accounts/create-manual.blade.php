@extends('layouts.app')

@section('title', 'Conectar Cuenta WhatsApp')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 via-teal-600 to-emerald-600 rounded-xl sm:rounded-2xl shadow-2xl p-4 sm:p-6 lg:p-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex-1">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-1 sm:mb-2 flex items-center">
                    <svg class="w-8 h-8 mr-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    Conectar WhatsApp Business
                </h1>
                <p class="text-green-100 text-sm sm:text-base lg:text-lg">Conecta tu cuenta de WhatsApp Business API para enviar mensajes</p>
            </div>
            <a href="{{ route('waba-accounts.index') }}" class="bg-white text-green-600 px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg sm:rounded-xl font-bold text-sm sm:text-base shadow-lg hover:shadow-xl transition-all cursor-pointer w-full sm:w-auto justify-center flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="truncate">Volver</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Columna Izquierda: Instrucciones -->
        <div class="space-y-6">
            <!-- Paso 1 -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-bold text-lg">1</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 text-lg mb-2">Accede a Facebook Developers</h3>
                        <p class="text-gray-600 mb-3">Crea una App de WhatsApp o usa una existente.</p>
                        <a href="https://developers.facebook.com/apps" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Abrir Facebook Developers
                        </a>
                        <div class="mt-3 p-3 bg-gray-50 rounded-lg text-sm text-gray-600">
                            <p class="font-semibold mb-1">Si no tienes una App:</p>
                            <ol class="list-decimal list-inside space-y-1 ml-2">
                                <li>Clic en "Crear app"</li>
                                <li>Selecciona "Otro" como caso de uso</li>
                                <li>Selecciona "Empresa" como tipo</li>
                                <li>Ponle un nombre y crea la app</li>
                                <li>Agrega el producto "WhatsApp"</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paso 2 -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-bold text-lg">2</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 text-lg mb-2">Copia los datos de API Setup</h3>
                        <p class="text-gray-600 mb-3">En tu App, ve a <strong>WhatsApp → API Setup</strong></p>

                        <div class="space-y-3">
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm font-semibold text-blue-900">Phone Number ID</p>
                                <p class="text-xs text-blue-700">Aparece debajo de tu numero de telefono</p>
                            </div>
                            <div class="p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                <p class="text-sm font-semibold text-purple-900">WhatsApp Business Account ID</p>
                                <p class="text-xs text-purple-700">Aparece en la seccion "Select business portfolio"</p>
                            </div>
                        </div>

                        <div class="mt-3">
                            <img src="https://i.imgur.com/placeholder.png" alt="API Setup Screenshot" class="rounded-lg border border-gray-200 hidden">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paso 3 -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-bold text-lg">3</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 text-lg mb-2">Genera un Token Permanente</h3>
                        <p class="text-gray-600 mb-3">El token temporal de API Setup expira en 24 horas. Para un token permanente:</p>

                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                            <p class="font-semibold text-amber-900 mb-2">Opcion A: Token Temporal (24 horas)</p>
                            <p class="text-sm text-amber-800">En API Setup, copia el "Temporary access token". Tendras que renovarlo cada dia.</p>
                        </div>

                        <div class="mt-3 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="font-semibold text-green-900 mb-2">Opcion B: Token Permanente (Recomendado)</p>
                            <ol class="list-decimal list-inside space-y-1 text-sm text-green-800 ml-2">
                                <li>Ve a <a href="https://business.facebook.com/settings/system-users" target="_blank" class="underline font-semibold">Business Settings → System Users</a></li>
                                <li>Crea un System User (tipo Admin)</li>
                                <li>Asignale tu App y tu cuenta de WhatsApp</li>
                                <li>Clic en "Generar token"</li>
                                <li>Selecciona permisos: <code class="bg-green-100 px-1 rounded">whatsapp_business_messaging</code> y <code class="bg-green-100 px-1 rounded">whatsapp_business_management</code></li>
                                <li>Copia el token generado</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Formulario -->
        <div class="lg:sticky lg:top-6 h-fit">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Datos de tu cuenta
                </h3>

                <form action="{{ route('waba-accounts.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Nombre -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre de la cuenta *
                        </label>
                        <input type="text" name="name" id="name" required
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 @error('name') border-red-500 @enderror"
                            value="{{ old('name') }}" placeholder="Ej: Mi WhatsApp Business">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Numero de Telefono -->
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">
                            Numero de telefono *
                        </label>
                        <input type="text" name="phone_number" id="phone_number" required
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 @error('phone_number') border-red-500 @enderror"
                            value="{{ old('phone_number') }}" placeholder="+52 123 456 7890">
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Number ID -->
                    <div>
                        <label for="phone_number_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Phone Number ID *
                            <span class="text-blue-600 font-normal">(de API Setup)</span>
                        </label>
                        <input type="text" name="phone_number_id" id="phone_number_id" required
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 font-mono @error('phone_number_id') border-red-500 @enderror"
                            value="{{ old('phone_number_id') }}" placeholder="123456789012345">
                        @error('phone_number_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- WABA ID -->
                    <div>
                        <label for="waba_id" class="block text-sm font-medium text-gray-700 mb-1">
                            WhatsApp Business Account ID *
                            <span class="text-purple-600 font-normal">(de API Setup)</span>
                        </label>
                        <input type="text" name="waba_id" id="waba_id" required
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 font-mono @error('waba_id') border-red-500 @enderror"
                            value="{{ old('waba_id') }}" placeholder="987654321098765">
                        @error('waba_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Business Account ID (hidden/optional) -->
                    <input type="hidden" name="business_account_id" value="{{ old('business_account_id', '0') }}">

                    <!-- Access Token -->
                    <div>
                        <label for="access_token" class="block text-sm font-medium text-gray-700 mb-1">
                            Access Token *
                            <span class="text-green-600 font-normal">(temporal o permanente)</span>
                        </label>
                        <textarea name="access_token" id="access_token" rows="3" required
                            class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500 font-mono text-sm @error('access_token') border-red-500 @enderror"
                            placeholder="EAAxxxxxxxxxxxxxxx...">{{ old('access_token') }}</textarea>
                        @error('access_token')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Hidden fields with defaults -->
                    <input type="hidden" name="status" value="active">
                    <input type="hidden" name="quality_rating" value="unknown">
                    <input type="hidden" name="is_verified" value="0">

                    <!-- Submit -->
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg hover:shadow-xl transition cursor-pointer flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Conectar cuenta
                        </button>
                    </div>
                </form>
            </div>

            <!-- Ayuda -->
            <div class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                <p class="text-sm text-gray-600">
                    <span class="font-semibold">Necesitas ayuda?</span> Contactanos y te guiamos en el proceso de configuracion.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

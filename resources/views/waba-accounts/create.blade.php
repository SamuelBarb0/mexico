@extends('layouts.app')

@section('title', 'Conectar Cuenta WABA')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 via-teal-600 to-blue-600 rounded-xl sm:rounded-2xl shadow-2xl p-4 sm:p-6 lg:p-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex-1">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-1 sm:mb-2 flex items-center">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 mr-2 sm:mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    <span class="truncate">Conectar WhatsApp Business</span>
                </h1>
                <p class="text-green-100 text-sm sm:text-base lg:text-lg">Conecta tu cuenta de WhatsApp Business en 3 simples pasos</p>
            </div>
            <a href="{{ route('waba-accounts.index') }}" class="bg-white text-green-600 px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg sm:rounded-xl font-bold text-sm sm:text-base shadow-lg hover:shadow-xl transition-all cursor-pointer w-full sm:w-auto justify-center flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="truncate">Volver</span>
            </a>
        </div>
    </div>

    <!-- Facebook Embedded Signup -->
    <div class="bg-white/70 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl border border-gray-200 p-6 sm:p-8">
        <div class="max-w-3xl mx-auto">
            <!-- Paso 1: Información -->
            <div class="mb-8">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center text-white font-bold text-lg sm:text-xl shadow-lg flex-shrink-0">
                        1
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900">¿Qué necesitas?</h3>
                        <p class="text-sm sm:text-base text-gray-600">Requisitos para conectar tu cuenta</p>
                    </div>
                </div>
                <div class="ml-14 sm:ml-16 bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <ul class="space-y-2 text-sm sm:text-base text-gray-700">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Una cuenta de <strong>Facebook Business Manager</strong></span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Un número de teléfono que no esté en uso en WhatsApp</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Permisos de administrador en tu Business Manager</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Paso 2: Conectar con Facebook -->
            <div class="mb-8">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-green-500 to-teal-600 flex items-center justify-center text-white font-bold text-lg sm:text-xl shadow-lg flex-shrink-0">
                        2
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Conectar con Facebook</h3>
                        <p class="text-sm sm:text-base text-gray-600">Inicia sesión y autoriza el acceso</p>
                    </div>
                </div>
                <div class="ml-14 sm:ml-16">
                    <!-- Facebook Embedded Signup Button -->
                    <div id="fb-embedded-signup-container"></div>

                    <!-- Loading State -->
                    <div id="loading-state" class="hidden">
                        <div class="flex items-center justify-center p-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                            <div class="text-center">
                                <svg class="animate-spin h-10 w-10 text-green-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-gray-600 font-semibold">Procesando tu conexión con Facebook...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Fallback: Manual Connection -->
                    <div id="manual-connection" class="hidden mt-4">
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div class="ml-3 flex-1">
                                    <h4 class="text-sm font-bold text-amber-900 mb-1">¿Problemas con la conexión automática?</h4>
                                    <p class="text-sm text-amber-800 mb-3">Si el botón de Facebook no aparece o tienes problemas, puedes conectar manualmente tu cuenta.</p>
                                    <a href="{{ route('waba-accounts.create-manual') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold text-sm shadow-md hover:shadow-lg transition cursor-pointer">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Conexión Manual
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paso 3: Configuración Completa -->
            <div>
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gray-300 flex items-center justify-center text-white font-bold text-lg sm:text-xl shadow-lg flex-shrink-0">
                        3
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900">¡Listo!</h3>
                        <p class="text-sm sm:text-base text-gray-600">Tu cuenta estará conectada y lista para usar</p>
                    </div>
                </div>
                <div class="ml-14 sm:ml-16 bg-green-50 rounded-lg p-4 border border-green-200">
                    <p class="text-sm sm:text-base text-gray-700">Después de conectar, podrás enviar y recibir mensajes de WhatsApp directamente desde la plataforma.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Info adicional -->
    <div class="bg-blue-50 rounded-xl sm:rounded-2xl border border-blue-200 p-4 sm:p-6">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <h4 class="font-bold text-blue-900 mb-2 text-sm sm:text-base">Importante</h4>
                <ul class="space-y-1 text-sm text-blue-800">
                    <li>• Esta conexión es segura y utiliza el protocolo oficial de Facebook</li>
                    <li>• Tus credenciales nunca se almacenan en nuestros servidores</li>
                    <li>• Puedes desconectar tu cuenta en cualquier momento</li>
                    <li>• El proceso es completamente gratuito</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Facebook App Configuration
    const FACEBOOK_APP_ID = '{{ config("services.facebook.app_id") }}';
    const FACEBOOK_API_VERSION = '{{ config("services.facebook.api_version", "v21.0") }}';

    // Load Facebook SDK
    window.fbAsyncInit = function() {
        FB.init({
            appId: FACEBOOK_APP_ID,
            autoLogAppEvents: true,
            xfbml: true,
            version: FACEBOOK_API_VERSION
        });

        // Show login button
        showFacebookLoginButton();
    };

    // Load Facebook SDK asynchronously
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = "https://connect.facebook.net/es_LA/sdk.js";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    function showFacebookLoginButton() {
        if (!FACEBOOK_APP_ID || FACEBOOK_APP_ID === '') {
            console.error('Facebook App ID no configurado');
            showManualConnection();
            return;
        }

        const container = document.getElementById('fb-embedded-signup-container');
        container.innerHTML = `
            <button onclick="loginWithFacebook()" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold text-base sm:text-lg shadow-lg hover:shadow-xl transition-all cursor-pointer">
                <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                Conectar con Facebook Business
            </button>
            <p class="text-sm text-gray-500 mt-3 text-center sm:text-left">Inicia sesión con Facebook para importar tus cuentas de WhatsApp Business</p>
        `;

        document.getElementById('loading-state').classList.add('hidden');

        // Show manual connection option after 3 seconds
        setTimeout(() => {
            document.getElementById('manual-connection').classList.remove('hidden');
        }, 3000);
    }

    function loginWithFacebook() {
        // Show loading
        document.getElementById('loading-state').classList.remove('hidden');
        document.getElementById('fb-embedded-signup-container').classList.add('hidden');

        // Standard Facebook Login to fetch user's WABA accounts
        FB.login(function(response) {
            console.log('FB.login response:', response);

            if (response.authResponse) {
                const accessToken = response.authResponse.accessToken;
                const userID = response.authResponse.userID;

                // Send to backend to fetch WABA accounts
                fetchWabaAccounts(accessToken, userID);
            } else {
                document.getElementById('loading-state').classList.add('hidden');
                document.getElementById('fb-embedded-signup-container').classList.remove('hidden');
                showError('Inicio de sesión cancelado o no autorizado.');
            }
        }, {
            scope: 'business_management,whatsapp_business_management,whatsapp_business_messaging',
            return_scopes: true
        });
    }

    function fetchWabaAccounts(accessToken, userID) {
        // First, try to get WABA accounts from the backend
        fetch('{{ route("waba-accounts.facebook.callback") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                access_token: accessToken,
                user_id: userID
            })
        })
        .then(response => response.json())
        .then(result => {
            console.log('Backend response:', result);

            if (result.success) {
                showSuccess(result.message);
                setTimeout(() => {
                    window.location.href = '{{ route("waba-accounts.index") }}';
                }, 1500);
            } else if (result.waba_accounts && result.waba_accounts.length > 0) {
                // Show account selector
                showAccountSelector(result.waba_accounts, accessToken, userID);
            } else {
                // No accounts found - show options
                showNoAccountsFound(accessToken, userID, result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error al conectar con Facebook. Por favor intenta la conexión manual.');
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('fb-embedded-signup-container').classList.remove('hidden');
        });
    }

    function showAccountSelector(accounts, accessToken, userID) {
        document.getElementById('loading-state').classList.add('hidden');

        const container = document.getElementById('fb-embedded-signup-container');
        container.classList.remove('hidden');

        // Separate connected and available accounts
        const connectedAccounts = accounts.filter(acc => acc.already_connected);
        const availableAccounts = accounts.filter(acc => !acc.already_connected);

        // Check if all accounts are already connected
        if (availableAccounts.length === 0) {
            container.innerHTML = `
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                    <svg class="w-16 h-16 text-blue-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="text-lg font-bold text-blue-900 mb-2">Todas las cuentas ya están conectadas</h3>
                    <p class="text-blue-800 mb-4">Todas tus cuentas de WhatsApp Business ya están vinculadas a esta plataforma.</p>
                    <a href="{{ route('waba-accounts.index') }}"
                       class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition cursor-pointer">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Ver mis cuentas conectadas
                    </a>
                </div>
            `;
            return;
        }

        // Build HTML for available accounts (selectable)
        let availableHtml = availableAccounts.map(acc => `
            <div class="border border-gray-200 rounded-lg p-4 hover:border-green-500 hover:bg-green-50 cursor-pointer transition-all account-option"
                 data-phone-id="${acc.phone_number_id}"
                 data-waba-id="${acc.waba_id}"
                 data-business-id="${acc.business_id}"
                 data-name="${acc.name}"
                 data-phone="${acc.phone_number}">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-gray-900">${acc.name}</h4>
                        <p class="text-sm text-gray-600">${acc.phone_number || 'Sin número'}</p>
                        ${acc.quality_rating ? `<span class="inline-block mt-1 px-2 py-0.5 text-xs rounded-full ${acc.quality_rating === 'GREEN' ? 'bg-green-100 text-green-800' : acc.quality_rating === 'YELLOW' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'}">${acc.quality_rating}</span>` : ''}
                    </div>
                    <svg class="w-6 h-6 text-gray-400 check-icon hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        `).join('');

        // Build HTML for already connected accounts (disabled)
        let connectedHtml = connectedAccounts.length > 0 ? `
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-2 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Ya conectadas:
                </p>
                ${connectedAccounts.map(acc => `
                    <div class="border border-green-200 bg-green-50 rounded-lg p-3 opacity-60 mb-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-gray-700">${acc.name}</h4>
                                <p class="text-sm text-gray-500">${acc.phone_number || 'Sin número'}</p>
                            </div>
                            <span class="px-2 py-1 text-xs bg-green-200 text-green-800 rounded-full">Conectada</span>
                        </div>
                    </div>
                `).join('')}
            </div>
        ` : '';

        container.innerHTML = `
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <h3 class="font-bold text-lg text-gray-900 mb-2">Selecciona la cuenta a conectar</h3>
                <p class="text-sm text-gray-600 mb-4">Encontramos ${availableAccounts.length} cuenta(s) disponible(s) para conectar</p>
                <div class="space-y-3 max-h-64 overflow-y-auto mb-4">
                    ${availableHtml}
                    ${connectedHtml}
                </div>
                <button id="connect-selected-btn" onclick="connectSelectedAccount('${accessToken}', '${userID}')"
                        class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-bold shadow-lg disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer" disabled>
                    Conectar cuenta seleccionada
                </button>
            </div>
        `;

        // Add click handlers for account selection (only for available accounts)
        document.querySelectorAll('.account-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.account-option').forEach(opt => {
                    opt.classList.remove('border-green-500', 'bg-green-50');
                    opt.querySelector('.check-icon').classList.add('hidden');
                });
                this.classList.add('border-green-500', 'bg-green-50');
                this.querySelector('.check-icon').classList.remove('hidden');
                document.getElementById('connect-selected-btn').disabled = false;
            });
        });
    }

    function connectSelectedAccount(accessToken, userID) {
        const selected = document.querySelector('.account-option.border-green-500');
        if (!selected) return;

        document.getElementById('loading-state').classList.remove('hidden');
        document.getElementById('fb-embedded-signup-container').classList.add('hidden');

        fetch('{{ route("waba-accounts.facebook.callback") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                access_token: accessToken,
                user_id: userID,
                phone_number_id: selected.dataset.phoneId,
                waba_id: selected.dataset.wabaId,
                business_account_id: selected.dataset.businessId,
                name: selected.dataset.name,
                phone_number: selected.dataset.phone,
                connect_specific: true
            })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showSuccess(result.message);
                setTimeout(() => {
                    window.location.href = '{{ route("waba-accounts.index") }}';
                }, 1500);
            } else {
                showError(result.message);
                document.getElementById('loading-state').classList.add('hidden');
                document.getElementById('fb-embedded-signup-container').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error al conectar la cuenta.');
            document.getElementById('loading-state').classList.add('hidden');
            document.getElementById('fb-embedded-signup-container').classList.remove('hidden');
        });
    }

    function showNoAccountsFound(accessToken, userID, message) {
        document.getElementById('loading-state').classList.add('hidden');

        const container = document.getElementById('fb-embedded-signup-container');
        container.classList.remove('hidden');
        container.innerHTML = `
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-6 text-center">
                <svg class="w-16 h-16 text-amber-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-lg font-bold text-amber-900 mb-2">No se encontraron cuentas de WhatsApp Business</h3>
                <p class="text-amber-800 mb-4">${message || 'No encontramos cuentas de WhatsApp Business asociadas a tu cuenta de Facebook.'}</p>
                <div class="space-y-3">
                    <p class="text-sm text-gray-600">Asegúrate de tener:</p>
                    <ul class="text-sm text-gray-600 text-left max-w-md mx-auto space-y-1">
                        <li class="flex items-start">
                            <span class="text-amber-500 mr-2">1.</span>
                            Una cuenta de WhatsApp Business creada en Meta Business Suite
                        </li>
                        <li class="flex items-start">
                            <span class="text-amber-500 mr-2">2.</span>
                            Un número de teléfono verificado y configurado
                        </li>
                        <li class="flex items-start">
                            <span class="text-amber-500 mr-2">3.</span>
                            Permisos de administrador en el Business Manager
                        </li>
                    </ul>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center mt-4">
                        <a href="https://business.facebook.com/latest/whatsapp_manager" target="_blank"
                           class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition cursor-pointer">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Ir a WhatsApp Manager
                        </a>
                        <a href="{{ route('waba-accounts.create-manual') }}"
                           class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold transition cursor-pointer">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Conexión Manual
                        </a>
                    </div>
                </div>
            </div>
        `;
    }

    function showSuccess(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'fixed top-4 right-4 z-50 p-4 bg-green-100 border border-green-400 text-green-800 rounded-lg shadow-lg flex items-center';
        alertDiv.innerHTML = `
            <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="font-semibold">${message}</span>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }

    function showError(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'fixed top-4 right-4 z-50 p-4 bg-red-100 border border-red-400 text-red-800 rounded-lg shadow-lg flex items-center';
        alertDiv.innerHTML = `
            <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span class="font-semibold">${message}</span>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }

    function showManualConnection() {
        document.getElementById('fb-embedded-signup-container').innerHTML = `
            <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-600 mb-4">La conexión automática con Facebook no está disponible en este momento.</p>
                <a href="{{ route('waba-accounts.create-manual') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition cursor-pointer">
                    Conectar Manualmente
                </a>
            </div>
        `;
        document.getElementById('manual-connection').classList.remove('hidden');
    }
</script>
@endpush
@endsection
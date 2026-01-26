@extends('layouts.app')

@section('title', 'Detalle de Cuenta WABA')

@section('content')
<div class="mb-6">
    <a href="{{ route('waba-accounts.index') }}" class="text-blue-600 hover:text-blue-900">
        ← Volver a WABA Accounts
    </a>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">{{ $wabaAccount->name }}</h2>
        <div class="flex space-x-3">
            <form action="{{ route('waba-accounts.verify', $wabaAccount) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Verificar
                </button>
            </form>
            <form action="{{ route('waba-accounts.register', $wabaAccount) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center"
                    onclick="return confirm('¿Registrar este número en WhatsApp Business API? Esto es necesario si ves error Account not Registered.')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Registrar
                </button>
            </form>
            <a href="{{ route('waba-accounts.edit', $wabaAccount) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Editar
            </a>
            <form action="{{ route('waba-accounts.destroy', $wabaAccount) }}" method="POST" class="inline" id="delete-form">
                @csrf
                @method('DELETE')
                <button type="button" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
                    onclick="showDeleteModal()">
                    Eliminar
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Número de Teléfono</h3>
            <p class="mt-1 text-lg text-gray-900">{{ $wabaAccount->phone_number }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Phone Number ID</h3>
            <p class="mt-1 text-lg text-gray-900 font-mono">{{ $wabaAccount->phone_number_id }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Business Account ID</h3>
            <p class="mt-1 text-lg text-gray-900 font-mono">{{ $wabaAccount->business_account_id }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">WABA ID</h3>
            <p class="mt-1 text-lg text-gray-900 font-mono">{{ $wabaAccount->waba_id }}</p>
        </div>
    </div>

    <div class="mt-6 pt-6 border-t border-gray-200">
        <h3 class="text-sm font-medium text-gray-500 mb-2">Access Token</h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-900 font-mono break-all">
                {{ Str::limit($wabaAccount->access_token, 50) }}...
            </p>
            <p class="mt-2 text-xs text-gray-500">El token completo está oculto por seguridad</p>
        </div>
    </div>

    @if($wabaAccount->settings && count($wabaAccount->settings) > 0)
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Configuración</h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <dl class="grid grid-cols-1 gap-4">
                @foreach($wabaAccount->settings as $key => $value)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
    @endif

    @if($wabaAccount->campaigns->count() > 0)
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Campañas Asociadas ({{ $wabaAccount->campaigns->count() }})</h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <ul class="space-y-2">
                @foreach($wabaAccount->campaigns as $campaign)
                    <li class="flex items-center justify-between">
                        <a href="{{ route('campaigns.show', $campaign) }}" class="text-blue-600 hover:text-blue-900">
                            {{ $campaign->name }}
                        </a>
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $campaign->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($campaign->status) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="mt-6 pt-6 border-t border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-500">
            <div>
                <span class="font-medium">Creada:</span> {{ $wabaAccount->created_at->format('d/m/Y H:i') }}
            </div>
            <div>
                <span class="font-medium">Actualizada:</span> {{ $wabaAccount->updated_at->format('d/m/Y H:i') }}
            </div>
            <div>
                <span class="font-medium">Última sincronización:</span>
                {{ $wabaAccount->last_sync_at ? $wabaAccount->last_sync_at->format('d/m/Y H:i') : 'Nunca' }}
            </div>
        </div>
    </div>

    <!-- Troubleshooting Help -->
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Solución de problemas</h3>
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <h4 class="font-semibold text-amber-900 mb-2">¿Ves el error "Account not Registered" o "Fuera de internet"?</h4>
            <p class="text-sm text-amber-800 mb-3">El número necesita registrarse en la Cloud API de WhatsApp. Sigue estos pasos:</p>
            <ul class="text-sm text-amber-800 space-y-2 mb-4">
                <li class="flex items-start">
                    <span class="mr-2">1.</span>
                    <span>Primero intenta usar el <strong>Token Global</strong> (si está configurado en el servidor)</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">2.</span>
                    <span>Luego haz clic en <strong>Registrar</strong> para registrar el número en la API</span>
                </li>
                <li class="flex items-start">
                    <span class="mr-2">3.</span>
                    <span>Si no funciona, ve a <a href="https://business.facebook.com/latest/whatsapp_manager/phone_numbers" target="_blank" class="text-blue-600 hover:underline font-medium">Meta Business Suite → WhatsApp Manager</a> y registra el número manualmente</span>
                </li>
            </ul>
            <div class="flex flex-wrap gap-2">
                <form action="{{ route('waba-accounts.use-global-token', $wabaAccount) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium flex items-center"
                        onclick="return confirm('¿Usar el META_ACCESS_TOKEN global del servidor? Esto reemplazará el token actual.')">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Usar Token Global
                    </button>
                </form>
                <a href="https://business.facebook.com/latest/whatsapp_manager/phone_numbers" target="_blank"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Ir a WhatsApp Manager
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Eliminar cuenta WABA</h3>

        @if($wabaAccount->campaigns->count() > 0 || $wabaAccount->templates->count() > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                <p class="text-amber-800 font-semibold mb-2">Esta cuenta tiene datos asociados:</p>
                <ul class="text-sm text-amber-700 space-y-1">
                    @if($wabaAccount->campaigns->count() > 0)
                        <li>• {{ $wabaAccount->campaigns->count() }} campaña(s)</li>
                    @endif
                    @if($wabaAccount->templates->count() > 0)
                        <li>• {{ $wabaAccount->templates->count() }} plantilla(s)</li>
                    @endif
                </ul>
            </div>

            <label class="flex items-center mb-4 cursor-pointer">
                <input type="checkbox" id="force-delete" class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2">
                <span class="text-sm text-gray-700">Eliminar también las campañas y plantillas asociadas</span>
            </label>
        @else
            <p class="text-gray-600 mb-4">¿Estás seguro de que deseas eliminar esta cuenta WABA?</p>
        @endif

        <div class="flex justify-end gap-3">
            <button type="button" onclick="hideDeleteModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                Cancelar
            </button>
            <button type="button" onclick="confirmDelete()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold">
                Eliminar
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function showDeleteModal() {
        document.getElementById('delete-modal').classList.remove('hidden');
    }

    function hideDeleteModal() {
        document.getElementById('delete-modal').classList.add('hidden');
    }

    function confirmDelete() {
        const form = document.getElementById('delete-form');
        const forceCheckbox = document.getElementById('force-delete');

        if (forceCheckbox && forceCheckbox.checked) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'force';
            input.value = '1';
            form.appendChild(input);
        }

        form.submit();
    }

    // Close modal on backdrop click
    document.getElementById('delete-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideDeleteModal();
        }
    });
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'Inbox - Conversaciones')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl sm:rounded-2xl shadow-2xl p-4 sm:p-6 lg:p-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex-1">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-1 sm:mb-2 flex items-center">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span class="truncate">Inbox / Conversaciones</span>
                </h1>
                <p class="text-indigo-100 text-sm sm:text-base lg:text-lg">Gestiona todas tus conversaciones de WhatsApp</p>
            </div>
            <a href="{{ route('campaigns.index') }}" class="bg-white text-indigo-600 px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg sm:rounded-xl font-bold text-sm sm:text-base shadow-lg hover:shadow-xl transition-all cursor-pointer w-full sm:w-auto justify-center flex items-center">
                <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                <span class="truncate">Crear Campaña</span>
            </a>
        </div>
    </div>

    <!-- WABA Account Warning -->
    @if(!Auth::user()->tenant || !Auth::user()->tenant->wabaAccounts || Auth::user()->tenant->wabaAccounts->count() === 0)
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-500 rounded-lg p-6 shadow-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-bold text-amber-900 mb-2">Cuenta de WhatsApp Business requerida</h3>
                    <p class="text-amber-800 mb-4">
                        Para recibir y enviar mensajes, necesitas conectar una cuenta de WhatsApp Business API (WABA) primero.
                    </p>
                    <div class="bg-white/50 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-amber-900 mb-2">¿Qué necesitas hacer?</h4>
                        <ul class="list-disc list-inside space-y-1 text-sm text-amber-800">
                            <li>Registra una cuenta de WhatsApp Business API</li>
                            <li>Conecta tu cuenta WABA a esta plataforma</li>
                            <li>Una vez conectada, podrás recibir y enviar mensajes</li>
                        </ul>
                    </div>
                    <a href="{{ route('waba-accounts.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Conectar Cuenta WABA
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Search and Filters -->
    <div class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 border border-gray-200">
        <form method="GET" action="{{ route('inbox.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Buscar Conversación</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               placeholder="Buscar por nombre o teléfono..."
                               class="pl-10 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base">
                    </div>
                </div>

                <!-- Campaign Filter -->
                <div>
                    <label for="campaign_filter" class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Campaña</label>
                    <select name="campaign_filter" id="campaign_filter" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm sm:text-base">
                        <option value="">Todas las conversaciones</option>
                        <option value="campaign" {{ request('campaign_filter') == 'campaign' ? 'selected' : '' }}>Solo de campañas</option>
                        <option value="direct" {{ request('campaign_filter') == 'direct' ? 'selected' : '' }}>Solo mensajes directos</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold text-sm sm:text-base shadow-md hover:shadow-lg transition-all cursor-pointer">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>
                @if(request()->hasAny(['search', 'campaign_filter']))
                    <a href="{{ route('inbox.index') }}" class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-2.5 bg-gray-100 text-gray-700 rounded-lg font-semibold text-sm sm:text-base hover:bg-gray-200 transition-all cursor-pointer">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Limpiar Filtros
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if($conversations->isEmpty())
        <!-- Estado Vacío -->
        <div class="bg-white/70 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl p-6 sm:p-12 text-center border border-gray-200">
            <div class="max-w-md mx-auto">
                <div class="mb-6">
                    <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">No hay conversaciones</h3>
                <p class="text-gray-600 mb-6">
                    @if(Auth::user()->tenant && Auth::user()->tenant->wabaAccounts && Auth::user()->tenant->wabaAccounts->count() > 0)
                        Las conversaciones aparecerán aquí cuando envíes o recibas mensajes de tus contactos.
                    @else
                        Primero conecta una cuenta WABA para empezar a recibir y enviar mensajes.
                    @endif
                </p>
                @if(Auth::user()->tenant && Auth::user()->tenant->wabaAccounts && Auth::user()->tenant->wabaAccounts->count() > 0)
                    <a href="{{ route('campaigns.index') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                        Crear tu Primera Campaña
                    </a>
                @else
                    <a href="{{ route('waba-accounts.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Conectar Cuenta WABA
                    </a>
                @endif
            </div>
        </div>
    @else
        <!-- Lista de Conversaciones -->
        <div class="space-y-3">
            @foreach($conversations as $contact)
                @php
                    $lastMessage = $contact->messages->first();
                @endphp
                <a href="{{ route('inbox.show', $contact) }}" class="block bg-white/70 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-lg hover:shadow-xl border border-gray-200 hover:border-indigo-300 transition-all cursor-pointer">
                    <div class="p-4 sm:p-5">
                        <div class="flex items-start gap-3 sm:gap-4">
                            <!-- Avatar con badge de no leídos -->
                            <div class="relative flex-shrink-0">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg sm:text-xl shadow-lg">
                                    {{ substr($contact->name ?? 'U', 0, 1) }}
                                </div>
                                @if($contact->unread_count > 0)
                                    <div class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold shadow-lg border-2 border-white">
                                        {{ $contact->unread_count > 9 ? '9+' : $contact->unread_count }}
                                    </div>
                                @endif
                            </div>

                            <!-- Información de la conversación -->
                            <div class="flex-1 min-w-0">
                                <!-- Nombre y hora -->
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <h3 class="text-base sm:text-lg font-bold text-gray-900 truncate">
                                        {{ $contact->name ?? 'Sin nombre' }}
                                    </h3>
                                    <span class="text-xs sm:text-sm text-gray-500 whitespace-nowrap flex-shrink-0">
                                        {{ $contact->last_message_at ? \Carbon\Carbon::parse($contact->last_message_at)->diffForHumans() : 'N/A' }}
                                    </span>
                                </div>

                                <!-- Teléfono -->
                                <p class="text-xs sm:text-sm text-gray-500 mb-2">
                                    {{ $contact->phone }}
                                </p>

                                <!-- Último mensaje -->
                                @if($lastMessage)
                                    <div class="flex items-start gap-2 mb-2">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-gray-700 truncate">
                                                @if($lastMessage->direction === 'outbound')
                                                    <span class="text-blue-600 font-semibold">Tú:</span>
                                                @endif
                                                {{ Str::limit($lastMessage->content ?? 'Mensaje de plantilla', 60) }}
                                            </p>
                                        </div>
                                        @if($lastMessage->direction === 'outbound')
                                            <div class="flex items-center gap-0.5 flex-shrink-0">
                                                @if($lastMessage->status === 'sent')
                                                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                @elseif($lastMessage->status === 'delivered')
                                                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <svg class="w-4 h-4 text-blue-500 -ml-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                @elseif($lastMessage->status === 'read')
                                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <svg class="w-4 h-4 text-blue-600 -ml-2" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-sm text-gray-400 italic mb-2">Sin mensajes</p>
                                @endif

                                <!-- Stats -->
                                <div class="flex items-center gap-3 flex-wrap">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        <span class="text-xs text-gray-600">{{ $contact->messages_count ?? 0 }} mensajes</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Paginación -->
        @if($conversations->hasPages())
            <div class="bg-white/70 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-lg border border-gray-200 p-4 sm:p-5">
                {{ $conversations->links() }}
            </div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh inbox every 5 seconds
    let refreshInterval;

    function startAutoRefresh() {
        refreshInterval = setInterval(() => {
            // Reload the page to get new messages
            window.location.reload();
        }, 5000); // 5 segundos
    }

    // Start auto-refresh when page loads
    document.addEventListener('DOMContentLoaded', () => {
        startAutoRefresh();

        // Show a small indicator that auto-refresh is active
        console.log('✓ Auto-refresh activado - actualizando cada 5 segundos');
    });

    // Stop auto-refresh when user leaves the page
    window.addEventListener('beforeunload', () => {
        clearInterval(refreshInterval);
    });
</script>
@endpush

@extends('layouts.app')

@section('title', 'Inbox - Conversaciones')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-extrabold text-white mb-2 flex items-center">
                    <svg class="w-10 h-10 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Inbox / Conversaciones
                </h1>
                <p class="text-indigo-100 text-lg">Gestiona todas tus conversaciones de WhatsApp</p>
            </div>
            <a href="{{ route('campaigns.index') }}" class="bg-white text-indigo-600 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                Crear Campaña
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

    @if($conversations->isEmpty())
        <!-- Estado Vacío -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-12 text-center border border-gray-200">
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
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Contacto
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Último Mensaje
                            </th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Total
                            </th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Sin Leer
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Última Actividad
                            </th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($conversations as $contact)
                            @php
                                $lastMessage = $contact->messages->first();
                            @endphp
                            <tr class="hover:bg-indigo-50 transition-colors">
                                <!-- Contacto -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12">
                                            <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                                {{ substr($contact->name ?? 'U', 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">
                                                {{ $contact->name ?? 'Sin nombre' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $contact->phone }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Último Mensaje -->
                                <td class="px-6 py-4">
                                    @if($lastMessage)
                                        <div class="text-sm text-gray-900 max-w-xs truncate">
                                            @if($lastMessage->direction === 'outbound')
                                                <span class="text-blue-600 font-semibold">Tú:</span>
                                            @endif
                                            {{ Str::limit($lastMessage->content ?? 'Mensaje de plantilla', 50) }}
                                        </div>
                                        <div class="flex items-center mt-1">
                                            @if($lastMessage->direction === 'outbound')
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
                                                <span class="ml-1 text-xs text-gray-500">{{ ucfirst($lastMessage->status) }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400 italic">Sin mensajes</span>
                                    @endif
                                </td>

                                <!-- Total Mensajes -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $contact->messages_count ?? 0 }}
                                    </span>
                                </td>

                                <!-- Sin Leer -->
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($contact->unread_count > 0)
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ $contact->unread_count }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <!-- Última Actividad -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $contact->last_message_at ? \Carbon\Carbon::parse($contact->last_message_at)->diffForHumans() : 'N/A' }}
                                </td>

                                <!-- Acciones -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('inbox.show', $contact) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition-all">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        Ver Chat
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($conversations->hasPages())
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    {{ $conversations->links() }}
                </div>
            @endif
        </div>
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

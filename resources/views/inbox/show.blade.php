@extends('layouts.app')

@section('title', 'Chat - ' . ($contact->name ?? $contact->phone))

@section('content')
<div class="flex h-[calc(100vh-120px)] bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
    <!-- Sidebar - Lista de Conversaciones -->
    <div class="w-80 border-r border-gray-200 flex flex-col bg-gray-50">
        <!-- Header Sidebar -->
        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-white">Conversaciones</h2>
                <a href="{{ route('inbox.index') }}" class="text-white hover:text-indigo-100 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Lista de Conversaciones -->
        <div class="flex-1 overflow-y-auto">
            @foreach($conversations as $conv)
                @php
                    $lastMsg = $conv->messages->first();
                    $isActive = $conv->id === $contact->id;
                @endphp
                <a href="{{ route('inbox.show', $conv) }}"
                   class="block p-4 border-b border-gray-200 hover:bg-indigo-50 transition {{ $isActive ? 'bg-indigo-100' : '' }}">
                    <div class="flex items-center space-x-3">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold shadow">
                                {{ substr($conv->name ?? 'U', 0, 1) }}
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm font-bold text-gray-900 truncate">
                                    {{ $conv->name ?? $conv->phone }}
                                </p>
                                <span class="text-xs text-gray-500">
                                    {{ $conv->last_message_at ? \Carbon\Carbon::parse($conv->last_message_at)->format('H:i') : '' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-sm text-gray-600 truncate">
                                    {{ $lastMsg ? Str::limit($lastMsg->content ?? 'Mensaje de plantilla', 30) : 'Sin mensajes' }}
                                </p>
                                @if($conv->unread_count > 0 && !$isActive)
                                    <span class="ml-2 px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full">
                                        {{ $conv->unread_count }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Área Principal - Chat -->
    <div class="flex-1 flex flex-col">
        <!-- Header del Chat -->
        <div class="p-4 border-b border-gray-200 bg-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Avatar del contacto -->
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-lg">
                        {{ substr($contact->name ?? 'U', 0, 1) }}
                    </div>
                    <!-- Info del contacto -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            {{ $contact->name ?? 'Sin nombre' }}
                        </h3>
                        <p class="text-sm text-gray-500">{{ $contact->phone }}</p>
                    </div>
                </div>
                <!-- Acciones -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('contacts.show', $contact) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Ver Perfil
                    </a>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gradient-to-b from-gray-50 to-white">
            @if($messages->isEmpty())
                <div class="flex flex-col items-center justify-center h-full">
                    <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="text-gray-500 text-lg">No hay mensajes en esta conversación</p>
                </div>
            @else
                @foreach($messages as $message)
                    <div class="flex {{ $message->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-md">
                            <!-- Bubble -->
                            <div class="rounded-2xl shadow-md p-4 {{ $message->direction === 'outbound' ? 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white' : 'bg-white text-gray-900' }}">
                                <!-- Header del mensaje -->
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold {{ $message->direction === 'outbound' ? 'text-indigo-100' : 'text-gray-500' }}">
                                        {{ $message->direction === 'outbound' ? 'Tú' : ($contact->name ?? $contact->phone) }}
                                    </span>
                                    <span class="text-xs {{ $message->direction === 'outbound' ? 'text-indigo-200' : 'text-gray-400' }}">
                                        {{ $message->created_at->format('H:i') }}
                                    </span>
                                </div>

                                <!-- Contenido del mensaje -->
                                <div class="text-sm leading-relaxed">
                                    {{ $message->content ?? 'Mensaje de plantilla' }}
                                </div>

                                <!-- Footer con info del mensaje -->
                                <div class="mt-2 flex items-center justify-between text-xs {{ $message->direction === 'outbound' ? 'text-indigo-200' : 'text-gray-500' }}">
                                    <div class="flex items-center space-x-1">
                                        @if($message->campaign)
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                                            </svg>
                                            <span>Campaña</span>
                                        @endif

                                        @if($message->messageTemplate)
                                            <span class="ml-2">📋 {{ $message->messageTemplate->name }}</span>
                                        @endif
                                    </div>

                                    <!-- Estado del mensaje -->
                                    @if($message->direction === 'outbound')
                                        <div class="flex items-center space-x-1">
                                            @if($message->status === 'sent')
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($message->status === 'delivered')
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <svg class="w-4 h-4 -ml-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($message->status === 'read')
                                                <svg class="w-4 h-4 text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                                <svg class="w-4 h-4 text-blue-300 -ml-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($message->status === 'failed')
                                                <svg class="w-4 h-4 text-red-300" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                            <span class="ml-1">{{ ucfirst($message->status) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Timestamp debajo del bubble -->
                            <div class="mt-1 px-2 text-xs text-gray-500 {{ $message->direction === 'outbound' ? 'text-right' : 'text-left' }}">
                                {{ $message->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Input de mensaje -->
        <div class="p-4 border-t border-gray-200 bg-white">
            @if(session('success'))
                <div class="mb-3 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-3 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('inbox.send', $contact) }}" method="POST" class="flex items-start space-x-3">
                @csrf
                <div class="flex-1">
                    <textarea name="message" rows="2" required
                              class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 px-4 py-3 text-gray-900 resize-none"
                              placeholder="Escribe tu mensaje..."></textarea>
                    @error('message')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="p-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
            <p class="text-xs text-gray-500 mt-2 text-center">
                💬 Envía mensajes de texto directamente desde aquí
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-refresh chat every 5 seconds using AJAX
    let refreshInterval;
    let lastMessageCount = {{ $messages->count() }};
    let isTyping = false;

    // Detectar cuando el usuario está escribiendo
    const messageInput = document.querySelector('textarea[name="message"]');
    if (messageInput) {
        messageInput.addEventListener('focus', () => {
            isTyping = true;
        });

        messageInput.addEventListener('blur', () => {
            // Esperar un momento antes de marcar como no escribiendo
            setTimeout(() => {
                if (document.activeElement !== messageInput) {
                    isTyping = false;
                }
            }, 500);
        });
    }

    function checkForNewMessages() {
        // No actualizar si el usuario está escribiendo
        if (isTyping) {
            return;
        }

        // Hacer petición AJAX para obtener los mensajes actualizados
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                // Crear un elemento temporal para parsear el HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Obtener el contenedor de mensajes del nuevo HTML
                const newMessagesContainer = doc.querySelector('.flex-1.overflow-y-auto.p-6.space-y-4');
                const currentMessagesContainer = document.querySelector('.flex-1.overflow-y-auto.p-6.space-y-4');

                if (newMessagesContainer && currentMessagesContainer) {
                    // Guardar la posición de scroll actual
                    const scrollContainer = currentMessagesContainer;
                    const wasAtBottom = scrollContainer.scrollHeight - scrollContainer.scrollTop <= scrollContainer.clientHeight + 100;

                    // Actualizar el contenido
                    currentMessagesContainer.innerHTML = newMessagesContainer.innerHTML;

                    // Si estaba al fondo, mantenerlo al fondo
                    if (wasAtBottom) {
                        scrollContainer.scrollTop = scrollContainer.scrollHeight;
                    }
                }

                // Actualizar contador de la barra lateral si hay cambios
                const newSidebar = doc.querySelector('.w-80.border-r');
                const currentSidebar = document.querySelector('.w-80.border-r');
                if (newSidebar && currentSidebar) {
                    currentSidebar.innerHTML = newSidebar.innerHTML;
                }
            })
            .catch(error => {
                console.error('Error al actualizar mensajes:', error);
            });
    }

    function startAutoRefresh() {
        refreshInterval = setInterval(() => {
            checkForNewMessages();
        }, 5000); // 5 segundos
    }

    // Start auto-refresh when page loads
    document.addEventListener('DOMContentLoaded', () => {
        startAutoRefresh();
        console.log('✓ Auto-refresh de chat activado - actualizando cada 5 segundos (sin recargar página)');

        // Scroll inicial al final de los mensajes
        const messagesContainer = document.querySelector('.flex-1.overflow-y-auto.p-6.space-y-4');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    });

    // Stop auto-refresh when user leaves the page
    window.addEventListener('beforeunload', () => {
        clearInterval(refreshInterval);
    });
</script>
@endpush

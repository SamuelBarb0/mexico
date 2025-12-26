@extends('layouts.app')

@section('title', 'Métricas de Campaña')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-violet-700 rounded-2xl shadow-2xl p-8">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-4xl font-extrabold text-white mb-2">Métricas de Campaña</h1>
                <p class="text-purple-100 text-lg">{{ $campaign->name }}</p>
                <div class="flex items-center gap-3 mt-3">
                    <span class="px-3 py-1 bg-white/20 text-white text-sm font-semibold rounded-full">
                        {{ ucfirst($campaign->type) }}
                    </span>
                    @if($campaign->messageTemplate)
                        <span class="px-3 py-1 bg-white/20 text-white text-sm font-semibold rounded-full">
                            {{ $campaign->messageTemplate->name }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('campaigns.show', $campaign) }}" class="bg-white text-purple-600 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                    Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white/70 backdrop-blur-sm rounded-xl shadow-lg p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total Destinatarios</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($campaign->total_recipients) }}</p>
                </div>
                <div class="p-3 bg-gray-100 rounded-full">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white/70 backdrop-blur-sm rounded-xl shadow-lg p-6 border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-green-600 font-medium">Tasa de Entrega</p>
                    <p class="text-3xl font-bold text-green-700">{{ number_format($metrics['delivery_rate'], 1) }}%</p>
                    <p class="text-xs text-gray-600 mt-1">{{ number_format($campaign->delivered_count) }} de {{ number_format($campaign->sent_count) }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white/70 backdrop-blur-sm rounded-xl shadow-lg p-6 border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-purple-600 font-medium">Tasa de Lectura</p>
                    <p class="text-3xl font-bold text-purple-700">{{ number_format($metrics['read_rate'], 1) }}%</p>
                    <p class="text-xs text-gray-600 mt-1">{{ number_format($campaign->read_count) }} de {{ number_format($campaign->delivered_count) }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white/70 backdrop-blur-sm rounded-xl shadow-lg p-6 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-600 font-medium">Tasa de Respuesta</p>
                    <p class="text-3xl font-bold text-blue-700">
                        {{ $campaign->total_recipients > 0 ? number_format(($campaign->response_count / $campaign->total_recipients) * 100, 1) : 0 }}%
                    </p>
                    <p class="text-xs text-gray-600 mt-1">{{ number_format($campaign->response_count) }} respuestas</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Message Status Breakdown -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Desglose por Estado
            </h3>

            <div class="space-y-4">
                <!-- Pending -->
                <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-yellow-200 rounded-lg">
                            <svg class="w-5 h-5 text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-yellow-900">Pendientes</p>
                            <p class="text-sm text-yellow-700">En espera de envío</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-yellow-900">{{ number_format($messageStats['pending']) }}</p>
                        @if($campaign->total_recipients > 0)
                            <p class="text-xs text-yellow-700">{{ number_format(($messageStats['pending'] / $campaign->total_recipients) * 100, 1) }}%</p>
                        @endif
                    </div>
                </div>

                <!-- Queued -->
                <div class="flex items-center justify-between p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-200 rounded-lg">
                            <svg class="w-5 h-5 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-indigo-900">En Cola</p>
                            <p class="text-sm text-indigo-700">Procesando</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-indigo-900">{{ number_format($messageStats['queued']) }}</p>
                        @if($campaign->total_recipients > 0)
                            <p class="text-xs text-indigo-700">{{ number_format(($messageStats['queued'] / $campaign->total_recipients) * 100, 1) }}%</p>
                        @endif
                    </div>
                </div>

                <!-- Sent -->
                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-200 rounded-lg">
                            <svg class="w-5 h-5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-blue-900">Enviados</p>
                            <p class="text-sm text-blue-700">Entregados a WhatsApp</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-blue-900">{{ number_format($messageStats['sent']) }}</p>
                        @if($campaign->total_recipients > 0)
                            <p class="text-xs text-blue-700">{{ number_format(($messageStats['sent'] / $campaign->total_recipients) * 100, 1) }}%</p>
                        @endif
                    </div>
                </div>

                <!-- Delivered -->
                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-200 rounded-lg">
                            <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-green-900">Entregados</p>
                            <p class="text-sm text-green-700">Recibidos por usuario</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-green-900">{{ number_format($messageStats['delivered']) }}</p>
                        @if($campaign->total_recipients > 0)
                            <p class="text-xs text-green-700">{{ number_format(($messageStats['delivered'] / $campaign->total_recipients) * 100, 1) }}%</p>
                        @endif
                    </div>
                </div>

                <!-- Read -->
                <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-200 rounded-lg">
                            <svg class="w-5 h-5 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-purple-900">Leídos</p>
                            <p class="text-sm text-purple-700">Abiertos por usuario</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-purple-900">{{ number_format($messageStats['read']) }}</p>
                        @if($campaign->total_recipients > 0)
                            <p class="text-xs text-purple-700">{{ number_format(($messageStats['read'] / $campaign->total_recipients) * 100, 1) }}%</p>
                        @endif
                    </div>
                </div>

                <!-- Failed -->
                <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-200 rounded-lg">
                            <svg class="w-5 h-5 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-red-900">Fallidos</p>
                            <p class="text-sm text-red-700">Con errores</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-red-900">{{ number_format($messageStats['failed']) }}</p>
                        @if($campaign->total_recipients > 0)
                            <p class="text-xs text-red-700">{{ number_format(($messageStats['failed'] / $campaign->total_recipients) * 100, 1) }}%</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Campaign Timeline & Failed Messages -->
        <div class="space-y-6">
            <!-- Timeline -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Línea de Tiempo
                </h3>

                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-2 h-2 mt-2 bg-gray-500 rounded-full"></div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Campaña Creada</p>
                            <p class="text-xs text-gray-600">{{ $campaign->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if($campaign->started_at)
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-2 h-2 mt-2 bg-blue-500 rounded-full"></div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Campaña Iniciada</p>
                            <p class="text-xs text-gray-600">{{ $campaign->started_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif

                    @if($campaign->completed_at)
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-2 h-2 mt-2 bg-green-500 rounded-full"></div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Campaña Completada</p>
                            <p class="text-xs text-gray-600">{{ $campaign->completed_at->format('d/m/Y H:i') }}</p>
                            @if($campaign->started_at)
                                <p class="text-xs text-gray-500">Duración: {{ $campaign->started_at->diffForHumans($campaign->completed_at, true) }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Recent Failed Messages -->
            @if($failedMessages->count() > 0)
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-red-100">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Mensajes Fallidos Recientes
                </h3>

                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($failedMessages as $message)
                    <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="font-semibold text-red-900">{{ $message->contact->name ?? $message->phone_number }}</p>
                                <p class="text-xs text-red-700">{{ $message->phone_number }}</p>
                            </div>
                            <span class="text-xs text-red-600">{{ $message->failed_at->format('d/m H:i') }}</span>
                        </div>
                        @if($message->error_message)
                            <p class="text-sm text-red-800 mt-2">{{ $message->error_message }}</p>
                        @endif
                        @if($message->error_code)
                            <p class="text-xs text-red-600 mt-1">Código: {{ $message->error_code }}</p>
                        @endif
                        <p class="text-xs text-red-700 mt-2">Reintentos: {{ $message->retry_count }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Campaign Info Summary -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
        <h3 class="text-xl font-bold text-gray-900 mb-6">Resumen de Campaña</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-600 font-medium mb-2">Información General</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tipo:</span>
                        <span class="font-semibold">{{ ucfirst($campaign->type) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Estado:</span>
                        <span class="font-semibold">{{ ucfirst($campaign->status) }}</span>
                    </div>
                    @if($campaign->wabaAccount)
                    <div class="flex justify-between">
                        <span class="text-gray-600">WABA:</span>
                        <span class="font-semibold">{{ $campaign->wabaAccount->name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-600 font-medium mb-2">Plantilla</p>
                <div class="space-y-2 text-sm">
                    @if($campaign->messageTemplate)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nombre:</span>
                            <span class="font-semibold">{{ $campaign->messageTemplate->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Categoría:</span>
                            <span class="font-semibold">{{ ucfirst($campaign->messageTemplate->category) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Idioma:</span>
                            <span class="font-semibold">{{ $campaign->messageTemplate->language }}</span>
                        </div>
                    @else
                        <p class="text-gray-500">Sin plantilla</p>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-600 font-medium mb-2">Rendimiento</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tasa de Éxito:</span>
                        <span class="font-semibold text-green-600">
                            {{ $campaign->total_recipients > 0 ? number_format((($campaign->delivered_count + $campaign->read_count) / $campaign->total_recipients) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tasa de Error:</span>
                        <span class="font-semibold text-red-600">
                            {{ $campaign->total_recipients > 0 ? number_format(($campaign->failed_count / $campaign->total_recipients) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Engagement:</span>
                        <span class="font-semibold text-purple-600">
                            {{ $campaign->total_recipients > 0 ? number_format((($campaign->read_count + $campaign->response_count) / $campaign->total_recipients) * 100, 1) : 0 }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

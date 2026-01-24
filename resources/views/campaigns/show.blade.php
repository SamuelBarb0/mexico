@extends('layouts.app')

@section('title', 'Detalle de Campaña')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-violet-700 rounded-2xl shadow-2xl p-8">
        <div class="flex justify-between items-start">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-4xl font-extrabold text-white">{{ $campaign->name }}</h1>
                    @if($campaign->status === 'completed')
                        <span class="px-4 py-2 bg-green-500 text-white text-sm font-semibold rounded-full">Completada</span>
                    @elseif($campaign->status === 'active')
                        <span class="px-4 py-2 bg-blue-500 text-white text-sm font-semibold rounded-full">Activa</span>
                    @elseif($campaign->status === 'scheduled')
                        <span class="px-4 py-2 bg-yellow-500 text-white text-sm font-semibold rounded-full">Programada</span>
                    @elseif($campaign->status === 'paused')
                        <span class="px-4 py-2 bg-orange-500 text-white text-sm font-semibold rounded-full">Pausada</span>
                    @else
                        <span class="px-4 py-2 bg-gray-500 text-white text-sm font-semibold rounded-full">Borrador</span>
                    @endif
                </div>
                @if($campaign->description)
                    <p class="text-purple-100 text-lg">{{ $campaign->description }}</p>
                @endif
                <div class="flex items-center gap-4 mt-3 text-purple-100 text-sm">
                    <span>Tipo: <strong>{{ ucfirst($campaign->type) }}</strong></span>
                    @if($campaign->scheduled_at)
                        <span>•</span>
                        <span>Programada: <strong>{{ $campaign->scheduled_at->format('d/m/Y H:i') }}</strong></span>
                    @endif
                    @if($campaign->started_at)
                        <span>•</span>
                        <span>Iniciada: <strong>{{ $campaign->started_at->format('d/m/Y H:i') }}</strong></span>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('campaigns.index') }}" class="bg-white text-purple-600 px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg sm:rounded-xl font-bold text-sm sm:text-base shadow-lg hover:shadow-xl transition-all cursor-pointer">
                    Volver
                </a>

                @if(in_array($campaign->status, ['draft', 'scheduled']) && $campaign->total_recipients == 0)
                    <form action="{{ route('campaigns.prepare', $campaign) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-yellow-500 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg sm:rounded-xl font-bold text-sm sm:text-base shadow-lg hover:shadow-xl transition-all hover:bg-yellow-600 cursor-pointer whitespace-nowrap">
                            <i class="bi bi-gear"></i> Preparar Campaña
                        </button>
                    </form>
                @endif

                @if(in_array($campaign->status, ['draft', 'scheduled', 'paused']) && $campaign->total_recipients > 0)
                    <button type="button" id="executeButton" onclick="startCampaignExecution()" class="bg-green-500 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg sm:rounded-xl font-bold text-sm sm:text-base shadow-lg hover:shadow-xl transition-all hover:bg-green-600 cursor-pointer">
                        <span class="hidden sm:inline">Ejecutar Campaña ({{ number_format($campaign->total_recipients) }} mensajes)</span>
                        <span class="sm:hidden">Ejecutar ({{ number_format($campaign->total_recipients) }})</span>
                    </button>
                @endif

                @if($campaign->total_recipients > 0)
                    <a href="{{ route('campaigns.metrics', $campaign) }}" class="bg-blue-500 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg sm:rounded-xl font-bold text-sm sm:text-base shadow-lg hover:shadow-xl transition-all hover:bg-blue-600 cursor-pointer whitespace-nowrap">
                        <i class="bi bi-bar-chart"></i> Ver Métricas
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white/70 backdrop-blur-sm rounded-xl shadow-lg p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 font-medium">Total</p>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($metrics['total']) }}</p>
                </div>
                <div class="p-3 bg-gray-100 rounded-full">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white/70 backdrop-blur-sm rounded-xl shadow-lg p-6 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-600 font-medium">Enviados</p>
                    <p class="text-3xl font-bold text-blue-700">{{ number_format($metrics['sent']) }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white/70 backdrop-blur-sm rounded-xl shadow-lg p-6 border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-green-600 font-medium">Entregados</p>
                    <p class="text-3xl font-bold text-green-700">{{ number_format($metrics['delivered']) }}</p>
                    @if($metrics['delivery_rate'] > 0)
                        <p class="text-xs text-green-600 mt-1">{{ number_format($metrics['delivery_rate'], 1) }}%</p>
                    @endif
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white/70 backdrop-blur-sm rounded-xl shadow-lg p-6 border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-purple-600 font-medium">Leídos</p>
                    <p class="text-3xl font-bold text-purple-700">{{ number_format($metrics['read']) }}</p>
                    @if($metrics['read_rate'] > 0)
                        <p class="text-xs text-purple-600 mt-1">{{ number_format($metrics['read_rate'], 1) }}%</p>
                    @endif
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white/70 backdrop-blur-sm rounded-xl shadow-lg p-6 border border-red-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-red-600 font-medium">Fallidos</p>
                    <p class="text-3xl font-bold text-red-700">{{ number_format($metrics['failed']) }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Template Info -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Plantilla de Mensaje
                </h3>

                @if($campaign->messageTemplate)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $campaign->messageTemplate->name }}</p>
                                <p class="text-sm text-gray-600">{{ ucfirst($campaign->messageTemplate->category) }} • {{ $campaign->messageTemplate->language }}</p>
                            </div>
                            <a href="{{ route('templates.show', $campaign->messageTemplate) }}" class="text-purple-600 hover:text-purple-800 font-semibold text-sm">
                                Ver plantilla →
                            </a>
                        </div>

                        @if(isset($campaign->messageTemplate->components['body']))
                            <div class="p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                                <p class="text-sm font-semibold text-green-900 mb-2">Vista Previa del Mensaje:</p>
                                <p class="text-sm text-green-800 whitespace-pre-wrap">{{ $campaign->messageTemplate->components['body']['text'] }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500">No hay plantilla asignada</p>
                @endif
            </div>

            <!-- Target Audience -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Audiencia Objetivo
                </h3>

                <div class="p-4 bg-blue-50 rounded-lg">
                    @if(isset($campaign->target_audience['type']))
                        <p class="text-sm font-semibold text-blue-900">
                            Tipo:
                            @if($campaign->target_audience['type'] === 'all')
                                Todos los contactos activos
                            @elseif($campaign->target_audience['type'] === 'lists')
                                Contactos de listas específicas
                            @elseif($campaign->target_audience['type'] === 'tags')
                                Contactos con etiquetas específicas
                            @else
                                Filtro personalizado
                            @endif
                        </p>
                        <p class="text-sm text-blue-700 mt-2">Total de destinatarios: <strong>{{ number_format($campaign->total_recipients) }}</strong></p>
                    @else
                        <p class="text-sm text-gray-600">No definida</p>
                    @endif
                </div>
            </div>

            <!-- Recent Messages (if any) -->
            @if($metrics['pending'] > 0)
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Estado de Mensajes
                </h3>

                <div class="space-y-2">
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                        <span class="text-sm font-medium text-yellow-900">Mensajes pendientes</span>
                        <span class="text-lg font-bold text-yellow-700">{{ number_format($metrics['pending']) }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions Card -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Acciones</h3>
                <div class="space-y-3">
                    @if($campaign->status === 'draft' && $campaign->total_recipients == 0)
                        <form action="{{ route('campaigns.prepare', $campaign) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition shadow-lg cursor-pointer">
                                Preparar Campaña
                            </button>
                        </form>
                        <p class="text-xs text-gray-600">Genera mensajes individuales para todos los contactos</p>
                    @endif

                    @if($campaign->status === 'draft' && !$campaign->started_at)
                        <a href="{{ route('campaigns.edit', $campaign) }}" class="block w-full px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold text-center transition shadow-lg cursor-pointer">
                            Editar Campaña
                        </a>
                    @endif

                    @if(in_array($campaign->status, ['draft', 'completed', 'failed', 'canceled']))
                        <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta campaña? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition shadow-lg cursor-pointer">
                                <i class="bi bi-trash"></i> Eliminar Campaña
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- WABA Account Info -->
            @if($campaign->wabaAccount)
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">WABA Account</h3>
                <div class="space-y-2">
                    <p class="text-sm"><span class="font-semibold">Nombre:</span> {{ $campaign->wabaAccount->name }}</p>
                    <p class="text-sm"><span class="font-semibold">Teléfono:</span> {{ $campaign->wabaAccount->phone_number }}</p>
                    <a href="{{ route('waba-accounts.show', $campaign->wabaAccount) }}" class="text-sm text-purple-600 hover:text-purple-800 font-semibold">
                        Ver detalles →
                    </a>
                </div>
            </div>
            @endif

            <!-- Campaign Info -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Información</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Creada:</span>
                        <span class="font-semibold">{{ $campaign->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($campaign->started_at)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Iniciada:</span>
                        <span class="font-semibold">{{ $campaign->started_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                    @if($campaign->completed_at)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Completada:</span>
                        <span class="font-semibold">{{ $campaign->completed_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Progreso -->
<div id="progressModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="text-center">
            <div class="mb-4">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-green-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Enviando Mensajes</h3>
                <p class="text-gray-600 mb-4" id="progressText">Iniciando envío...</p>
            </div>

            <!-- Barra de Progreso -->
            <div class="w-full bg-gray-200 rounded-full h-4 mb-4 overflow-hidden">
                <div id="progressBar" class="bg-gradient-to-r from-green-500 to-green-600 h-4 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-2xl font-bold text-gray-900" id="sentCount">0</div>
                    <div class="text-xs text-gray-600">Enviados</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-2xl font-bold text-gray-900" id="pendingCount">{{ $pendingCount ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Pendientes</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-2xl font-bold text-red-600" id="failedCount">0</div>
                    <div class="text-xs text-gray-600">Fallidos</div>
                </div>
            </div>

            <!-- Botón de Cerrar (oculto hasta que termine) -->
            <button type="button" onclick="closeProgressModal()" id="closeProgressButton" class="hidden mt-6 w-full px-4 py-3 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 transition">
                Aceptar
            </button>
        </div>
    </div>
</div>

<script>
const campaignId = {{ $campaign->id }};
let progressInterval = null;

function startCampaignExecution() {
    // Confirmar
    if (!confirm('¿Estás seguro de ejecutar esta campaña y enviar los mensajes?')) {
        return;
    }

    // Mostrar modal
    document.getElementById('progressModal').classList.remove('hidden');
    document.getElementById('progressModal').classList.add('flex');
    document.getElementById('executeButton').disabled = true;

    // Iniciar envío y polling
    sendNextBatch();
}

function sendNextBatch() {
    fetch('{{ route('campaigns.execute', $campaign) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    }).then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Error al ejecutar campaña');
            });
        }
        return response.json();
    }).then(data => {
        // Actualizar progreso
        updateProgress();

        // Si hay más mensajes pendientes, enviar siguiente batch
        if (data.remaining > 0 && !data.completed) {
            // Pequeña pausa antes del siguiente batch
            setTimeout(sendNextBatch, 1000);
        }
    }).catch(error => {
        console.error('Error:', error);
        // Intentar continuar si hay error temporal
        setTimeout(() => {
            updateProgress();
        }, 2000);
    });
}

function startProgressPolling() {
    // Actualizar inmediatamente
    updateProgress();

    // Actualizar cada 2 segundos
    progressInterval = setInterval(updateProgress, 2000);
}

function updateProgress() {
    fetch('{{ route('campaigns.progress', $campaign) }}')
        .then(response => response.json())
        .then(data => {
            // Actualizar contador
            document.getElementById('sentCount').textContent = data.sent;
            document.getElementById('pendingCount').textContent = data.pending;
            document.getElementById('failedCount').textContent = data.failed;

            // Calcular porcentaje
            const total = data.total;
            const processed = data.sent + data.failed;
            const percentage = total > 0 ? Math.round((processed / total) * 100) : 0;

            // Actualizar barra de progreso
            document.getElementById('progressBar').style.width = percentage + '%';
            document.getElementById('progressText').textContent =
                `${processed} de ${total} mensajes procesados (${percentage}%)`;

            // Si terminó
            if (data.pending === 0) {
                clearInterval(progressInterval);
                document.getElementById('progressText').textContent =
                    data.failed > 0
                        ? `Campaña completada con ${data.failed} errores`
                        : '¡Campaña completada exitosamente!';
                document.getElementById('closeProgressButton').classList.remove('hidden');

                // Recargar página después de 3 segundos
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error al obtener progreso:', error);
        });
}

function closeProgressModal() {
    document.getElementById('progressModal').classList.add('hidden');
    document.getElementById('progressModal').classList.remove('flex');
    clearInterval(progressInterval);
    window.location.reload();
}
</script>

@endsection

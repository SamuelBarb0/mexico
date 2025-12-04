@extends('layouts.app')

@section('title', 'Detalle de la Campaña')

@section('content')
<div class="mb-6">
    <a href="{{ route('campaigns.index') }}" class="text-blue-600 hover:text-blue-900">
        ← Volver a Campañas
    </a>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">{{ $campaign->name }}</h2>
        <div class="flex space-x-3">
            @if($campaign->status === 'draft' || $campaign->status === 'scheduled')
                <a href="{{ route('campaigns.edit', $campaign) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Editar
                </a>
            @endif
            @if($campaign->status === 'scheduled' || $campaign->status === 'draft')
                <form action="{{ route('campaigns.execute', $campaign) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded"
                        onclick="return confirm('¿Ejecutar esta campaña ahora?')">
                        Ejecutar Ahora
                    </button>
                </form>
            @endif
            <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
                    onclick="return confirm('¿Estás seguro de eliminar esta campaña?')">
                    Eliminar
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-sm font-medium text-gray-500">WABA Account</h3>
            <p class="mt-1 text-lg text-gray-900">
                @if($campaign->wabaAccount)
                    <a href="{{ route('waba-accounts.show', $campaign->wabaAccount) }}" class="text-blue-600 hover:text-blue-900">
                        {{ $campaign->wabaAccount->name }}
                    </a>
                    <span class="text-sm text-gray-500">({{ $campaign->wabaAccount->phone_number }})</span>
                @else
                    <span class="text-gray-400">Sin WABA asignada</span>
                @endif
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Estado</h3>
            <p class="mt-1">
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                    {{ $campaign->status === 'completed' ? 'bg-green-100 text-green-800' :
                       ($campaign->status === 'active' || $campaign->status === 'running' ? 'bg-blue-100 text-blue-800' :
                       ($campaign->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                    @if($campaign->status === 'draft')
                        Borrador
                    @elseif($campaign->status === 'scheduled')
                        Programada
                    @elseif($campaign->status === 'active')
                        Activa
                    @elseif($campaign->status === 'running')
                        En ejecución
                    @elseif($campaign->status === 'paused')
                        Pausada
                    @elseif($campaign->status === 'completed')
                        Completada
                    @else
                        Fallida
                    @endif
                </span>
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Tipo</h3>
            <p class="mt-1 text-lg text-gray-900">
                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded">{{ ucfirst($campaign->type) }}</span>
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Fecha Programada</h3>
            <p class="mt-1 text-lg text-gray-900">
                {{ $campaign->scheduled_at ? $campaign->scheduled_at->format('d/m/Y H:i') : '-' }}
            </p>
        </div>

        @if($campaign->started_at)
        <div>
            <h3 class="text-sm font-medium text-gray-500">Fecha de Inicio</h3>
            <p class="mt-1 text-lg text-gray-900">{{ $campaign->started_at->format('d/m/Y H:i') }}</p>
        </div>
        @endif

        @if($campaign->completed_at)
        <div>
            <h3 class="text-sm font-medium text-gray-500">Fecha de Finalización</h3>
            <p class="mt-1 text-lg text-gray-900">{{ $campaign->completed_at->format('d/m/Y H:i') }}</p>
        </div>
        @endif
    </div>

    @if($campaign->description)
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h3 class="text-sm font-medium text-gray-500">Descripción</h3>
        <p class="mt-2 text-gray-900">{{ $campaign->description }}</p>
    </div>
    @endif

    <div class="mt-6 pt-6 border-t border-gray-200">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Estadísticas de Mensajes</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <p class="text-sm text-blue-600 font-medium">Mensajes Enviados</p>
                <p class="text-3xl font-bold text-blue-900">{{ $campaign->messages_sent }}</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <p class="text-sm text-green-600 font-medium">Mensajes Entregados</p>
                <p class="text-3xl font-bold text-green-900">{{ $campaign->messages_delivered }}</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4">
                <p class="text-sm text-red-600 font-medium">Mensajes Fallidos</p>
                <p class="text-3xl font-bold text-red-900">{{ $campaign->messages_failed }}</p>
            </div>
        </div>
    </div>

    @if($campaign->message_template)
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Plantilla de Mensaje</h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <pre class="text-sm text-gray-900 whitespace-pre-wrap">{{ json_encode($campaign->message_template, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
    @endif

    @if($campaign->target_audience)
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Audiencia Objetivo</h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <pre class="text-sm text-gray-900 whitespace-pre-wrap">{{ json_encode($campaign->target_audience, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
    @endif

    <div class="mt-6 pt-6 border-t border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-500">
            <div>
                <span class="font-medium">Creada:</span> {{ $campaign->created_at->format('d/m/Y H:i') }}
            </div>
            <div>
                <span class="font-medium">Actualizada:</span> {{ $campaign->updated_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</div>
@endsection

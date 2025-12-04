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
            <a href="{{ route('waba-accounts.edit', $wabaAccount) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Editar
            </a>
            <form action="{{ route('waba-accounts.destroy', $wabaAccount) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
                    onclick="return confirm('¿Estás seguro de eliminar esta cuenta WABA?')">
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
            <h3 class="text-sm font-medium text-gray-500">Estado</h3>
            <p class="mt-1">
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                    {{ $wabaAccount->status === 'active' ? 'bg-green-100 text-green-800' :
                       ($wabaAccount->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                       ($wabaAccount->status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                    @if($wabaAccount->status === 'active')
                        Activa
                    @elseif($wabaAccount->status === 'pending')
                        Pendiente
                    @elseif($wabaAccount->status === 'suspended')
                        Suspendida
                    @else
                        Inactiva
                    @endif
                </span>
            </p>
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

        <div>
            <h3 class="text-sm font-medium text-gray-500">Calificación de Calidad</h3>
            <p class="mt-1">
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                    {{ $wabaAccount->quality_rating === 'green' ? 'bg-green-100 text-green-800' :
                       ($wabaAccount->quality_rating === 'yellow' ? 'bg-yellow-100 text-yellow-800' :
                       ($wabaAccount->quality_rating === 'red' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                    {{ ucfirst($wabaAccount->quality_rating) }}
                </span>
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Cuenta Verificada</h3>
            <p class="mt-1 text-lg">
                @if($wabaAccount->is_verified)
                    <span class="inline-flex items-center text-green-600">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Verificada
                    </span>
                @else
                    <span class="inline-flex items-center text-gray-500">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        No Verificada
                    </span>
                @endif
            </p>
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-500">
            <div>
                <span class="font-medium">Creada:</span> {{ $wabaAccount->created_at->format('d/m/Y H:i') }}
            </div>
            <div>
                <span class="font-medium">Actualizada:</span> {{ $wabaAccount->updated_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</div>
@endsection

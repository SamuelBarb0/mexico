@extends('layouts.app')

@section('title', 'Ver Cliente')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Detalles del Cliente</h2>
        <div class="space-x-2">
            <a href="{{ route('clients.edit', $client) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Editar
            </a>
            <a href="{{ route('clients.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">
                Volver
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Nombre</h3>
            <p class="mt-1 text-lg text-gray-900">{{ $client->name }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Empresa</h3>
            <p class="mt-1 text-lg text-gray-900">{{ $client->company ?? '-' }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Email</h3>
            <p class="mt-1 text-lg text-gray-900">{{ $client->email ?? '-' }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Teléfono</h3>
            <p class="mt-1 text-lg text-gray-900">{{ $client->phone ?? '-' }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">País</h3>
            <p class="mt-1 text-lg text-gray-900">{{ $client->country ?? '-' }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Estado</h3>
            <p class="mt-1">
                @if($client->status === 'active')
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Activo
                    </span>
                @else
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                        Inactivo
                    </span>
                @endif
            </p>
        </div>

        <div class="md:col-span-2">
            <h3 class="text-sm font-medium text-gray-500">Dirección</h3>
            <p class="mt-1 text-gray-900">{{ $client->address ?? '-' }}</p>
        </div>

        <div class="md:col-span-2">
            <h3 class="text-sm font-medium text-gray-500">Notas</h3>
            <p class="mt-1 text-gray-900 whitespace-pre-line">{{ $client->notes ?? '-' }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Creado</h3>
            <p class="mt-1 text-gray-900">{{ $client->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Última Actualización</h3>
            <p class="mt-1 text-gray-900">{{ $client->updated_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    @if($client->contacts->isNotEmpty())
    <div class="mt-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Contactos Asociados ({{ $client->contacts->count() }})</h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <ul class="divide-y divide-gray-200">
                @foreach($client->contacts as $contact)
                <li class="py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $contact->name }}</p>
                        <p class="text-sm text-gray-500">{{ $contact->phone }}</p>
                    </div>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                        {{ $contact->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ ucfirst($contact->status) }}
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
</div>
@endsection

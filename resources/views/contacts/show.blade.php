@extends('layouts.app')

@section('title', 'Detalle del Contacto')

@section('content')
<div class="mb-6">
    <a href="{{ route('contacts.index') }}" class="text-blue-600 hover:text-blue-900">
        ← Volver a Contactos
    </a>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">{{ $contact->name }}</h2>
        <div class="flex space-x-3">
            <a href="{{ route('contacts.edit', $contact) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Editar
            </a>
            <form action="{{ route('contacts.destroy', $contact) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
                    onclick="return confirm('¿Estás seguro de eliminar este contacto?')">
                    Eliminar
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-sm font-medium text-gray-500">Cliente</h3>
            <p class="mt-1 text-lg text-gray-900">
                @if($contact->client)
                    <a href="{{ route('clients.show', $contact->client) }}" class="text-blue-600 hover:text-blue-900">
                        {{ $contact->client->name }}
                    </a>
                @else
                    <span class="text-gray-400">Sin cliente asignado</span>
                @endif
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Estado</h3>
            <p class="mt-1">
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                    {{ $contact->status === 'active' ? 'bg-green-100 text-green-800' : ($contact->status === 'blocked' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                    @if($contact->status === 'active')
                        Activo
                    @elseif($contact->status === 'inactive')
                        Inactivo
                    @else
                        Bloqueado
                    @endif
                </span>
            </p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Teléfono</h3>
            <p class="mt-1 text-lg text-gray-900">{{ $contact->phone ?? '-' }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">Email</h3>
            <p class="mt-1 text-lg text-gray-900">{{ $contact->email ?? '-' }}</p>
        </div>

        <div>
            <h3 class="text-sm font-medium text-gray-500">WhatsApp ID</h3>
            <p class="mt-1 text-lg text-gray-900">{{ $contact->whatsapp_id ?? '-' }}</p>
        </div>

        @if($contact->tags)
        <div>
            <h3 class="text-sm font-medium text-gray-500">Tags</h3>
            <div class="mt-1 flex flex-wrap gap-2">
                @foreach($contact->tags as $tag)
                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    @if($contact->custom_fields && count($contact->custom_fields) > 0)
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Campos Personalizados</h3>
        <div class="bg-gray-50 rounded-lg p-4">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($contact->custom_fields as $key => $value)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ ucfirst($key) }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
    @endif

    <div class="mt-6 pt-6 border-t border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-500">
            <div>
                <span class="font-medium">Creado:</span> {{ $contact->created_at->format('d/m/Y H:i') }}
            </div>
            <div>
                <span class="font-medium">Actualizado:</span> {{ $contact->updated_at->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Editar Contacto')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Editar Contacto</h2>
    </div>

    <form action="{{ route('contacts.update', $contact) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="client_id" class="block text-sm font-medium text-gray-700">Cliente</label>
                <select name="client_id" id="client_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('client_id') border-red-500 @enderror">
                    <option value="">Seleccionar cliente...</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id', $contact->client_id) == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
                @error('client_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre *</label>
                <input type="text" name="name" id="name" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    value="{{ old('name', $contact->name) }}">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Teléfono</label>
                <input type="text" name="phone" id="phone"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('phone') border-red-500 @enderror"
                    value="{{ old('phone', $contact->phone) }}" placeholder="+52 123 456 7890">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                    value="{{ old('email', $contact->email) }}">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="whatsapp_id" class="block text-sm font-medium text-gray-700">WhatsApp ID</label>
                <input type="text" name="whatsapp_id" id="whatsapp_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('whatsapp_id') border-red-500 @enderror"
                    value="{{ old('whatsapp_id', $contact->whatsapp_id) }}" placeholder="521234567890">
                @error('whatsapp_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                <select name="status" id="status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="active" {{ old('status', $contact->status) === 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="inactive" {{ old('status', $contact->status) === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    <option value="blocked" {{ old('status', $contact->status) === 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="tags" class="block text-sm font-medium text-gray-700">Tags (separados por comas)</label>
                <input type="text" name="tags" id="tags"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    value="{{ old('tags', is_array($contact->tags) ? implode(', ', $contact->tags) : '') }}"
                    placeholder="vip, premium, importante">
                <p class="mt-1 text-sm text-gray-500">Ejemplo: vip, premium, cliente-frecuente</p>
            </div>

            <div class="md:col-span-2">
                <label for="custom_fields" class="block text-sm font-medium text-gray-700">Campos Personalizados (JSON)</label>
                <textarea name="custom_fields" id="custom_fields" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('custom_fields') border-red-500 @enderror"
                    placeholder='{"birthday": "1990-05-15", "preferencia": "nocturno"}'>{{ old('custom_fields', $contact->custom_fields ? json_encode($contact->custom_fields, JSON_PRETTY_PRINT) : '') }}</textarea>
                @error('custom_fields')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Formato JSON válido. Ejemplo: {"campo1": "valor1", "campo2": "valor2"}</p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-3">
            <a href="{{ route('contacts.show', $contact) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">
                Cancelar
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Actualizar Contacto
            </button>
        </div>
    </form>
</div>
@endsection

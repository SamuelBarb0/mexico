@extends('layouts.app')

@section('title', 'Crear Campaña')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Crear Nueva Campaña</h2>
    </div>

    <form action="{{ route('campaigns.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="waba_account_id" class="block text-sm font-medium text-gray-700">WABA Account *</label>
                <select name="waba_account_id" id="waba_account_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('waba_account_id') border-red-500 @enderror">
                    <option value="">Seleccionar cuenta WABA...</option>
                    @foreach($wabaAccounts as $waba)
                        <option value="{{ $waba->id }}" {{ old('waba_account_id') == $waba->id ? 'selected' : '' }}>
                            {{ $waba->name }} ({{ $waba->phone_number }})
                        </option>
                    @endforeach
                </select>
                @error('waba_account_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre *</label>
                <input type="text" name="name" id="name" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    value="{{ old('name') }}">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Tipo *</label>
                <select name="type" id="type" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="broadcast" {{ old('type', 'broadcast') === 'broadcast' ? 'selected' : '' }}>Broadcast</option>
                    <option value="drip" {{ old('type') === 'drip' ? 'selected' : '' }}>Drip</option>
                    <option value="trigger" {{ old('type') === 'trigger' ? 'selected' : '' }}>Trigger</option>
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                <select name="status" id="status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Borrador</option>
                    <option value="scheduled" {{ old('status') === 'scheduled' ? 'selected' : '' }}>Programada</option>
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Activa</option>
                </select>
            </div>

            <div>
                <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Fecha Programada</label>
                <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    value="{{ old('scheduled_at') }}">
                <p class="mt-1 text-sm text-gray-500">Dejar vacío para campaña inmediata</p>
            </div>

            <div>
                <label for="started_at" class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                <input type="datetime-local" name="started_at" id="started_at"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    value="{{ old('started_at') }}">
            </div>

            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                <textarea name="description" id="description" rows="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label for="message_template" class="block text-sm font-medium text-gray-700">Plantilla de Mensaje (JSON) *</label>
                <textarea name="message_template" id="message_template" rows="4" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('message_template') border-red-500 @enderror"
                    placeholder='{"text": "¡Hola {name}! Tenemos ofertas especiales", "buttons": ["Ver Ofertas"]}'>{{ old('message_template') }}</textarea>
                @error('message_template')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Formato JSON válido. Usa @{{ "{{name}}" }} para variables dinámicas</p>
            </div>

            <div class="md:col-span-2">
                <label for="target_audience" class="block text-sm font-medium text-gray-700">Audiencia Objetivo (JSON)</label>
                <textarea name="target_audience" id="target_audience" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('target_audience') border-red-500 @enderror"
                    placeholder='{"tags": ["vip", "premium"], "status": "active"}'>{{ old('target_audience') }}</textarea>
                @error('target_audience')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Formato JSON válido. Define los criterios de segmentación</p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-3">
            <a href="{{ route('campaigns.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">
                Cancelar
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Crear Campaña
            </button>
        </div>
    </form>
</div>
@endsection

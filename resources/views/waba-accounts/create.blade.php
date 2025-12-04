@extends('layouts.app')

@section('title', 'Crear Cuenta WABA')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Crear Nueva Cuenta WABA</h2>
    </div>

    <form action="{{ route('waba-accounts.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nombre *</label>
                <input type="text" name="name" id="name" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    value="{{ old('name') }}" placeholder="WABA Principal">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone_number" class="block text-sm font-medium text-gray-700">Número de Teléfono *</label>
                <input type="text" name="phone_number" id="phone_number" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('phone_number') border-red-500 @enderror"
                    value="{{ old('phone_number') }}" placeholder="+52 123 456 7890">
                @error('phone_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone_number_id" class="block text-sm font-medium text-gray-700">Phone Number ID *</label>
                <input type="text" name="phone_number_id" id="phone_number_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('phone_number_id') border-red-500 @enderror"
                    value="{{ old('phone_number_id') }}" placeholder="123456789">
                @error('phone_number_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">ID del número de teléfono de WhatsApp Business</p>
            </div>

            <div>
                <label for="business_account_id" class="block text-sm font-medium text-gray-700">Business Account ID *</label>
                <input type="text" name="business_account_id" id="business_account_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('business_account_id') border-red-500 @enderror"
                    value="{{ old('business_account_id') }}" placeholder="987654321">
                @error('business_account_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="waba_id" class="block text-sm font-medium text-gray-700">WABA ID *</label>
                <input type="text" name="waba_id" id="waba_id" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('waba_id') border-red-500 @enderror"
                    value="{{ old('waba_id') }}" placeholder="111222333">
                @error('waba_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">WhatsApp Business Account ID</p>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                <select name="status" id="status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="pending" {{ old('status', 'pending') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Activa</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactiva</option>
                    <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspendida</option>
                </select>
            </div>

            <div>
                <label for="quality_rating" class="block text-sm font-medium text-gray-700">Calificación de Calidad</label>
                <select name="quality_rating" id="quality_rating"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="unknown" {{ old('quality_rating', 'unknown') === 'unknown' ? 'selected' : '' }}>Desconocido</option>
                    <option value="green" {{ old('quality_rating') === 'green' ? 'selected' : '' }}>Verde</option>
                    <option value="yellow" {{ old('quality_rating') === 'yellow' ? 'selected' : '' }}>Amarillo</option>
                    <option value="red" {{ old('quality_rating') === 'red' ? 'selected' : '' }}>Rojo</option>
                </select>
            </div>

            <div>
                <label for="is_verified" class="block text-sm font-medium text-gray-700">Verificado</label>
                <select name="is_verified" id="is_verified"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="0" {{ old('is_verified', '0') === '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_verified') === '1' ? 'selected' : '' }}>Sí</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="access_token" class="block text-sm font-medium text-gray-700">Access Token *</label>
                <textarea name="access_token" id="access_token" rows="2" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('access_token') border-red-500 @enderror"
                    placeholder="EAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">{{ old('access_token') }}</textarea>
                @error('access_token')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Token de acceso de la API de WhatsApp Business</p>
            </div>

            <div class="md:col-span-2">
                <label for="settings" class="block text-sm font-medium text-gray-700">Configuración (JSON)</label>
                <textarea name="settings" id="settings" rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('settings') border-red-500 @enderror"
                    placeholder='{"webhook_url": "https://example.com/webhook", "verify_token": "mi_token_secreto"}'>{{ old('settings') }}</textarea>
                @error('settings')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Formato JSON válido. Configuraciones adicionales de la cuenta</p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end space-x-3">
            <a href="{{ route('waba-accounts.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">
                Cancelar
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Crear Cuenta WABA
            </button>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Editar Plan')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.plans.index') }}" class="text-white hover:text-gray-200 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Volver a Planes
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-xl p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Editar Plan: {{ $plan->name }}</h1>

        <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Información Básica -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Información Básica</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre del Plan *</label>
                        <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Slug *</label>
                        <input type="text" name="slug" value="{{ old('slug', $plan->slug) }}" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @error('slug')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('description', $plan->description) }}</textarea>
                </div>
            </div>

            <!-- Precios -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Precios</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Precio Mensual ($) *</label>
                        <input type="number" name="price_monthly" value="{{ old('price_monthly', $plan->price_monthly) }}" step="0.01" min="0" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Precio Anual ($) *</label>
                        <input type="number" name="price_yearly" value="{{ old('price_yearly', $plan->price_yearly) }}" step="0.01" min="0" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Moneda *</label>
                        <select name="currency" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="usd" {{ old('currency', $plan->currency) == 'usd' ? 'selected' : '' }}>USD</option>
                            <option value="mxn" {{ old('currency', $plan->currency) == 'mxn' ? 'selected' : '' }}>MXN</option>
                            <option value="eur" {{ old('currency', $plan->currency) == 'eur' ? 'selected' : '' }}>EUR</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Período de Prueba -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Período de Prueba</h2>

                <div class="flex items-center mb-4">
                    <input type="checkbox" name="has_trial" id="has_trial" value="1" {{ old('has_trial', $plan->has_trial) ? 'checked' : '' }}
                        class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="has_trial" class="ml-2 text-sm font-semibold text-gray-700">Ofrecer período de prueba</label>
                </div>

                <div id="trialDaysField" style="display: {{ old('has_trial', $plan->has_trial) ? 'block' : 'none' }};">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Días de Prueba</label>
                    <input type="number" name="trial_days" value="{{ old('trial_days', $plan->trial_days) }}" min="-1"
                        class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Use -1 para prueba ilimitada</p>
                </div>
            </div>

            <!-- Límites -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Límites del Plan</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Máximo Usuarios *</label>
                        <input type="number" name="max_users" value="{{ old('max_users', $plan->max_users) }}" min="1" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Máximo Contactos *</label>
                        <input type="number" name="max_contacts" value="{{ old('max_contacts', $plan->max_contacts) }}" min="0" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Máximo Campañas *</label>
                        <input type="number" name="max_campaigns" value="{{ old('max_campaigns', $plan->max_campaigns) }}" min="0" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Máximo Cuentas WABA *</label>
                        <input type="number" name="max_waba_accounts" value="{{ old('max_waba_accounts', $plan->max_waba_accounts) }}" min="0" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mensajes por Mes *</label>
                        <input type="number" name="max_messages_per_month" value="{{ old('max_messages_per_month', $plan->max_messages_per_month) }}" min="0" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Almacenamiento (MB) *</label>
                        <input type="number" name="max_storage_mb" value="{{ old('max_storage_mb', $plan->max_storage_mb) }}" min="0" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            <!-- Características -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Características</h2>
                <div id="featuresContainer">
                    @if(old('features', $plan->features))
                        @foreach(old('features', $plan->features) as $feature)
                        <div class="feature-item flex gap-2 mb-2">
                            <input type="text" name="features[]" value="{{ $feature }}" placeholder="Ej: Campañas ilimitadas"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <button type="button" onclick="removeField(this)" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Eliminar</button>
                        </div>
                        @endforeach
                    @else
                        <div class="feature-item flex gap-2 mb-2">
                            <input type="text" name="features[]" placeholder="Ej: Campañas ilimitadas"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <button type="button" onclick="removeField(this)" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Eliminar</button>
                        </div>
                    @endif
                </div>
                <button type="button" onclick="addFeature()" class="mt-2 text-indigo-600 hover:text-indigo-700 font-semibold text-sm">+ Agregar Característica</button>
            </div>

            <!-- Opciones -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Opciones</h2>

                <div class="space-y-3">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                            class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_active" class="ml-2 text-sm font-semibold text-gray-700">Plan Activo</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_visible" id="is_visible" value="1" {{ old('is_visible', $plan->is_visible) ? 'checked' : '' }}
                            class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_visible" class="ml-2 text-sm font-semibold text-gray-700">Visible para Usuarios</label>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_default" id="is_default" value="1" {{ old('is_default', $plan->is_default) ? 'checked' : '' }}
                            class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_default" class="ml-2 text-sm font-semibold text-gray-700">Plan por Defecto</label>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Orden de Visualización</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" min="1"
                        class="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="mb-8 bg-gray-50 rounded-lg p-4">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Estadísticas</h2>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Suscripciones Activas:</span>
                        <span class="font-semibold ml-2">{{ $plan->subscriptions()->where('status', 'active')->count() }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Total Suscripciones:</span>
                        <span class="font-semibold ml-2">{{ $plan->subscriptions()->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-3 rounded-lg transition-all">
                    Actualizar Plan
                </button>
                <a href="{{ route('admin.plans.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold px-6 py-3 rounded-lg text-center transition-all">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('has_trial').addEventListener('change', function() {
    document.getElementById('trialDaysField').style.display = this.checked ? 'block' : 'none';
});

function addFeature() {
    const container = document.getElementById('featuresContainer');
    const div = document.createElement('div');
    div.className = 'feature-item flex gap-2 mb-2';
    div.innerHTML = `
        <input type="text" name="features[]" placeholder="Ej: Soporte prioritario"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        <button type="button" onclick="removeField(this)" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Eliminar</button>
    `;
    container.appendChild(div);
}

function removeField(button) {
    button.parentElement.remove();
}
</script>
@endsection

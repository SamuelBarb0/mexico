@extends('layouts.app')

@section('title', 'Editar Tenant')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white">Editar Tenant</h1>
            <p class="text-purple-200 mt-1">{{ $tenant->name }}</p>
        </div>
        <a href="{{ route('admin.tenants.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-all">
            ← Volver
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <form action="{{ route('admin.tenants.update', $tenant) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Información Básica -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Básica</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre de la Empresa *
                            </label>
                            <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Slug (URL amigable) *
                            </label>
                            <input type="text" name="slug" value="{{ old('slug', $tenant->slug) }}" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="mi-empresa">
                            <p class="mt-1 text-xs text-gray-500">Solo letras minúsculas, números y guiones</p>
                            @error('slug')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Dominio
                            </label>
                            <input type="text" name="domain" value="{{ old('domain', $tenant->domain) }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="empresa.example.com">
                            @error('domain')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Estado *
                            </label>
                            <select name="status" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="active" {{ old('status', $tenant->status) === 'active' ? 'selected' : '' }}>Activo</option>
                                <option value="suspended" {{ old('status', $tenant->status) === 'suspended' ? 'selected' : '' }}>Suspendido</option>
                                <option value="trial" {{ old('status', $tenant->status) === 'trial' ? 'selected' : '' }}>En Prueba</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Trial Period -->
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Periodo de Prueba</h3>

                    <div class="space-y-4">
                        @if($tenant->trial_ends_at)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <p class="text-sm text-blue-800">
                                    <span class="font-semibold">Periodo de prueba activo:</span>
                                    Termina el {{ $tenant->trial_ends_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        @else
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <p class="text-sm text-gray-600">No hay periodo de prueba activo</p>
                            </div>
                        @endif

                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="enable_trial" value="1"
                                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 h-5 w-5">
                                <span class="ml-2 text-sm font-medium text-gray-700">Extender/Activar periodo de prueba</span>
                            </label>

                            <div>
                                <input type="number" name="trial_days" value="30" min="1"
                                    class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <span class="text-sm text-gray-600 ml-2">días adicionales</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuraciones Avanzadas -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuración</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Zona Horaria
                            </label>
                            <select name="timezone"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="America/Mexico_City" {{ old('timezone', $tenant->settings['timezone'] ?? '') === 'America/Mexico_City' ? 'selected' : '' }}>América/Ciudad de México</option>
                                <option value="America/New_York" {{ old('timezone', $tenant->settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' }}>América/Nueva York</option>
                                <option value="America/Los_Angeles" {{ old('timezone', $tenant->settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' }}>América/Los Ángeles</option>
                                <option value="Europe/Madrid" {{ old('timezone', $tenant->settings['timezone'] ?? '') === 'Europe/Madrid' ? 'selected' : '' }}>Europa/Madrid</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Idioma
                            </label>
                            <select name="language"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="es" {{ old('language', $tenant->settings['language'] ?? '') === 'es' ? 'selected' : '' }}>Español</option>
                                <option value="en" {{ old('language', $tenant->settings['language'] ?? '') === 'en' ? 'selected' : '' }}>English</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('admin.tenants.index') }}"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg hover:from-purple-700 hover:to-indigo-700 font-medium shadow-lg">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

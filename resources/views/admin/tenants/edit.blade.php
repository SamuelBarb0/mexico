@extends('layouts.app')

@section('title', 'Editar Tenant')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-extrabold text-white mb-2">Editar Tenant</h1>
                <p class="text-indigo-100 text-lg">{{ $tenant->name }}</p>
            </div>
            <a href="{{ route('admin.tenants.index') }}" class="bg-white text-indigo-600 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                Volver
            </a>
        </div>
    </div>

    <form action="{{ route('admin.tenants.update', $tenant) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-200">
            <!-- Información Básica -->
            <div class="mb-8">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">Información Básica</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre de la Empresa *</label>
                        <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                            placeholder="Ej: Acme Corporation">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Slug (URL amigable) *</label>
                        <input type="text" name="slug" value="{{ old('slug', $tenant->slug) }}" required
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('slug') border-red-500 @enderror"
                            placeholder="mi-empresa">
                        <p class="mt-1 text-xs text-gray-500">Solo letras minúsculas, números y guiones</p>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Dominio</label>
                        <input type="text" name="domain" value="{{ old('domain', $tenant->domain) }}"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('domain') border-red-500 @enderror"
                            placeholder="empresa.example.com">
                        <p class="mt-1 text-xs text-gray-500">Dominio personalizado (opcional)</p>
                        @error('domain')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Estado *</label>
                        <select name="status" required
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('status') border-red-500 @enderror">
                            <option value="active" {{ old('status', $tenant->status) === 'active' ? 'selected' : '' }}>✅ Activo</option>
                            <option value="suspended" {{ old('status', $tenant->status) === 'suspended' ? 'selected' : '' }}>⛔ Suspendido</option>
                            <option value="trial" {{ old('status', $tenant->status) === 'trial' ? 'selected' : '' }}>🔍 En Prueba</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Divider -->
            <div class="relative mb-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500 font-semibold">Periodo de Prueba</span>
                </div>
            </div>

            <!-- Trial Period -->
            <div class="mb-8">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">Gestión de Prueba</h3>
                </div>

                <div class="space-y-4">
                    @if($tenant->trial_ends_at)
                        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-blue-900 mb-1">Periodo de prueba activo</p>
                                    <p class="text-sm text-blue-800">Termina el {{ $tenant->trial_ends_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 border-l-4 border-gray-300 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-gray-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                <p class="text-sm text-gray-600">No hay periodo de prueba activo</p>
                            </div>
                        </div>
                    @endif

                    <div class="bg-indigo-50 rounded-lg border border-indigo-200 p-5">
                        <label class="flex items-center cursor-pointer mb-3">
                            <input type="checkbox" name="enable_trial" value="1"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm font-semibold text-gray-900">Extender o activar periodo de prueba</span>
                        </label>

                        <div class="flex items-center">
                            <input type="number" name="trial_days" value="30" min="1"
                                class="w-24 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700 ml-3 font-medium">días adicionales</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Divider -->
            <div class="relative mb-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500 font-semibold">Configuración Regional</span>
                </div>
            </div>

            <!-- Configuraciones Avanzadas -->
            <div class="mb-8">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">Preferencias</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Zona Horaria</label>
                        <select name="timezone"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="America/Mexico_City" {{ old('timezone', $tenant->settings['timezone'] ?? '') === 'America/Mexico_City' ? 'selected' : '' }}>🇲🇽 América/Ciudad de México</option>
                            <option value="America/New_York" {{ old('timezone', $tenant->settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : '' }}>🇺🇸 América/Nueva York</option>
                            <option value="America/Los_Angeles" {{ old('timezone', $tenant->settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : '' }}>🇺🇸 América/Los Ángeles</option>
                            <option value="Europe/Madrid" {{ old('timezone', $tenant->settings['timezone'] ?? '') === 'Europe/Madrid' ? 'selected' : '' }}>🇪🇸 Europa/Madrid</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Idioma</label>
                        <select name="language"
                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="es" {{ old('language', $tenant->settings['language'] ?? '') === 'es' ? 'selected' : '' }}>🇪🇸 Español</option>
                            <option value="en" {{ old('language', $tenant->settings['language'] ?? '') === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mb-8 p-4 bg-amber-50 rounded-lg border border-amber-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-sm text-amber-800">
                        <p class="font-semibold mb-1">Nota Importante:</p>
                        <p>Los cambios en el estado del tenant afectarán inmediatamente el acceso de todos sus usuarios.</p>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-between pt-6">
                <a href="{{ route('admin.tenants.index') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition">
                    Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

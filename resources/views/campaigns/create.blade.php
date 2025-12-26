@extends('layouts.app')

@section('title', 'Crear Campaña')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-violet-700 rounded-2xl shadow-2xl p-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-extrabold text-white mb-2">Crear Nueva Campaña</h1>
                <p class="text-purple-100 text-lg">Configura tu campaña de mensajería WhatsApp</p>
            </div>
            <a href="{{ route('campaigns.index') }}" class="bg-white text-purple-600 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                Volver
            </a>
        </div>
    </div>

    <form action="{{ route('campaigns.store') }}" method="POST" x-data="{
        selectedTemplate: null,
        templates: {{ Js::from($templates) }},
        variableMapping: {},
        targetType: 'all'
    }">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info Card -->
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Información Básica
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre de la Campaña *</label>
                            <input type="text" name="name" required
                                class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 @error('name') border-red-500 @enderror"
                                value="{{ old('name') }}" placeholder="Ej: Promoción Verano 2025">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Descripción</label>
                            <textarea name="description" rows="2"
                                class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                placeholder="Descripción breve de la campaña">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">WABA Account *</label>
                            <select name="waba_account_id" required
                                class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 @error('waba_account_id') border-red-500 @enderror">
                                <option value="">Seleccionar cuenta...</option>
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
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo *</label>
                            <select name="type" required
                                class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                <option value="broadcast" {{ old('type', 'broadcast') === 'broadcast' ? 'selected' : '' }}>Broadcast (Envío masivo)</option>
                                <option value="drip" {{ old('type') === 'drip' ? 'selected' : '' }}>Drip (Secuencial)</option>
                                <option value="triggered" {{ old('type') === 'triggered' ? 'selected' : '' }}>Triggered (Por evento)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Programar Envío</label>
                            <input type="datetime-local" name="scheduled_at"
                                class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                                value="{{ old('scheduled_at') }}">
                            <p class="mt-1 text-xs text-gray-500">Dejar vacío para borrador</p>
                        </div>
                    </div>
                </div>

                <!-- Template Selection -->
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Seleccionar Plantilla *
                    </h3>

                    <div class="space-y-4">
                        <select name="message_template_id" required x-model="selectedTemplate"
                            class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 @error('message_template_id') border-red-500 @enderror">
                            <option value="">Seleccionar plantilla aprobada...</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ old('message_template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }} - {{ ucfirst($template->category) }}
                                </option>
                            @endforeach
                        </select>
                        @error('message_template_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <!-- Template Preview -->
                        <div x-show="selectedTemplate" class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <template x-for="template in templates" :key="template.id">
                                <div x-show="selectedTemplate == template.id">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">Vista Previa:</p>
                                    <div class="text-sm text-gray-600 whitespace-pre-wrap" x-text="template.components?.body?.text || 'Sin contenido'"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Target Audience -->
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Audiencia Objetivo *
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Audiencia</label>
                            <select x-model="targetType" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                <option value="all">Todos los contactos</option>
                                <option value="lists">Por listas</option>
                                <option value="tags">Por etiquetas</option>
                                <option value="custom">Filtros personalizados</option>
                            </select>
                        </div>

                        <input type="hidden" name="target_audience[type]" :value="targetType">

                        <div x-show="targetType === 'all'" class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-blue-700">Se enviarán mensajes a todos los contactos activos.</p>
                        </div>

                        <div x-show="targetType === 'lists'" class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Seleccionar listas de contactos (funcionalidad próximamente)</p>
                        </div>

                        <div x-show="targetType === 'tags'" class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Seleccionar etiquetas (funcionalidad próximamente)</p>
                        </div>

                        <div x-show="targetType === 'custom'" class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Filtros personalizados (funcionalidad próximamente)</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('campaigns.index') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                            Cancelar
                        </a>
                        <div class="flex space-x-3">
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-violet-600 hover:from-purple-700 hover:to-violet-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition">
                                Crear Campaña
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Help Card -->
                <div class="bg-blue-50 rounded-2xl shadow-lg p-6 border border-blue-200">
                    <h4 class="text-lg font-bold text-blue-900 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        Ayuda
                    </h4>
                    <div class="space-y-3 text-sm text-blue-800">
                        <p><strong>1.</strong> Selecciona una plantilla aprobada por Meta</p>
                        <p><strong>2.</strong> Define tu audiencia objetivo</p>
                        <p><strong>3.</strong> Opcionalmente programa el envío</p>
                        <p><strong>4.</strong> Crea la campaña como borrador</p>
                        <p><strong>5.</strong> Prepara los mensajes y ejecuta</p>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="bg-purple-50 rounded-2xl shadow-lg p-6 border border-purple-200">
                    <h4 class="text-lg font-bold text-purple-900 mb-3">Plantillas Disponibles</h4>
                    <div class="text-3xl font-extrabold text-purple-600">{{ count($templates) }}</div>
                    <p class="text-sm text-purple-700 mt-1">plantillas aprobadas</p>
                    @if(count($templates) == 0)
                        <a href="{{ route('templates.create') }}" class="mt-3 block text-sm text-purple-600 hover:text-purple-800 font-semibold">
                            Crear primera plantilla →
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

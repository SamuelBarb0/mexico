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
        templates: {{ Js::from($templates->map(fn($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'category' => $t->category,
            'components' => $t->components,
            'header_type' => $t->getHeaderType(),
            'has_media_header' => $t->hasMediaHeader(),
            'is_carousel' => $t->isCarousel(),
            'has_catalog' => $t->hasCatalog(),
        ])) }},
        variableMapping: {},
        targetType: 'client',
        selectedClientId: '{{ $defaultClient?->id ?? '' }}',
        getSelectedTemplate() {
            return this.templates.find(t => t.id == this.selectedTemplate);
        },
        // Helper functions to handle both Meta array format and object format
        isArrayFormat(components) {
            if (!components) return false;
            return Array.isArray(components) && components.length > 0 && components[0]?.type;
        },
        getComponent(components, type) {
            if (!components) return null;
            if (this.isArrayFormat(components)) {
                return components.find(c => c.type === type);
            }
            return components[type.toLowerCase()] || null;
        },
        getHeaderText(template) {
            if (!template?.components) return null;
            const header = this.getComponent(template.components, 'HEADER');
            if (header?.format === 'TEXT' && header?.text) return header.text;
            return null;
        },
        getBodyText(template) {
            if (!template?.components) return 'Sin contenido';
            const body = this.getComponent(template.components, 'BODY');
            return body?.text || 'Sin contenido de texto';
        },
        getFooterText(template) {
            if (!template?.components) return null;
            const footer = this.getComponent(template.components, 'FOOTER');
            return footer?.text || null;
        },
        getButtons(template) {
            if (!template?.components) return [];
            const buttons = this.getComponent(template.components, 'BUTTONS');
            return buttons?.buttons || [];
        },
        getHeaderFormat(template) {
            if (!template?.components) return null;
            const header = this.getComponent(template.components, 'HEADER');
            return header?.format || null;
        }
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
                        <div x-show="selectedTemplate" class="mt-4">
                            <p class="text-sm font-semibold text-gray-700 mb-3">Vista Previa:</p>

                            <template x-for="template in templates" :key="template.id">
                                <div x-show="selectedTemplate == template.id" class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 max-w-sm">
                                    <!-- Header -->
                                    <template x-if="getHeaderFormat(template)">
                                        <div class="bg-purple-50 border-b border-gray-200">
                                            <!-- Text Header -->
                                            <template x-if="getHeaderFormat(template) === 'TEXT'">
                                                <div class="p-3">
                                                    <p class="font-bold text-gray-900 text-sm" x-text="getHeaderText(template)"></p>
                                                </div>
                                            </template>
                                            <!-- Image Header -->
                                            <template x-if="getHeaderFormat(template) === 'IMAGE'">
                                                <div class="h-32 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
                                                    <div class="text-center">
                                                        <svg class="w-10 h-10 text-blue-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        <p class="text-xs text-blue-600 font-semibold">IMAGEN</p>
                                                    </div>
                                                </div>
                                            </template>
                                            <!-- Video Header -->
                                            <template x-if="getHeaderFormat(template) === 'VIDEO'">
                                                <div class="h-32 bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center">
                                                    <div class="text-center">
                                                        <svg class="w-10 h-10 text-red-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                        <p class="text-xs text-red-600 font-semibold">VIDEO</p>
                                                    </div>
                                                </div>
                                            </template>
                                            <!-- Document Header -->
                                            <template x-if="getHeaderFormat(template) === 'DOCUMENT'">
                                                <div class="h-32 bg-gradient-to-br from-amber-100 to-amber-200 flex items-center justify-center">
                                                    <div class="text-center">
                                                        <svg class="w-10 h-10 text-amber-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        <p class="text-xs text-amber-600 font-semibold">DOCUMENTO</p>
                                                    </div>
                                                </div>
                                            </template>
                                            <!-- Location Header -->
                                            <template x-if="getHeaderFormat(template) === 'LOCATION'">
                                                <div class="h-32 bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center">
                                                    <div class="text-center">
                                                        <svg class="w-10 h-10 text-green-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                        <p class="text-xs text-green-600 font-semibold">UBICACIÓN</p>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Carousel Indicator -->
                                    <template x-if="template.is_carousel">
                                        <div class="p-3 bg-indigo-50 border-b border-indigo-200">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                                </svg>
                                                <span class="text-sm font-semibold text-indigo-700">Plantilla tipo Carrusel</span>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Catalog Indicator -->
                                    <template x-if="template.has_catalog">
                                        <div class="p-3 bg-emerald-50 border-b border-emerald-200">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                                </svg>
                                                <span class="text-sm font-semibold text-emerald-700">Incluye Catálogo de Productos</span>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Body -->
                                    <div class="p-3">
                                        <p class="text-sm text-gray-700 whitespace-pre-wrap" x-text="getBodyText(template)"></p>
                                    </div>

                                    <!-- Footer -->
                                    <template x-if="getFooterText(template)">
                                        <div class="px-3 pb-3">
                                            <p class="text-xs text-gray-500" x-text="getFooterText(template)"></p>
                                        </div>
                                    </template>

                                    <!-- Buttons -->
                                    <template x-if="getButtons(template).length > 0">
                                        <div class="p-3 border-t border-gray-200 space-y-2">
                                            <template x-for="(button, index) in getButtons(template)" :key="index">
                                                <div class="w-full px-3 py-2 bg-gray-100 text-purple-600 font-semibold rounded-lg text-sm text-center flex items-center justify-center gap-2">
                                                    <template x-if="button.type === 'URL'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="button.type === 'PHONE_NUMBER'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="button.type === 'QUICK_REPLY'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="button.type === 'COPY_CODE'">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                                        </svg>
                                                    </template>
                                                    <span x-text="button.text"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Media Header Warning -->
                            <div x-show="getSelectedTemplate()?.has_media_header" class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm text-blue-700">
                                        Esta plantilla requiere
                                        <span x-text="getSelectedTemplate()?.header_type === 'IMAGE' ? 'una imagen' : (getSelectedTemplate()?.header_type === 'VIDEO' ? 'un video' : (getSelectedTemplate()?.header_type === 'DOCUMENT' ? 'un documento' : 'una ubicación'))"></span>
                                        en el header. Deberás proporcionar la URL abajo.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Header Media URL (shown when template has media header) -->
                        <div x-show="getSelectedTemplate()?.has_media_header" class="mt-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                URL del <span x-text="getSelectedTemplate()?.header_type === 'IMAGE' ? 'Imagen' : (getSelectedTemplate()?.header_type === 'VIDEO' ? 'Video' : 'Documento')"></span> *
                            </label>
                            <input type="url" name="header_media_url"
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="https://ejemplo.com/imagen.jpg"
                                x-bind:required="getSelectedTemplate()?.has_media_header">
                            <p class="mt-2 text-xs text-blue-700">
                                <strong>Importante:</strong> La URL debe ser pública y accesible. Formatos aceptados:
                                <span x-show="getSelectedTemplate()?.header_type === 'IMAGE'">JPG, PNG (máx 5MB)</span>
                                <span x-show="getSelectedTemplate()?.header_type === 'VIDEO'">MP4 (máx 16MB)</span>
                                <span x-show="getSelectedTemplate()?.header_type === 'DOCUMENT'">PDF (máx 100MB)</span>
                            </p>
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
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Filtrar Por</label>
                            <select name="target_audience[type]" x-model="targetType" required class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                <option value="all">Todos los contactos activos</option>
                                <option value="client">Por Cliente</option>
                                <option value="tags">Por Etiquetas</option>
                                <option value="status">Por Estado</option>
                            </select>
                        </div>

                        <!-- All Contacts -->
                        <div x-show="targetType === 'all'" class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-green-800">Todos los contactos activos</p>
                                    <p class="text-xs text-green-700 mt-1">Se enviarán mensajes a todos los contactos con estado "activo" en tu base de datos.</p>
                                </div>
                            </div>
                        </div>

                        <!-- By Client -->
                        <div x-show="targetType === 'client'" class="space-y-3">
                            <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm text-blue-800 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Cliente seleccionado para la campaña
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Cliente</label>
                                <select name="target_audience[client_id]" x-model="selectedClientId" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                    <option value="">Seleccionar cliente...</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }} @if($client->company)({{ $client->company }})@endif</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Se enviarán mensajes a todos los contactos asociados a este cliente</p>
                            </div>
                        </div>

                        <!-- By Tags -->
                        <div x-show="targetType === 'tags'" class="space-y-3">
                            <div class="p-3 bg-purple-50 rounded-lg border border-purple-200">
                                <p class="text-sm text-purple-800 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    Filtra por etiquetas de contactos
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Etiquetas</label>
                                <div class="space-y-2 max-h-48 overflow-y-auto p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    @if($allTags->isEmpty())
                                        <p class="text-sm text-gray-500 italic">No hay etiquetas disponibles. Agrega etiquetas a tus contactos primero.</p>
                                    @else
                                        @foreach($allTags as $tag)
                                            <label class="flex items-center hover:bg-white p-2 rounded transition-colors cursor-pointer">
                                                <input type="checkbox" name="target_audience[tags][]" value="{{ $tag }}" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                                <span class="ml-2 text-sm text-gray-700">{{ $tag }}</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Se enviarán mensajes a contactos que tengan al menos una de las etiquetas seleccionadas</p>
                            </div>
                        </div>

                        <!-- By Status -->
                        <div x-show="targetType === 'status'" class="space-y-3">
                            <div class="p-3 bg-amber-50 rounded-lg border border-amber-200">
                                <p class="text-sm text-amber-800 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Filtra por estado de contactos
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                                <select name="target_audience[status]" class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                                    <option value="active">Activo</option>
                                    <option value="inactive">Inactivo</option>
                                    <option value="blocked">Bloqueado</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Se enviarán mensajes solo a contactos con el estado seleccionado</p>
                            </div>
                        </div>

                        <!-- Contact Count Preview -->
                        <div class="mt-4 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-indigo-900">Audiencia estimada</span>
                                </div>
                                <span class="text-sm text-indigo-700">Se calculará después de crear la campaña</span>
                            </div>
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
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-violet-600 hover:from-purple-700 hover:to-violet-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition cursor-pointer">
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

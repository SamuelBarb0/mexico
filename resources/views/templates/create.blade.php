@extends('layouts.app')

@section('title', 'Crear Plantilla')

@section('content')
<div x-data="templateCreator()" class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-display font-bold text-neutral-900">Nueva Plantilla de Mensaje</h1>
            <p class="text-neutral-600 mt-1">Crea una plantilla para enviar a Meta</p>
        </div>
        <a href="{{ route('templates.index') }}" class="text-neutral-600 hover:text-neutral-900 font-semibold">
            ← Volver
        </a>
    </div>

    <form action="{{ route('templates.store') }}" method="POST" @submit="prepareSubmit" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column - Form -->
            <div class="space-y-6">
                <!-- Basic Info -->
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-6 border border-primary-100">
                    <h2 class="text-lg font-display font-bold text-neutral-900 mb-4">Información Básica</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">Cuenta WABA *</label>
                            <select name="waba_account_id" required class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <option value="">Selecciona una cuenta</option>
                                @foreach($wabaAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->phone_number }})</option>
                                @endforeach
                            </select>
                            @error('waba_account_id')
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">Nombre de Plantilla *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required pattern="[a-z0-9_]+" placeholder="ej: bienvenida_cliente" class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <p class="mt-1 text-xs text-neutral-500">Solo minúsculas, números y guiones bajos</p>
                            @error('name')
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-neutral-700 mb-2">Categoría *</label>
                                <select name="category" required class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="MARKETING">Marketing</option>
                                    <option value="UTILITY">Utilidad</option>
                                    <option value="AUTHENTICATION">Autenticación</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-neutral-700 mb-2">Idioma *</label>
                                <select name="language" required class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                    <option value="es">Español</option>
                                    <option value="en">English</option>
                                    <option value="pt">Português</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">Descripción</label>
                            <textarea name="description" rows="2" placeholder="Descripción interna..." class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Header Component -->
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-6 border border-primary-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-display font-bold text-neutral-900">Header (Opcional)</h2>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="hasHeader" class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-neutral-600">Incluir header</span>
                        </label>
                    </div>

                    <div x-show="hasHeader" x-transition>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-neutral-700 mb-2">Tipo de Header</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <button type="button" @click="headerFormat = 'TEXT'" :class="headerFormat === 'TEXT' ? 'bg-primary-100 border-primary-500 text-primary-700' : 'bg-white border-neutral-300 text-neutral-600'" class="px-3 py-2 rounded-lg border text-sm font-medium transition-colors flex items-center justify-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                                        Texto
                                    </button>
                                    <button type="button" @click="headerFormat = 'IMAGE'" :class="headerFormat === 'IMAGE' ? 'bg-primary-100 border-primary-500 text-primary-700' : 'bg-white border-neutral-300 text-neutral-600'" class="px-3 py-2 rounded-lg border text-sm font-medium transition-colors flex items-center justify-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Imagen
                                    </button>
                                    <button type="button" @click="headerFormat = 'VIDEO'" :class="headerFormat === 'VIDEO' ? 'bg-primary-100 border-primary-500 text-primary-700' : 'bg-white border-neutral-300 text-neutral-600'" class="px-3 py-2 rounded-lg border text-sm font-medium transition-colors flex items-center justify-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        Video
                                    </button>
                                    <button type="button" @click="headerFormat = 'DOCUMENT'" :class="headerFormat === 'DOCUMENT' ? 'bg-primary-100 border-primary-500 text-primary-700' : 'bg-white border-neutral-300 text-neutral-600'" class="px-3 py-2 rounded-lg border text-sm font-medium transition-colors flex items-center justify-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Doc
                                    </button>
                                </div>
                            </div>

                            <!-- Text Header Input -->
                            <div x-show="headerFormat === 'TEXT'">
                                <label class="block text-sm font-semibold text-neutral-700 mb-2">Texto del Header</label>
                                <input type="text" x-model="headerText" placeholder="Título del mensaje..." class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                                <p class="mt-1 text-xs text-neutral-500">Usa @{{1}} para variables. Máximo 60 caracteres.</p>
                            </div>

                            <!-- Media Header Note -->
                            <div x-show="headerFormat !== 'TEXT'" class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <p class="text-sm text-blue-700">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    El archivo de <span x-text="headerFormat === 'IMAGE' ? 'imagen' : (headerFormat === 'VIDEO' ? 'video' : 'documento')"></span> se proporcionará al momento de enviar la campaña.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Body Component -->
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-6 border border-primary-100">
                    <h2 class="text-lg font-display font-bold text-neutral-900 mb-4">Cuerpo del Mensaje *</h2>

                    <div>
                        <textarea x-model="bodyText" rows="4" required placeholder="Hola {{1}}, gracias por tu interés en {{2}}..." class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono text-sm"></textarea>
                        <div class="flex justify-between mt-1">
                            <p class="text-xs text-neutral-500">Usa @{{1}}, @{{2}}, etc. para variables</p>
                            <p class="text-xs" :class="bodyText.length > 1024 ? 'text-danger-600' : 'text-neutral-500'">
                                <span x-text="bodyText.length"></span>/1024
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer Component -->
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-6 border border-primary-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-display font-bold text-neutral-900">Footer (Opcional)</h2>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="hasFooter" class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-neutral-600">Incluir footer</span>
                        </label>
                    </div>

                    <div x-show="hasFooter" x-transition>
                        <input type="text" x-model="footerText" placeholder="Texto del pie de página..." class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <p class="mt-1 text-xs text-neutral-500">Máximo 60 caracteres. No soporta variables.</p>
                    </div>
                </div>

                <!-- Buttons Component -->
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-6 border border-primary-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-display font-bold text-neutral-900">Botones (Opcional)</h2>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="hasButtons" class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-sm text-neutral-600">Incluir botones</span>
                        </label>
                    </div>

                    <div x-show="hasButtons" x-transition>
                        <div class="space-y-3">
                            <template x-for="(button, index) in buttons" :key="index">
                                <div class="p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-sm font-semibold text-neutral-700" x-text="'Botón ' + (index + 1)"></span>
                                        <button type="button" @click="removeButton(index)" class="ml-auto text-danger-600 hover:text-danger-700">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 mb-2">
                                        <select x-model="button.type" class="px-3 py-2 rounded-lg border border-neutral-300 text-sm">
                                            <option value="QUICK_REPLY">Respuesta Rápida</option>
                                            <option value="URL">URL</option>
                                            <option value="PHONE_NUMBER">Teléfono</option>
                                        </select>
                                        <input type="text" x-model="button.text" placeholder="Texto del botón" class="px-3 py-2 rounded-lg border border-neutral-300 text-sm">
                                    </div>
                                    <div x-show="button.type === 'URL'">
                                        <input type="text" x-model="button.url" placeholder="https://ejemplo.com" class="w-full px-3 py-2 rounded-lg border border-neutral-300 text-sm">
                                    </div>
                                    <div x-show="button.type === 'PHONE_NUMBER'">
                                        <input type="text" x-model="button.phone_number" placeholder="+521234567890" class="w-full px-3 py-2 rounded-lg border border-neutral-300 text-sm">
                                    </div>
                                </div>
                            </template>

                            <button type="button" @click="addButton" :disabled="buttons.length >= 3" class="w-full px-4 py-2 border-2 border-dashed border-neutral-300 rounded-lg text-neutral-600 hover:border-primary-400 hover:text-primary-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                + Agregar Botón (máx. 3)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hidden field for components JSON -->
                <input type="hidden" name="components" id="components-field" :value="JSON.stringify(buildComponents())">

                <!-- Actions -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('templates.index') }}" class="px-6 py-3 bg-neutral-200 text-neutral-700 rounded-lg font-semibold hover:bg-neutral-300 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-white rounded-lg font-semibold hover:from-primary-700 hover:to-secondary-700 transition-all shadow-md hover:shadow-lg cursor-pointer">
                        Crear Plantilla
                    </button>
                </div>
            </div>

            <!-- Right Column - Preview -->
            <div class="lg:sticky lg:top-6 space-y-6">
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-6 border border-primary-100">
                    <h2 class="text-lg font-display font-bold text-neutral-900 mb-4">Vista Previa</h2>

                    {{-- WhatsApp-style chat background --}}
                    <div class="rounded-xl shadow-lg overflow-hidden border border-neutral-200" style="background-color: #efeae2; background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyBAMAAADsEZWCAAAAElBMVEX////p5uHp5uHo5eDo5eDo5eC5gEN8AAAABXRSTlMAESIzRBQ9aFkAAAA1SURBVDjLY2AYBYMKsLi4gAATAwMTM5QJZDExMzExQZhMYCYTkAnhgJhABowJYsKYI9b2AQAGBgFRK+QAZQAAAABJRU5ErkJggg==');">
                        <div class="p-3">
                            {{-- Message bubble --}}
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden" style="max-width: 85%;">

                                {{-- Header --}}
                                <template x-if="hasHeader">
                                    <div>
                                        {{-- Text Header --}}
                                        <template x-if="headerFormat === 'TEXT'">
                                            <div class="px-3 pt-2">
                                                <p class="font-bold text-neutral-900 text-[15px]" x-text="headerText || 'Título del mensaje'"></p>
                                            </div>
                                        </template>

                                        {{-- Image Header --}}
                                        <template x-if="headerFormat === 'IMAGE'">
                                            <div class="relative h-40 bg-gradient-to-br from-neutral-200 to-neutral-300 flex items-center justify-center">
                                                <svg class="w-16 h-16 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        </template>

                                        {{-- Video Header --}}
                                        <template x-if="headerFormat === 'VIDEO'">
                                            <div class="relative h-40 bg-gradient-to-br from-neutral-800 to-neutral-900 flex items-center justify-center">
                                                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M8 5v14l11-7z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Document Header --}}
                                        <template x-if="headerFormat === 'DOCUMENT'">
                                            <div class="p-3 bg-neutral-100 flex items-center gap-3">
                                                <div class="w-10 h-12 bg-red-500 rounded flex items-center justify-center">
                                                    <span class="text-white text-xs font-bold">PDF</span>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-neutral-700">documento.pdf</p>
                                                    <p class="text-xs text-neutral-500">PDF • Documento</p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Body --}}
                                <div class="px-3 py-2">
                                    <p class="text-neutral-800 whitespace-pre-wrap text-[14px] leading-relaxed" x-text="bodyText || 'Escribe el cuerpo del mensaje...'"></p>
                                </div>

                                {{-- Footer --}}
                                <template x-if="hasFooter && footerText">
                                    <div class="px-3 pb-2">
                                        <p class="text-xs text-neutral-500" x-text="footerText"></p>
                                    </div>
                                </template>

                                {{-- Buttons --}}
                                <template x-if="hasButtons && buttons.length > 0">
                                    <div class="border-t border-neutral-200">
                                        <template x-for="(button, index) in buttons" :key="index">
                                            <div class="px-4 py-2.5 border-b border-neutral-100 last:border-b-0 text-center">
                                                <span class="text-[#00a884] font-medium text-sm flex items-center justify-center gap-2">
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
                                                    <span x-text="button.text || 'Botón'"></span>
                                                </span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Components info --}}
                    <div class="mt-4 p-3 bg-neutral-50 rounded-lg">
                        <p class="text-xs text-neutral-600 font-semibold mb-2">Componentes incluidos:</p>
                        <div class="flex flex-wrap gap-1">
                            <span x-show="hasHeader" class="px-2 py-0.5 bg-primary-100 text-primary-700 rounded text-xs font-medium">HEADER</span>
                            <span class="px-2 py-0.5 bg-primary-100 text-primary-700 rounded text-xs font-medium">BODY</span>
                            <span x-show="hasFooter && footerText" class="px-2 py-0.5 bg-primary-100 text-primary-700 rounded text-xs font-medium">FOOTER</span>
                            <span x-show="hasButtons && buttons.length > 0" class="px-2 py-0.5 bg-primary-100 text-primary-700 rounded text-xs font-medium">BUTTONS</span>
                        </div>
                    </div>
                </div>

                {{-- Help Card --}}
                <div class="bg-blue-50 rounded-2xl p-6 border border-blue-200">
                    <h3 class="text-sm font-bold text-blue-900 mb-2">Tips para plantillas</h3>
                    <ul class="text-xs text-blue-700 space-y-1">
                        <li>• Las plantillas de Marketing requieren opt-in del usuario</li>
                        <li>• Usa variables como @{{1}}, @{{2}} para personalización</li>
                        <li>• Los botones URL pueden tener variables: ejemplo.com/@{{1}}</li>
                        <li>• Meta revisa y aprueba las plantillas (24-48 horas)</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function templateCreator() {
    return {
        // Header
        hasHeader: false,
        headerFormat: 'TEXT',
        headerText: '',

        // Body
        bodyText: '',

        // Footer
        hasFooter: false,
        footerText: '',

        // Buttons
        hasButtons: false,
        buttons: [],

        addButton() {
            if (this.buttons.length < 3) {
                this.buttons.push({
                    type: 'QUICK_REPLY',
                    text: '',
                    url: '',
                    phone_number: ''
                });
            }
        },

        removeButton(index) {
            this.buttons.splice(index, 1);
        },

        buildComponents() {
            const components = [];

            // Header
            if (this.hasHeader) {
                const header = {
                    type: 'HEADER',
                    format: this.headerFormat
                };

                if (this.headerFormat === 'TEXT' && this.headerText) {
                    header.text = this.headerText;

                    // Add example if there are variables
                    const matches = this.headerText.match(/\{\{(\d+)\}\}/g);
                    if (matches) {
                        header.example = {
                            header_text: matches.map(() => 'ejemplo')
                        };
                    }
                }

                components.push(header);
            }

            // Body (required)
            if (this.bodyText) {
                const body = {
                    type: 'BODY',
                    text: this.bodyText
                };

                // Add example if there are variables
                const matches = this.bodyText.match(/\{\{(\d+)\}\}/g);
                if (matches) {
                    body.example = {
                        body_text: [matches.map(() => 'ejemplo')]
                    };
                }

                components.push(body);
            }

            // Footer
            if (this.hasFooter && this.footerText) {
                components.push({
                    type: 'FOOTER',
                    text: this.footerText
                });
            }

            // Buttons
            if (this.hasButtons && this.buttons.length > 0) {
                const buttonsData = this.buttons
                    .filter(b => b.text)
                    .map(b => {
                        const btn = {
                            type: b.type,
                            text: b.text
                        };

                        if (b.type === 'URL' && b.url) {
                            btn.url = b.url;
                        }

                        if (b.type === 'PHONE_NUMBER' && b.phone_number) {
                            btn.phone_number = b.phone_number;
                        }

                        return btn;
                    });

                if (buttonsData.length > 0) {
                    components.push({
                        type: 'BUTTONS',
                        buttons: buttonsData
                    });
                }
            }

            return components;
        },

        prepareSubmit(e) {
            // Components are already being built reactively via :value binding
            console.log('Components to send:', JSON.stringify(this.buildComponents()));
        }
    };
}
</script>
@endsection

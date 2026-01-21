@extends('layouts.app')

@section('title', 'Crear Plantilla')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
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

    <form action="{{ route('templates.store') }}" method="POST" id="template-form" class="space-y-6">
        @csrf

        <!-- Basic Info -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-8 border border-primary-100">
            <h2 class="text-xl font-display font-bold text-neutral-900 mb-6">Información Básica</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">Categoría *</label>
                    <select name="category" required class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="MARKETING">Marketing</option>
                        <option value="UTILITY">Utilidad</option>
                        <option value="AUTHENTICATION">Autenticación</option>
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">Idioma *</label>
                    <select name="language" required class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="es">Español</option>
                        <option value="en">English</option>
                        <option value="pt">Português</option>
                    </select>
                    @error('language')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">Descripción</label>
                    <textarea name="description" rows="2" placeholder="Descripción interna de la plantilla..." class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Template Components -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-8 border border-primary-100">
            <h2 class="text-xl font-display font-bold text-neutral-900 mb-6">Componentes del Mensaje</h2>

            <!-- Body Component (Required) -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-neutral-700 mb-2">
                    Cuerpo del Mensaje *
                    <span class="text-xs text-neutral-500 font-normal">(Usa {{1}}, {{2}}, etc. para variables)</span>
                </label>
                <textarea id="body-text" rows="4" required placeholder="Hola {{1}}, gracias por tu interés en {{2}}..." class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono text-sm"></textarea>
                <p class="mt-1 text-xs text-neutral-500">Máximo 1024 caracteres</p>
            </div>

            <!-- Note: Header, Footer y Buttons opcionales pueden agregarse después -->

            <!-- Hidden field for components JSON -->
            <input type="hidden" name="components" id="components-field" value="">
        </div>

        <!-- Preview -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-8 border border-primary-100">
            <h2 class="text-xl font-display font-bold text-neutral-900 mb-6">Vista Previa</h2>

            <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg overflow-hidden border border-neutral-200">
                <!-- Body -->
                <div class="p-4">
                    <p class="text-neutral-900 whitespace-pre-wrap" id="preview-text">Escribe el cuerpo del mensaje...</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-4">
            <a href="{{ route('templates.index') }}" class="px-6 py-3 bg-neutral-200 text-neutral-700 rounded-lg font-semibold hover:bg-neutral-300 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-primary-600 to-secondary-600 text-white rounded-lg font-semibold hover:from-primary-700 hover:to-secondary-700 transition-all shadow-md hover:shadow-lg cursor-pointer">
                Crear Plantilla
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('template-form');
    const componentsField = document.getElementById('components-field');
    const bodyText = document.getElementById('body-text');
    const previewText = document.getElementById('preview-text');

    // Update preview in real-time
    bodyText.addEventListener('input', function() {
        previewText.textContent = this.value || 'Escribe el cuerpo del mensaje...';
    });

    // Update components field before submit
    form.addEventListener('submit', function(e) {
        const components = [];

        // Body (always required)
        const bodyValue = bodyText.value.trim();
        if (bodyValue) {
            components.push({
                type: 'BODY',
                text: bodyValue
            });
        }

        // Set the components field value
        componentsField.value = JSON.stringify(components);

        console.log('Components to send:', componentsField.value);

        // Let the form submit normally
    });
});
</script>
@endsection

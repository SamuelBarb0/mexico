@extends('layouts.app')

@section('title', 'Plantilla: ' . $template->name)

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <h1 class="text-3xl font-display font-bold text-neutral-900">{{ $template->name }}</h1>
                <span class="px-3 py-1 rounded-lg text-sm font-semibold {{ $template->getStatusBadgeClass() }}">
                    {{ ucfirst(strtolower($template->status)) }}
                </span>
                <span class="px-3 py-1 rounded-lg text-sm font-semibold {{ $template->getCategoryBadgeClass() }}">
                    {{ $template->category }}
                </span>
            </div>
            <p class="text-neutral-600">{{ $template->description ?? 'Sin descripción' }}</p>
        </div>
        <a href="{{ route('templates.index') }}" class="text-neutral-600 hover:text-neutral-900 font-semibold">
            ← Volver
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Template Preview -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-8 border border-primary-100">
                <h2 class="text-xl font-display font-bold text-neutral-900 mb-6">Vista Previa</h2>

                <div class="max-w-md mx-auto bg-white rounded-xl shadow-lg overflow-hidden border border-neutral-200">
                    @foreach($template->components as $component)
                        @if($component['type'] === 'HEADER')
                            <div class="p-4 bg-primary-50 border-b border-neutral-200">
                                @if($component['format'] === 'TEXT')
                                    <p class="font-bold text-neutral-900">{{ $component['text'] ?? '' }}</p>
                                @else
                                    <div class="flex items-center justify-center h-32 bg-neutral-100 rounded-lg">
                                        <div class="text-center">
                                            <svg class="w-12 h-12 text-neutral-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <p class="text-xs text-neutral-500">{{ strtoupper($component['format']) }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @elseif($component['type'] === 'BODY')
                            <div class="p-4">
                                <p class="text-neutral-900 whitespace-pre-wrap">{{ $component['text'] }}</p>
                            </div>
                        @elseif($component['type'] === 'FOOTER')
                            <div class="px-4 pb-4">
                                <p class="text-xs text-neutral-500">{{ $component['text'] }}</p>
                            </div>
                        @elseif($component['type'] === 'BUTTONS')
                            <div class="p-4 border-t border-neutral-200 space-y-2">
                                @foreach($component['buttons'] as $button)
                                    <button type="button" class="w-full px-4 py-2 bg-neutral-100 text-primary-600 font-semibold rounded-lg">
                                        {{ $button['text'] }}
                                        @if($button['type'] === 'URL')
                                            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Variables -->
            @if(count($template->variables ?? []) > 0)
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-8 border border-primary-100">
                    <h2 class="text-xl font-display font-bold text-neutral-900 mb-4">Variables</h2>
                    <div class="space-y-2">
                        @foreach($template->variables as $variable)
                            <div class="flex items-center gap-3 p-3 bg-neutral-50 rounded-lg">
                                <span class="px-3 py-1 bg-primary-600 text-white rounded-md font-mono text-sm font-bold">
                                    @{{ '{{' . $variable['index'] . '}}' }}
                                </span>
                                <span class="text-sm text-neutral-600">
                                    En: {{ $variable['component'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Rejection Reason -->
            @if($template->isRejected() && $template->rejection_reason)
                <div class="bg-danger-50 border-2 border-danger-200 rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-danger-900 mb-2 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Razón de Rechazo
                    </h3>
                    <p class="text-danger-800">{{ $template->rejection_reason }}</p>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Actions -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-6 border border-primary-100">
                <h3 class="text-lg font-bold text-neutral-900 mb-4">Acciones</h3>

                <div class="space-y-3">
                    @if($template->isDraft())
                        <form action="{{ route('templates.submit', $template) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-700 hover:to-secondary-700 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg">
                                Enviar a Meta
                            </button>
                        </form>

                        <a href="{{ route('templates.edit', $template) }}" class="block w-full bg-neutral-600 hover:bg-neutral-700 text-white px-4 py-2 rounded-lg font-semibold text-center transition-colors">
                            Editar
                        </a>
                    @endif

                    @if($template->meta_template_id)
                        <form action="{{ route('templates.sync', $template) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-accent-600 hover:bg-accent-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                                Sincronizar con Meta
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('templates.destroy', $template) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta plantilla?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-danger-600 hover:bg-danger-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>

            <!-- Info -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-6 border border-primary-100">
                <h3 class="text-lg font-bold text-neutral-900 mb-4">Información</h3>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-neutral-500 font-medium">Idioma</dt>
                        <dd class="text-neutral-900 font-semibold">{{ strtoupper($template->language) }}</dd>
                    </div>

                    @if($template->wabaAccount)
                        <div>
                            <dt class="text-neutral-500 font-medium">Cuenta WABA</dt>
                            <dd class="text-neutral-900 font-semibold">{{ $template->wabaAccount->name }} ({{ $template->wabaAccount->phone_number }})</dd>
                        </div>
                    @endif

                    @if($template->meta_template_id)
                        <div>
                            <dt class="text-neutral-500 font-medium">ID de Meta</dt>
                            <dd class="text-neutral-900 font-mono text-xs">{{ $template->meta_template_id }}</dd>
                        </div>
                    @endif

                    @if($template->quality_score !== 'UNKNOWN')
                        <div>
                            <dt class="text-neutral-500 font-medium">Calidad</dt>
                            <dd>
                                <span class="px-2 py-1 rounded-md text-xs font-semibold {{ $template->getQualityBadgeClass() }}">
                                    {{ $template->quality_score }}
                                </span>
                            </dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-neutral-500 font-medium">Usado</dt>
                        <dd class="text-neutral-900 font-semibold">{{ $template->usage_count }} veces</dd>
                    </div>

                    <div>
                        <dt class="text-neutral-500 font-medium">Creado</dt>
                        <dd class="text-neutral-900">{{ $template->created_at->format('d/m/Y H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="text-neutral-500 font-medium">Actualizado</dt>
                        <dd class="text-neutral-900">{{ $template->updated_at->diffForHumans() }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Tags -->
            @if($template->tags && count($template->tags) > 0)
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-6 border border-primary-100">
                    <h3 class="text-lg font-bold text-neutral-900 mb-4">Etiquetas</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($template->tags as $tag)
                            <span class="px-3 py-1 bg-primary-100 text-primary-800 rounded-full text-xs font-semibold">
                                {{ $tag }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

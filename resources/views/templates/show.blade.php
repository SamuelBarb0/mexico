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
                    @php
                        $components = $template->components ?? [];
                        // Handle both array format (from Meta) and object format (from local creation)
                        $isArrayFormat = isset($components[0]) || (is_array($components) && !empty($components) && isset(array_values($components)[0]['type']));
                    @endphp

                    @if($isArrayFormat)
                        {{-- Meta array format: [['type' => 'HEADER', ...], ['type' => 'BODY', ...]] --}}
                        @foreach($components as $component)
                            @php $componentType = strtoupper($component['type'] ?? ''); @endphp

                            @if($componentType === 'HEADER')
                                <div class="p-4 bg-primary-50 border-b border-neutral-200">
                                    @php $format = strtoupper($component['format'] ?? 'TEXT'); @endphp
                                    @if($format === 'TEXT')
                                        <p class="font-bold text-neutral-900">{{ $component['text'] ?? '' }}</p>
                                    @elseif($format === 'IMAGE')
                                        <div class="flex items-center justify-center h-32 bg-neutral-100 rounded-lg">
                                            <div class="text-center">
                                                <svg class="w-12 h-12 text-neutral-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <p class="text-xs text-neutral-500">IMAGEN</p>
                                            </div>
                                        </div>
                                    @elseif($format === 'VIDEO')
                                        <div class="flex items-center justify-center h-32 bg-neutral-100 rounded-lg">
                                            <div class="text-center">
                                                <svg class="w-12 h-12 text-neutral-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                <p class="text-xs text-neutral-500">VIDEO</p>
                                            </div>
                                        </div>
                                    @elseif($format === 'DOCUMENT')
                                        <div class="flex items-center justify-center h-32 bg-neutral-100 rounded-lg">
                                            <div class="text-center">
                                                <svg class="w-12 h-12 text-neutral-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <p class="text-xs text-neutral-500">DOCUMENTO</p>
                                            </div>
                                        </div>
                                    @elseif($format === 'LOCATION')
                                        <div class="flex items-center justify-center h-32 bg-neutral-100 rounded-lg">
                                            <div class="text-center">
                                                <svg class="w-12 h-12 text-neutral-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                <p class="text-xs text-neutral-500">UBICACIÓN</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center h-32 bg-neutral-100 rounded-lg">
                                            <div class="text-center">
                                                <svg class="w-12 h-12 text-neutral-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <p class="text-xs text-neutral-500">{{ $format }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            @elseif($componentType === 'BODY')
                                <div class="p-4">
                                    <p class="text-neutral-900 whitespace-pre-wrap">{{ $component['text'] ?? '' }}</p>
                                </div>

                            @elseif($componentType === 'FOOTER')
                                <div class="px-4 pb-4">
                                    <p class="text-xs text-neutral-500">{{ $component['text'] ?? '' }}</p>
                                </div>

                            @elseif($componentType === 'BUTTONS')
                                <div class="p-4 border-t border-neutral-200 space-y-2">
                                    @foreach($component['buttons'] ?? [] as $button)
                                        <button type="button" class="w-full px-4 py-2 bg-neutral-100 text-primary-600 font-semibold rounded-lg flex items-center justify-center gap-2">
                                            @php $buttonType = strtoupper($button['type'] ?? ''); @endphp
                                            @if($buttonType === 'URL')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            @elseif($buttonType === 'PHONE_NUMBER')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                            @elseif($buttonType === 'QUICK_REPLY')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                </svg>
                                            @elseif($buttonType === 'COPY_CODE')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                                </svg>
                                            @endif
                                            {{ $button['text'] ?? '' }}
                                        </button>
                                    @endforeach
                                </div>

                            @elseif($componentType === 'CAROUSEL')
                                <div class="p-4 border-t border-neutral-200">
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                        </svg>
                                        <span class="font-semibold text-neutral-700">Carrusel</span>
                                    </div>
                                    @if(isset($component['cards']))
                                        <div class="flex gap-2 overflow-x-auto pb-2">
                                            @foreach($component['cards'] as $index => $card)
                                                <div class="flex-shrink-0 w-48 bg-neutral-50 rounded-lg border border-neutral-200 overflow-hidden">
                                                    <div class="h-24 bg-neutral-200 flex items-center justify-center">
                                                        <span class="text-xs text-neutral-500">Tarjeta {{ $index + 1 }}</span>
                                                    </div>
                                                    @if(isset($card['components']))
                                                        @foreach($card['components'] as $cardComponent)
                                                            @if(($cardComponent['type'] ?? '') === 'BODY')
                                                                <div class="p-2">
                                                                    <p class="text-xs text-neutral-700 line-clamp-2">{{ $cardComponent['text'] ?? '' }}</p>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-neutral-500">{{ count($component['cards'] ?? []) }} tarjetas</p>
                                    @endif
                                </div>

                            @elseif($componentType === 'LIMITED_TIME_OFFER')
                                <div class="p-4 bg-warning-50 border-t border-warning-200">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-semibold text-warning-800">Oferta por tiempo limitado</span>
                                    </div>
                                    @if(isset($component['limited_time_offer']))
                                        <p class="text-sm text-warning-700 mt-1">
                                            Expira: {{ $component['limited_time_offer']['expiration_time_ms'] ?? 'No especificado' }}
                                        </p>
                                    @endif
                                </div>

                            @elseif($componentType === 'CATALOG')
                                <div class="p-4 border-t border-neutral-200">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                        <span class="font-semibold text-neutral-700">Catálogo de productos</span>
                                    </div>
                                    <p class="text-sm text-neutral-500">Este mensaje incluye productos del catálogo</p>
                                </div>

                            @elseif($componentType === 'MPM')
                                <div class="p-4 border-t border-neutral-200">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        <span class="font-semibold text-neutral-700">Multi-Product Message</span>
                                    </div>
                                    <p class="text-sm text-neutral-500">Mensaje con múltiples productos</p>
                                </div>

                            @elseif($componentType === 'ORDER_DETAILS')
                                <div class="p-4 border-t border-neutral-200">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                        <span class="font-semibold text-neutral-700">Detalles del pedido</span>
                                    </div>
                                    <p class="text-sm text-neutral-500">Información de pedido incluida</p>
                                </div>

                            @elseif($componentType === 'ORDER_STATUS')
                                <div class="p-4 border-t border-neutral-200">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-semibold text-neutral-700">Estado del pedido</span>
                                    </div>
                                    <p class="text-sm text-neutral-500">Actualización de estado de pedido</p>
                                </div>

                            @else
                                {{-- Unknown component type - show raw info --}}
                                <div class="p-4 border-t border-neutral-200 bg-neutral-50">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-semibold text-neutral-600">{{ $componentType }}</span>
                                    </div>
                                    @if(isset($component['text']))
                                        <p class="text-sm text-neutral-600">{{ $component['text'] }}</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @else
                        {{-- Object format (from local creation): {header: {...}, body: {...}, ...} --}}
                        @if(isset($components['header']))
                            <div class="p-4 bg-primary-50 border-b border-neutral-200">
                                @php $format = strtoupper($components['header']['format'] ?? 'TEXT'); @endphp
                                @if($format === 'TEXT')
                                    <p class="font-bold text-neutral-900">{{ $components['header']['text'] ?? '' }}</p>
                                @else
                                    <div class="flex items-center justify-center h-32 bg-neutral-100 rounded-lg">
                                        <div class="text-center">
                                            <svg class="w-12 h-12 text-neutral-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <p class="text-xs text-neutral-500">{{ $format }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(isset($components['body']))
                            <div class="p-4">
                                <p class="text-neutral-900 whitespace-pre-wrap">{{ $components['body']['text'] ?? '' }}</p>
                            </div>
                        @endif

                        @if(isset($components['footer']))
                            <div class="px-4 pb-4">
                                <p class="text-xs text-neutral-500">{{ $components['footer']['text'] ?? '' }}</p>
                            </div>
                        @endif

                        @if(isset($components['buttons']) && !empty($components['buttons']))
                            <div class="p-4 border-t border-neutral-200 space-y-2">
                                @foreach($components['buttons'] as $button)
                                    <button type="button" class="w-full px-4 py-2 bg-neutral-100 text-primary-600 font-semibold rounded-lg">
                                        {{ $button['text'] ?? '' }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    {{-- Show message if no content --}}
                    @if(empty($components))
                        <div class="p-8 text-center">
                            <svg class="w-12 h-12 text-neutral-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-neutral-500">Sin contenido para mostrar</p>
                        </div>
                    @endif
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

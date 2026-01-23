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

                {{-- WhatsApp-style chat background --}}
                <div class="max-w-md mx-auto rounded-xl shadow-lg overflow-hidden border border-neutral-200" style="background-color: #efeae2; background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyBAMAAADsEZWCAAAAElBMVEX////p5uHp5uHo5eDo5eDo5eC5gEN8AAAABXRSTlMAESIzRBQ9aFkAAAA1SURBVDjLY2AYBYMKsLi4gAATAwMTM5QJZDExMzExQZhMYCYTkAnhgJhABowJYsKYI9b2AQAGBgFRK+QAZQAAAABJRU5ErkJggg==');">
                    @php
                        $components = $template->components ?? [];
                        $isArrayFormat = isset($components[0]) || (is_array($components) && !empty($components) && isset(array_values($components)[0]['type']));

                        // Extract components for easier access
                        $headerComponent = null;
                        $bodyComponent = null;
                        $footerComponent = null;
                        $buttonsComponent = null;
                        $carouselComponent = null;
                        $otherComponents = [];

                        if ($isArrayFormat) {
                            foreach ($components as $comp) {
                                $type = strtoupper($comp['type'] ?? '');
                                switch ($type) {
                                    case 'HEADER': $headerComponent = $comp; break;
                                    case 'BODY': $bodyComponent = $comp; break;
                                    case 'FOOTER': $footerComponent = $comp; break;
                                    case 'BUTTONS': $buttonsComponent = $comp; break;
                                    case 'CAROUSEL': $carouselComponent = $comp; break;
                                    default: $otherComponents[] = $comp; break;
                                }
                            }
                        }
                    @endphp

                    {{-- Main message bubble --}}
                    <div class="p-3">
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden" style="max-width: 85%;">

                    @if($isArrayFormat)
                        {{-- Meta array format - render components in order --}}

                        {{-- HEADER --}}
                        @if($headerComponent)
                            @php $format = strtoupper($headerComponent['format'] ?? 'TEXT'); @endphp
                            @if($format === 'TEXT')
                                <div class="px-3 pt-2">
                                    <p class="font-bold text-neutral-900 text-[15px]">{{ $headerComponent['text'] ?? '' }}</p>
                                </div>
                            @elseif($format === 'IMAGE')
                                <div class="relative h-40 bg-gradient-to-br from-neutral-200 to-neutral-300 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @elseif($format === 'VIDEO')
                                <div class="relative h-40 bg-gradient-to-br from-neutral-800 to-neutral-900 flex items-center justify-center">
                                    <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </div>
                                </div>
                            @elseif($format === 'DOCUMENT')
                                <div class="p-3 bg-neutral-100 flex items-center gap-3">
                                    <div class="w-10 h-12 bg-red-500 rounded flex items-center justify-center">
                                        <span class="text-white text-xs font-bold">PDF</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-neutral-700">documento.pdf</p>
                                        <p class="text-xs text-neutral-500">PDF • Documento</p>
                                    </div>
                                </div>
                            @elseif($format === 'LOCATION')
                                <div class="relative h-32 bg-gradient-to-br from-green-100 to-green-200 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                            @else
                                <div class="relative h-32 bg-gradient-to-br from-neutral-200 to-neutral-300 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="absolute bottom-2 text-xs text-neutral-500 bg-white/80 px-2 py-0.5 rounded">{{ $format }}</p>
                                </div>
                            @endif
                        @endif

                        {{-- BODY --}}
                        @if($bodyComponent)
                            <div class="px-3 py-2">
                                <p class="text-neutral-800 whitespace-pre-wrap text-[14px] leading-relaxed">{{ $bodyComponent['text'] ?? '' }}</p>
                            </div>
                        @endif

                        {{-- FOOTER --}}
                        @if($footerComponent)
                            <div class="px-3 pb-2">
                                <p class="text-xs text-neutral-500">{{ $footerComponent['text'] ?? '' }}</p>
                            </div>
                        @endif

                        {{-- BUTTONS (non-carousel) --}}
                        @if($buttonsComponent && !$carouselComponent)
                            <div class="border-t border-neutral-200">
                                @foreach($buttonsComponent['buttons'] ?? [] as $button)
                                    @php $buttonType = strtoupper($button['type'] ?? ''); @endphp
                                    <div class="px-4 py-2.5 border-b border-neutral-100 last:border-b-0 text-center">
                                        <span class="text-[#00a884] font-medium text-sm flex items-center justify-center gap-2">
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
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Close message bubble before carousel --}}
                        </div>
                        </div>

                        {{-- CAROUSEL --}}
                        @if($carouselComponent && isset($carouselComponent['cards']) && count($carouselComponent['cards']) > 0)
                            <div class="px-3 pb-3 pt-1">
                                <div class="flex gap-2 overflow-x-auto pb-2 snap-x snap-mandatory" style="scrollbar-width: thin;">
                                    @foreach($carouselComponent['cards'] as $index => $card)
                                        @php
                                            $cardHeader = collect($card['components'] ?? [])->firstWhere('type', 'HEADER');
                                            $cardBody = collect($card['components'] ?? [])->firstWhere('type', 'BODY');
                                            $cardButtons = collect($card['components'] ?? [])->firstWhere('type', 'BUTTONS');
                                            $cardHeaderFormat = strtoupper($cardHeader['format'] ?? 'IMAGE');
                                        @endphp
                                        <div class="flex-shrink-0 w-52 bg-white rounded-xl overflow-hidden shadow-sm snap-start">
                                            {{-- Card Header (Image/Video) --}}
                                            <div class="relative h-32 bg-gradient-to-br from-neutral-200 to-neutral-300 flex items-center justify-center">
                                                @if($cardHeaderFormat === 'IMAGE')
                                                    <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                @elseif($cardHeaderFormat === 'VIDEO')
                                                    <div class="w-10 h-10 rounded-full bg-black/30 flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M8 5v14l11-7z"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                                {{-- Card indicator --}}
                                                <div class="absolute top-2 right-2 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded-full font-medium">
                                                    {{ $index + 1 }}/{{ count($carouselComponent['cards']) }}
                                                </div>
                                            </div>

                                            {{-- Card Body --}}
                                            @if($cardBody)
                                                <div class="px-3 py-2">
                                                    <p class="text-[13px] text-neutral-800 leading-snug">{{ $cardBody['text'] ?? '' }}</p>
                                                </div>
                                            @endif

                                            {{-- Card Buttons --}}
                                            @if($cardButtons && isset($cardButtons['buttons']))
                                                <div class="border-t border-neutral-100">
                                                    @foreach($cardButtons['buttons'] as $btn)
                                                        <div class="px-3 py-2 text-center border-b border-neutral-100 last:border-b-0">
                                                            <span class="text-[13px] text-[#00a884] font-medium flex items-center justify-center gap-1">
                                                                @if(strtoupper($btn['type'] ?? '') === 'URL')
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                                    </svg>
                                                                @elseif(strtoupper($btn['type'] ?? '') === 'QUICK_REPLY')
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                                    </svg>
                                                                @endif
                                                                {{ $btn['text'] ?? '' }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                {{-- Carousel scroll indicator --}}
                                <div class="flex justify-center gap-1.5 mt-2">
                                    @foreach($carouselComponent['cards'] as $index => $card)
                                        <div class="w-1.5 h-1.5 rounded-full {{ $index === 0 ? 'bg-[#00a884]' : 'bg-neutral-300' }}"></div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Other components (CATALOG, MPM, etc.) --}}
                        @foreach($otherComponents as $component)
                            @php $componentType = strtoupper($component['type'] ?? ''); @endphp
                            @if($componentType === 'LIMITED_TIME_OFFER')
                                <div class="mx-3 mb-3 p-3 bg-warning-50 rounded-lg border border-warning-200">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-semibold text-warning-800 text-sm">Oferta por tiempo limitado</span>
                                    </div>
                                </div>
                            @elseif($componentType === 'CATALOG')
                                <div class="mx-3 mb-3 p-3 bg-white rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-[#00a884]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                        <span class="font-medium text-neutral-700 text-sm">Ver catálogo</span>
                                    </div>
                                </div>
                            @elseif($componentType === 'MPM')
                                <div class="mx-3 mb-3 p-3 bg-white rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-[#00a884]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        <span class="font-medium text-neutral-700 text-sm">Multi-Product Message</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        {{-- Object format (from local creation): {header: {...}, body: {...}, ...} --}}
                        @if(isset($components['header']))
                            @php $format = strtoupper($components['header']['format'] ?? 'TEXT'); @endphp
                            @if($format === 'TEXT')
                                <div class="px-3 pt-2">
                                    <p class="font-bold text-neutral-900 text-[15px]">{{ $components['header']['text'] ?? '' }}</p>
                                </div>
                            @elseif($format === 'IMAGE')
                                <div class="relative h-40 bg-gradient-to-br from-neutral-200 to-neutral-300 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @elseif($format === 'VIDEO')
                                <div class="relative h-40 bg-gradient-to-br from-neutral-800 to-neutral-900 flex items-center justify-center">
                                    <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </div>
                                </div>
                            @else
                                <div class="relative h-32 bg-gradient-to-br from-neutral-200 to-neutral-300 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="absolute bottom-2 text-xs text-neutral-500 bg-white/80 px-2 py-0.5 rounded">{{ $format }}</p>
                                </div>
                            @endif
                        @endif

                        @if(isset($components['body']))
                            <div class="px-3 py-2">
                                <p class="text-neutral-800 whitespace-pre-wrap text-[14px] leading-relaxed">{{ $components['body']['text'] ?? '' }}</p>
                            </div>
                        @endif

                        @if(isset($components['footer']))
                            <div class="px-3 pb-2">
                                <p class="text-xs text-neutral-500">{{ $components['footer']['text'] ?? '' }}</p>
                            </div>
                        @endif

                        @if(isset($components['buttons']) && !empty($components['buttons']))
                            <div class="border-t border-neutral-200">
                                @foreach($components['buttons'] as $button)
                                    @php $buttonType = strtoupper($button['type'] ?? ''); @endphp
                                    <div class="px-4 py-2.5 border-b border-neutral-100 last:border-b-0 text-center">
                                        <span class="text-[#00a884] font-medium text-sm flex items-center justify-center gap-2">
                                            @if($buttonType === 'URL')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                </svg>
                                            @elseif($buttonType === 'QUICK_REPLY')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                </svg>
                                            @endif
                                            {{ $button['text'] ?? '' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Close bubble for object format --}}
                        </div>
                        </div>
                    @endif

                    {{-- Show message if no content --}}
                    @if(empty($components))
                        <div class="p-3">
                            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                                <svg class="w-12 h-12 text-neutral-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-neutral-500">Sin contenido para mostrar</p>
                            </div>
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

                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="mb-4 p-3 bg-success-100 border border-success-300 text-success-800 rounded-lg text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-3 bg-danger-100 border border-danger-300 text-danger-800 rounded-lg text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-4 p-3 bg-warning-100 border border-warning-300 text-warning-800 rounded-lg text-sm">
                        {{ session('warning') }}
                    </div>
                @endif

                <div class="space-y-3">
                    @if($template->isDraft())
                        <form action="{{ route('templates.submit', $template) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-700 hover:to-secondary-700 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg cursor-pointer">
                                Enviar a Meta
                            </button>
                        </form>

                        <a href="{{ route('templates.edit', $template) }}" class="block w-full bg-neutral-600 hover:bg-neutral-700 text-white px-4 py-2 rounded-lg font-semibold text-center transition-colors cursor-pointer">
                            Editar
                        </a>
                    @endif

                    @if($template->meta_template_id)
                        <form action="{{ route('templates.sync', $template) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-accent-600 hover:bg-accent-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors cursor-pointer">
                                Sincronizar con Meta
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('templates.destroy', $template) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta plantilla?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-danger-600 hover:bg-danger-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors cursor-pointer">
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

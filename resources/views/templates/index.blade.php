@extends('layouts.app')

@section('title', 'Plantillas de Mensajes')

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-success-50 border-l-4 border-success-500 text-success-700 p-4 rounded-lg shadow-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-danger-50 border-l-4 border-danger-500 text-danger-700 p-4 rounded-lg shadow-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-danger-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    @foreach($errors->all() as $error)
                        <p class="text-sm font-semibold">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Header -->
    <div class="relative overflow-hidden bg-gradient-to-r from-secondary-600 via-secondary-700 to-primary-700 rounded-2xl shadow-2xl p-8">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-extrabold text-white mb-2 flex items-center">
                    <svg class="w-10 h-10 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                    Plantillas de Mensajes
                </h1>
                <p class="text-secondary-100 text-lg">Gestiona tus plantillas aprobadas por Meta</p>
            </div>
            <div class="flex gap-3">
                <!-- Sync from Meta Button -->
                @if(Auth::user()->tenant && Auth::user()->tenant->wabaAccounts && Auth::user()->tenant->wabaAccounts->count() > 0)
                    <form action="{{ route('templates.sync-all') }}" method="POST" id="sync-form">
                        @csrf
                        <input type="hidden" name="waba_account_id" value="{{ Auth::user()->tenant->wabaAccounts->first()->id }}">
                        <button type="submit" class="group relative overflow-hidden bg-white/10 backdrop-blur-sm text-white border-2 border-white/30 px-6 py-4 rounded-xl font-bold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex items-center hover:bg-white/20">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span class="relative z-10">Sincronizar desde Meta</span>
                        </button>
                    </form>
                @endif

                @if(Auth::user()->tenant && Auth::user()->tenant->wabaAccounts && Auth::user()->tenant->wabaAccounts->count() > 0)
                    <a href="{{ route('templates.create') }}" class="group relative overflow-hidden bg-white text-secondary-600 px-8 py-4 rounded-xl font-bold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex items-center">
                        <span class="absolute inset-0 bg-gradient-to-r from-secondary-400 to-primary-500 opacity-0 group-hover:opacity-20 transition-opacity"></span>
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="relative z-10">Nueva Plantilla</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- WABA Account Warning -->
    @if(!Auth::user()->tenant || !Auth::user()->tenant->wabaAccounts || Auth::user()->tenant->wabaAccounts->count() === 0)
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border-l-4 border-amber-500 rounded-lg p-6 shadow-lg">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-bold text-amber-900 mb-2">Cuenta de WhatsApp Business requerida</h3>
                    <p class="text-amber-800 mb-4">
                        Para crear y gestionar plantillas de mensajes, necesitas conectar una cuenta de WhatsApp Business API (WABA) primero.
                    </p>
                    <div class="bg-white/50 rounded-lg p-4 mb-4">
                        <h4 class="font-semibold text-amber-900 mb-2">¿Qué necesitas hacer?</h4>
                        <ul class="list-disc list-inside space-y-1 text-sm text-amber-800">
                            <li>Registra una cuenta de WhatsApp Business API</li>
                            <li>Conecta tu cuenta WABA a esta plataforma</li>
                            <li>Una vez conectada, podrás crear y sincronizar plantillas</li>
                        </ul>
                    </div>
                    <a href="{{ route('waba-accounts.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Conectar Cuenta WABA
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white/70 backdrop-blur-sm rounded-xl shadow-soft p-6 border border-primary-100">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-neutral-700 mb-2">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre o descripción..." class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-700 mb-2">Estado</label>
                <select name="status" class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Todos</option>
                    <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Borrador</option>
                    <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pendiente</option>
                    <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>Aprobada</option>
                    <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rechazada</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-neutral-700 mb-2">Categoría</label>
                <select name="category" class="w-full px-4 py-2 rounded-lg border border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Todas</option>
                    <option value="MARKETING" {{ request('category') == 'MARKETING' ? 'selected' : '' }}>Marketing</option>
                    <option value="UTILITY" {{ request('category') == 'UTILITY' ? 'selected' : '' }}>Utilidad</option>
                    <option value="AUTHENTICATION" {{ request('category') == 'AUTHENTICATION' ? 'selected' : '' }}>Autenticación</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-gradient-to-r from-primary-600 to-secondary-600 text-white px-6 py-2 rounded-lg font-semibold hover:from-primary-700 hover:to-secondary-700 transition-all shadow-md hover:shadow-lg">
                    Filtrar
                </button>
                <a href="{{ route('templates.index') }}" class="px-4 py-2 bg-neutral-200 text-neutral-700 rounded-lg hover:bg-neutral-300 transition-colors">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Templates Grid -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden border border-primary-100">
        @if($templates->isEmpty())
            <div class="text-center py-16 px-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-secondary-100 mb-6">
                    <svg class="w-12 h-12 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No hay plantillas registradas</h3>
                <p class="text-gray-600 mb-6">
                    @if(Auth::user()->tenant && Auth::user()->tenant->wabaAccounts && Auth::user()->tenant->wabaAccounts->count() > 0)
                        Comienza creando tu primera plantilla de mensaje
                    @else
                        Primero conecta una cuenta WABA para crear plantillas
                    @endif
                </p>
                @if(Auth::user()->tenant && Auth::user()->tenant->wabaAccounts && Auth::user()->tenant->wabaAccounts->count() > 0)
                    <a href="{{ route('templates.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-secondary-600 to-primary-600 hover:from-secondary-700 hover:to-primary-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Crear Primera Plantilla
                    </a>
                @else
                    <a href="{{ route('waba-accounts.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Conectar Cuenta WABA
                    </a>
                @endif
            </div>
        @else
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($templates as $template)
                    <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 border-2 border-neutral-100 hover:border-primary-300 overflow-hidden group">
                        <!-- Card Header -->
                        <div class="p-4 bg-gradient-to-r from-primary-50 to-secondary-50 border-b border-neutral-200">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-lg font-bold text-neutral-900 group-hover:text-primary-600 transition-colors">
                                    {{ $template->name }}
                                </h3>
                                <span class="px-2 py-1 rounded-lg text-xs font-semibold {{ $template->getStatusBadgeClass() }}">
                                    {{ ucfirst(strtolower($template->status)) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 rounded-md text-xs font-semibold {{ $template->getCategoryBadgeClass() }}">
                                    {{ $template->category }}
                                </span>
                                <span class="text-xs text-neutral-600">
                                    {{ strtoupper($template->language) }}
                                </span>
                                @if($template->quality_score !== 'UNKNOWN')
                                    <span class="px-2 py-1 rounded-md text-xs font-semibold {{ $template->getQualityBadgeClass() }}">
                                        {{ $template->quality_score }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4">
                            <p class="text-sm text-neutral-700 mb-3 line-clamp-3">
                                {{ $template->getPreviewText() }}
                            </p>

                            @if($template->description)
                                <p class="text-xs text-neutral-500 mb-3 line-clamp-2">
                                    {{ $template->description }}
                                </p>
                            @endif

                            <div class="flex items-center justify-between text-xs text-neutral-500 mb-4">
                                <span>Usado {{ $template->usage_count }} veces</span>
                                <span>{{ $template->created_at->diffForHumans() }}</span>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <a href="{{ route('templates.show', $template) }}" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-center text-sm font-semibold transition-colors">
                                    Ver
                                </a>
                                @if($template->isDraft())
                                    <form action="{{ route('templates.submit', $template) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full bg-success-600 hover:bg-success-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                                            Enviar a Meta
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($templates->hasPages())
                <div class="px-6 py-4 bg-neutral-50 border-t border-neutral-200">
                    {{ $templates->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

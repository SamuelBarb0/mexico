@extends('layouts.app')

@section('title', 'Gestión de Planes')

@section('content')
<div class="space-y-8">
    <!-- Header con gradiente y glassmorphism -->
    <div class="relative overflow-hidden bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-extrabold text-white mb-2 flex items-center">
                    <svg class="w-10 h-10 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Gestión de Planes
                </h1>
                <p class="text-indigo-100 text-lg">Administra los planes de suscripción de la plataforma</p>
            </div>
            <div class="flex gap-3">
                <form action="{{ route('admin.plans.sync-stripe') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="group relative overflow-hidden bg-gradient-to-r from-purple-500 to-indigo-600 text-white px-6 py-4 rounded-xl font-bold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex items-center">
                        <span class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity"></span>
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span class="relative z-10">Sincronizar Stripe</span>
                    </button>
                </form>
                <a href="{{ route('admin.plans.create') }}" class="group relative overflow-hidden bg-white text-indigo-600 px-8 py-4 rounded-xl font-bold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex items-center">
                    <span class="absolute inset-0 bg-gradient-to-r from-green-400 to-emerald-500 opacity-0 group-hover:opacity-20 transition-opacity"></span>
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="relative z-10">Crear Nuevo Plan</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse($plans as $plan)
        <div class="group relative bg-white rounded-2xl shadow-xl overflow-hidden transition-all duration-300 transform hover:-translate-y-2 hover:shadow-2xl {{ !$plan->is_active ? 'opacity-60' : '' }}">
            <!-- Decorative gradient border on hover -->
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl" style="padding: 2px;">
                <div class="h-full w-full bg-white rounded-2xl"></div>
            </div>

            <!-- Header -->
            <div class="relative bg-gradient-to-br from-indigo-500 to-purple-600 p-6 text-white">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold">{{ $plan->name }}</h3>
                            <p class="text-indigo-100 text-sm mt-1">{{ $plan->slug }}</p>
                        </div>
                        @if($plan->is_default)
                            <span class="bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full text-xs font-bold shadow-md">
                                ⭐ Default
                            </span>
                        @endif
                    </div>
                    <div class="space-y-1">
                        <div class="text-4xl font-bold">
                            ${{ number_format($plan->price_monthly, 2) }}
                            <span class="text-lg font-normal text-indigo-100">/mes</span>
                        </div>
                        <div class="text-sm text-indigo-100">
                            o ${{ number_format($plan->price_yearly, 2) }}/año
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="relative p-6">
                @if($plan->description)
                <p class="text-gray-600 text-sm mb-4 italic">{{ $plan->description }}</p>
                @endif

                <!-- Limits con iconos -->
                <div class="space-y-3 mb-5">
                    <div class="flex items-center justify-between text-sm bg-blue-50 p-2 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span class="text-gray-700">Usuarios</span>
                        </div>
                        <span class="font-bold text-blue-700">{{ $plan->max_users }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm bg-green-50 p-2 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <span class="text-gray-700">Contactos</span>
                        </div>
                        <span class="font-bold text-green-700">{{ number_format($plan->max_contacts) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm bg-purple-50 p-2 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                            <span class="text-gray-700">Campañas</span>
                        </div>
                        <span class="font-bold text-purple-700">{{ $plan->max_campaigns }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm bg-amber-50 p-2 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-amber-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            <span class="text-gray-700">WABA</span>
                        </div>
                        <span class="font-bold text-amber-700">{{ $plan->max_waba_accounts }}</span>
                    </div>
                </div>

                <!-- Badges -->
                <div class="flex flex-wrap gap-2 mb-4">
                    @if($plan->has_trial)
                        <span class="bg-blue-100 text-blue-800 text-xs px-3 py-1.5 rounded-full font-semibold flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            {{ $plan->trial_days == -1 ? 'Prueba ∞' : $plan->trial_days . 'd prueba' }}
                        </span>
                    @endif
                    @if($plan->is_visible)
                        <span class="bg-green-100 text-green-800 text-xs px-3 py-1.5 rounded-full font-semibold flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                            </svg>
                            Visible
                        </span>
                    @endif
                    @if(!$plan->is_active)
                        <span class="bg-red-100 text-red-800 text-xs px-3 py-1.5 rounded-full font-semibold flex items-center animate-pulse">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                            </svg>
                            Inactivo
                        </span>
                    @endif
                </div>

                <!-- Stats -->
                <div class="border-t pt-4 mb-5">
                    <div class="flex items-center justify-center bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-3">
                        <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">
                            <span class="text-2xl font-bold text-indigo-600">{{ $plan->subscriptions()->count() }}</span>
                            suscripciones activas
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="space-y-2">
                    <a href="{{ route('admin.plans.edit', $plan) }}" class="block bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white text-center px-4 py-3 rounded-lg font-semibold transition-all transform hover:scale-105 shadow-md hover:shadow-xl">
                        ✏️ Editar Plan
                    </a>
                    <div class="grid grid-cols-2 gap-2">
                        <form action="{{ route('admin.plans.toggle', $plan) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full {{ $plan->is_active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' }} text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all transform hover:scale-105 shadow-md">
                                {{ $plan->is_active ? '⏸️ Desactivar' : '▶️ Activar' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este plan? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all transform hover:scale-105 shadow-md">
                                🗑️ Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-2xl shadow-xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">No hay planes creados</h3>
            <p class="text-gray-600 mb-4">Crea tu primer plan de suscripción</p>
            <a href="{{ route('admin.plans.create') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition-all">
                Crear Plan
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection

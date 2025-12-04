@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Hero Banner con Gradiente -->
    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 rounded-2xl shadow-2xl p-8 text-white">
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="relative z-10">
            <h1 class="text-4xl font-extrabold mb-3">
                ¡Hola, {{ $user->name }}! 👋
            </h1>
            <p class="text-blue-100 text-lg">
                @if($user->isPlatformAdmin())
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-white/20 backdrop-blur-sm">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Platform Administrator
                    </span>
                    <span class="block mt-2 text-sm">Tienes acceso completo a toda la plataforma</span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-white/20 backdrop-blur-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        {{ $user->tenant->name }}
                    </span>
                @endif
            </p>
        </div>
    </div>

    @if(!$user->isPlatformAdmin())
    <!-- Subscription Status Banner -->
    @php
        $currentSubscription = $user->tenant->currentSubscription();
    @endphp

    @if($currentSubscription)
        @if($currentSubscription->isOnTrial())
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-blue-900">Periodo de Prueba - {{ $currentSubscription->plan->name }}</h3>
                            <p class="text-sm text-blue-700 mt-1">
                                {{ $currentSubscription->trialDaysRemaining() }} días restantes en tu prueba gratis
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('subscriptions.index') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                        Gestionar Plan
                    </a>
                </div>
            </div>
        @elseif($currentSubscription->isPastDue())
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-red-900">Pago Pendiente</h3>
                            <p class="text-sm text-red-700 mt-1">
                                Hay un problema con tu método de pago. Actualízalo para continuar usando el servicio.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('payment-methods.index') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">
                        Actualizar Pago
                    </a>
                </div>
            </div>
        @elseif($currentSubscription->isCanceled())
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <h3 class="font-semibold text-yellow-900">Suscripción Cancelada</h3>
                            <p class="text-sm text-yellow-700 mt-1">
                                Tu suscripción terminará el {{ $currentSubscription->ends_at?->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('subscriptions.index') }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 text-sm">
                        Reanudar
                    </a>
                </div>
            </div>
        @endif
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-yellow-900">Sin Suscripción Activa</h3>
                        <p class="text-sm text-yellow-700 mt-1">
                            Selecciona un plan para acceder a todas las funcionalidades
                        </p>
                    </div>
                </div>
                <a href="{{ route('subscriptions.plans') }}" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 text-sm">
                    Ver Planes
                </a>
            </div>
        </div>
    @endif

    <!-- Tarjetas de Estadísticas con Hover Effects -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Clientes -->
        <div class="group relative bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 p-6 text-white overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-white/20 backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">Total</span>
                </div>
                <p class="text-sm text-blue-100 mb-1">Clientes</p>
                <p class="text-4xl font-bold">{{ $user->isPlatformAdmin() ? \App\Models\Client::count() : $user->tenant->clients()->count() }}</p>
            </div>
        </div>

        <!-- Contactos -->
        <div class="group relative bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 p-6 text-white overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-white/20 backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">Total</span>
                </div>
                <p class="text-sm text-green-100 mb-1">Contactos</p>
                <p class="text-4xl font-bold">{{ number_format($user->isPlatformAdmin() ? \App\Models\Contact::count() : $user->tenant->contacts()->count()) }}</p>
            </div>
        </div>

        <!-- Campañas -->
        <div class="group relative bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 p-6 text-white overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-white/20 backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">Total</span>
                </div>
                <p class="text-sm text-purple-100 mb-1">Campañas</p>
                <p class="text-4xl font-bold">{{ $user->isPlatformAdmin() ? \App\Models\Campaign::count() : $user->tenant->campaigns()->count() }}</p>
            </div>
        </div>

        <!-- WABA Accounts -->
        <div class="group relative bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 p-6 text-white overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-white/20 backdrop-blur-sm">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold bg-white/20 px-2 py-1 rounded-full">Total</span>
                </div>
                <p class="text-sm text-amber-100 mb-1">WABA Accounts</p>
                <p class="text-4xl font-bold">{{ $user->isPlatformAdmin() ? \App\Models\WabaAccount::count() : $user->tenant->wabaAccounts()->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Límites del Plan con Diseño Mejorado -->
    <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="p-3 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg mr-4">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800">Límites de tu Plan</h3>
            </div>
            @if($currentSubscription)
                <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md">
                    {{ $currentSubscription->plan->name }}
                </span>
            @endif
        </div>
        @if($currentSubscription)
        <div class="space-y-5">
            <!-- Users -->
            <div class="group">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-700 font-medium flex items-center">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span>
                        Usuarios
                    </span>
                    <span class="text-gray-900 font-bold">
                        {{ $user->tenant->users()->count() }} <span class="text-gray-400 font-normal">/ {{ $currentSubscription->plan->max_users }}</span>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500 shadow-md" style="width: {{ $currentSubscription->getLimitPercentage('users') }}%"></div>
                </div>
            </div>

            <!-- Contacts -->
            <div class="group">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-700 font-medium flex items-center">
                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                        Contactos
                    </span>
                    <span class="text-gray-900 font-bold">
                        {{ number_format($user->tenant->contacts()->count()) }} <span class="text-gray-400 font-normal">/ {{ number_format($currentSubscription->plan->max_contacts) }}</span>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 h-3 rounded-full transition-all duration-500 shadow-md" style="width: {{ $currentSubscription->getLimitPercentage('contacts') }}%"></div>
                </div>
            </div>

            <!-- Campaigns -->
            <div class="group">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-700 font-medium flex items-center">
                        <span class="w-2 h-2 rounded-full bg-purple-500 mr-2"></span>
                        Campañas
                    </span>
                    <span class="text-gray-900 font-bold">
                        {{ $user->tenant->campaigns()->count() }} <span class="text-gray-400 font-normal">/ {{ $currentSubscription->plan->max_campaigns }}</span>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full transition-all duration-500 shadow-md" style="width: {{ $currentSubscription->getLimitPercentage('campaigns') }}%"></div>
                </div>
            </div>

            <!-- WABA Accounts -->
            <div class="group">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-700 font-medium flex items-center">
                        <span class="w-2 h-2 rounded-full bg-amber-500 mr-2"></span>
                        Cuentas WABA
                    </span>
                    <span class="text-gray-900 font-bold">
                        {{ $user->tenant->wabaAccounts()->count() }} <span class="text-gray-400 font-normal">/ {{ $currentSubscription->plan->max_waba_accounts }}</span>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-amber-500 to-orange-600 h-3 rounded-full transition-all duration-500 shadow-md" style="width: {{ $currentSubscription->getLimitPercentage('waba_accounts') }}%"></div>
                </div>
            </div>

            <!-- Storage -->
            <div class="group">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-gray-700 font-medium flex items-center">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 mr-2"></span>
                        Almacenamiento
                    </span>
                    <span class="text-gray-900 font-bold">
                        0 MB <span class="text-gray-400 font-normal">/ {{ $currentSubscription->plan->max_storage_mb }} MB</span>
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-3 rounded-full transition-all duration-500 shadow-md" style="width: 0%"></div>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200 mt-6">
                <a href="{{ route('subscriptions.index') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition-colors">
                    Ver detalles completos
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
        @else
        <div class="text-center py-8">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-gray-600 mb-4">Selecciona un plan para ver tus límites</p>
            <a href="{{ route('subscriptions.plans') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all">
                Ver Planes Disponibles
            </a>
        </div>
        @endif
    </div>
    @endif

    <!-- Accesos Rápidos con Diseño Moderno -->
    <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        <div class="flex items-center mb-6">
            <div class="p-3 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl shadow-lg mr-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-800">Accesos Rápidos</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <a href="{{ route('clients.index') }}" class="group relative overflow-hidden p-6 text-center bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg border border-blue-200">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <svg class="w-8 h-8 mx-auto mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-blue-700 font-semibold relative z-10">Clientes</p>
            </a>
            <a href="{{ route('contacts.index') }}" class="group relative overflow-hidden p-6 text-center bg-gradient-to-br from-green-50 to-emerald-100 hover:from-green-100 hover:to-emerald-200 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg border border-green-200">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-emerald-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <svg class="w-8 h-8 mx-auto mb-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <p class="text-green-700 font-semibold relative z-10">Contactos</p>
            </a>
            <a href="{{ route('campaigns.index') }}" class="group relative overflow-hidden p-6 text-center bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg border border-purple-200">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <svg class="w-8 h-8 mx-auto mb-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                <p class="text-purple-700 font-semibold relative z-10">Campañas</p>
            </a>
            <a href="{{ route('waba-accounts.index') }}" class="group relative overflow-hidden p-6 text-center bg-gradient-to-br from-amber-50 to-orange-100 hover:from-amber-100 hover:to-orange-200 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg border border-amber-200">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-500 to-orange-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <svg class="w-8 h-8 mx-auto mb-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <p class="text-amber-700 font-semibold relative z-10">WABA</p>
            </a>
            <a href="{{ route('subscriptions.index') }}" class="group relative overflow-hidden p-6 text-center bg-gradient-to-br from-indigo-50 to-indigo-100 hover:from-indigo-100 hover:to-indigo-200 rounded-xl transition-all duration-300 transform hover:scale-105 hover:shadow-lg border border-indigo-200">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-indigo-600 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                <svg class="w-8 h-8 mx-auto mb-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <p class="text-indigo-700 font-semibold relative z-10">Suscripción</p>
            </a>
        </div>
    </div>
</div>
@endsection

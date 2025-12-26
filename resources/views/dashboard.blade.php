@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6 animate-fade-in">
    @if(!$user->isPlatformAdmin())
    <!-- Subscription Status Banner -->
    @php
        $currentSubscription = $user->tenant->currentSubscription();
    @endphp

    @if($currentSubscription)
        @if($currentSubscription->isOnTrial())
            <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl shadow-soft p-6 text-white animate-slide-up">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-display font-bold text-lg">Periodo de Prueba - {{ $currentSubscription->plan->name }}</h3>
                            <p class="text-primary-100 text-sm mt-1">
                                Te quedan <span class="font-bold">{{ $currentSubscription->trialDaysRemaining() }} días</span> en tu prueba gratuita
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('subscriptions.index') }}" class="bg-white text-primary-600 px-5 py-2.5 rounded-lg hover:bg-primary-50 transition-colors font-semibold shadow-md hover:shadow-lg whitespace-nowrap">
                        Gestionar Plan
                    </a>
                </div>
            </div>
        @elseif($currentSubscription->isPastDue())
            <div class="bg-gradient-to-r from-danger-500 to-danger-600 rounded-2xl shadow-soft p-6 text-white animate-slide-up">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center animate-pulse">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-display font-bold text-lg">Pago Pendiente</h3>
                            <p class="text-danger-100 text-sm mt-1">
                                Hay un problema con tu método de pago. Actualízalo para continuar usando el servicio.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('payment-methods.index') }}" class="bg-white text-danger-600 px-5 py-2.5 rounded-lg hover:bg-danger-50 transition-colors font-semibold shadow-md hover:shadow-lg whitespace-nowrap">
                        Actualizar Pago
                    </a>
                </div>
            </div>
        @elseif($currentSubscription->isCanceled())
            <div class="bg-gradient-to-r from-warning-500 to-warning-600 rounded-2xl shadow-soft p-6 text-white animate-slide-up">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-display font-bold text-lg">Suscripción Cancelada</h3>
                            <p class="text-warning-100 text-sm mt-1">
                                Tu suscripción terminará el {{ $currentSubscription->ends_at?->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('subscriptions.index') }}" class="bg-white text-warning-600 px-5 py-2.5 rounded-lg hover:bg-warning-50 transition-colors font-semibold shadow-md hover:shadow-lg whitespace-nowrap">
                        Reanudar
                    </a>
                </div>
            </div>
        @endif
    @else
        <div class="bg-gradient-to-r from-warning-500 to-warning-600 rounded-2xl shadow-soft p-6 text-white animate-slide-up">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display font-bold text-lg">Sin Suscripción Activa</h3>
                        <p class="text-warning-100 text-sm mt-1">
                            Selecciona un plan para acceder a todas las funcionalidades
                        </p>
                    </div>
                </div>
                <a href="{{ route('subscriptions.plans') }}" class="bg-white text-warning-600 px-5 py-2.5 rounded-lg hover:bg-warning-50 transition-colors font-semibold shadow-md hover:shadow-lg whitespace-nowrap">
                    Ver Planes
                </a>
            </div>
        </div>
    @endif
    @endif

    <!-- Welcome Section -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-8 border border-primary-100">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-display font-bold text-neutral-900 mb-2">
                    Hola, {{ $user->name }}! 👋
                </h1>
                <p class="text-neutral-600">
                    @if($user->isPlatformAdmin())
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-semibold bg-gradient-to-r from-secondary-500 to-secondary-600 text-white shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Platform Administrator
                        </span>
                    @else
                        Bienvenido a tu panel de control de <span class="font-semibold text-primary-600">{{ $user->tenant->name }}</span>
                    @endif
                </p>
            </div>
            <div class="hidden md:block">
                <svg class="w-24 h-24 text-primary-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Clientes -->
        <div class="stat-card bg-white border-primary-200 hover:shadow-glow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <a href="{{ route('clients.index') }}" class="text-primary-600 hover:text-primary-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <p class="text-sm font-medium text-neutral-600 mb-1">Clientes</p>
            <p class="text-3xl font-display font-bold text-neutral-900">{{ $user->isPlatformAdmin() ? \App\Models\Client::count() : $user->tenant->clients()->count() }}</p>
        </div>

        <!-- Contactos -->
        <div class="stat-card bg-white border-success-200 hover:shadow-glow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-success-500 to-success-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
                <a href="{{ route('contacts.index') }}" class="text-success-600 hover:text-success-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <p class="text-sm font-medium text-neutral-600 mb-1">Contactos</p>
            <p class="text-3xl font-display font-bold text-neutral-900">{{ number_format($user->isPlatformAdmin() ? \App\Models\Contact::count() : $user->tenant->contacts()->count()) }}</p>
        </div>

        <!-- Campañas -->
        <div class="stat-card bg-white border-secondary-200 hover:shadow-glow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-secondary-500 to-secondary-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                    </svg>
                </div>
                <a href="{{ route('campaigns.index') }}" class="text-secondary-600 hover:text-secondary-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <p class="text-sm font-medium text-neutral-600 mb-1">Campañas</p>
            <p class="text-3xl font-display font-bold text-neutral-900">{{ $user->isPlatformAdmin() ? \App\Models\Campaign::count() : $user->tenant->campaigns()->count() }}</p>
        </div>

        <!-- WABA Accounts -->
        <div class="stat-card bg-white border-accent-200 hover:shadow-glow group">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-accent-500 to-accent-600 rounded-xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                </div>
                <a href="{{ route('waba-accounts.index') }}" class="text-accent-600 hover:text-accent-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <p class="text-sm font-medium text-neutral-600 mb-1">WABA Accounts</p>
            <p class="text-3xl font-display font-bold text-neutral-900">{{ $user->isPlatformAdmin() ? \App\Models\WabaAccount::count() : $user->tenant->wabaAccounts()->count() }}</p>
        </div>
    </div>

    @if(!$user->isPlatformAdmin() && $currentSubscription)
    <!-- Plan Limits -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-8 border border-primary-100">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-display font-bold text-neutral-900">Límites de tu Plan</h3>
            </div>
            <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-md">
                {{ $currentSubscription->plan->name }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Users -->
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-neutral-700 flex items-center">
                        <span class="w-2 h-2 rounded-full bg-primary-500 mr-2"></span>
                        Usuarios
                    </span>
                    <span class="text-sm font-bold text-neutral-900">
                        {{ $user->tenant->users()->count() }} / {{ $currentSubscription->plan->max_users }}
                    </span>
                </div>
                <div class="w-full bg-neutral-200 rounded-full h-2.5 overflow-hidden">
                    @php
                        $percentage = $currentSubscription->getLimitPercentage('users');
                        $barColor = $percentage >= 90 ? 'bg-danger-500' : ($percentage >= 75 ? 'bg-warning-500' : 'bg-primary-500');
                    @endphp
                    <div class="h-2.5 rounded-full transition-all duration-500 {{ $barColor }}" style="width: {{ $percentage }}%"></div>
                </div>
            </div>

            <!-- Contacts -->
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-neutral-700 flex items-center">
                        <span class="w-2 h-2 rounded-full bg-success-500 mr-2"></span>
                        Contactos
                    </span>
                    <span class="text-sm font-bold text-neutral-900">
                        {{ number_format($user->tenant->contacts()->count()) }} / {{ number_format($currentSubscription->plan->max_contacts) }}
                    </span>
                </div>
                <div class="w-full bg-neutral-200 rounded-full h-2.5 overflow-hidden">
                    @php
                        $percentage = $currentSubscription->getLimitPercentage('contacts');
                        $barColor = $percentage >= 90 ? 'bg-danger-500' : ($percentage >= 75 ? 'bg-warning-500' : 'bg-success-500');
                    @endphp
                    <div class="h-2.5 rounded-full transition-all duration-500 {{ $barColor }}" style="width: {{ $percentage }}%"></div>
                </div>
            </div>

            <!-- Campaigns -->
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-neutral-700 flex items-center">
                        <span class="w-2 h-2 rounded-full bg-secondary-500 mr-2"></span>
                        Campañas
                    </span>
                    <span class="text-sm font-bold text-neutral-900">
                        {{ $user->tenant->campaigns()->count() }} / {{ $currentSubscription->plan->max_campaigns }}
                    </span>
                </div>
                <div class="w-full bg-neutral-200 rounded-full h-2.5 overflow-hidden">
                    @php
                        $percentage = $currentSubscription->getLimitPercentage('campaigns');
                        $barColor = $percentage >= 90 ? 'bg-danger-500' : ($percentage >= 75 ? 'bg-warning-500' : 'bg-secondary-500');
                    @endphp
                    <div class="h-2.5 rounded-full transition-all duration-500 {{ $barColor }}" style="width: {{ $percentage }}%"></div>
                </div>
            </div>

            <!-- WABA Accounts -->
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-neutral-700 flex items-center">
                        <span class="w-2 h-2 rounded-full bg-accent-500 mr-2"></span>
                        Cuentas WABA
                    </span>
                    <span class="text-sm font-bold text-neutral-900">
                        {{ $user->tenant->wabaAccounts()->count() }} / {{ $currentSubscription->plan->max_waba_accounts }}
                    </span>
                </div>
                <div class="w-full bg-neutral-200 rounded-full h-2.5 overflow-hidden">
                    @php
                        $percentage = $currentSubscription->getLimitPercentage('waba_accounts');
                        $barColor = $percentage >= 90 ? 'bg-danger-500' : ($percentage >= 75 ? 'bg-warning-500' : 'bg-accent-500');
                    @endphp
                    <div class="h-2.5 rounded-full transition-all duration-500 {{ $barColor }}" style="width: {{ $percentage }}%"></div>
                </div>
            </div>

            <!-- Storage -->
            <div class="space-y-2 md:col-span-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-neutral-700 flex items-center">
                        <span class="w-2 h-2 rounded-full bg-neutral-500 mr-2"></span>
                        Almacenamiento
                    </span>
                    <span class="text-sm font-bold text-neutral-900">
                        0 MB / {{ $currentSubscription->plan->max_storage_mb }} MB
                    </span>
                </div>
                <div class="w-full bg-neutral-200 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-neutral-500 h-2.5 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-neutral-200 mt-6">
            <a href="{{ route('subscriptions.index') }}" class="inline-flex items-center text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors group">
                Ver detalles completos de tu suscripción
                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-soft p-8 border border-primary-100">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 bg-gradient-to-br from-accent-500 to-accent-600 rounded-xl flex items-center justify-center shadow-md">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h3 class="text-xl font-display font-bold text-neutral-900">Accesos Rápidos</h3>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            <a href="{{ route('clients.index') }}" class="quick-action-card group border-primary-200 hover:border-primary-400 hover:shadow-soft">
                <svg class="w-8 h-8 text-primary-600 mb-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-neutral-700 font-semibold text-sm">Clientes</p>
            </a>

            <a href="{{ route('contacts.index') }}" class="quick-action-card group border-success-200 hover:border-success-400 hover:shadow-soft">
                <svg class="w-8 h-8 text-success-600 mb-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <p class="text-neutral-700 font-semibold text-sm">Contactos</p>
            </a>

            <a href="{{ route('campaigns.index') }}" class="quick-action-card group border-secondary-200 hover:border-secondary-400 hover:shadow-soft">
                <svg class="w-8 h-8 text-secondary-600 mb-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                <p class="text-neutral-700 font-semibold text-sm">Campañas</p>
            </a>

            <a href="{{ route('waba-accounts.index') }}" class="quick-action-card group border-accent-200 hover:border-accent-400 hover:shadow-soft">
                <svg class="w-8 h-8 text-accent-600 mb-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <p class="text-neutral-700 font-semibold text-sm">WABA</p>
            </a>

            <a href="{{ route('subscriptions.index') }}" class="quick-action-card group border-neutral-200 hover:border-neutral-400 hover:shadow-soft">
                <svg class="w-8 h-8 text-neutral-600 mb-3 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <p class="text-neutral-700 font-semibold text-sm">Suscripción</p>
            </a>
        </div>
    </div>
</div>

<style>
    .stat-card {
        @apply p-6 rounded-xl shadow-soft border-2 transition-all duration-300 hover:-translate-y-1;
    }

    .quick-action-card {
        @apply flex flex-col items-center justify-center p-6 rounded-xl bg-white border-2 transition-all duration-300 hover:-translate-y-1 cursor-pointer;
    }
</style>
@endsection

@extends('layouts.app')

@section('title', 'Detalles del Tenant')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="h-16 w-16 bg-white/20 rounded-xl flex items-center justify-center">
                    <span class="text-3xl font-bold">{{ substr($tenant->name, 0, 1) }}</span>
                </div>
                <div>
                    <h1 class="text-3xl font-bold">{{ $tenant->name }}</h1>
                    <p class="text-purple-200 mt-1">{{ $tenant->slug }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.tenants.edit', $tenant) }}" class="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-purple-50 font-semibold">
                    Editar
                </a>
                <a href="{{ route('admin.tenants.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg">
                    ← Volver
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Usuarios</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $tenant->users()->count() }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Clientes</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $tenant->clients()->count() }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Contactos</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($tenant->contacts()->count()) }}</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Campañas</p>
                    <p class="text-3xl font-bold text-gray-800 mt-1">{{ $tenant->campaigns()->count() }}</p>
                </div>
                <div class="bg-amber-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información del Tenant -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Detalles Básicos -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h3 class="text-xl font-bold text-gray-800 mb-6">Información del Tenant</h3>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Nombre</p>
                        <p class="font-semibold text-gray-900">{{ $tenant->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">Slug</p>
                        <p class="font-semibold text-gray-900">{{ $tenant->slug }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">Dominio</p>
                        <p class="font-semibold text-gray-900">{{ $tenant->domain ?? 'No configurado' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">Estado</p>
                        @if($tenant->status === 'active')
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                Activo
                            </span>
                        @elseif($tenant->status === 'suspended')
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                Suspendido
                            </span>
                        @else
                            <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                {{ ucfirst($tenant->status) }}
                            </span>
                        @endif
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">Creado</p>
                        <p class="font-semibold text-gray-900">{{ $tenant->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 mb-1">Última actualización</p>
                        <p class="font-semibold text-gray-900">{{ $tenant->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Suscripción Actual -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <h3 class="text-xl font-bold text-gray-800 mb-6">Suscripción</h3>

                @php
                    $subscription = $tenant->currentSubscription();
                @endphp

                @if($subscription)
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg">
                            <div>
                                <p class="text-sm text-gray-600">Plan Actual</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $subscription->plan->name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Precio</p>
                                <p class="text-xl font-bold text-indigo-600">
                                    ${{ number_format($subscription->billing_cycle === 'monthly' ? $subscription->plan->price_monthly : $subscription->plan->price_yearly, 2) }}
                                    <span class="text-sm text-gray-500">/{{ $subscription->billing_cycle === 'monthly' ? 'mes' : 'año' }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Estado</p>
                                <span class="px-3 py-1 inline-flex text-xs font-semibold rounded-full
                                    {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $subscription->status === 'trial' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $subscription->status === 'canceled' ? 'bg-red-100 text-red-800' : '' }}
                                    {{ $subscription->status === 'past_due' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                    {{ $subscription->getStatusLabel() }}
                                </span>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500 mb-1">Tipo de Facturación</p>
                                <p class="font-semibold text-gray-900">{{ ucfirst($subscription->billing_cycle) }}</p>
                            </div>

                            @if($subscription->trial_ends_at)
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Prueba Termina</p>
                                <p class="font-semibold text-gray-900">{{ $subscription->trial_ends_at->format('d/m/Y') }}</p>
                            </div>
                            @endif

                            <div>
                                <p class="text-sm text-gray-500 mb-1">Próximo Pago</p>
                                <p class="font-semibold text-gray-900">{{ $subscription->current_period_end->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">No hay suscripción activa</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Trial Info -->
            @if($tenant->trial_ends_at)
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Periodo de Prueba</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Finaliza</span>
                        <span class="font-semibold text-gray-900">{{ $tenant->trial_ends_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Días restantes</span>
                        <span class="font-semibold text-blue-600">{{ $tenant->trial_ends_at->diffInDays() }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Configuración -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Configuración</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">Zona Horaria</span>
                        <p class="font-semibold text-gray-900">{{ $tenant->settings['timezone'] ?? 'No configurada' }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Idioma</span>
                        <p class="font-semibold text-gray-900">{{ $tenant->settings['language'] ?? 'No configurado' }}</p>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Acciones</h3>
                <div class="space-y-2">
                    <form action="{{ route('admin.tenants.destroy', $tenant) }}" method="POST"
                        onsubmit="return confirm('¿Estás seguro? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium">
                            Eliminar Tenant
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

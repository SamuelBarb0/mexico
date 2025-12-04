@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Planes de Suscripción</h1>
        <p class="text-xl text-gray-600">Elige el plan perfecto para tu negocio</p>
    </div>

    <!-- Current Subscription Banner -->
    @if($currentSubscription)
    <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-blue-900">Plan Actual: {{ $currentSubscription->plan->name }}</h3>
                <p class="text-blue-700 mt-1">
                    @if($currentSubscription->isOnTrial())
                        Periodo de prueba - {{ $currentSubscription->trialDaysRemaining() }} días restantes
                    @else
                        {{ ucfirst($currentSubscription->billing_cycle) }} - Renovación: {{ $currentSubscription->getNextBillingDate()?->format('d/m/Y') }}
                    @endif
                </p>
            </div>
            <a href="{{ route('subscriptions.index') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Gestionar Suscripción
            </a>
        </div>
    </div>
    @endif

    <!-- Pricing Cards -->
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
        @foreach($plans as $plan)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden {{ $currentSubscription && $currentSubscription->plan->id === $plan->id ? 'ring-2 ring-blue-500' : '' }}">
            <!-- Plan Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8 text-white">
                <h3 class="text-2xl font-bold mb-2">{{ $plan->name }}</h3>
                <div class="mb-4">
                    @if($plan->isFree())
                        <div class="text-4xl font-bold">Gratis</div>
                        <div class="text-blue-100">Para siempre</div>
                    @else
                        <div class="text-4xl font-bold">${{ number_format($plan->price_monthly, 0) }}</div>
                        <div class="text-blue-100">USD/mes</div>
                        @if($plan->price_yearly > 0)
                            <div class="text-sm mt-2 text-blue-100">
                                o ${{ number_format($plan->price_yearly, 0) }}/año
                                <span class="bg-blue-800 px-2 py-1 rounded text-xs ml-1">
                                    Ahorra {{ $plan->getYearlySavingsPercentage() }}%
                                </span>
                            </div>
                        @endif
                    @endif
                </div>
                <p class="text-blue-100 text-sm">{{ $plan->description }}</p>
            </div>

            <!-- Plan Features -->
            <div class="px-6 py-8">
                <!-- Limits -->
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Límites:</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $plan->max_users }} {{ $plan->max_users === 1 ? 'usuario' : 'usuarios' }}
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            {{ number_format($plan->max_contacts) }} contactos
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $plan->max_campaigns }} campañas
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            {{ number_format($plan->max_messages_per_month) }} mensajes/mes
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $plan->max_storage_mb }}MB almacenamiento
                        </li>
                    </ul>
                </div>

                <!-- Features -->
                @if(count($plan->features) > 0)
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Características:</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        @foreach($plan->features as $feature)
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span>{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Trial Info -->
                @if($plan->has_trial && $plan->trial_days > 0)
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-3">
                    <p class="text-sm text-green-800 text-center">
                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                        </svg>
                        @if($plan->trial_days === -1)
                            Prueba ilimitada
                        @else
                            {{ $plan->trial_days }} días de prueba gratis
                        @endif
                    </p>
                </div>
                @endif

                <!-- CTA Button -->
                @if($currentSubscription && $currentSubscription->plan->id === $plan->id)
                    <button disabled class="w-full bg-gray-300 text-gray-600 px-6 py-3 rounded-lg font-semibold cursor-not-allowed">
                        Plan Actual
                    </button>
                @else
                    <a href="{{ route('subscriptions.checkout', $plan) }}"
                       class="block w-full bg-blue-600 text-white text-center px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        @if($plan->isFree())
                            Comenzar Gratis
                        @else
                            Seleccionar Plan
                        @endif
                    </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- FAQ or Additional Info -->
    <div class="mt-16 text-center">
        <p class="text-gray-600">
            ¿Necesitas ayuda para elegir?
            <a href="#" class="text-blue-600 hover:underline">Contáctanos</a>
        </p>
    </div>
</div>
@endsection

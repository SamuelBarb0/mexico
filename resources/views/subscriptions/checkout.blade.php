@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout - {{ $plan->name }}</h1>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Plan Summary -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Resumen del Plan</h2>

            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-900">{{ $plan->name }}</h3>
                <p class="text-gray-600 mt-2">{{ $plan->description }}</p>
            </div>

            <!-- Pricing -->
            @if(!$plan->isFree())
            <div class="mb-6" id="pricing-display">
                <div class="flex items-baseline mb-2">
                    <span class="text-4xl font-bold text-gray-900" id="price-amount">${{ number_format($plan->price_monthly, 0) }}</span>
                    <span class="text-gray-600 ml-2" id="price-period">/mes</span>
                </div>
                @if($plan->price_yearly > 0)
                <p class="text-sm text-green-600 font-semibold">
                    Ahorra {{ $plan->getYearlySavingsPercentage() }}% con facturación anual
                </p>
                @endif
            </div>
            @endif

            <!-- Trial Info -->
            @if($plan->has_trial && $plan->trial_days > 0)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <p class="text-green-800 font-semibold">
                    @if($plan->trial_days === -1)
                        ✓ Prueba ilimitada incluida
                    @else
                        ✓ {{ $plan->trial_days }} días de prueba gratis
                    @endif
                </p>
                @if(!$plan->isFree())
                <p class="text-sm text-green-700 mt-1">No se te cobrará hasta que termine el período de prueba</p>
                @endif
            </div>
            @endif

            <!-- Features -->
            <div>
                <h4 class="font-semibold text-gray-900 mb-3">Incluye:</h4>
                <ul class="space-y-2">
                    <li class="flex items-center text-sm text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        {{ $plan->max_users }} {{ $plan->max_users === 1 ? 'usuario' : 'usuarios' }}
                    </li>
                    <li class="flex items-center text-sm text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        {{ number_format($plan->max_contacts) }} contactos
                    </li>
                    <li class="flex items-center text-sm text-gray-600">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        {{ number_format($plan->max_messages_per_month) }} mensajes/mes
                    </li>
                    @foreach($plan->features as $feature)
                    <li class="flex items-center text-sm text-gray-600">
                        <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Checkout Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de Pago</h2>

            <form action="{{ route('subscriptions.subscribe', $plan) }}" method="POST" id="payment-form">
                @csrf

                <!-- Billing Cycle -->
                @if(!$plan->isFree() && $plan->price_yearly > 0)
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ciclo de Facturación</label>
                    <div class="space-y-2">
                        <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="billing_cycle" value="monthly" checked class="mr-3" onchange="updatePricing()">
                            <div class="flex-1">
                                <span class="font-semibold">Mensual</span>
                                <span class="text-gray-600 ml-2">${{ number_format($plan->price_monthly, 0) }}/mes</span>
                            </div>
                        </label>
                        <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="billing_cycle" value="yearly" class="mr-3" onchange="updatePricing()">
                            <div class="flex-1">
                                <span class="font-semibold">Anual</span>
                                <span class="text-gray-600 ml-2">${{ number_format($plan->price_yearly, 0) }}/año</span>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs ml-2">
                                    Ahorra {{ $plan->getYearlySavingsPercentage() }}%
                                </span>
                            </div>
                        </label>
                    </div>
                </div>
                @else
                <input type="hidden" name="billing_cycle" value="monthly">
                @endif

                <!-- Payment Method (Only for paid plans) -->
                @if(!$plan->isFree())
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Método de Pago</label>

                    @php
                        $savedMethods = auth()->user()->tenant->paymentMethods()->active()->get();
                    @endphp

                    <!-- Saved Payment Methods -->
                    @if($savedMethods->count() > 0)
                    <div class="space-y-3 mb-4">
                        @foreach($savedMethods as $method)
                        <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition-colors payment-method-option">
                            <input type="radio" name="payment_method_type" value="saved"
                                data-payment-method-id="{{ $method->stripe_payment_method_id }}"
                                class="mr-3 saved-method-radio"
                                {{ $method->is_default ? 'checked' : '' }}>
                            <div class="flex items-center flex-1">
                                <!-- Card Icon -->
                                <div class="h-10 w-16 bg-gradient-to-br from-gray-700 to-gray-900 rounded flex items-center justify-center mr-3">
                                    @if(strtolower($method->brand) === 'visa')
                                        <span class="text-white font-bold text-xs">VISA</span>
                                    @elseif(strtolower($method->brand) === 'mastercard')
                                        <div class="flex space-x-[-4px]">
                                            <div class="h-4 w-4 rounded-full bg-red-500"></div>
                                            <div class="h-4 w-4 rounded-full bg-yellow-500"></div>
                                        </div>
                                    @else
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">
                                        {{ ucfirst($method->brand) }} •••• {{ $method->last4 }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Expira {{ str_pad($method->exp_month, 2, '0', STR_PAD_LEFT) }}/{{ $method->exp_year }}
                                    </div>
                                </div>
                                @if($method->is_default)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                        Predeterminada
                                    </span>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @endif

                    <!-- New Card Option -->
                    <label class="flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500 transition-colors payment-method-option mb-3">
                        <input type="radio" name="payment_method_type" value="new" class="mr-3" id="new-card-radio" {{ $savedMethods->count() === 0 ? 'checked' : '' }}>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="font-medium text-gray-900">Usar nueva tarjeta</span>
                        </div>
                    </label>

                    <!-- New Card Form (Hidden by default if there are saved methods) -->
                    <div id="new-card-form" class="{{ $savedMethods->count() > 0 ? 'hidden' : '' }}">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Información de la Tarjeta</label>
                            <div id="card-element" class="p-3 border border-gray-300 rounded-lg bg-white">
                                <!-- Stripe Card Element will be inserted here -->
                            </div>
                            <div id="card-errors" class="text-red-600 text-sm mt-2"></div>

                            <!-- Save Card Option -->
                            <div class="mt-3 flex items-center">
                                <input type="checkbox" name="save_payment_method" id="save_payment_method" value="1" checked
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="save_payment_method" class="ml-2 block text-sm text-gray-700">
                                    Guardar tarjeta para futuros pagos
                                </label>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="payment_method_id" id="payment-method-id">
                </div>
                @else
                <input type="hidden" name="payment_method_id" value="">
                @endif

                <!-- Submit Button -->
                <button type="submit" id="submit-button" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors cursor-pointer">
                    @if($plan->isFree())
                        Activar Plan Gratuito
                    @else
                        @if($plan->has_trial && $plan->trial_days > 0)
                            Iniciar Prueba Gratis
                        @else
                            Suscribirse Ahora
                        @endif
                    @endif
                </button>

                <p class="text-xs text-gray-500 text-center mt-4">
                    Al hacer clic, aceptas nuestros términos de servicio y política de privacidad.
                </p>
            </form>
        </div>
    </div>
</div>

@if(!$plan->isFree())
<!-- Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('{{ config('services.stripe.key') }}');
const elements = stripe.elements();
let cardElement = null;
let cardMounted = false;

// Initialize card element when needed
function initializeCardElement() {
    if (!cardMounted) {
        cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#32325d',
                    fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            }
        });
        cardElement.mount('#card-element');
        cardMounted = true;

        // Handle card errors
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    }
}

// Show/hide new card form based on selection
document.querySelectorAll('input[name="payment_method_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const newCardForm = document.getElementById('new-card-form');
        const newCardRadio = document.getElementById('new-card-radio');

        if (newCardRadio.checked) {
            newCardForm.classList.remove('hidden');
            initializeCardElement();
        } else {
            newCardForm.classList.add('hidden');
        }
    });
});

// Initialize card element if new card is selected by default
if (document.getElementById('new-card-radio').checked) {
    initializeCardElement();
}

// Handle form submission
const form = document.getElementById('payment-form');
const submitButton = document.getElementById('submit-button');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    submitButton.disabled = true;
    submitButton.textContent = 'Procesando...';

    const paymentMethodType = document.querySelector('input[name="payment_method_type"]:checked').value;

    if (paymentMethodType === 'saved') {
        // Use saved payment method
        const selectedMethod = document.querySelector('input.saved-method-radio:checked');
        document.getElementById('payment-method-id').value = selectedMethod.dataset.paymentMethodId;
        form.submit();
    } else {
        // Create new payment method
        const {paymentMethod, error} = await stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
        });

        if (error) {
            document.getElementById('card-errors').textContent = error.message;
            submitButton.disabled = false;
            submitButton.textContent = '@if($plan->has_trial && $plan->trial_days > 0) Iniciar Prueba Gratis @else Suscribirse Ahora @endif';
        } else {
            document.getElementById('payment-method-id').value = paymentMethod.id;
            form.submit();
        }
    }
});

// Update pricing display
function updatePricing() {
    const cycle = document.querySelector('input[name="billing_cycle"]:checked').value;
    const priceAmount = document.getElementById('price-amount');
    const pricePeriod = document.getElementById('price-period');

    if (cycle === 'monthly') {
        priceAmount.textContent = '${{ number_format($plan->price_monthly, 0) }}';
        pricePeriod.textContent = '/mes';
    } else {
        priceAmount.textContent = '${{ number_format($plan->price_yearly, 0) }}';
        pricePeriod.textContent = '/año';
    }
}

// Add visual feedback for payment method selection
document.querySelectorAll('.payment-method-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-method-option').forEach(option => {
            option.classList.remove('border-indigo-500', 'bg-indigo-50');
            option.classList.add('border-gray-300');
        });
        if (this.checked) {
            this.closest('.payment-method-option').classList.remove('border-gray-300');
            this.closest('.payment-method-option').classList.add('border-indigo-500', 'bg-indigo-50');
        }
    });
});

// Set initial selection styling
const checkedRadio = document.querySelector('.payment-method-option input[type="radio"]:checked');
if (checkedRadio) {
    checkedRadio.closest('.payment-method-option').classList.remove('border-gray-300');
    checkedRadio.closest('.payment-method-option').classList.add('border-indigo-500', 'bg-indigo-50');
}
</script>
@endif
@endsection

@extends('layouts.app')

@section('title', 'Métodos de Pago')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-2xl shadow-xl p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Métodos de Pago</h1>
                <p class="text-indigo-100">Gestiona tus tarjetas y métodos de pago</p>
            </div>
            <button onclick="openAddCardModal()" class="bg-white text-indigo-600 px-6 py-3 rounded-lg hover:bg-indigo-50 font-semibold shadow-lg transition-all cursor-pointer">
                + Agregar Tarjeta
            </button>
        </div>
    </div>

    <!-- Payment Methods List -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        @if($paymentMethods->count() > 0)
        <div class="divide-y divide-gray-200">
            @foreach($paymentMethods as $method)
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Card Icon -->
                        <div class="h-14 w-20 bg-gradient-to-br from-gray-700 to-gray-900 rounded-lg flex items-center justify-center shadow-md">
                            @if(strtolower($method->brand) === 'visa')
                                <svg class="h-8 w-8 text-white" viewBox="0 0 48 16" fill="currentColor">
                                    <text x="0" y="12" font-family="Arial" font-size="12" font-weight="bold">VISA</text>
                                </svg>
                            @elseif(strtolower($method->brand) === 'mastercard')
                                <div class="flex space-x-[-4px]">
                                    <div class="h-6 w-6 rounded-full bg-red-500"></div>
                                    <div class="h-6 w-6 rounded-full bg-yellow-500"></div>
                                </div>
                            @elseif(strtolower($method->brand) === 'amex')
                                <svg class="h-8 w-8 text-blue-400" viewBox="0 0 48 16" fill="currentColor">
                                    <text x="0" y="12" font-family="Arial" font-size="10" font-weight="bold">AMEX</text>
                                </svg>
                            @else
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            @endif
                        </div>

                        <!-- Card Info -->
                        <div>
                            <div class="flex items-center space-x-2">
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ ucfirst($method->brand) }} •••• {{ $method->last4 }}
                                </p>
                                @if($method->is_default)
                                    <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">
                                        Predeterminada
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                Expira {{ str_pad($method->exp_month, 2, '0', STR_PAD_LEFT) }}/{{ $method->exp_year }}
                                @if($method->isExpired())
                                    <span class="text-red-600 font-semibold">• Expirada</span>
                                @elseif($method->isExpiringSoon())
                                    <span class="text-yellow-600 font-semibold">• Expira pronto</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-3">
                        @if(!$method->is_default)
                            <form action="{{ route('payment-methods.set-default', $method) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors cursor-pointer">
                                    Hacer predeterminada
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('payment-methods.destroy', $method) }}" method="POST"
                            onsubmit="return confirm('¿Estás seguro de eliminar este método de pago?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                                {{ $method->is_default ? 'disabled' : '' }}>
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-16">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No hay métodos de pago</h3>
            <p class="mt-2 text-sm text-gray-500">Agrega una tarjeta para gestionar tus pagos.</p>
            <div class="mt-6">
                <button onclick="openAddCardModal()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 cursor-pointer">
                    + Agregar Tarjeta
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Info Card -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-900">Información de Seguridad</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Tus datos de pago están protegidos con cifrado SSL</li>
                        <li>Nunca almacenamos el número completo de tu tarjeta</li>
                        <li>Procesamos pagos a través de Stripe, un proveedor PCI-DSS certificado</li>
                        <li>Puedes eliminar tus métodos de pago en cualquier momento</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Card Modal -->
<div id="addCardModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-8 border w-full max-w-md shadow-2xl rounded-2xl bg-white">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-900">Agregar Tarjeta</h3>
            <button onclick="closeAddCardModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="payment-form" action="{{ route('payment-methods.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <!-- Stripe Card Element -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Información de la Tarjeta
                    </label>
                    <div id="card-element" class="p-3 border border-gray-300 rounded-lg"></div>
                    <div id="card-errors" class="text-red-600 text-sm mt-2"></div>
                </div>

                <!-- Cardholder Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Titular
                    </label>
                    <input type="text" name="cardholder_name" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <!-- Set as Default -->
                <div class="flex items-center">
                    <input type="checkbox" name="set_as_default" id="set_as_default" value="1"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="set_as_default" class="ml-2 block text-sm text-gray-700">
                        Establecer como método de pago predeterminado
                    </label>
                </div>

                <input type="hidden" name="payment_method_id" id="payment_method_id">

                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="closeAddCardModal()"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" id="submit-button"
                        class="flex-1 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg hover:from-indigo-700 hover:to-purple-700 font-medium cursor-pointer">
                        Agregar Tarjeta
                    </button>
                </div>
            </div>
        </form>

        <!-- Test Cards Info -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <p class="text-xs font-semibold text-gray-700 mb-2">Tarjetas de prueba Stripe:</p>
            <ul class="text-xs text-gray-600 space-y-1">
                <li>• 4242 4242 4242 4242 - Visa exitosa</li>
                <li>• 4000 0000 0000 9995 - Tarjeta declinada</li>
                <li>• Fecha: Cualquier fecha futura</li>
                <li>• CVC: Cualquier 3 dígitos</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Initialize Stripe
    const stripe = Stripe('{{ config('services.stripe.key') }}');
    const elements = stripe.elements();
    const cardElement = elements.create('card', {
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

    function openAddCardModal() {
        document.getElementById('addCardModal').classList.remove('hidden');
        if (!cardElement._parent) {
            cardElement.mount('#card-element');
        }
    }

    function closeAddCardModal() {
        document.getElementById('addCardModal').classList.add('hidden');
    }

    // Handle card errors
    cardElement.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    // Handle form submission
    const form = document.getElementById('payment-form');
    const submitButton = document.getElementById('submit-button');

    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        submitButton.disabled = true;
        submitButton.textContent = 'Procesando...';

        const {paymentMethod, error} = await stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
            billing_details: {
                name: form.cardholder_name.value,
            }
        });

        if (error) {
            document.getElementById('card-errors').textContent = error.message;
            submitButton.disabled = false;
            submitButton.textContent = 'Agregar Tarjeta';
        } else {
            document.getElementById('payment_method_id').value = paymentMethod.id;
            form.submit();
        }
    });
</script>
@endpush
@endsection

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Gestión de Suscripción</h1>

    <!-- Current Subscription -->
    @if($currentSubscription)
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">Plan Actual</h2>
            <span class="px-4 py-2 rounded-full text-sm font-semibold
                {{ $currentSubscription->isOnTrial() ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $currentSubscription->isActive() && !$currentSubscription->isOnTrial() ? 'bg-green-100 text-green-800' : '' }}
                {{ $currentSubscription->isCanceled() ? 'bg-red-100 text-red-800' : '' }}
                {{ $currentSubscription->isPastDue() ? 'bg-yellow-100 text-yellow-800' : '' }}">
                {{ $currentSubscription->getStatusLabel() }}
            </span>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Plan Info -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $currentSubscription->plan->name }}</h3>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Precio:</dt>
                        <dd class="font-semibold text-gray-900">${{ number_format($currentSubscription->getCurrentPrice(), 2) }}/{{ $currentSubscription->isMonthly() ? 'mes' : 'año' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Ciclo de facturación:</dt>
                        <dd class="font-semibold text-gray-900">{{ ucfirst($currentSubscription->billing_cycle) }}</dd>
                    </div>
                    @if($currentSubscription->isOnTrial())
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Prueba termina:</dt>
                        <dd class="font-semibold text-blue-600">{{ $currentSubscription->trial_ends_at?->format('d/m/Y') }} ({{ $currentSubscription->trialDaysRemaining() }} días)</dd>
                    </div>
                    @endif
                    @if($currentSubscription->getNextBillingDate())
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Próxima facturación:</dt>
                        <dd class="font-semibold text-gray-900">{{ $currentSubscription->getNextBillingDate()->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                    @if($currentSubscription->isCanceled() && $currentSubscription->ends_at)
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Termina el:</dt>
                        <dd class="font-semibold text-red-600">{{ $currentSubscription->ends_at->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Usage Stats -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Uso Actual</h3>
                <div class="space-y-4">
                    <!-- Users -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Usuarios</span>
                            <span class="font-semibold">{{ $tenant->users()->count() }} / {{ $currentSubscription->plan->max_users }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, ($tenant->users()->count() / $currentSubscription->plan->max_users) * 100) }}%"></div>
                        </div>
                    </div>

                    <!-- Contacts -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Contactos</span>
                            <span class="font-semibold">{{ number_format($tenant->contacts()->count()) }} / {{ number_format($currentSubscription->plan->max_contacts) }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, ($tenant->contacts()->count() / $currentSubscription->plan->max_contacts) * 100) }}%"></div>
                        </div>
                    </div>

                    <!-- Campaigns -->
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Campañas</span>
                            <span class="font-semibold">{{ $tenant->campaigns()->count() }} / {{ $currentSubscription->plan->max_campaigns }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(100, ($tenant->campaigns()->count() / $currentSubscription->plan->max_campaigns) * 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex gap-4">
            <a href="{{ route('subscriptions.plans') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Cambiar Plan
            </a>

            @if($currentSubscription->canResume())
            <form action="{{ route('subscriptions.resume') }}" method="POST">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 cursor-pointer">
                    Reanudar Suscripción
                </button>
            </form>
            @endif

            @if($currentSubscription->canCancel())
            <button onclick="confirmCancel()" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 cursor-pointer">
                Cancelar Suscripción
            </button>
            @endif
        </div>
    </div>
    @else
    <!-- No Subscription -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-yellow-900 mb-2">No tienes una suscripción activa</h3>
        <p class="text-yellow-700 mb-4">Para acceder a todas las funcionalidades, selecciona un plan de suscripción.</p>
        <a href="{{ route('subscriptions.plans') }}" class="bg-yellow-600 text-white px-6 py-2 rounded-lg hover:bg-yellow-700 inline-block">
            Ver Planes Disponibles
        </a>
    </div>
    @endif

    <!-- Payment Method -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">Método de Pago</h2>
            <a href="{{ route('payment-methods.index') }}" class="text-blue-600 hover:underline">
                Gestionar Métodos de Pago
            </a>
        </div>

        @if($paymentMethod)
        <div class="flex items-center space-x-4">
            <div class="text-3xl">{!! $paymentMethod->getBrandIcon() !!}</div>
            <div>
                <p class="font-semibold text-gray-900">{{ $paymentMethod->getDisplayName() }}</p>
                <p class="text-sm text-gray-600">Expira: {{ $paymentMethod->getExpirationDisplay() }}</p>
            </div>
            @if($paymentMethod->isExpiringSoon())
            <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">
                Expira pronto
            </span>
            @endif
        </div>
        @else
        <p class="text-gray-600">No hay métodos de pago registrados.</p>
        <a href="{{ route('payment-methods.index') }}" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
            Agregar Método de Pago
        </a>
        @endif
    </div>

    <!-- Invoices -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Historial de Facturas</h2>

        @if($invoices->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($invoices as $invoice)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $invoice->invoice_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $invoice->invoice_date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $invoice->getFormattedTotal() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $invoice->getStatusBadgeClass() }}">
                                {{ $invoice->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($invoice->canView())
                            <a href="{{ $invoice->stripe_hosted_invoice_url }}" target="_blank" class="text-blue-600 hover:underline mr-3">
                                Ver
                            </a>
                            @endif
                            @if($invoice->canDownload())
                            <a href="{{ route('subscriptions.invoice.download', $invoice) }}" class="text-blue-600 hover:underline">
                                Descargar
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-600">No hay facturas disponibles.</p>
        @endif
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<script>
function confirmCancel() {
    if (confirm('¿Estás seguro de que deseas cancelar tu suscripción? Seguirás teniendo acceso hasta el final del período actual.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('subscriptions.cancel') }}';
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection

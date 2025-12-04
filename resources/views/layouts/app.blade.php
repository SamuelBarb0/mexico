<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema Multitenant')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <nav class="relative z-40 bg-white/10 backdrop-blur-md text-white shadow-2xl border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold flex items-center">
                        <svg class="w-8 h-8 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        Sistema Multitenant
                    </h1>
                    @auth
                        @if(auth()->user()->isPlatformAdmin())
                            <span class="ml-4 text-sm bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-lg border border-white/30 font-semibold">
                                Platform Admin
                            </span>
                        @else
                            @php
                                $tenant = auth()->user()->tenant;
                                $currentSub = $tenant ? $tenant->currentSubscription() : null;
                                $badgeClass = 'bg-white/20 border-white/30';
                                $badgeText = $tenant ? $tenant->name : 'Sin Tenant';

                                if ($tenant) {
                                    if (!$currentSub) {
                                        $badgeClass = 'bg-yellow-500/30 border-yellow-300/50';
                                        $badgeText .= ' • Sin Plan';
                                    } elseif ($currentSub->isOnTrial()) {
                                        $badgeClass = 'bg-blue-500/30 border-blue-300/50';
                                        $badgeText .= ' • ' . $currentSub->plan->name . ' (Prueba)';
                                    } elseif ($currentSub->isPastDue()) {
                                        $badgeClass = 'bg-red-500/30 border-red-300/50 animate-pulse';
                                        $badgeText .= ' • Pago Pendiente';
                                    } else {
                                        $badgeClass = 'bg-green-500/30 border-green-300/50';
                                        $badgeText .= ' • ' . $currentSub->plan->name;
                                    }
                                } else {
                                    $badgeClass = 'bg-red-500/30 border-red-300/50';
                                }
                            @endphp
                            <span class="ml-4 text-sm {{ $badgeClass }} backdrop-blur-sm px-3 py-1.5 rounded-lg border font-semibold">
                                {{ $badgeText }}
                            </span>
                        @endif
                    @endauth
                </div>

                @auth
                <div class="flex items-center space-x-2">
                    <a href="{{ route('dashboard') }}" class="hover:bg-white/20 backdrop-blur-sm px-3 py-2 rounded-lg transition-all duration-200">Dashboard</a>
                    <a href="{{ route('clients.index') }}" class="hover:bg-white/20 backdrop-blur-sm px-3 py-2 rounded-lg transition-all duration-200">Clientes</a>
                    <a href="{{ route('contacts.index') }}" class="hover:bg-white/20 backdrop-blur-sm px-3 py-2 rounded-lg transition-all duration-200">Contactos</a>
                    <a href="{{ route('campaigns.index') }}" class="hover:bg-white/20 backdrop-blur-sm px-3 py-2 rounded-lg transition-all duration-200">Campañas</a>
                    <a href="{{ route('waba-accounts.index') }}" class="hover:bg-white/20 backdrop-blur-sm px-3 py-2 rounded-lg transition-all duration-200">WABA</a>

                    @if(!auth()->user()->isPlatformAdmin() && auth()->user()->tenant)
                        <!-- Subscription Dropdown -->
                        <div class="relative group z-50">
                            <button class="hover:bg-white/20 backdrop-blur-sm px-3 py-2 rounded-lg flex items-center transition-all duration-200">
                                Suscripción
                                @php
                                    $navTenant = auth()->user()->tenant;
                                    $sub = $navTenant ? $navTenant->currentSubscription() : null;
                                    $showAlert = false;
                                    if ($sub) {
                                        // Check if near any limit (>80%)
                                        $nearLimit = $sub->getLimitPercentage('users') >= 80 ||
                                                    $sub->getLimitPercentage('contacts') >= 80 ||
                                                    $sub->getLimitPercentage('campaigns') >= 80 ||
                                                    $sub->getLimitPercentage('waba_accounts') >= 80;

                                        // Check if trial ending soon (<7 days)
                                        $trialEndingSoon = $sub->isOnTrial() && $sub->trialDaysRemaining() <= 7 && $sub->trialDaysRemaining() > 0;

                                        $showAlert = $nearLimit || $trialEndingSoon || $sub->isPastDue();
                                    } else {
                                        $showAlert = true; // No subscription
                                    }
                                @endphp
                                @if($showAlert)
                                    <span class="absolute top-1 right-1 flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                    </span>
                                @endif
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="absolute right-0 mt-0 pt-2 w-56 hidden group-hover:block z-50">
                                <div class="bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden">
                                <a href="{{ route('subscriptions.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors font-medium">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Mi Suscripción
                                    </div>
                                </a>
                                <a href="{{ route('subscriptions.plans') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors font-medium border-t border-gray-100">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                        Ver Planes
                                    </div>
                                </a>
                                <a href="{{ route('payment-methods.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors font-medium border-t border-gray-100">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        Métodos de Pago
                                    </div>
                                </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->isPlatformAdmin())
                        <div class="relative group z-50">
                            <button class="bg-white/20 backdrop-blur-sm hover:bg-white/30 px-3 py-2 rounded-lg border border-white/30 transition-all duration-200 flex items-center">
                                Admin
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="absolute right-0 mt-0 pt-2 w-56 hidden group-hover:block z-50">
                                <div class="bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden">
                                <a href="{{ route('admin.tenants.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition-colors font-medium">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Gestionar Tenants
                                    </div>
                                </a>
                                <a href="{{ route('admin.plans.index') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition-colors font-medium border-t border-gray-100">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                        </svg>
                                        Gestionar Planes
                                    </div>
                                </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <span class="text-sm font-semibold bg-white/10 px-3 py-2 rounded-lg">{{ auth()->user()->name }}</span>

                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-500/80 hover:bg-red-500 backdrop-blur-sm px-4 py-2 rounded-lg transition-all duration-200 font-semibold shadow-lg">
                            Salir
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <main class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="bg-green-500/90 backdrop-blur-sm border border-green-300/50 text-white px-6 py-4 rounded-xl mb-6 shadow-xl flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-500/90 backdrop-blur-sm border border-red-300/50 text-white px-6 py-4 rounded-xl mb-6 shadow-xl flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema Multitenant')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&family=lexend:400,500,600,700" rel="stylesheet" />
</head>
<body class="bg-gradient-to-br from-primary-50 via-white to-secondary-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        @auth
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-primary-600 via-primary-700 to-primary-800 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 -translate-x-full shadow-2xl">
            <div class="flex flex-col h-full">
                <!-- Logo & Header -->
                <div class="flex items-center justify-between px-6 py-6 border-b border-white/10">
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center justify-center w-12 h-12 bg-white/10 backdrop-blur-md rounded-xl shadow-lg border border-white/20">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-display font-bold text-white">WhatsApp</h1>
                            <p class="text-xs text-primary-200">Manager</p>
                        </div>
                    </div>
                    <button id="closeSidebar" class="lg:hidden text-white hover:bg-white/10 p-2 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Tenant/Admin Badge -->
                <div class="px-6 py-4 border-b border-white/10">
                    @if(auth()->user()->isPlatformAdmin())
                        <div class="flex items-center space-x-2 px-4 py-3 bg-white/10 backdrop-blur-md rounded-xl shadow-lg border border-white/20">
                            <svg class="w-5 h-5 text-warning-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="text-sm font-bold text-white">Platform Admin</span>
                        </div>
                    @else
                        @php
                            $tenant = auth()->user()->tenant;
                            $currentSub = $tenant ? $tenant->currentSubscription() : null;
                            $badgeClass = 'bg-white/10';
                            $badgeText = $tenant ? $tenant->name : 'Sin Tenant';
                            $statusText = 'Sin Plan';
                            $iconColor = 'text-white/60';

                            if ($tenant && $currentSub) {
                                if ($currentSub->isOnTrial()) {
                                    $badgeClass = 'bg-white/10';
                                    $statusText = $currentSub->plan->name . ' (Prueba)';
                                    $iconColor = 'text-primary-300';
                                } elseif ($currentSub->isPastDue()) {
                                    $badgeClass = 'bg-danger-500/20';
                                    $statusText = 'Pago Pendiente';
                                    $iconColor = 'text-danger-300';
                                } else {
                                    $badgeClass = 'bg-white/10';
                                    $statusText = $currentSub->plan->name;
                                    $iconColor = 'text-success-300';
                                }
                            }
                        @endphp
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-primary-200 uppercase tracking-wider">Tenant</span>
                                @if($currentSub && ($currentSub->isPastDue() || ($currentSub->isOnTrial() && $currentSub->trialDaysRemaining() <= 7)))
                                    <span class="flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-danger-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-danger-400"></span>
                                    </span>
                                @endif
                            </div>
                            <div class="{{ $badgeClass }} backdrop-blur-md rounded-xl px-4 py-3 shadow-lg border border-white/20">
                                <div class="flex items-start space-x-3">
                                    <svg class="w-5 h-5 {{ $iconColor }} mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-white truncate">{{ $badgeText }}</p>
                                        <p class="text-xs text-white/80 truncate mt-0.5">{{ $statusText }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-5 py-6 space-y-1.5 overflow-y-auto scrollbar-thin">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <div class="pt-5 pb-2">
                        <p class="px-3 text-xs font-bold text-primary-200 uppercase tracking-wider">Gestión</p>
                    </div>

                    <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Clientes</span>
                    </a>

                    <a href="{{ route('contacts.index') }}" class="nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>Contactos</span>
                    </a>

                    <a href="{{ route('inbox.index') }}" class="nav-link {{ request()->routeIs('inbox.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Inbox</span>
                    </a>

                    <a href="{{ route('campaigns.index') }}" class="nav-link {{ request()->routeIs('campaigns.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                        <span>Campañas</span>
                    </a>

                    <a href="{{ route('templates.index') }}" class="nav-link {{ request()->routeIs('templates.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                        <span>Plantillas</span>
                    </a>

                    <a href="{{ route('waba-accounts.index') }}" class="nav-link {{ request()->routeIs('waba-accounts.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span>WABA Accounts</span>
                    </a>

                    @if(!auth()->user()->isPlatformAdmin() && auth()->user()->tenant)
                        <div class="pt-5 pb-2">
                            <p class="px-3 text-xs font-bold text-primary-200 uppercase tracking-wider">Suscripción</p>
                        </div>

                        <a href="{{ route('subscriptions.index') }}" class="nav-link {{ request()->routeIs('subscriptions.index') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Mi Suscripción</span>
                        </a>

                        <a href="{{ route('subscriptions.plans') }}" class="nav-link {{ request()->routeIs('subscriptions.plans') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            <span>Ver Planes</span>
                        </a>

                        <a href="{{ route('payment-methods.index') }}" class="nav-link {{ request()->routeIs('payment-methods.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span>Métodos de Pago</span>
                        </a>
                    @endif

                    @if(auth()->user()->isPlatformAdmin())
                        <div class="pt-5 pb-2">
                            <p class="px-3 text-xs font-bold text-primary-200 uppercase tracking-wider">Administración</p>
                        </div>

                        <a href="{{ route('admin.tenants.index') }}" class="nav-link {{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span>Gestionar Tenants</span>
                        </a>

                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>Gestionar Usuarios</span>
                        </a>

                        <a href="{{ route('admin.plans.index') }}" class="nav-link {{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            <span>Gestionar Planes</span>
                        </a>

                        <div class="pt-5 pb-2">
                            <p class="px-3 text-xs font-bold text-primary-200 uppercase tracking-wider">Desarrolladores</p>
                        </div>

                        <a href="/api-docs" target="_blank" class="nav-link">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                            <span>API Documentation</span>
                        </a>
                    @endif
                </nav>

                <!-- User Menu -->
                <div class="px-3 sm:px-5 py-4 sm:py-5 border-t border-white/10 bg-black/10">
                    <div class="mb-3 sm:mb-4">
                        <div class="flex items-center space-x-2 sm:space-x-3">
                            <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-white/20 to-white/10 backdrop-blur-md rounded-xl flex items-center justify-center text-white font-bold shadow-lg border border-white/20 text-base sm:text-lg">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs sm:text-sm font-bold text-white truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] sm:text-xs text-primary-200 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <a href="{{ route('profile.show') }}" class="w-full flex items-center justify-center space-x-2 px-3 sm:px-4 py-2.5 sm:py-3 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white rounded-lg sm:rounded-xl transition-all duration-200 font-semibold text-xs sm:text-sm border border-white/20 hover:border-white/30 shadow-lg cursor-pointer">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="truncate">Mi Perfil</span>
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center space-x-2 px-3 sm:px-4 py-2.5 sm:py-3 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white rounded-lg sm:rounded-xl transition-all duration-200 font-semibold text-xs sm:text-sm border border-white/20 hover:border-white/30 shadow-lg cursor-pointer">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span class="truncate">Cerrar Sesión</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-neutral-900/50 backdrop-blur-sm z-40 lg:hidden hidden"></div>
        @endauth

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            @auth
            <!-- Top Bar -->
            <header class="bg-white border-b border-neutral-200 shadow-sm z-30">
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <button id="openSidebar" class="lg:hidden text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 p-2 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <h2 class="text-lg sm:text-xl font-display font-bold text-neutral-900">@yield('page-title', 'Dashboard')</h2>
                        <div class="w-10 lg:w-auto"></div>
                    </div>
                </div>
            </header>
            @endauth

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-neutral-50">
                <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8 max-w-7xl mx-auto">
                    @if(session('success'))
                        <div class="mb-6 animate-slide-up">
                            <div class="bg-success-50 border border-success-200 text-success-800 px-6 py-4 rounded-xl flex items-start shadow-soft">
                                <svg class="w-6 h-6 mr-3 flex-shrink-0 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-semibold">¡Éxito!</p>
                                    <p class="text-sm mt-1">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 animate-slide-up">
                            <div class="bg-danger-50 border border-danger-200 text-danger-800 px-6 py-4 rounded-xl flex items-start shadow-soft">
                                <svg class="w-6 h-6 mr-3 flex-shrink-0 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-semibold">Error</p>
                                    <p class="text-sm mt-1">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <style>
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(4px);
        }
        .nav-link.active {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0.05));
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(10px);
        }
        .nav-link svg {
            flex-shrink: 0;
            width: 1.25rem;
            height: 1.25rem;
        }

        /* Scrollbar personalizado para el sidebar */
        nav.scrollbar-thin::-webkit-scrollbar {
            width: 4px;
        }

        nav.scrollbar-thin::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        nav.scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        nav.scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>

    <script>
        // Mobile sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const openBtn = document.getElementById('openSidebar');
        const closeBtn = document.getElementById('closeSidebar');

        if (openBtn) {
            openBtn.addEventListener('click', () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }
    </script>

    @stack('scripts')
</body>
</html>

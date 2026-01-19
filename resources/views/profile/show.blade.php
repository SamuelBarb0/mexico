@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 via-purple-700 to-violet-700 rounded-xl sm:rounded-2xl shadow-2xl p-4 sm:p-6 lg:p-8">
        <div class="flex items-center gap-3 sm:gap-4">
            <div class="w-14 h-14 sm:w-16 sm:h-16 lg:w-20 lg:h-20 bg-white/20 backdrop-blur-md rounded-xl sm:rounded-2xl flex items-center justify-center text-white font-bold text-2xl sm:text-3xl shadow-lg border border-white/30 flex-shrink-0">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-0.5 sm:mb-1 truncate">Mi Perfil</h1>
                <p class="text-purple-100 text-sm sm:text-base lg:text-lg">Administra tu información personal</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Información Personal -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Información Personal
            </h2>

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nombre Completo</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition @error('name') border-red-500 @enderror"
                        required
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Correo Electrónico</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition @error('email') border-red-500 @enderror"
                        required
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full px-6 py-3 bg-gradient-to-r from-purple-600 to-violet-600 hover:from-purple-700 hover:to-violet-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all transform hover:scale-105 cursor-pointer"
                    >
                        <i class="bi bi-check-circle mr-2"></i>
                        Actualizar Información
                    </button>
                </div>
            </form>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Cambiar Contraseña
            </h2>

            <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-2">Contraseña Actual</label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition @error('current_password') border-red-500 @enderror"
                        required
                    >
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Nueva Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition @error('password') border-red-500 @enderror"
                        required
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirmar Nueva Contraseña</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                        required
                    >
                </div>

                <div class="pt-4">
                    <button
                        type="submit"
                        class="w-full px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all transform hover:scale-105 cursor-pointer"
                    >
                        <i class="bi bi-shield-lock mr-2"></i>
                        Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-purple-100">
        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Información de la Cuenta
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">Tipo de Usuario</p>
                <p class="text-lg font-semibold text-gray-900">
                    @if($user->user_type === 'tenant_admin')
                        Administrador del Tenant
                    @elseif($user->user_type === 'platform_admin')
                        Administrador de Plataforma
                    @else
                        Usuario
                    @endif
                </p>
            </div>

            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">Estado</p>
                <p class="text-lg font-semibold">
                    @if($user->is_active)
                        <span class="text-green-600">Activo</span>
                    @else
                        <span class="text-red-600">Inactivo</span>
                    @endif
                </p>
            </div>

            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">Miembro Desde</p>
                <p class="text-lg font-semibold text-gray-900">{{ $user->created_at->format('d/m/Y') }}</p>
            </div>

            <div class="p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-1">Última Actualización</p>
                <p class="text-lg font-semibold text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
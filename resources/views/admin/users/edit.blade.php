@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-extrabold text-white mb-2">Editar Usuario</h1>
                <p class="text-indigo-100 text-lg">{{ $user->name }} ({{ $user->email }})</p>
            </div>
            <a href="{{ route('admin.users.show', $user) }}" class="bg-white text-indigo-600 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                Volver
            </a>
        </div>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" x-data="{ userType: '{{ old('user_type', $user->user_type) }}' }">
        @csrf
        @method('PUT')

        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre Completo *</label>
                    <input type="text" name="name" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                        value="{{ old('name', $user->name) }}" placeholder="Ej: Juan Pérez">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror"
                        value="{{ old('email', $user->email) }}" placeholder="usuario@ejemplo.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- User Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Usuario *</label>
                    <select name="user_type" x-model="userType" required
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('user_type') border-red-500 @enderror">
                        <option value="tenant_user" {{ old('user_type', $user->user_type) == 'tenant_user' ? 'selected' : '' }}>Usuario de Tenant</option>
                        <option value="tenant_admin" {{ old('user_type', $user->user_type) == 'tenant_admin' ? 'selected' : '' }}>Administrador de Tenant</option>
                        <option value="platform_admin" {{ old('user_type', $user->user_type) == 'platform_admin' ? 'selected' : '' }}>Administrador de Plataforma</option>
                    </select>
                    @error('user_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tenant Selection -->
                <div x-show="userType !== 'platform_admin'" class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tenant/Organización *</label>
                    <select name="tenant_id"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('tenant_id') border-red-500 @enderror">
                        <option value="">Seleccionar tenant...</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}" {{ old('tenant_id', $user->tenant_id) == $tenant->id ? 'selected' : '' }}>
                                {{ $tenant->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('tenant_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Divider -->
                <div class="md:col-span-2">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500 font-semibold">Cambiar Contraseña (Opcional)</span>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nueva Contraseña</label>
                    <input type="password" name="password"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('password') border-red-500 @enderror"
                        placeholder="Dejar vacío para mantener actual">
                    <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres. Dejar vacío si no deseas cambiarla.</p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Repetir nueva contraseña">
                </div>

                <!-- Active Status -->
                <div class="md:col-span-2">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm font-semibold text-gray-700">Usuario activo</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Si está desactivado, el usuario no podrá iniciar sesión</p>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-6 p-4 bg-amber-50 rounded-lg border border-amber-200">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="text-sm text-amber-800">
                        <p class="font-semibold mb-1">Nota Importante:</p>
                        <p>Los cambios en el tipo de usuario o tenant afectarán inmediatamente los permisos y accesos del usuario.</p>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('admin.users.show', $user) }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition">
                    Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

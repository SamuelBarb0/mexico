@extends('layouts.app')

@section('title', 'Detalle de Usuario')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8">
        <div class="flex justify-between items-center">
            <div class="flex items-center">
                <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center text-white text-2xl font-bold mr-4">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-4xl font-extrabold text-white mb-1">{{ $user->name }}</h1>
                    <p class="text-indigo-100 text-lg">{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.users.edit', $user) }}" class="bg-white text-indigo-600 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                    Editar
                </a>
                <a href="{{ route('admin.users.index') }}" class="bg-white/20 text-white px-6 py-3 rounded-xl font-bold hover:bg-white/30 transition-all">
                    Volver
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- User Details -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Información del Usuario</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-500 mb-1">Nombre Completo</label>
                        <p class="text-lg text-gray-900">{{ $user->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-500 mb-1">Email</label>
                        <p class="text-lg text-gray-900">{{ $user->email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-500 mb-1">Tipo de Usuario</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                            @if($user->user_type === 'platform_admin') bg-red-100 text-red-800
                            @elseif($user->user_type === 'tenant_admin') bg-purple-100 text-purple-800
                            @else bg-blue-100 text-blue-800
                            @endif">
                            @if($user->user_type === 'platform_admin')
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Administrador de Plataforma
                            @elseif($user->user_type === 'tenant_admin')
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Administrador de Tenant
                            @else
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Usuario de Tenant
                            @endif
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-500 mb-1">Estado</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                            {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            @if($user->is_active)
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Activo
                            @else
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Inactivo
                            @endif
                        </span>
                    </div>

                    @if($user->tenant)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-500 mb-1">Organización/Tenant</label>
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold mr-3">
                                    {{ strtoupper(substr($user->tenant->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-lg text-gray-900 font-semibold">{{ $user->tenant->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $user->tenant->plan }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-semibold text-gray-500 mb-1">Fecha de Registro</label>
                        <p class="text-lg text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-500 mb-1">Última Actualización</label>
                        <p class="text-lg text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl shadow-xl p-6 border border-indigo-200">
                <h4 class="text-lg font-bold text-indigo-900 mb-4">Acciones Rápidas</h4>
                <div class="space-y-3">
                    <a href="{{ route('admin.users.edit', $user) }}" class="block w-full px-4 py-3 bg-white text-indigo-600 rounded-lg font-semibold text-center hover:bg-indigo-50 transition shadow-sm">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editar Usuario
                    </a>

                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="block w-full px-4 py-3 bg-white rounded-lg font-semibold text-center transition shadow-sm
                            {{ $user->is_active ? 'text-red-600 hover:bg-red-50' : 'text-green-600 hover:bg-green-50' }}">
                            @if($user->is_active)
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                Desactivar Usuario
                            @else
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Activar Usuario
                            @endif
                        </button>
                    </form>

                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                            onsubmit="return confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="block w-full px-4 py-3 bg-red-100 text-red-700 rounded-lg font-semibold text-center hover:bg-red-200 transition shadow-sm">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Eliminar Usuario
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

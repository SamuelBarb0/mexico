@extends('layouts.app')

@section('title', 'WABA Accounts')

@section('content')
<div class="space-y-6">
    <!-- Header con gradiente -->
    <div class="relative overflow-hidden bg-gradient-to-r from-amber-600 via-orange-600 to-orange-700 rounded-xl sm:rounded-2xl shadow-2xl p-4 sm:p-6 lg:p-8">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex-1">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white mb-1 sm:mb-2 flex items-center">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 lg:w-10 lg:h-10 mr-2 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <span class="truncate">WABA Accounts</span>
                </h1>
                <p class="text-amber-100 text-sm sm:text-base lg:text-lg">Administra tus cuentas de WhatsApp Business API</p>
            </div>
            <a href="{{ route('waba-accounts.create') }}" class="group relative overflow-hidden bg-white text-amber-600 px-4 sm:px-6 lg:px-8 py-2.5 sm:py-3 lg:py-4 rounded-lg sm:rounded-xl font-bold text-sm sm:text-base shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex items-center cursor-pointer w-full sm:w-auto justify-center">
                <span class="absolute inset-0 bg-gradient-to-r from-amber-400 to-orange-500 opacity-0 group-hover:opacity-20 transition-opacity"></span>
                <svg class="w-5 h-5 sm:w-6 sm:h-6 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="relative z-10 truncate">Nueva Cuenta WABA</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        @if($wabaAccounts->isEmpty())
            <div class="text-center py-16 px-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-amber-100 mb-6">
                    <svg class="w-12 h-12 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No hay cuentas WABA registradas</h3>
                <p class="text-gray-600 mb-6">Comienza agregando tu primera cuenta de WhatsApp Business API</p>
                <a href="{{ route('waba-accounts.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Crear Primera Cuenta WABA
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-amber-50 to-orange-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Teléfono</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">WABA ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($wabaAccounts as $waba)
                        <tr class="hover:bg-amber-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($waba->name, 0, 1)) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $waba->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700">{{ $waba->phone_number }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $waba->phone_number_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700">{{ $waba->waba_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('waba-accounts.show', $waba) }}" class="text-amber-600 hover:text-amber-800 font-semibold transition-colors">
                                        👁️ Ver
                                    </a>
                                    <a href="{{ route('waba-accounts.edit', $waba) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                                        ✏️ Editar
                                    </a>
                                    <form action="{{ route('waba-accounts.destroy', $waba) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta cuenta WABA?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-semibold transition-colors">
                                            🗑️ Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($wabaAccounts->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $wabaAccounts->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection

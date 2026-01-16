@extends('layouts.app')

@section('title', 'Contactos')

@section('content')
<div class="space-y-6">
    <!-- Header con gradiente -->
    <div class="relative overflow-hidden bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 rounded-2xl shadow-2xl p-8">
        <div class="absolute top-0 right-0 -mt-8 -mr-8 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-48 h-48 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-extrabold text-white mb-2 flex items-center">
                    <svg class="w-10 h-10 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Contactos
                </h1>
                <p class="text-green-100 text-lg">Administra tu lista de contactos</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('contacts.import') }}" class="group relative overflow-hidden bg-white/90 text-green-600 px-6 py-4 rounded-xl font-bold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex items-center">
                    <span class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-500 opacity-0 group-hover:opacity-20 transition-opacity"></span>
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <span class="relative z-10">Importar CSV</span>
                </a>
                <a href="{{ route('contacts.create') }}" class="group relative overflow-hidden bg-white text-green-600 px-8 py-4 rounded-xl font-bold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 flex items-center">
                    <span class="absolute inset-0 bg-gradient-to-r from-green-400 to-emerald-500 opacity-0 group-hover:opacity-20 transition-opacity"></span>
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="relative z-10">Nuevo Contacto</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-gray-200">
        <form method="GET" action="{{ route('contacts.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               placeholder="Buscar por nombre, teléfono, email..."
                               class="pl-10 w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                    </div>
                </div>

                <!-- Client Filter -->
                <div>
                    <label for="client_id" class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                    <select name="client_id" id="client_id" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                        <option value="">Todos los clientes</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select name="status" id="status" class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg font-semibold shadow-md hover:shadow-lg transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>
                @if(request()->hasAny(['search', 'client_id', 'status']))
                    <a href="{{ route('contacts.index') }}" class="inline-flex items-center px-6 py-2 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Limpiar Filtros
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl overflow-hidden border border-success-100">
        @if($contacts->isEmpty())
            <div class="text-center py-16 px-6">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 mb-6">
                    <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No se encontraron contactos</h3>
                <p class="text-gray-600 mb-6">
                    @if(request()->hasAny(['search', 'client_id', 'status']))
                        No hay contactos que coincidan con los filtros seleccionados
                    @else
                        Comienza agregando tu primer contacto
                    @endif
                </p>
                @if(request()->hasAny(['search', 'client_id', 'status']))
                    <a href="{{ route('contacts.index') }}" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Limpiar Filtros
                    </a>
                @else
                    <a href="{{ route('contacts.create') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Crear Primer Contacto
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-green-50 to-emerald-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Teléfono</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">WhatsApp</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($contacts as $contact)
                        <tr class="hover:bg-green-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white font-bold">
                                        {{ strtoupper(substr($contact->name, 0, 1)) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $contact->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($contact->client)
                                    <a href="{{ route('clients.show', $contact->client) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                                        {{ $contact->client->name }}
                                    </a>
                                @else
                                    <span class="text-gray-400 text-sm">Sin cliente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700">{{ $contact->phone ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700">{{ $contact->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700">{{ $contact->whatsapp_id ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($contact->status === 'active')
                                    <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Activo
                                    </span>
                                @else
                                    <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('contacts.show', $contact) }}" class="text-green-600 hover:text-green-800 font-semibold transition-colors">
                                        👁️ Ver
                                    </a>
                                    <a href="{{ route('contacts.edit', $contact) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                                        ✏️ Editar
                                    </a>
                                    <form action="{{ route('contacts.destroy', $contact) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este contacto?')">
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

            @if($contacts->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $contacts->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection

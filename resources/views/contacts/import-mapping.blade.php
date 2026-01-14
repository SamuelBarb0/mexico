@extends('layouts.app')

@section('title', 'Mapeo de Columnas - Importar Contactos')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8">
        <div>
            <h1 class="text-4xl font-extrabold text-white mb-2 flex items-center">
                <svg class="w-10 h-10 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Importar Contactos - Mapeo de Columnas
            </h1>
            <p class="text-indigo-100 text-lg">Configura cómo se mapearán las columnas del CSV a los campos de contacto</p>
        </div>
    </div>

    <!-- Error Message -->
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-md">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-800 font-semibold">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <form action="{{ route('contacts.import.process') }}" method="POST">
        @csrf
        <input type="hidden" name="filename" value="{{ $filename }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Mapping Table -->
            <div class="lg:col-span-2">
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <span class="bg-indigo-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">2</span>
                            Mapear Columnas
                        </h2>
                    </div>

                    <div class="p-6">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg">
                            <p class="text-sm text-blue-800">
                                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Selecciona a qué campo de contacto corresponde cada columna de tu CSV.
                                <strong>El campo Teléfono es obligatorio.</strong>
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider w-1/3">
                                            Columna del CSV
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider w-1/3">
                                            Mapear a Campo
                                        </th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider w-1/3">
                                            Vista Previa
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($header as $index => $column)
                                        <tr class="hover:bg-indigo-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="bg-indigo-100 text-indigo-800 rounded-full px-3 py-1 text-xs font-bold mr-2">
                                                        {{ $index + 1 }}
                                                    </span>
                                                    <span class="text-sm font-bold text-gray-900">{{ $column }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <select name="mapping[{{ $column }}]" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                    @foreach($availableFields as $value => $label)
                                                        @php
                                                            $guessed = guessMapping($column);
                                                        @endphp
                                                        <option value="{{ $value }}" {{ $guessed === $value ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="text-sm text-gray-600">
                                                    @if(isset($preview[0][$index]))
                                                        {{ Str::limit($preview[0][$index] ?? '', 40) }}
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 mt-6">
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all hover:from-indigo-700 hover:to-purple-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Iniciar Importación
                    </button>
                    <button type="button" onclick="cancelImport()" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold shadow-md hover:shadow-lg hover:bg-gray-200 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancelar
                    </button>
                </div>
            </div>

            <!-- Preview & Info Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Preview Card -->
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200 overflow-hidden sticky top-6">
                    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Vista Previa
                        </h2>
                    </div>

                    <div class="p-6">
                        <p class="text-sm text-gray-600 mb-4">
                            Mostrando las primeras {{ count($preview) }} filas del archivo
                        </p>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead class="bg-gray-100">
                                    <tr>
                                        @foreach($header as $column)
                                            <th class="px-2 py-2 text-left text-gray-700 font-bold">
                                                {{ Str::limit($column, 12) }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($preview as $row)
                                        @if(!empty(array_filter($row)))
                                            <tr>
                                                @foreach($row as $cell)
                                                    <td class="px-2 py-2 text-gray-600">
                                                        {{ Str::limit($cell ?? '', 15) }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl shadow-xl border border-blue-200 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 mb-2">Nota Importante</h3>
                                <p class="text-sm text-gray-700">
                                    La importación se procesará en segundo plano. Si tu archivo tiene muchos contactos,
                                    puede tardar algunos minutos. Podrás seguir usando el sistema mientras se procesa.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Hidden Cancel Form -->
<form id="cancel-form" action="{{ route('contacts.import.cancel') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="filename" value="{{ $filename }}">
</form>

<script>
function cancelImport() {
    if (confirm('¿Estás seguro de que deseas cancelar la importación?')) {
        document.getElementById('cancel-form').submit();
    }
}
</script>

@php
    // Helper function to guess column mapping
    function guessMapping($columnName) {
        $columnLower = strtolower($columnName);

        $mappings = [
            'telefono' => 'phone',
            'teléfono' => 'phone',
            'phone' => 'phone',
            'celular' => 'phone',
            'movil' => 'phone',
            'móvil' => 'phone',
            'numero' => 'phone',
            'número' => 'phone',
            'nombre' => 'name',
            'name' => 'name',
            'email' => 'email',
            'correo' => 'email',
            'e-mail' => 'email',
            'mail' => 'email',
        ];

        return $mappings[$columnLower] ?? '';
    }
@endphp
@endsection

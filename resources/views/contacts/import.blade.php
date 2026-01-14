@extends('layouts.app')

@section('title', 'Importar Contactos')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl p-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-extrabold text-white mb-2 flex items-center">
                    <svg class="w-10 h-10 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Importar Contactos desde CSV
                </h1>
                <p class="text-indigo-100 text-lg">Sube un archivo CSV para importar contactos masivamente</p>
            </div>
            <a href="{{ route('contacts.index') }}" class="bg-white text-indigo-600 px-6 py-3 rounded-xl font-bold shadow-lg hover:shadow-xl transition-all">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver a Contactos
            </a>
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upload Form -->
        <div class="lg:col-span-2">
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 flex items-center">
                        <span class="bg-indigo-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm">1</span>
                        Subir archivo CSV
                    </h2>
                </div>

                <div class="p-6">
                    <form action="{{ route('contacts.import.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- File Input -->
                        <div class="mb-6">
                            <label for="csv_file" class="block text-sm font-bold text-gray-700 mb-2">
                                Selecciona tu archivo CSV
                            </label>
                            <div class="relative">
                                <input type="file"
                                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('csv_file') border-red-500 @enderror"
                                       id="csv_file"
                                       name="csv_file"
                                       accept=".csv,.txt"
                                       required>
                            </div>
                            @error('csv_file')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Tamaño máximo: 10MB. El archivo debe estar en formato CSV.
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all hover:from-indigo-700 hover:to-purple-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            Subir y Continuar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Information Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl shadow-xl border border-indigo-200 overflow-hidden sticky top-6">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Formato del CSV
                    </h2>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Requisitos -->
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Requisitos:
                        </h3>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                La primera fila debe contener los nombres de las columnas
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Debe incluir al menos una columna con números de teléfono
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Los teléfonos deben estar en formato internacional (ej: +52 1234567890)
                            </li>
                        </ul>
                    </div>

                    <!-- Columnas recomendadas -->
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            Columnas recomendadas:
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center text-sm">
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-bold mr-2">REQUERIDO</span>
                                <span class="text-gray-700">Teléfono</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-semibold mr-2">OPCIONAL</span>
                                <span class="text-gray-700">Nombre</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-semibold mr-2">OPCIONAL</span>
                                <span class="text-gray-700">Email</span>
                            </div>
                        </div>
                    </div>

                    <!-- Ejemplo -->
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Ejemplo de CSV:
                        </h3>
                        <pre class="bg-white p-3 rounded-lg border border-gray-300 text-xs overflow-x-auto"><code>telefono,nombre,email
+5215551234567,Juan Pérez,juan@example.com
+5215559876543,María García,maria@example.com</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

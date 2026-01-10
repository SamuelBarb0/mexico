@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Importar Contactos desde CSV</h1>
            <p class="text-muted">Sube un archivo CSV para importar contactos masivamente</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('contacts.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Contactos
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Paso 1: Subir archivo CSV</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('contacts.import.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Selecciona tu archivo CSV</label>
                            <input type="file" class="form-control @error('csv_file') is-invalid @enderror"
                                   id="csv_file" name="csv_file" accept=".csv,.txt" required>
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Tamaño máximo: 10MB. El archivo debe estar en formato CSV.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Subir y Continuar
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Formato del CSV
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Requisitos:</h6>
                    <ul class="small mb-3">
                        <li>La primera fila debe contener los nombres de las columnas</li>
                        <li>Debe incluir al menos una columna con números de teléfono</li>
                        <li>Los teléfonos deben estar en formato internacional (ej: +52 1234567890)</li>
                    </ul>

                    <h6>Columnas recomendadas:</h6>
                    <ul class="small mb-3">
                        <li>Teléfono (requerido)</li>
                        <li>Nombre</li>
                        <li>Apellido</li>
                        <li>Email</li>
                        <li>Empresa</li>
                        <li>Cargo</li>
                    </ul>

                    <h6>Ejemplo de CSV:</h6>
                    <pre class="small bg-white p-2 border rounded"><code>telefono,nombre,apellido,email
+5215551234567,Juan,Pérez,juan@example.com
+5215559876543,María,García,maria@example.com</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Importar Contactos - Mapeo de Columnas</h1>
            <p class="text-muted">Configura cómo se mapearán las columnas del CSV a los campos de contacto</p>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('contacts.import.process') }}" method="POST">
        @csrf
        <input type="hidden" name="filename" value="{{ $filename }}">

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Paso 2: Mapear Columnas</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Selecciona a qué campo de contacto corresponde cada columna de tu CSV.
                            <strong>El campo Teléfono es obligatorio.</strong>
                        </p>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%">Columna del CSV</th>
                                        <th style="width: 35%">Mapear a Campo</th>
                                        <th style="width: 35%">Vista Previa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($header as $index => $column)
                                        <tr>
                                            <td>
                                                <strong>{{ $column }}</strong>
                                            </td>
                                            <td>
                                                <select name="mapping[{{ $column }}]" class="form-select">
                                                    @foreach($availableFields as $value => $label)
                                                        <option value="{{ $value }}"
                                                            {{ $this->guessMapping($column) === $value ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    @if(isset($preview[0][$index]))
                                                        {{ Str::limit($preview[0][$index], 50) }}
                                                    @endif
                                                </small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Iniciar Importación
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="cancelImport()">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-eye"></i> Vista Previa de Datos
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">
                            Mostrando las primeras {{ count($preview) }} filas del archivo
                        </p>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered small">
                                <thead class="table-light">
                                    <tr>
                                        @foreach($header as $column)
                                            <th>{{ Str::limit($column, 15) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($preview as $row)
                                        @if(!empty(array_filter($row)))
                                            <tr>
                                                @foreach($row as $cell)
                                                    <td>{{ Str::limit($cell, 20) }}</td>
                                                @endforeach
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mt-3 bg-info bg-opacity-10">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-lightbulb"></i> Nota Importante
                        </h6>
                        <p class="small mb-0">
                            La importación se procesará en segundo plano. Si tu archivo tiene muchos contactos,
                            puede tardar algunos minutos. Podrás seguir usando el sistema mientras se procesa.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<form id="cancel-form" action="{{ route('contacts.import.cancel') }}" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="filename" value="{{ $filename }}">
</form>

@push('scripts')
<script>
function cancelImport() {
    if (confirm('¿Estás seguro de que deseas cancelar la importación?')) {
        document.getElementById('cancel-form').submit();
    }
}
</script>
@endpush

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
            'nombre' => 'name',
            'name' => 'name',
            'email' => 'email',
            'correo' => 'email',
        ];

        return $mappings[$columnLower] ?? '';
    }
@endphp
@endsection

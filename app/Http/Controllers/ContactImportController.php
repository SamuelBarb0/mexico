<?php

namespace App\Http\Controllers;

use App\Jobs\ImportContactsFromCsvJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ContactImportController extends Controller
{
    /**
     * Show the import form
     */
    public function index()
    {
        return view('contacts.import');
    }

    /**
     * Upload CSV and preview mapping
     */
    public function upload(Request $request)
    {
        \Log::info('=== UPLOAD CSV - Iniciando ===', [
            'user_id' => auth()->id(),
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            \Log::warning('CSV upload validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);
            return back()
                ->withErrors($validator)
                ->with('error', 'Por favor selecciona un archivo CSV válido (máximo 10MB).');
        }

        try {
            $file = $request->file('csv_file');

            \Log::info('CSV file uploaded', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            // Generate unique filename
            $filename = 'imports/' . Str::uuid() . '.csv';

            // Store the file
            $path = $file->storeAs('', $filename, 'local');

            \Log::info('CSV file stored', [
                'path' => $path,
                'full_path' => storage_path('app/' . $path),
            ]);

            // Read first few rows for preview
            $csvContent = Storage::disk('local')->get($path);
            $rows = array_map('str_getcsv', explode("\n", $csvContent));

            // Get header
            $header = array_shift($rows);

            \Log::info('CSV parsed', [
                'header' => $header,
                'total_rows' => count($rows),
            ]);

            // Get first 5 rows for preview
            $preview = array_slice($rows, 0, 5);

            // Available contact fields for mapping
            $availableFields = [
                '' => '-- No importar --',
                'phone' => 'Teléfono (requerido)',
                'name' => 'Nombre',
                'email' => 'Email',
            ];

            return view('contacts.import-mapping', [
                'filename' => $filename,
                'header' => $header,
                'preview' => $preview,
                'availableFields' => $availableFields,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error uploading CSV', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Process the import with the specified mapping
     */
    public function process(Request $request)
    {
        \Log::info('=== PROCESS IMPORT - Iniciando ===', [
            'user_id' => auth()->id(),
            'tenant_id' => auth()->user()->tenant_id,
            'request_data' => $request->all(),
        ]);

        $validator = Validator::make($request->all(), [
            'filename' => 'required|string',
            'mapping' => 'required|array',
        ]);

        if ($validator->fails()) {
            \Log::warning('Import validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);
            return back()
                ->withErrors($validator)
                ->with('error', 'Datos de importación inválidos.');
        }

        $mapping = $request->input('mapping');
        $filename = $request->input('filename');

        \Log::info('Mapping received', [
            'filename' => $filename,
            'mapping' => $mapping,
        ]);

        // Validate that phone is mapped
        if (!in_array('phone', $mapping)) {
            \Log::warning('Phone field not mapped', [
                'mapping' => $mapping,
            ]);
            return back()->with('error', 'Debes mapear al menos la columna de Teléfono.');
        }

        // Verify file exists
        if (!Storage::disk('local')->exists($filename)) {
            \Log::error('CSV file not found', [
                'filename' => $filename,
                'storage_path' => storage_path('app/' . $filename),
            ]);
            return back()->with('error', 'El archivo CSV no fue encontrado. Por favor vuelve a subirlo.');
        }

        try {
            $tenant = auth()->user()->tenant;
            $userId = auth()->id();

            \Log::info('Executing import job synchronously', [
                'tenant_id' => $tenant->id,
                'filename' => $filename,
                'user_id' => $userId,
            ]);

            // Execute the job synchronously (immediately)
            ImportContactsFromCsvJob::dispatchSync($tenant, $filename, $mapping, $userId);

            \Log::info('Import job completed successfully');

            return redirect()
                ->route('contacts.index')
                ->with('success', '¡Importación completada! Se han importado los contactos exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error starting import', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error al iniciar la importación: ' . $e->getMessage());
        }
    }

    /**
     * Cancel import and delete uploaded file
     */
    public function cancel(Request $request)
    {
        $filename = $request->input('filename');

        if ($filename && Storage::disk('local')->exists($filename)) {
            Storage::disk('local')->delete($filename);
        }

        return redirect()
            ->route('contacts.index')
            ->with('info', 'Importación cancelada.');
    }
}

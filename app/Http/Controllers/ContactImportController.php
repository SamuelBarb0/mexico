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
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->with('error', 'Por favor selecciona un archivo CSV válido (máximo 10MB).');
        }

        try {
            $file = $request->file('csv_file');

            // Generate unique filename
            $filename = 'imports/' . Str::uuid() . '.csv';

            // Store the file
            $path = $file->storeAs('', $filename, 'local');

            // Read first few rows for preview
            $csvContent = Storage::disk('local')->get($path);
            $rows = array_map('str_getcsv', explode("\n", $csvContent));

            // Get header
            $header = array_shift($rows);

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
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Process the import with the specified mapping
     */
    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required|string',
            'mapping' => 'required|array',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->with('error', 'Datos de importación inválidos.');
        }

        $mapping = $request->input('mapping');
        $filename = $request->input('filename');

        // Validate that phone is mapped
        if (!in_array('phone', $mapping)) {
            return back()->with('error', 'Debes mapear al menos la columna de Teléfono.');
        }

        // Verify file exists
        if (!Storage::disk('local')->exists($filename)) {
            return back()->with('error', 'El archivo CSV no fue encontrado. Por favor vuelve a subirlo.');
        }

        try {
            $tenant = auth()->user()->tenant;
            $userId = auth()->id();

            // Dispatch the job
            ImportContactsFromCsvJob::dispatch($tenant, $filename, $mapping, $userId);

            return redirect()
                ->route('contacts.index')
                ->with('success', 'La importación de contactos ha comenzado. Recibirás una notificación cuando termine.');

        } catch (\Exception $e) {
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

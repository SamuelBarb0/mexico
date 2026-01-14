<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Estado del Sistema de Importación ===\n\n";

// Check queue jobs
echo "📋 JOBS EN COLA:\n";
$pendingJobs = DB::table('jobs')->count();
echo "  Pendientes: {$pendingJobs}\n";

if ($pendingJobs > 0) {
    $jobs = DB::table('jobs')->get();
    foreach ($jobs as $job) {
        $payload = json_decode($job->payload);
        echo sprintf(
            "  - ID: %d | Queue: %s | Intentos: %d | Creado: %s\n",
            $job->id,
            $job->queue,
            $job->attempts,
            date('Y-m-d H:i:s', $job->created_at)
        );
    }
}
echo "\n";

// Check failed jobs
echo "❌ JOBS FALLIDOS:\n";
$failedJobs = DB::table('failed_jobs')->count();
echo "  Total: {$failedJobs}\n";

if ($failedJobs > 0) {
    $jobs = DB::table('failed_jobs')->orderBy('failed_at', 'desc')->take(3)->get();
    foreach ($jobs as $job) {
        echo sprintf(
            "  - ID: %d | Queue: %s | Falló: %s\n",
            $job->id,
            $job->queue,
            $job->failed_at
        );
        echo "    Error: " . substr($job->exception, 0, 200) . "...\n";
    }
}
echo "\n";

// Check contacts count
echo "👥 CONTACTOS:\n";
$contacts = App\Models\Contact::count();
echo "  Total: {$contacts}\n";

$contactsToday = App\Models\Contact::whereDate('created_at', today())->count();
echo "  Creados hoy: {$contactsToday}\n";

$lastContact = App\Models\Contact::latest()->first();
if ($lastContact) {
    echo sprintf(
        "  Último: %s (%s) - %s\n",
        $lastContact->name ?? 'Sin nombre',
        $lastContact->phone,
        $lastContact->created_at->format('Y-m-d H:i:s')
    );
}
echo "\n";

// Check storage files
echo "📁 ARCHIVOS CSV:\n";
$storage = storage_path('app/imports');
if (is_dir($storage)) {
    $files = glob($storage . '/*.csv');
    echo "  Total archivos: " . count($files) . "\n";
    if (count($files) > 0) {
        foreach (array_slice($files, -5) as $file) {
            echo sprintf(
                "  - %s (%s KB) - %s\n",
                basename($file),
                round(filesize($file) / 1024, 2),
                date('Y-m-d H:i:s', filemtime($file))
            );
        }
    }
} else {
    echo "  Directorio imports no existe\n";
}
echo "\n";

// Recent logs
echo "📝 ÚLTIMAS LÍNEAS DEL LOG:\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -10);
    foreach ($lastLines as $line) {
        echo "  " . trim($line) . "\n";
    }
} else {
    echo "  No hay archivo de log\n";
}

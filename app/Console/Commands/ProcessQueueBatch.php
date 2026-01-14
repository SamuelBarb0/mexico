<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ProcessQueueBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:process-batch
                            {--limit=10 : Number of jobs to process}
                            {--max-time=50 : Maximum execution time in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process a batch of queue jobs (optimized for shared hosting)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $maxTime = $this->option('max-time');

        $this->info("Processing up to {$limit} jobs (max time: {$maxTime}s)...");

        // Ejecutar queue:work con límites específicos para hosting compartido
        $exitCode = Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--max-jobs' => $limit,
            '--max-time' => $maxTime,
            '--tries' => 3,
            '--timeout' => 60,
        ]);

        if ($exitCode === 0) {
            $this->info('Queue batch processed successfully.');
        } else {
            $this->error('Queue processing failed.');
        }

        return $exitCode;
    }
}

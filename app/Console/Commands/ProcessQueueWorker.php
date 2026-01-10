<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessQueueWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:process
                            {--queue=default : The name of the queue to work}
                            {--once : Only process the next job on the queue}
                            {--stop-when-empty : Stop when the queue is empty}
                            {--delay=0 : The number of seconds to delay failed jobs}
                            {--memory=128 : The memory limit in megabytes}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--sleep=3 : Number of seconds to sleep when no job is available}
                            {--tries=1 : Number of times to attempt a job before logging it failed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the queue jobs continuously';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting queue worker...');
        $this->info('Press Ctrl+C to stop');

        $options = [
            '--queue' => $this->option('queue'),
            '--delay' => $this->option('delay'),
            '--memory' => $this->option('memory'),
            '--timeout' => $this->option('timeout'),
            '--sleep' => $this->option('sleep'),
            '--tries' => $this->option('tries'),
        ];

        if ($this->option('once')) {
            $options['--once'] = true;
        }

        if ($this->option('stop-when-empty')) {
            $options['--stop-when-empty'] = true;
        }

        $this->call('queue:work', $options);

        return Command::SUCCESS;
    }
}

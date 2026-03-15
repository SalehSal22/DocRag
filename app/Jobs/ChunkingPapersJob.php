<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ChunkingPapersJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $data;

    public function __construct($data)
    {

        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        
    }
}

<?php

namespace App\Jobs;

use App\Models\Paper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ChunkingPapersJob implements ShouldQueue
{
    use Queueable;
    public $tries = 3;
    public $timeout = 180;
    public $backoff = [15, 45, 90];
    /**
     * Create a new job instance.
     */
    protected $path;
    protected $docCreatedId;
    public function __construct($path, $docCreatedId)
    {
        $this->docCreatedId = $docCreatedId;
        $this->path = $path;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $doc = Storage::get($this->path);



        Paper::findOrFail($this->docCreatedId)->update([
            'status' => 'processing',
            'raw_text' => $doc
        ]);
        
        EmbedingChunksJob::dispatch($this->docCreatedId);
    }
    public function failed(\Throwable $e): void
    {
        Paper::where('id', $this->docCreatedId)
            ->update(['status' => 'failed']);

        Log::error('ChunkingPapersJob failed', [
            'doc_id' => $this->docCreatedId,
        'error'  => $e->getMessage(),
        ]);
    }
}

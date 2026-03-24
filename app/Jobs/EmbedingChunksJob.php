<?php

namespace App\Jobs;

use App\Models\Embeding;
use App\Models\Paper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbedingChunksJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 180;
    public $backoff = [15, 45, 90];
    /**
     * Create a new job instance.
     */

    protected $docId;
    public function __construct($docId)
    {
        $this->docId = $docId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $paper = Paper::findOrFail($this->docId);

        $rawText = $paper->raw_text;

        $chunks = $this->chunking($rawText);

        foreach ($chunks as $chunk) {

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openai.key')
            ])->post('https://api.openai.com/v1/embeddings', [
                'model' => 'text-embedding-3-small',
                'input' => $chunk
            ]);
            if ($response->failed()) {
                throw new \Exception('open ai failed :' . $response->status());
            }

            $embedding = $response->json();

            Embeding::create([
                'paper_id' => $this->docId,
                'embedding' => $embedding['data'][0]['embedding'],
                'origin' => $chunk
            ]);
        }

        $paper->update(['status' => 'ready']);
    }





    public function chunking($data)
    {
        $v1 = preg_split('/\n\s*\n/', $data);

        $v2 = [];

        foreach ($v1 as $chunk) {
            $chunk = trim($chunk);
            if (strlen($chunk) < 50)
                continue;

            elseif (strlen($chunk) > 1000) {
                array_push($v2, ...preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $chunk));
            } else $v2[] = $chunk;
        }

        $v2 = array_values(array_filter($v2, fn($c) => strlen(trim($c)) > 50));
       
        for ($i = 1; $i < count($v2); $i++) {
            $v2[$i] = substr($v2[$i - 1], -150) . $v2[$i];
            
        }

        return $v2;
    }


    public function failed(\Throwable $e)
    {


        Paper::where('id', $this->docId)->update(['status' => 'failed']);
        Log::error('EmbeddingChunksJob failed', [
            'doc_id' => $this->docId,
            'error'  => $e->getMessage(),
        ]);
    }
}

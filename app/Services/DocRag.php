<?php

namespace App\Services;

use App\Jobs\ChunkingPapersJob;
use App\Models\Embeding;
use App\Models\Paper;
use Blaspsoft\Doxswap\Facades\Doxswap;
use Illuminate\Support\Facades\Http;

class DocRag
{
    public function uploadFileAndChunck($data)
    {
        $doc = $data->file('document');
        

        $path = $doc->store('docs');

        $docCreated = Paper::create([
            'document' => $path,
            'status' => 'pending',
        ]);
        ChunkingPapersJob::dispatch($path, $docCreated->id);
        return $docCreated;
    }

    public function prompt($data)
    {

        $documents = Embeding::where('paper_id', $data['doc_id'])->get()->keyBy('id');
        $documentVectors = $documents->pluck('embedding')->values()->toArray();
        $documentIds = $documents->keys()->toArray();
        $embedded = $this->embeddingFunction($data['prompt']);
        $result = $this->findMostSimilar($documentVectors, $embedded);
        $resultIds = collect($result)->pluck('index');
        for ($i = 0; $i < 4; $i++) {
            $messages[] = $documents[$documentIds[$resultIds[$i]]]['origin'];
        }
        $messages[] = 'answer only using the context above';
        $messages = implode('\n\n', $messages);
        $prompt = $data['prompt'];
        $toSend = [
            [
                'role'    => 'system',
                'content' => 'You are a document assistant. Answer the user\'s question using ONLY the context provided. Do not use outside knowledge. If the answer is not in the context say: I cannot find that information in this document.'
            ],
            [
                'role'    => 'user',
                'content' => "Context:\n\n{$messages}\n\nQuestion:\n{$prompt}"
            ]
        ];


        set_time_limit(0);
        $response = Http::timeout(300)->post('http://localhost:11434/api/chat', [
            'model'   => 'qwen3:14b-Q4_K_M',
            'stream'  => false,
            'messages' => $toSend,
            'options' => [
                'num_predict' => 500,
                'num_ctx'     => 8192,
            ],
        ]);
        return $response->json('message.content');
    }
    function findMostSimilar(array $vectors, array $promptVector): array
    {
        $results = [];

        foreach ($vectors as $index => $vector) {
            $results[] = [
                'index'      => $index,
                'similarity' => $this->cosineSimilarity($promptVector, $vector),
            ];
        }


        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return $results;
    }

    function cosineSimilarity(array $a, array $b): float
    {
        $dot   = 0;
        $normA = 0;
        $normB = 0;

        foreach ($a as $i => $val) {
            $dot   += $val * $b[$i];
            $normA += $val * $val;
            $normB += $b[$i] * $b[$i];
        }

        if ($normA == 0 || $normB == 0) return 0.0;

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    public function embeddingFunction($data)
    {
        $response = Http::post('http://localhost:11434/api/embed', [
            'model' => 'qwen3-embedding:4b',
            'input' => $data
        ]);
        if ($response->failed()) {
            throw new \Exception($response->json('error') ?? 'unknown error');
        }

        $embedding = $response->json();
        return $embedding['embeddings'][0];
    }
}
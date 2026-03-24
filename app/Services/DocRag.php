<?php

namespace App\Services;

use App\Jobs\ChunkingPapersJob;
use App\Models\Paper;
use Illuminate\Support\Facades\Storage;

class DocRag
{
    public function uploadFileAndChunck($data)
    {
        $path = $data->file('document')->store('docs');
        $docCreated = Paper::create([
            'document' => $path,
            'status' => 'pending',
        ]);
        ChunkingPapersJob::dispatch($path,$docCreated->id);
    }
}

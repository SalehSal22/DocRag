<?php

namespace App\Services;

use App\Models\Paper;
use Illuminate\Support\Facades\Storage;

class DocRag
{
    public function uploadFileAndChunck($data)
    {
        $path = $data->file('document')->storage('docs');
        Paper::create([
            'document' => $path
        ]);
        $this->chunckDoc($path);
    }
    public function chunckDoc($path)
    {
        $doc = Storage::get($path);
        $v1 = preg_split('/\n\s*\n/', $doc);
        $v2 = [];
        foreach ($v1 as $chunk) {
            $chunk = trim($chunk);
            if (strlen($chunk) < 50)
                continue;
            if (strlen($chunk) > 1000) {
                array_push($v2, ...preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $chunk));
            } else $v2[] = $chunk;
        }
        $v2 = array_values(array_filter($v2, fn($c) => strlen(trim($c)) > 50));
        for ($i = 1; $i < count($v2); $i++) {
            $v2[$i] = substr($v2[$i - 1], -150) . $v2[$i];
        }
    }
}

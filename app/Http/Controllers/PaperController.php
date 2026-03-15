<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocUploadRequest;
use App\Services\DocRag;
use Exception;
use Illuminate\Http\Request;

class PaperController extends Controller
{
    public function __construct(protected DocRag $docRag) {}
    public function uploadFile(DocUploadRequest $request)
    {
        $validated = $request->validated();
        try {
            $this->docRag->uploadFileAndChunck($validated);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

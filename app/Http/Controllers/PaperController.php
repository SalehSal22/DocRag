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
        $request->validated();
        try {
            $this->docRag->uploadFileAndChunck($request);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'uploaded'
        ], 201);
    }
    public function prompt(){
        
    }
}

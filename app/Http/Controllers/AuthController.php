<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequests\RegisterRequest;
use App\Http\Resources\AuthResources\RegisterResource;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceResponse;

class AuthController extends Controller
{

    public function Register(RegisterRequest $request, AuthService $authService)
    {
        $validated = $request->validated();
        try {
            $loggedInUser = $authService->Register($validated);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
        return (new RegisterResource($loggedInUser))->response()->setStatusCode(201);
    }


    public function refresh(Request $request, AuthService $authService)
    {
        $request->validate(['refresh_token' => 'required|string']);

        try {
            $result = $authService->refreshAccessToken($request->refresh_token);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 401);
        }

        return (new RegisterResource($result))->response()->setStatusCode(200);
    }
}




<?php

namespace App\Http\Controllers;

use App\Http\Requests\AUthRequests\LoginRequest;
use App\Http\Requests\AuthRequests\RegisterRequest;
use App\Http\Resources\AuthResources\RegisterResource;
use App\Services\AuthService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function register(RegisterRequest $request, AuthService $authService)
    {
        $validated = $request->validated();
        try {
            $loggedInUser = $authService->registerService($validated);
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




    public function login(LoginRequest $request, AuthService $authService)
    {
        $validated = $request->validated();
        try {
            $authed = $authService->loginService($validated);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
        return (new RegisterResource($authed))->response()->setStatusCode(200);
    }


    public function logout(AuthService $authService)
    {
        try {
            $authService->logout();
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'logged out successfully'
        ], 200);
    }
    public function me()
    {

            $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'name' => $user->name,
                'email' => $user->email
            ]
        ], 200);
    }
}

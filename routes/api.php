<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaperController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);
Route::middleware("auth:api")->group(function () {
    Route::post('/user/prompt', [PaperController::class, 'prompt']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/me', [AuthController::class, 'me']);
    Route::post('/user/uploadFile', [PaperController::class, 'uploadFile']);
});

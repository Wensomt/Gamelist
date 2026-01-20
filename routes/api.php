<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\Api\GameApiController; // <--- DODANE
use Illuminate\Support\Facades\Http;

// AUTORYZACJA
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);

// PROFIL
Route::middleware('auth:sanctum')->patch('/profile/password', [ProfileController::class, 'updatePassword']);

// PANEL ADMINA
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'listUsers']);
    Route::get('/users/{id}', [AdminController::class, 'showUser']);
    Route::patch('/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
});

// DOKUMENTACJA
Route::get('/documentation', [DocumentationController::class, 'index']);

// WYSZUKIWARKA GIER (Nowa trasa)
Route::get('/games/search', [GameApiController::class, 'search']); // <--- DODANE

// RAWG — NAJPROSTSZY MOŻLIWY TEST JEDNEJ GRY
Route::get('/rawg-one', function () {
    $id = 3498; 
    $response = Http::get(config('rawg.base_url') . "/games/{$id}", [
        'key' => config('rawg.key'),
    ]);
    return $response->json();
});

Route::middleware('auth:sanctum')->get('/check-auth', function () {
    return response()->json(['authenticated' => true]);
});
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProdukController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{produk}', [ProdukController::class, 'show']);
Route::get('/merek', [ProdukController::class, 'merek']);

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/produk', [ProdukController::class, 'adminIndex']);
    Route::post('/produk', [ProdukController::class, 'store']);
    Route::put('/produk/{produk}', [ProdukController::class, 'update']);
    Route::delete('/produk/{produk}', [ProdukController::class, 'destroy']);
});

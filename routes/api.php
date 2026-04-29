<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PublicCatalogoController;
use App\Http\Controllers\MiPedidoController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('categorias', CategoriaController::class);
Route::get('/catalogo', [PublicCatalogoController::class, 'getProductos']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('productos', ProductoController::class);
    Route::get('/mis-pedidos', [MiPedidoController::class, 'misPedidos']);
});
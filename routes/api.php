<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PublicCatalogoController;
use App\Http\Controllers\MiPedidoController;
use App\Http\Controllers\AdminPedidoController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::apiResource('categorias', CategoriaController::class);
Route::get('/catalogo', [PublicCatalogoController::class, 'getProductos']);
Route::get('/catalogo/{idProducto}', [PublicCatalogoController::class, 'show']);
Route::get('/mis-pedidos', [MiPedidoController::class, 'misPedidos']);
Route::post('/pedidos', [MiPedidoController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('productos', ProductoController::class);
    Route::get('/mis-pedidos', [MiPedidoController::class, 'misPedidos']);
    Route::get('/admin/pedidos', [AdminPedidoController::class, 'index']);
    Route::put('/admin/pedidos/{idPedido}/estado', [AdminPedidoController::class, 'updateEstado']);
    });
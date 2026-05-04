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

// Recuperar contraseña
Route::post('/password/forgot-email', [AuthController::class, 'forgotEmail']);
Route::post('/password/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

Route::apiResource('categorias', CategoriaController::class);

Route::get('/catalogo', [PublicCatalogoController::class, 'getProductos']);
Route::get('/catalogo/{idProducto}', [PublicCatalogoController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('productos', ProductoController::class);

    Route::post('/pedidos', [MiPedidoController::class, 'store']);
    Route::get('/mis-pedidos', [MiPedidoController::class, 'misPedidos']);

    Route::get('/admin/pedidos', [AdminPedidoController::class, 'index']);
    Route::put('/admin/pedidos/{idPedido}/estado', [AdminPedidoController::class, 'updateEstado']);
});
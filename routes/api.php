<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'ok' => true,
        'mensaje' => 'API funcionando'
    ]);
});

Route::get('/productos', function () {
    return response()->json([
        ['id' => 1, 'nombre' => 'Laptop', 'precio' => 3500],
        ['id' => 2, 'nombre' => 'Mouse', 'precio' => 120],
        ['id' => 3, 'nombre' => 'Teclado', 'precio' => 250],
    ]);
});
<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MiPedidoController extends Controller
{
    /**
     * Devuelve todos los pedidos del usuario autenticado con sus detalles,
     * productos y fotos.
     */
    public function misPedidos(Request $request): JsonResponse
    {
        /** @var \App\Models\Usuario $usuario */
        $usuario = $request->user();

        $pedidos = Pedido::with([
            'detalles.producto.fotos',   // carga producto y sus fotos dentro de cada detalle
        ])
        ->where('idUsuario', $usuario->idUsuario)
        ->orderBy('fechaCreacion', 'desc')
        ->get();

        return response()->json([
            'message' => 'Pedidos obtenidos correctamente',
            'pedidos' => $pedidos,
        ]);
    }
}
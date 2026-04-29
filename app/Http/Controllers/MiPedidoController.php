<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\DetallePedido;
use Illuminate\Support\Facades\DB;
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

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tipoPago'              => 'required|string',
            'observacion'          => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.idProducto'   => 'required|exists:Producto,idProducto',
            'items.*.cantidad'     => 'required|integer|min:1',
            'items.*.precioUnitario' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $total = collect($data['items'])->sum(
                fn($i) => $i['precioUnitario'] * $i['cantidad']
            );

            $pedido = Pedido::create([
                'idUsuario'     => $request->user()->idUsuario,
                'tipoPago'      => $data['tipoPago'],
                'observacion'   => $data['observacion'] ?? null,
                'estado'        => 'pendiente',
                'total'         => $total,
                'fechaCreacion' => now(),
            ]);

            foreach ($data['items'] as $item) {
                DetallePedido::create([
                    'idPedido'       => $pedido->idPedido,
                    'idProducto'     => $item['idProducto'],
                    'cantidad'       => $item['cantidad'],
                    'precioUnitario' => $item['precioUnitario'],
                    'subTotal'       => $item['precioUnitario'] * $item['cantidad'],
                ]);
            }

            return response()->json([
                'message' => 'Pedido creado correctamente',
                'pedido'  => $pedido->load('detalles.producto'),
            ], 201);
        });
    }
}
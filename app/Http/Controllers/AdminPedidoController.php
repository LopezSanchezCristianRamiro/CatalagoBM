<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPedidoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $pedidos = Pedido::with([
                'usuario',
                'detalles.producto.fotos',
                'detalles.producto.categoria',
            ])
            ->orderBy('fechaCreacion', 'desc')
            ->get();

        $pedidos = $pedidos->map(function ($pedido) {
            return [
                'idPedido' => $pedido->idPedido,
                'estado' => $pedido->estado,
                'total' => $pedido->total,
                'tipoPago' => $pedido->tipoPago,
                'observacion' => $pedido->observacion,
                'fechaCreacion' => $pedido->fechaCreacion,

                'usuario' => [
                    'idUsuario' => $pedido->usuario?->idUsuario,
                    'nombre' => $pedido->usuario?->nombre,
                    'nombres' => $pedido->usuario?->nombres,
                    'correo' => $pedido->usuario?->correo,
                    'telefono' => $pedido->usuario?->telefono,
                ],

                'detalles' => $pedido->detalles->map(function ($detalle) {
                    $producto = $detalle->producto;
                    $foto = $producto?->fotos?->first();

                    return [
                        'idDetallePedido' => $detalle->idDetallePedido,
                        'idProducto' => $detalle->idProducto,
                        'cantidad' => $detalle->cantidad,
                        'precioUnitario' => $detalle->precioUnitario,
                        'subTotal' => $detalle->subTotal,

                        'producto' => [
                            'idProducto' => $producto?->idProducto,
                            'nombre' => $producto?->nombre,
                            'precio' => $producto?->precio,
                            'imagen' => $foto?->urlFoto,

                            'categoria' => [
                                'idCategoria' => $producto?->categoria?->idCategoria,
                                'nombre' => $producto?->categoria?->nombre,
                            ],
                        ],
                    ];
                }),
            ];
        });

        return response()->json([
            'message' => 'Pedidos obtenidos correctamente',
            'pedidos' => $pedidos,
        ]);
    }
    public function updateEstado(Request $request, $idPedido): JsonResponse
{
    $data = $request->validate([
        'estado' => 'required|in:pendiente,pagado,cancelado,entregado',
    ]);

    $pedido = Pedido::findOrFail($idPedido);

    $pedido->update([
        'estado' => $data['estado'],
    ]);

    return response()->json([
        'message' => 'Estado actualizado correctamente',
        'pedido' => $pedido,
    ]);
}
}
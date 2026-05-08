<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPedidoController extends Controller
{
    private const ROL_ADMIN = 2;
    private const ROL_MASTER = 3;

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
                    'idRol' => $pedido->usuario?->idRol,
                    'nombre' => $pedido->usuario?->nombre,
                    'nombres' => $pedido->usuario?->nombres,
                    'name' => $pedido->usuario?->name,
                    'correo' => $pedido->usuario?->correo,
                    'celular' => $pedido->usuario?->celular,
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
                })->values(),
            ];
        })->values();

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

    public function usuarios(Request $request): JsonResponse
    {
        $usuarioAuth = $request->user();

        if (!$usuarioAuth || (int) $usuarioAuth->idRol !== self::ROL_MASTER) {
            return response()->json([
                'message' => 'Solo el usuario Master puede ver usuarios.',
            ], 403);
        }

        $usuarios = Usuario::orderBy('idUsuario', 'desc')
            ->get()
            ->map(function ($usuario) {
                return [
                    'idUsuario' => $usuario->idUsuario,
                    'idRol' => $usuario->idRol,
                    'nombre' => $usuario->nombre,
                    'nombres' => $usuario->nombres,
                    'apellido' => $usuario->apellido,
                    'apellidos' => $usuario->apellidos,
                    'correo' => $usuario->correo,
                    'email' => $usuario->email ?? null,
                    'celular' => $usuario->celular,
                    'telefono' => $usuario->telefono,
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Usuarios obtenidos correctamente.',
            'usuarios' => $usuarios,
        ]);
    }

    public function updateRolUsuario(Request $request, $idUsuario): JsonResponse
    {
        $usuarioAuth = $request->user();

        if (!$usuarioAuth || (int) $usuarioAuth->idRol !== self::ROL_MASTER) {
            return response()->json([
                'message' => 'Solo el usuario Master puede cambiar roles.',
            ], 403);
        }

        $data = $request->validate([
            'idRol' => 'required|integer|in:1,2',
        ]);

        $usuario = Usuario::findOrFail($idUsuario);

        if ((int) $usuario->idUsuario === (int) $usuarioAuth->idUsuario) {
            return response()->json([
                'message' => 'No puedes cambiar tu propio rol.',
            ], 400);
        }

        if ((int) $usuario->idRol === self::ROL_MASTER) {
            return response()->json([
                'message' => 'No puedes modificar otro usuario Master.',
            ], 403);
        }

        $usuario->update([
            'idRol' => $data['idRol'],
        ]);

        return response()->json([
            'message' => 'Rol actualizado correctamente.',
            'usuario' => $usuario,
        ]);
    }
}
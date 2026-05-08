<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    public function ventas(Request $request)
    {
        $request->validate([
            'fechaInicio' => 'required|date',
            'fechaFin'    => 'required|date|after_or_equal:fechaInicio',
        ]);

        $inicio = $request->fechaInicio . ' 00:00:00';
        $fin    = $request->fechaFin    . ' 23:59:59';

        // ─── Resumen general: todo en una sola query SQL ───────────────────────
        $resumen = DB::table('pedidos')
            ->whereBetween('fechaCreacion', [$inicio, $fin])
            ->where('estado', '!=', 'cancelado')
            ->selectRaw('
                COUNT(*)            AS totalPedidos,
                SUM(total)          AS ingresoTotal,
                AVG(total)          AS promedioVenta
            ')
            ->first();

        // ─── Total de productos vendidos ───────────────────────────────────────
        $totalProductos = DB::table('detalle_pedidos')
            ->join('pedidos', 'detalle_pedidos.idPedido', '=', 'pedidos.idPedido')
            ->whereBetween('pedidos.fechaCreacion', [$inicio, $fin])
            ->where('pedidos.estado', '!=', 'cancelado')
            ->sum('detalle_pedidos.cantidad');

        // ─── Agrupación por estado ─────────────────────────────────────────────
        $porEstado = DB::table('pedidos')
            ->whereBetween('fechaCreacion', [$inicio, $fin])
            ->where('estado', '!=', 'cancelado')
            ->groupBy('estado')
            ->selectRaw('estado, COUNT(*) AS cantidad, SUM(total) AS total')
            ->get()
            ->keyBy('estado');

        // ─── Productos más vendidos ────────────────────────────────────────────
        $productosMasVendidos = DB::table('detalle_pedidos AS d')
            ->join('pedidos AS p',   'p.idPedido',   '=', 'd.idPedido')
            ->join('productos AS pr', 'pr.idProducto', '=', 'd.idProducto')
            ->whereBetween('p.fechaCreacion', [$inicio, $fin])
            ->where('p.estado', '!=', 'cancelado')
            ->groupBy('d.idProducto', 'pr.nombre')
            ->selectRaw('d.idProducto, pr.nombre, SUM(d.cantidad) AS totalVendido, SUM(d.subTotal) AS ingresoTotal')
            ->orderByDesc('ingresoTotal')
            ->limit(20)
            ->get();

        // ─── Pedidos para el detalle del reporte (con relaciones mínimas) ──────
        // Solo cargamos lo necesario para la vista; sin flatMap en PHP.
        $pedidos = Pedido::with([
                'usuario:idUsuario,nombre,correo',
                'detalles:idDetalle,idPedido,idProducto,cantidad,subTotal',
                'detalles.producto:idProducto,nombre',
            ])
            ->whereBetween('fechaCreacion', [$inicio, $fin])
            ->where('estado', '!=', 'cancelado')
            ->latest('fechaCreacion')
            ->get();

        $data = [
            'periodo' => $request->only(['fechaInicio', 'fechaFin']),
            'resumen' => [
                'totalPedidos'   => (int)   ($resumen->totalPedidos  ?? 0),
                'ingresoTotal'   => (float)  ($resumen->ingresoTotal  ?? 0),
                'promedioVenta'  => (float)  ($resumen->promedioVenta ?? 0),
                'totalProductos' => (int)    $totalProductos,
            ],
            'porEstado'            => $porEstado,
            'productosMasVendidos' => $productosMasVendidos,
            'pedidos'              => $pedidos,
        ];

        return Pdf::loadView('reportes.reporte_ventas', $data)
            ->setPaper('a4', 'portrait')
            ->download('reporte.pdf');
    }
}
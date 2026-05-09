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

        // ─── Resumen general ───────────────────────────────────────────────────
        $resumen = DB::table('Pedido') // <-- Cambiado a 'Pedido'
            ->whereBetween('fechaCreacion', [$inicio, $fin])
            ->where('estado', '!=', 'cancelado')
            ->selectRaw('
                COUNT(*)            AS totalPedidos,
                SUM(total)          AS ingresoTotal,
                AVG(total)          AS promedioVenta
            ')
            ->first();

        // ─── Total de productos vendidos ───────────────────────────────────────
        $totalProductos = DB::table('DetallePedido') // <-- Cambiado a 'DetallePedido'
            ->join('Pedido', 'DetallePedido.idPedido', '=', 'Pedido.idPedido') // <-- Cambiado a 'Pedido'
            ->whereBetween('Pedido.fechaCreacion', [$inicio, $fin])
            ->where('Pedido.estado', '!=', 'cancelado')
            ->sum('DetallePedido.cantidad');

        // ─── Agrupación por estado ─────────────────────────────────────────────
        $porEstado = DB::table('Pedido') // <-- Cambiado a 'Pedido'
            ->whereBetween('fechaCreacion', [$inicio, $fin])
            ->where('estado', '!=', 'cancelado')
            ->groupBy('estado')
            ->selectRaw('estado, COUNT(*) AS cantidad, SUM(total) AS total')
            ->get()
            ->keyBy('estado');

        // ─── Productos más vendidos ────────────────────────────────────────────
        $productosMasVendidos = DB::table('DetallePedido AS d') // <-- Cambiado a 'DetallePedido'
            ->join('Pedido AS p',   'p.idPedido',   '=', 'd.idPedido') // <-- Cambiado a 'Pedido'
            ->join('Producto AS pr', 'pr.idProducto', '=', 'd.idProducto') // <-- Cambiado a 'Producto'
            ->whereBetween('p.fechaCreacion', [$inicio, $fin])
            ->where('p.estado', '!=', 'cancelado')
            ->groupBy('d.idProducto', 'pr.nombre')
            ->selectRaw('d.idProducto, pr.nombre, SUM(d.cantidad) AS totalVendido, SUM(d.subTotal) AS ingresoTotal')
            ->orderByDesc('ingresoTotal')
            ->limit(20)
            ->get();

        // ─── Pedidos para el detalle del reporte ───────────────────────────────
        // Eloquent SÍ usa tus modelos automáticamente, así que esto ya funcionaba bien
        $pedidos = Pedido::with([
                'usuario:idUsuario,nombre,correo',
                'detalles:idDetallePedido,idPedido,idProducto,cantidad,subTotal', // <-- Asegúrate de que el ID aquí coincida con tu primaryKey 'idDetallePedido'
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
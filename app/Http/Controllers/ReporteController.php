<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function ventas(Request $request)
    {
        $request->validate([
            'fechaInicio' => 'required|date',
            'fechaFin'    => 'required|date|after_or_equal:fechaInicio',
        ]);

        $pedidos = Pedido::with(['usuario', 'detalles.producto'])
            ->whereBetween('fechaCreacion', [$request->fechaInicio.' 00:00:00', $request->fechaFin.' 23:59:59'])
            ->where('estado', '!=', 'cancelado')
            ->latest('fechaCreacion')
            ->get();

        // Cálculos rápidos
        $totalVentas = $pedidos->count();
        $ingresoTotal = $pedidos->sum('total');

        $data = [
            'periodo' => $request->only(['fechaInicio', 'fechaFin']),
            'resumen' => [
                'totalPedidos'  => $totalVentas,
                'ingresoTotal'  => $ingresoTotal,
                'promedioVenta' => $totalVentas > 0 ? $ingresoTotal / $totalVentas : 0,
                'totalProductos' => $pedidos->flatMap->detalles->sum('cantidad'),
            ],
            'porEstado' => $pedidos->groupBy('estado')->map(fn($g) => ['cantidad' => $g->count(), 'total' => $g->sum('total')]),
            'productosMasVendidos' => $pedidos->flatMap->detalles->groupBy('idProducto')->map(fn($d) => [
                'nombre' => $d->first()->producto->nombre ?? 'N/A',
                'ingresoTotal' => $d->sum('subTotal')
            ])->sortByDesc('ingresoTotal'),
            'pedidos' => $pedidos
        ];

        return Pdf::loadView('reportes.reporte_ventas', $data)
            ->setPaper('a4', 'portrait')
            ->download('reporte.pdf');
    }
}
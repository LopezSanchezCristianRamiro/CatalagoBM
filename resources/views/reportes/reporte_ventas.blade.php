<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 9px; color: #333; line-height: 1.2; }
        
        /* Header ultra compacto */
        .header-mini { 
            border-bottom: 2px solid #4f46e5; 
            padding-bottom: 5px; 
            margin-bottom: 15px; 
        }
        .title { font-size: 14px; font-weight: bold; color: #4f46e5; text-transform: uppercase; }
        
        .w-100 { width: 100%; border-collapse: collapse; }
        
        /* KPIs en una sola fila pequeña */
        .kpi-container { margin-bottom: 15px; }
        .kpi-box { 
            border: 1px solid #25252b; 
            padding: 5px; 
            text-align: center; 
            background: #fcfcfc;
        }
        .kpi-label { font-size: 7px; color: #666; display: block; }
        .kpi-value { font-size: 11px; font-weight: bold; }

        /* Tablas densas */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { 
            background: #4f46e5; 
            color: white; 
            padding: 4px 6px; 
            text-align: left; 
            font-size: 8px;
            border: 1px solid #33333b;
        }
        .data-table td { padding: 4px 6px; border-bottom: 0.5px solid #181515; }
        
        .section-title { 
            font-size: 10px; 
            font-weight: bold; 
            margin: 10px 0 5px 0; 
            color: #4f46e5;
            border-left: 3px solid #4f46e5;
            padding-left: 5px;
        }

        .text-right { text-align: right; }
        .badge { font-size: 7px; padding: 1px 4px; border-radius: 3px; font-weight: bold; background: #eee; }
    </style>
</head>
<body>

    <div class="header-mini">
        <table class="w-100">
            <tr>
                <td>
                    <span class="title">Streaming App</span><br>
                    <span>Reporte de Ventas Académico</span>
                </td>
                <td class="text-right" style="font-size: 8px;">
                    <strong>Periodo:</strong> {{ $periodo['fechaInicio'] }} al {{ $periodo['fechaFin'] }}<br>
                    <strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <table class="w-100 kpi-container">
        <tr>
            <td width="25%"><div class="kpi-box"><span class="kpi-label">PEDIDOS</span><span class="kpi-value">{{ $resumen['totalPedidos'] }}</span></div></td>
            <td width="25%"><div class="kpi-box"><span class="kpi-label">TOTAL INGRESOS</span><span class="kpi-value">Bs. {{ number_format($resumen['ingresoTotal'], 2) }}</span></div></td>
            <td width="25%"><div class="kpi-box"><span class="kpi-label">TICKET PROM.</span><span class="kpi-value">Bs. {{ number_format($resumen['promedioVenta'], 2) }}</span></div></td>
            <td width="25%"><div class="kpi-box"><span class="kpi-label">PRODUCTOS</span><span class="kpi-value">{{ $resumen['totalProductos'] }}</span></div></td>
        </tr>
    </table>

    <table class="w-100">
        <tr>
            <td width="48%" style="vertical-align: top;">
                <div class="section-title">VENTAS POR ESTADO</div>
                <table class="data-table">
                    <!-- CORRECCIÓN AQUÍ: Se usa -> en lugar de [] -->
                    @foreach($porEstado as $estado => $val)
                    <tr>
                        <td><span class="badge">{{ strtoupper($estado) }}</span></td>
                        <td>{{ $val->cantidad }} vtas</td>
                        <td class="text-right">Bs. {{ number_format($val->total, 2) }}</td>
                    </tr>
                    @endforeach
                </table>
            </td>
            <td width="4%"></td>
            <td width="48%" style="vertical-align: top;">
                <div class="section-title">TOP PRODUCTOS</div>
                <table class="data-table">
                    <!-- CORRECCIÓN AQUÍ: Se usa -> en lugar de [] -->
                    @foreach($productosMasVendidos->take(5) as $prod)
                    <tr>
                        <td>{{ $prod->nombre }}</td>
                        <td class="text-right">Bs. {{ number_format($prod->ingresoTotal, 2) }}</td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    <div class="section-title">DETALLE DE OPERACIONES</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>CLIENTE</th>
                <th>FECHA</th>
                <th>ESTADO</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pedidos as $p)
            <tr>
                <td>{{ $p->idPedido }}</td>
                <td>{{ $p->usuario->nombre ?? 'N/A' }}</td>
                <td>{{ \Carbon\Carbon::parse($p->fechaCreacion)->format('d/m/y') }}</td>
                <td>{{ $p->estado }}</td>
                <td class="text-right"><strong>Bs. {{ number_format($p->total, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
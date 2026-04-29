<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetallePedido extends Model
{
    protected $table = 'DetallePedido';
    protected $primaryKey = 'idDetallePedido';

    protected $fillable = [
        'idProducto',
        'cantidad',
        'precioUnitario',
        'subTotal',
        'idPedido',
    ];

    protected $casts = [
        'precioUnitario' => 'decimal:2',
        'subTotal' => 'decimal:2',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'idProducto', 'idProducto');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'idPedido', 'idPedido');
    }
}
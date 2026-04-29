<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    protected $table = 'Pedido';
    protected $primaryKey = 'idPedido';

    protected $fillable = [
        'estado',
        'total',
        'tipoPago',
        'observacion',
        'fechaCreacion',
        'idUsuario',
    ];

    protected $casts = [
        'fechaCreacion' => 'datetime',
        'total' => 'decimal:2',
    ];

    // Relación con Usuario (cliente que realizó el pedido)
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }

    // Relación con los detalles del pedido
    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class, 'idPedido', 'idPedido');
    }
}
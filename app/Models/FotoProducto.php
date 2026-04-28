<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoProducto extends Model
{
    protected $table = 'FotoProducto';
    protected $primaryKey = 'idFotoProducto';

    public $timestamps = false;

    protected $fillable = [
        'urlFoto',
        'idProducto',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'idProducto', 'idProducto');
    }
}
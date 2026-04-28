<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'Producto';
    protected $primaryKey = 'idProducto';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'precioDescuento',
        'idCategoria',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idCategoria', 'idCategoria');
    }

    public function fotos()
    {
        return $this->hasMany(FotoProducto::class, 'idProducto', 'idProducto');
    }
}
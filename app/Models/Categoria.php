<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'Categoria';
    protected $primaryKey = 'idCategoria';

    public $timestamps = true;

    protected $fillable = [
        'nombre',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'idCategoria', 'idCategoria');
    }
}
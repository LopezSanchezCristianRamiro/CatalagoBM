<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'Rol';
    protected $primaryKey = 'idRol';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['nombre'];
}
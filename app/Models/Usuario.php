<?php
// app/Models/Usuario.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'Usuario';           
    protected $primaryKey = 'idUsuario';    
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nombre',
        'nombreUsuario',
        'foto',
        'correo',
        'telefono',
        'password',
        'idRol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'idRol', 'idRol');
    }

    public function getEmailAttribute(): string
    {
        return $this->correo;
    }
}
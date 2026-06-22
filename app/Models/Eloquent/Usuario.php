<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use App\Models\Eloquent\Socio;
use App\Models\Eloquent\Empleado;

class Usuario extends Model
{
    protected $table = 'tusuarios';
    protected $primaryKey = 'idUsuario';
    public $timestamps = false;
    
    protected $fillable = [
        'idRol', 'nombre1', 'nombre2', 'apellido1', 'apellido2',
        'correo', 'telefono', 'contrasena', 'estado', 'estadoA', 'fechaA', 'usuarioA'
    ];
    
    public function socio()
    {
        return $this->hasOne(Socio::class, 'idUsuario', 'idUsuario');
    }
    
    public function empleado()
    {
        return $this->hasOne(Empleado::class, 'idUsuario', 'idUsuario');
    }
}

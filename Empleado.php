<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'templeados';
    protected $primaryKey = 'carnetEmpleado';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'carnetEmpleado',
        'idUsuario',
        'idSucursal',
        'sueldo',
        'especialidad',
        'fechaContratoInicio',
        'fechaContratoFin',
        'estadoA',
        'fechaA',
        'usuarioA'
    ];
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }
    
    public function clases()
    {
        return $this->hasMany(Clase::class, 'carnetEmpleado', 'carnetEmpleado');
    }
}
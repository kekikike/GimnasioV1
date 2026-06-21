<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clase extends Model
{
    protected $table = 'tclasegrupales';
    protected $primaryKey = 'idClaseGrupal';
    public $timestamps = false;
    
    protected $fillable = [
        'idActividad',
        'carnetEmpleado',
        'idSucursal',
        'fecha',
        'horaInicio',
        'horaFin',
        'cupoMaximo',
        'estadoClase',
        'estadoA',
        'fechaA',
        'usuarioA'
    ];
    
    public function instructor()
    {
        return $this->belongsTo(Empleado::class, 'carnetEmpleado', 'carnetEmpleado');
    }
    
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'idClaseGrupal', 'idClaseGrupal');
    }
    
    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'idActividad', 'idActividad');
    }
}
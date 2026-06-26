<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use App\Models\Clase;

class Empleado extends Model
{
    protected $table = 'templeados';
    protected $primaryKey = 'carnetEmpleado';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'carnetEmpleado', 'idUsuario', 'idSucursal',
        'fechaContratoInicio', 'fechaContratoFin', 'estadoA', 'fechaA', 'usuarioA'
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

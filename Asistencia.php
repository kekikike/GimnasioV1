<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'tcontrolasistencias';
    protected $primaryKey = 'idAsistencia';
    public $timestamps = false;
    
    protected $fillable = [
        'carnetEmpleado',
        'fecha',
        'horaEntrada',
        'horaSalida',
        'estadoAsistencia',
        'estadoA',
        'fechaA',
        'usuarioA'
    ];
    
    // Relación con Socio (para asistencias de socios)
    public function socio()
    {
        return $this->belongsTo(Socio::class, 'carnetEmpleado', 'carnetSocio');
    }
}
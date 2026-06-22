<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    protected $table = 'treportefallas';
    protected $primaryKey = 'idReporteFalla';
    public $timestamps = false;
    
    protected $fillable = [
        'idEquipo',
        'carnetEmpleado',
        'fechaReporte',
        'descripcionFalla',
        'gravedad',
        'estadoReporte',
        'estadoA',
        'fechaA',
        'usuarioA'
    ];
    
    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'idEquipo', 'idEquipo');
    }
}
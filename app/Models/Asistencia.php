<?php

namespace App\Models;

use App\Models\Eloquent\Socio as SocioEloquent;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'tasistenciaspersonal';
    protected $primaryKey = 'idAsistencia';
    public $timestamps = false;
    
    protected $fillable = [
        'carnetEmpleado',
        'fechaHoraEntrada',
        'fechaHoraSalida',
        'estadoAsistencia',
        'estadoA',
        'fechaA',
        'usuarioA'
    ];
    
    public function socio()
    {
        return $this->belongsTo(SocioEloquent::class, 'carnetEmpleado', 'carnetSocio');
    }
}

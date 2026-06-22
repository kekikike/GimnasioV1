<?php

namespace App\Models;

use App\Models\Eloquent\Socio as SocioEloquent;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'treservas';
    protected $primaryKey = 'idReserva';
    public $timestamps = false;
    
    protected $fillable = [
        'idClaseGrupal',
        'carnetSocio',
        'fechaReserva',
        'estadoReserva',
        'estadoA',
        'fechaA',
        'usuarioA'
    ];
    
    public function socio()
    {
        return $this->belongsTo(SocioEloquent::class, 'carnetSocio', 'carnetSocio');
    }
    
    public function clase()
    {
        return $this->belongsTo(Clase::class, 'idClaseGrupal', 'idClaseGrupal');
    }
}
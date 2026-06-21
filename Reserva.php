<?php

namespace App\Models;

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
        return $this->belongsTo(Socio::class, 'carnetSocio', 'carnetSocio');
    }
    
    public function clase()
    {
        return $this->belongsTo(Clase::class, 'idClaseGrupal', 'idClaseGrupal');
    }
}
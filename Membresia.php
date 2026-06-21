<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membresia extends Model
{
    protected $table = 'tmembresias';
    protected $primaryKey = 'idMembresia';
    public $timestamps = false;
    
    protected $fillable = [
        'idPlan',
        'carnetSocio',
        'idSucursal',
        'fechaInicioMembresia',
        'fechaFinMembresia',
        'estadoMembresia',
        'estadoA',
        'fechaA',
        'usuarioA'
    ];
    
    public function socio()
    {
        return $this->belongsTo(Socio::class, 'carnetSocio', 'carnetSocio');
    }
    
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'idPlan', 'idPlan');
    }
}
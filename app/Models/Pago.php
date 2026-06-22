<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'tcajas';
    protected $primaryKey = 'idCaja';
    public $timestamps = false;
    
    protected $fillable = [
        'idSucursal',
        'carnetEmpleado',
        'fechaApertura',
        'horaApertura',
        'montoApertura',
        'montoCierre',
        'montoCierreCalculado',
        'diferenciaArqueo',
        'estadoCaja',
        'estadoA',
        'fechaA',
        'usuarioA'
    ];
    
    public function socio()
    {
        return $this->belongsTo(Socio::class, 'carnetEmpleado', 'carnetSocio');
    }
}
<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use App\Models\Membresia;
use App\Models\Asistencia;
use App\Models\Pago;
use App\Models\Reserva;

class Socio extends Model
{
    protected $table = 'tsocios';
    protected $primaryKey = 'carnetSocio';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'carnetSocio', 'idUsuario', 'direccion', 'fotografiaUrl',
        'nombreContactoEmergencia', 'telefonoContactoEmergencia',
        'observacionesMedicas', 'estadoSocio', 'strikes', 'estadoA', 'fechaA', 'usuarioA'
    ];
    
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }
    
    public function membresia()
    {
        return $this->hasOne(Membresia::class, 'carnetSocio', 'carnetSocio');
    }
    
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'carnetSocio', 'carnetSocio');
    }
    
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'carnetSocio', 'carnetSocio');
    }
    
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'carnetSocio', 'carnetSocio');
    }
}

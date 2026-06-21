<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Socio extends Model
{
    protected $table = 'tsocios';
    protected $primaryKey = 'carnetSocio';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'carnetSocio',
        'idUsuario',
        'direccion',
        'fotografiaUrl',
        'nombreContactoEmergencia',
        'telefonoContactoEmergencia',
        'observacionesMedicas',
        'estadoSocio',
        'strikes',
        'estadoA',
        'fechaA',
        'usuarioA'
    ];
    
    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }
    
    // Relación con Membresia
    public function membresia()
    {
        return $this->hasOne(Membresia::class, 'carnetSocio', 'carnetSocio');
    }
    
    // Relación con Asistencias
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'carnetSocio', 'carnetSocio');
    }
    
    // Relación con Pagos
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'carnetSocio', 'carnetSocio');
    }
    
    // Relación con Reservas
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'carnetSocio', 'carnetSocio');
    }
}
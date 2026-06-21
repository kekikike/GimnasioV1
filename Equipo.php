<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    protected $table = 'tequipamientos';
    protected $primaryKey = 'idEquipo';
    public $timestamps = false;
    
    protected $fillable = [
        'idSucursal',
        'idMarca',
        'nombreEquipo',
        'modelo',
        'fechaAdquisicion',
        'estadoEquipo',
        'estadoA',
        'fechaA',
        'usuarioA'
    ];
    
    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'idEquipo', 'idEquipo');
    }
}
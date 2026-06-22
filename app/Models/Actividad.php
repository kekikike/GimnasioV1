<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'tactividades';
    protected $primaryKey = 'idActividad';
    public $timestamps = false;
    
    protected $fillable = [
        'nombreActividad',
        'descripcionActividad',
        'estado',
        'estadoA',
        'fechaA',
        'usuarioA'
    ];
    
    public function clases()
    {
        return $this->hasMany(Clase::class, 'idActividad', 'idActividad');
    }
}
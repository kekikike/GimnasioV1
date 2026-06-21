<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $table = 'tplanes';
    protected $primaryKey = 'idPlan';
    public $timestamps = false;
    
    protected $fillable = [
        'nombrePlan',
        'descripcion',
        'costoPlan',
        'duracionDias',
        'estadoA',
        'fechaA',
        'usuarioA'
    ];
    
    public function membresias()
    {
        return $this->hasMany(Membresia::class, 'idPlan', 'idPlan');
    }
}
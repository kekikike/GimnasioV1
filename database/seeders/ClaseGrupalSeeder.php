<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClaseGrupalSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TClaseGrupales')->insert([
            'idActividad' => 1,
            'carnetEmpleado' => 1002,
            'idSucursal' => 1,
            'fecha' => '2024-06-18',
            'horaInicio' => '07:00:00',
            'horaFin' => '08:00:00',
            'cupoMaximo' => 20,
            'cupoDisponible' => 15,
            'estadoClase' => 'Programada',
            'usuarioA' => $adminId,
        ]);
    }
}

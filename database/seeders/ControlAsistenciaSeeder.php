<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ControlAsistenciaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TControlAsistencias')->insert([
            [
                'carnetEmpleado' => 1001,
                'fecha' => '2024-06-17',
                'horaEntrada' => '08:05:00',
                'horaSalida' => '17:00:00',
                'estadoAsistencia' => 'Presente',
                'usuarioA' => $adminId,
            ],
            [
                'carnetEmpleado' => 1002,
                'fecha' => '2024-06-17',
                'horaEntrada' => '06:00:00',
                'horaSalida' => '14:00:00',
                'estadoAsistencia' => 'Presente',
                'usuarioA' => $adminId,
            ],
        ]);
    }
}

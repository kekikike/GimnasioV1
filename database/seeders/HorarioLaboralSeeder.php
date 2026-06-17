<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HorarioLaboralSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('THorarioLaborales')->insert([
            [
                'carnetEmpleado' => 1001,
                'diaSemana' => 'Lunes',
                'horaEntradaEsperada' => '08:00:00',
                'horaSalidaEsperada' => '17:00:00',
                'usuarioA' => $adminId,
            ],
            [
                'carnetEmpleado' => 1001,
                'diaSemana' => 'Martes',
                'horaEntradaEsperada' => '08:00:00',
                'horaSalidaEsperada' => '17:00:00',
                'usuarioA' => $adminId,
            ],
            [
                'carnetEmpleado' => 1002,
                'diaSemana' => 'Lunes',
                'horaEntradaEsperada' => '06:00:00',
                'horaSalidaEsperada' => '14:00:00',
                'usuarioA' => $adminId,
            ],
            [
                'carnetEmpleado' => 1002,
                'diaSemana' => 'Miércoles',
                'horaEntradaEsperada' => '06:00:00',
                'horaSalidaEsperada' => '14:00:00',
                'usuarioA' => $adminId,
            ],
        ]);
    }
}

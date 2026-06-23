<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HorarioLaboralSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        $empleados = [
            ['carnet' => 1001, 'entrada' => '08:00', 'salida' => '17:00'],
            ['carnet' => 1002, 'entrada' => '06:00', 'salida' => '14:00'],
            ['carnet' => 2001, 'entrada' => '14:00', 'salida' => '22:00'],
            ['carnet' => 2002, 'entrada' => '07:00', 'salida' => '15:00'],
            ['carnet' => 2003, 'entrada' => '09:00', 'salida' => '18:00'],
            ['carnet' => 5001, 'entrada' => '08:00', 'salida' => '17:00'],
        ];

        $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'];

        $registros = [];
        foreach ($empleados as $emp) {
            foreach ($dias as $dia) {
                $registros[] = [
                    'carnetEmpleado' => $emp['carnet'],
                    'diaSemana' => $dia,
                    'horaEntradaEsperada' => $emp['entrada'] . ':00',
                    'horaSalidaEsperada' => $emp['salida'] . ':00',
                    'usuarioA' => $adminId,
                    'estadoA' => 1,
                ];
            }
        }

        DB::table('THorarioLaborales')->insert($registros);
    }
}

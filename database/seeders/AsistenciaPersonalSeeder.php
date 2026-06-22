<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsistenciaPersonalSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');
        $empleados = [
            ['carnet' => 1000, 'horaEntrada' => '08:00', 'horaSalida' => '17:00'],
            ['carnet' => 1001, 'horaEntrada' => '08:00', 'horaSalida' => '17:00'],
            ['carnet' => 1002, 'horaEntrada' => '06:00', 'horaSalida' => '14:00'],
            ['carnet' => 2001, 'horaEntrada' => '07:00', 'horaSalida' => '16:00'],
            ['carnet' => 2002, 'horaEntrada' => '09:00', 'horaSalida' => '18:00'],
            ['carnet' => 2003, 'horaEntrada' => '10:00', 'horaSalida' => '19:00'],
            ['carnet' => 5001, 'horaEntrada' => '08:00', 'horaSalida' => '17:00'],
        ];
        $estados = ['Puntual', 'Puntual', 'Puntual', 'Tardanza', 'Falta'];

        $registros = [];
        for ($dia = 0; $dia < 172; $dia++) {
            $fecha = date('Y-m-d', strtotime("2026-01-01 + $dia days"));
            $diaSemana = date('w', strtotime($fecha));
            if ($diaSemana == 0 || $diaSemana == 6) continue;

            foreach ($empleados as $emp) {
                $estado = $estados[array_rand($estados)];

                if ($estado === 'Falta') {
                    continue;
                } elseif ($estado === 'Tardanza') {
                    $retraso = rand(5, 30);
                    $horaEntrada = date('H:i:s', strtotime($emp['horaEntrada'] . " + $retraso minutes"));
                    $horaSalida = $emp['horaSalida'] . ':00';
                } else {
                    $horaEntrada = $emp['horaEntrada'] . ':00';
                    $horaSalida = $emp['horaSalida'] . ':00';
                }

                $registros[] = [
                    'carnetEmpleado' => $emp['carnet'],
                    'fechaHoraEntrada' => $fecha . ' ' . $horaEntrada,
                    'fechaHoraSalida' => $fecha . ' ' . $horaSalida,
                    'estadoAsistencia' => $estado,
                    'estadoA' => 1,
                    'usuarioA' => $adminId,
                ];
            }
        }

        DB::table('TAsistenciasPersonal')->insert($registros);
    }
}

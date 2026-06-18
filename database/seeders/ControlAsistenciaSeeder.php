<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ControlAsistenciaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');
        $empleados = [
            ['carnet' => 1001, 'horaEntradaEsperada' => '08:00', 'horaSalidaEsperada' => '17:00'],
            ['carnet' => 1002, 'horaEntradaEsperada' => '06:00', 'horaSalidaEsperada' => '14:00'],
        ];
        $estados = ['Puntual', 'Puntual', 'Puntual', 'Tardanza', 'Falta'];

        $registros = [];
        for ($dia = 0; $dia < 172; $dia++) {
            if (count($registros) >= 105) break;
            $fecha = date('Y-m-d', strtotime("2026-01-01 + $dia days"));
            $diaSemana = date('w', strtotime($fecha));
            if ($diaSemana == 0 || $diaSemana == 6) continue;

            foreach ($empleados as $emp) {
                if (count($registros) >= 105) break;
                $estado = $estados[array_rand($estados)];

                if ($estado === 'Falta') {
                    $horaEntrada = '00:00:00';
                    $horaSalida = '00:00:00';
                } elseif ($estado === 'Tardanza') {
                    $retraso = rand(5, 30);
                    $horaEntrada = date('H:i:s', strtotime($emp['horaEntradaEsperada'] . " + $retraso minutes"));
                    $horaSalida = $emp['horaSalidaEsperada'] . ':00';
                } else {
                    $horaEntrada = $emp['horaEntradaEsperada'] . ':00';
                    $horaSalida = $emp['horaSalidaEsperada'] . ':00';
                }

                $registros[] = [
                    'carnetEmpleado' => $emp['carnet'],
                    'fecha' => $fecha,
                    'horaEntrada' => $horaEntrada,
                    'horaSalida' => $horaSalida,
                    'estadoAsistencia' => $estado,
                    'usuarioA' => $adminId,
                ];
            }
        }

        DB::table('TControlAsistencias')->insert($registros);
    }
}

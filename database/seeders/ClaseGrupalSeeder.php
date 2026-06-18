<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClaseGrupalSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        $actividades = [1, 2, 3, 4, 5];
        $horarios = [
            ['inicio' => '07:00:00', 'fin' => '08:00:00'],
            ['inicio' => '08:30:00', 'fin' => '09:30:00'],
            ['inicio' => '10:00:00', 'fin' => '11:00:00'],
            ['inicio' => '15:00:00', 'fin' => '16:00:00'],
            ['inicio' => '17:00:00', 'fin' => '18:00:00'],
            ['inicio' => '18:30:00', 'fin' => '19:30:00'],
        ];
        $estados = ['Programada', 'Programada', 'Programada', 'Cursandose', 'Cancelada'];

        $clases = [];
        for ($dia = 0; $dia < 172; $dia++) {
            if (count($clases) >= 105) break;
            $fecha = date('Y-m-d', strtotime("2026-01-01 + $dia days"));
            $diaSemana = date('w', strtotime($fecha));
            if ($diaSemana == 0 || $diaSemana == 6) continue;
            $numClases = rand(1, 3);
            $actividadesUsadas = [];
            for ($c = 0; $c < $numClases && count($clases) < 105; $c++) {
                $actividad = $actividades[array_rand($actividades)];
                while (in_array($actividad, $actividadesUsadas)) {
                    $actividad = $actividades[array_rand($actividades)];
                }
                $actividadesUsadas[] = $actividad;
                $horario = $horarios[array_rand($horarios)];
                $cupoMax = rand(15, 30);
                $cupoDisp = rand(0, $cupoMax);
                $estado = $estados[array_rand($estados)];

                $clases[] = [
                    'idActividad' => $actividad,
                    'carnetEmpleado' => 1002,
                    'idSucursal' => 1,
                    'fecha' => $fecha,
                    'horaInicio' => $horario['inicio'],
                    'horaFin' => $horario['fin'],
                    'cupoMaximo' => $cupoMax,
                    'cupoDisponible' => $cupoDisp,
                    'estadoClase' => $estado,
                    'usuarioA' => $adminId,
                ];
            }
        }

        DB::table('TClaseGrupales')->insert($clases);
    }
}

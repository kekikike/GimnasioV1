<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CajaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');
        $empleados = [1001, 1002];
        $estados = ['Cerrada', 'Cerrada', 'Cerrada', 'Auditada', 'Abierta'];

        $registros = [];
        for ($dia = 0; $dia < 172; $dia++) {
            if (count($registros) >= 105) break;
            $fecha = date('Y-m-d', strtotime("2026-01-01 + $dia days"));
            $diaSemana = date('w', strtotime($fecha));
            if ($diaSemana == 0 || $diaSemana == 6) continue;

            $empleado = $empleados[array_rand($empleados)];
            $estado = $estados[array_rand($estados)];
            $montoApertura = round(rand(200, 1000), 2);
            $montoCierre = $estado !== 'Abierta' ? round($montoApertura + rand(100, 3000), 2) : null;
            $montoCalculado = $montoCierre ? round($montoCierre + rand(-50, 50), 2) : null;
            $diferencia = $montoCierre && $montoCalculado ? round($montoCalculado - $montoCierre, 2) : null;

            $registros[] = [
                'idSucursal' => 1,
                'carnetEmpleado' => $empleado,
                'fechaApertura' => $fecha,
                'horaApertura' => '08:00:00',
                'montoApertura' => $montoApertura,
                'montoCierre' => $montoCierre,
                'montoCierreCalculado' => $montoCalculado,
                'diferenciaArqueo' => $diferencia,
                'estadoCaja' => $estado,
                'usuarioA' => $adminId,
            ];
        }

        DB::table('TCajas')->insert($registros);
    }
}

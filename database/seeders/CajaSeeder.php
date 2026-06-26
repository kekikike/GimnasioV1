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

        $hasta = date('Y-m-d', strtotime('-1 day'));
        $registros = [];
        for ($dia = 0; $dia < 500; $dia++) {
            $fecha = date('Y-m-d', strtotime("2026-01-01 + $dia days"));
            if ($fecha > $hasta) break;
            $diaSemana = date('w', strtotime($fecha));
            if ($diaSemana == 0 || $diaSemana == 6) continue;

            $empleado = $empleados[array_rand($empleados)];
            $estado = 'Cerrada';
            $montoApertura = round(rand(200, 1000), 2);
            $montoCierre = round($montoApertura + rand(100, 3000), 2);
            $montoCalculado = round($montoCierre + rand(-50, 50), 2);
            $diferencia = round($montoCalculado - $montoCierre, 2);
            $cierreEstado = abs($diferencia) <= 0.01 ? 'Bien' : 'Observado';
            $cierreObservacion = $cierreEstado === 'Observado' ? 'Diferencia encontrada en arqueo de caja.' : null;

            $registros[] = [
                'idSucursal' => 1,
                'carnetEmpleado' => $empleado,
                'fechaApertura' => $fecha,
                'horaApertura' => '08:00:00',
                'montoApertura' => $montoApertura,
                'montoCierre' => $montoCierre,
                'montoCierreCalculado' => $montoCalculado,
                'diferenciaArqueo' => $diferencia,
                'cierreEstado' => $cierreEstado,
                'cierreObservacion' => $cierreObservacion,
                'estadoCaja' => $estado,
                'usuarioA' => $adminId,
            ];
        }

        DB::table('TCajas')->insert($registros);
    }
}

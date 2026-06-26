<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagoSueldoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');
        $empleados = DB::table('TEmpleados')->where('estadoA', 1)->pluck('carnetEmpleado');

        $pagos = [];
        foreach ($empleados as $carnet) {
            $sueldoBase = 2000;

            $fecha = Carbon::parse('2026-01-15');
            while ($fecha <= Carbon::parse('2026-06-15')) {
                $pagos[] = [
                    'carnetEmpleado' => $carnet,
                    'fechaPago' => $fecha->format('Y-m-d') . ' 12:00:00',
                    'monto' => $sueldoBase + rand(-100, 200),
                    'usuarioA' => $adminId,
                ];
                $fecha->addMonth();
            }
        }

        if (!empty($pagos)) {
            DB::table('TPagoSueldos')->insert($pagos);
        }
    }
}

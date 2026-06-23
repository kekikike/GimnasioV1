<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EsquemaSueldoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        $esquemas = [
            // 1001 - Recepcionista (idRol=2)
            ['carnet' => 1001, 'modalidad' => 'Fijo Mensual', 'base' => 1500.00, 'tarifa' => 0],
            // 1002 - Entrenador (idRol=3)
            ['carnet' => 1002, 'modalidad' => 'Fijo Mensual', 'base' => 2000.00, 'tarifa' => 35],
            // 2001 - Recepcionista (idRol=2)
            ['carnet' => 2001, 'modalidad' => 'Fijo Mensual', 'base' => 1800.00, 'tarifa' => 0],
            // 2002 - Entrenador (idRol=3)
            ['carnet' => 2002, 'modalidad' => 'Fijo Mensual', 'base' => 2200.00, 'tarifa' => 30],
            // 2003 - Entrenador (idRol=3)
            ['carnet' => 2003, 'modalidad' => 'Fijo Mensual', 'base' => 2500.00, 'tarifa' => 40],
        ];

        foreach ($esquemas as $e) {
            DB::table('TEsquemaSueldos')->insert([
                'carnetEmpleado' => $e['carnet'],
                'modalidadPago' => $e['modalidad'],
                'montoBase' => $e['base'],
                'tarifaHoraOClase' => $e['tarifa'],
                'usuarioA' => $adminId,
            ]);
        }
    }
}

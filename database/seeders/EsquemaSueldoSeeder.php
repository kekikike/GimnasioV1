<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EsquemaSueldoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TEsquemaSueldos')->insert([
            [
                'carnetEmpleado' => 1001,
                'modalidadPago' => 'Mensual',
                'montoBase' => 1500.00,
                'tarifaHoraOClase' => 0,
                'usuarioA' => $adminId,
            ],
            [
                'carnetEmpleado' => 1002,
                'modalidadPago' => 'Mensual',
                'montoBase' => 2000.00,
                'tarifaHoraOClase' => 0,
                'usuarioA' => $adminId,
            ],
        ]);
    }
}

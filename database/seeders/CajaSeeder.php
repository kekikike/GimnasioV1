<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CajaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TCajas')->insert([
            'idSucursal' => 1,
            'carnetEmpleado' => 1001,
            'fechaApertura' => '2024-06-17',
            'horaApertura' => '08:00:00',
            'montoApertura' => 500.00,
            'estadoCaja' => 'Abierta',
            'usuarioA' => $adminId,
        ]);
    }
}

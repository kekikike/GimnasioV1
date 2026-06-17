<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ControlAccesoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TControlAccesos')->insert([
            'carnetSocio' => 6700001,
            'idSucursal' => 1,
            'fechaAcceso' => '2024-06-17',
            'horaAcceso' => '08:30:00',
            'bloqueo' => false,
            'usuarioA' => $adminId,
        ]);
    }
}

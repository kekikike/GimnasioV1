<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MembresiaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TMembresias')->insert([
            'idPlan' => 1,
            'carnetSocio' => 6700001,
            'idSucursal' => 1,
            'fechaInicioMembresia' => '2024-06-01',
            'fechaFinMembresia' => '2024-07-01',
            'estadoMembresia' => 'Activa',
            'usuarioA' => $adminId,
        ]);
    }
}

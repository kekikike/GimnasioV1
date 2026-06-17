<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SocioSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TSocios')->insert([
            [
                'idUsuario' => 5,
                'codigoAcceso' => 'ACC6700001',
                'direccion' => 'Av. Siempre Viva 742',
                'nombreContactoEmergencia' => 'María Apaza',
                'telefonoContactoEmergencia' => 98765433,
                'observacionesMedicas' => 'Ninguna',
                'estadoSocio' => 'Activo',
                'Asistencias' => 5,
                'Faltas' => 0,
                'strikes' => 0,
                'usuarioA' => $adminId,
            ],
        ]);
    }
}

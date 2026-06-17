<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TReservas')->insert([
            'idClaseGrupal' => 1,
            'carnetSocio' => 6700001,
            'fechaReserva' => '2024-06-17',
            'horaReserva' => '10:30:00',
            'estadoReserva' => 'Confirmada',
            'usuarioA' => $adminId,
        ]);
    }
}

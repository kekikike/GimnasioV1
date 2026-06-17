<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenalizacionSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TPenalizaciones')->insert([
            'carnetSocio' => 6700001,
            'fecha' => '2024-06-10',
            'estado' => true,
            'usuarioA' => $adminId,
        ]);
    }
}

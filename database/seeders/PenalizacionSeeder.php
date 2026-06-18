<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenalizacionSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        $penalizaciones = [
            ['carnetSocio' => 6700001, 'fecha' => '2026-02-15', 'estado' => true],
            ['carnetSocio' => 6700002, 'fecha' => '2026-03-10', 'estado' => false],
            ['carnetSocio' => 6700003, 'fecha' => '2026-04-20', 'estado' => true],
            ['carnetSocio' => 6700005, 'fecha' => '2026-05-05', 'estado' => true],
            ['carnetSocio' => 6700001, 'fecha' => '2026-06-01', 'estado' => false],
            ['carnetSocio' => 6700004, 'fecha' => '2026-03-25', 'estado' => true],
            ['carnetSocio' => 6700003, 'fecha' => '2026-01-30', 'estado' => true],
            ['carnetSocio' => 6700005, 'fecha' => '2026-02-28', 'estado' => true],
            ['carnetSocio' => 6700002, 'fecha' => '2026-05-15', 'estado' => true],
            ['carnetSocio' => 6700001, 'fecha' => '2026-04-10', 'estado' => false],
        ];

        foreach ($penalizaciones as $p) {
            DB::table('TPenalizaciones')->insert([
                'carnetSocio' => $p['carnetSocio'],
                'fecha' => $p['fecha'],
                'estado' => $p['estado'],
                'usuarioA' => $adminId,
            ]);
        }
    }
}

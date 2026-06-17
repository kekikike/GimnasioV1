<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReciboSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TRecibos')->insert([
            'idCaja' => 1,
            'idMembresia' => 1,
            'nroRecibo' => 'REC-000001',
            'montoTotal' => 300.00,
            'fechaPago' => '2024-06-01',
            'horaPago' => '10:00:00',
            'estadoRecibo' => 'Pagado',
            'usuarioA' => $adminId,
        ]);
    }
}

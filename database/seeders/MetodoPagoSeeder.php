<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TMetodoPagos')->insert([
            ['nombreMetodoPago' => 'Efectivo', 'estadoA' => 1, 'usuarioA' => $adminId],
            ['nombreMetodoPago' => 'Tarjeta', 'estadoA' => 1, 'usuarioA' => $adminId],
            ['nombreMetodoPago' => 'QR', 'estadoA' => 1, 'usuarioA' => $adminId],
            ['nombreMetodoPago' => 'Transferencia', 'estadoA' => 1, 'usuarioA' => $adminId],
        ]);
    }
}

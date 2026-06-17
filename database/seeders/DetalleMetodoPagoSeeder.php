<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetalleMetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TDetalleMetodoPagos')->insert([
            'idRecibo' => 1,
            'tipoPago' => 'Efectivo',
            'monto' => 300.00,
            'usuarioA' => $adminId,
        ]);
    }
}

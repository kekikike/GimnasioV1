<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetalleMetodoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');
        $recibos = DB::table('TRecibos')->where('estadoA', 1)->get(['idRecibo', 'montoTotal']);
        $tipoPagos = ['Efectivo', 'Tarjeta', 'QR', 'Transferencia'];

        $detalles = [];
        foreach ($recibos as $r) {
            $detalles[] = [
                'idRecibo' => $r->idRecibo,
                'tipoPago' => $tipoPagos[array_rand($tipoPagos)],
                'monto' => $r->montoTotal,
                'usuarioA' => $adminId,
            ];
        }

        if (!empty($detalles)) {
            DB::table('TDetalleMetodoPagos')->insert($detalles);
        }
    }
}

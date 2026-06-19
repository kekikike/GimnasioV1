<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReciboSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');
        $cajas = DB::table('TCajas')->where('estadoA', 1)->pluck('idCaja')->toArray();
        $socios = DB::table('TMembresias')->where('estadoA', 1)->pluck('idMembresia', 'carnetSocio')->toArray();
        $contador = 2;

        $recibos = [];
        foreach ($cajas as $idCaja) {
            $caja = DB::table('TCajas')->where('idCaja', $idCaja)->first();
            if (!$caja) continue;

            $carnetAleatorio = array_rand($socios);
            $idMembresia = $socios[$carnetAleatorio];
            $monto = [300.00, 500.00, 800.00][array_rand([300.00, 500.00, 800.00])];
            $fechaPago = date('Y-m-d', strtotime($caja->fechaApertura)) . ' ' . sprintf('%02d:%02d:00', rand(9, 17), rand(0, 59));

            $recibos[] = [
                'idCaja' => $idCaja,
                'idMembresia' => $idMembresia,
                'nroRecibo' => 'REC-' . str_pad($contador++, 6, '0', STR_PAD_LEFT),
                'montoTotal' => $monto,
                'fechaPago' => $fechaPago,
                'estadoRecibo' => 'Emitido',
                'usuarioA' => $adminId,
            ];
        }

        if (!empty($recibos)) {
            DB::table('TRecibos')->insert($recibos);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ControlAccesoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');
        $carnets = [6700001, 6700002, 6700003, 6700004, 6700005];
        $bloqueos = [false, false, false, false, true];

        for ($i = 0; $i < 30; $i++) {
            $carnet = $carnets[array_rand($carnets)];
            $dia = rand(1, 172);
            $fecha = date('Y-m-d', strtotime("2026-01-01 + $dia days"));
            $hora = sprintf('%02d:%02d:00', rand(6, 20), rand(0, 59));
            $bloqueo = $bloqueos[array_rand($bloqueos)];

            DB::table('TControlAccesos')->insert([
                'carnetSocio' => $carnet,
                'idSucursal' => 1,
                'fechaAcceso' => $fecha,
                'horaAcceso' => $hora,
                'bloqueo' => $bloqueo,
                'motivoDenegacion' => $bloqueo ? 'Membresia vencida' : null,
                'usuarioA' => $adminId,
            ]);
        }
    }
}

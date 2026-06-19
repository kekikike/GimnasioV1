<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');
        $carnetsSocios = [6700001, 6700002, 6700003, 6700004, 6700005];
        $claseIds = DB::table('TClaseGrupales')->where('estadoA', 1)->pluck('idClaseGrupal')->toArray();
        $estados = ['Reservado', 'Asistido', 'Cancelado', 'Penalizado'];

        $reservas = [];
        for ($i = 0; $i < 105 && count($claseIds) > 0; $i++) {
            $carnet = $carnetsSocios[array_rand($carnetsSocios)];
            $claseId = $claseIds[array_rand($claseIds)];
            $fechaClase = DB::table('TClaseGrupales')->where('idClaseGrupal', $claseId)->value('fecha');
            $diaReserva = rand(-5, 0);
            $fechaReserva = date('Y-m-d', strtotime($fechaClase . " + $diaReserva days")) . ' ' . sprintf('%02d:%02d:00', rand(8, 18), rand(0, 59));
            $estado = $estados[array_rand($estados)];

            $reservas[] = [
                'idClaseGrupal' => $claseId,
                'carnetSocio' => $carnet,
                'fechaReserva' => $fechaReserva,
                'estadoReserva' => $estado,
                'usuarioA' => $adminId,
            ];
        }

        if (!empty($reservas)) {
            DB::table('TReservas')->insert($reservas);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MembresiaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        $membresias = [
            ['idPlan' => 1, 'carnetSocio' => 6700001, 'inicio' => '2026-01-01', 'fin' => '2026-06-30', 'estado' => 'Activa'],
            ['idPlan' => 2, 'carnetSocio' => 6700002, 'inicio' => '2026-01-15', 'fin' => '2026-07-15', 'estado' => 'Activa'],
            ['idPlan' => 3, 'carnetSocio' => 6700003, 'inicio' => '2026-02-01', 'fin' => '2026-07-31', 'estado' => 'Activa'],
            ['idPlan' => 1, 'carnetSocio' => 6700004, 'inicio' => '2025-12-01', 'fin' => '2026-05-31', 'estado' => 'Vencida'],
            ['idPlan' => 2, 'carnetSocio' => 6700005, 'inicio' => '2026-03-01', 'fin' => '2026-04-01', 'estado' => 'Vencida'],
            ['idPlan' => 2, 'carnetSocio' => 6700005, 'inicio' => '2026-04-15', 'fin' => '2026-07-31', 'estado' => 'Activa'],
        ];

        foreach ($membresias as $m) {
            DB::table('TMembresias')->insert([
                'idPlan' => $m['idPlan'],
                'carnetSocio' => $m['carnetSocio'],
                'idSucursal' => 1,
                'fechaInicioMembresia' => $m['inicio'],
                'fechaFinMembresia' => $m['fin'],
                'estadoMembresia' => $m['estado'],
                'usuarioA' => $adminId,
            ]);
        }
    }
}

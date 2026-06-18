<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MantenimientoPreventivoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TMantenimientoPreventivos')->insert([
            'idEquipo' => 1,
            'fechaProgramada' => '2024-07-15',
            'descripcionMantenimiento' => 'Mantenimiento de rutina - revisión de motor y banda',
            'costoMantenimiento' => 150.00,
            'tecnicoAsignado' => 'Técnico externo',
            'estadoMantenimiento' => 'Pendiente',
            'usuarioA' => $adminId,
        ]);
    }
}

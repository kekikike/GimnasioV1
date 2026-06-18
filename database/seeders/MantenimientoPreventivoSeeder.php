<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MantenimientoPreventivoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');
        $equipos = DB::table('TEquipamientos')->where('estadoA', 1)->pluck('idEquipo')->toArray();
        $estados = ['Pendiente', 'Realizado', 'Cancelado'];
        $tecnicos = ['Tecnico externo', 'Proveedor oficial', 'Personal interno', 'Servicio tecnico Matrix'];

        $mantenimientos = [];
        for ($i = 0; $i < 30; $i++) {
            $idEquipo = $equipos[array_rand($equipos)];
            $dia = rand(0, 172);
            $fechaProg = date('Y-m-d', strtotime("2026-01-01 + $dia days"));
            $estado = $estados[array_rand($estados)];

            $fechaReal = null;
            if ($estado === 'Realizado') {
                $diasDiff = rand(0, 5);
                $fechaReal = date('Y-m-d', strtotime($fechaProg . " + $diasDiff days"));
            }

            $mantenimientos[] = [
                'idEquipo' => $idEquipo,
                'fechaProgramada' => $fechaProg,
                'fechaRealizada' => $fechaReal,
                'descripcionMantenimiento' => 'Mantenimiento preventivo de rutina - ' . rand(1, 5),
                'costoMantenimiento' => round(rand(50, 500), 2),
                'tecnicoAsignado' => $tecnicos[array_rand($tecnicos)],
                'estadoMantenimiento' => $estado,
                'usuarioA' => $adminId,
            ];
        }

        DB::table('TMantenimientoPreventivos')->insert($mantenimientos);
    }
}

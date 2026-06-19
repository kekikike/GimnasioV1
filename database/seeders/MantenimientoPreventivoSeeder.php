<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MantenimientoPreventivoSeeder extends Seeder
{
    // Indices of equipos set to 'En Mantenimiento' in EquipamientoSeeder (0-based)
    private const EQUIPOS_MANTENIMIENTO = [6, 17, 22, 36, 47];

    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');
        $equipos = DB::table('TEquipamientos')->where('estadoA', 1)->orderBy('idEquipo')->pluck('idEquipo')->toArray();
        $tecnicos = ['Tecnico externo', 'Proveedor oficial', 'Personal interno', 'Servicio tecnico Matrix'];
        $today = '2026-06-19';

        $mantenimientos = [];
        $usedEquipos = [];

        // 1) For the 5 equipos in 'En Mantenimiento': create 1-2 future maintenance records (Pendiente)
        foreach (self::EQUIPOS_MANTENIMIENTO as $idx) {
            if (!isset($equipos[$idx])) continue;
            $idEquipo = $equipos[$idx];
            $usedEquipos[] = $idEquipo;

            $numRecords = rand(1, 2);
            for ($r = 0; $r < $numRecords; $r++) {
                $dia = rand(1, 26);
                $fechaProg = "2026-06-{$dia}";
                if ($fechaProg <= $today) $fechaProg = "2026-06-" . str_pad(rand(20, 30), 2, '0', STR_PAD_LEFT);

                $mantenimientos[] = [
                    'idEquipo' => $idEquipo,
                    'fechaProgramada' => $fechaProg,
                    'fechaRealizada' => null,
                    'descripcionMantenimiento' => 'Mantenimiento preventivo programado - ' . rand(1, 5),
                    'costoMantenimiento' => round(rand(80, 600), 2),
                    'tecnicoAsignado' => $tecnicos[array_rand($tecnicos)],
                    'estadoMantenimiento' => $fechaProg <= $today ? 'Realizado' : 'Pendiente',
                    'usuarioA' => $adminId,
                ];
            }
        }

        // 2) For other random equipos: create records with past dates, mostly Realizado
        for ($i = 0; $i < 25; $i++) {
            $idEquipo = $equipos[array_rand($equipos)];
            if (in_array($idEquipo, $usedEquipos) && !in_array($idEquipo, $usedEquipos)) {
                // avoid over-concentration, but allow some overlap
            }
            if (in_array($idEquipo, $usedEquipos) && rand(0, 3) > 0) continue; // 25% chance to repeat

            $daysAgo = rand(30, 170); // between Jan 1 and May 20 2026
            $fechaProg = date('Y-m-d', strtotime("2026-01-01 + {$daysAgo} days"));

            $estado = 'Realizado';
            if (rand(0, 5) === 0) $estado = 'Cancelado';

            $fechaReal = null;
            if ($estado === 'Realizado') {
                $diasDiff = rand(0, 5);
                $fechaReal = date('Y-m-d', strtotime($fechaProg . " + {$diasDiff} days"));
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

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReporteFallaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');
        $equipos = DB::table('TEquipamientos')->where('estadoA', 1)->pluck('idEquipo')->toArray();
        $gravedades = ['Baja', 'Media', 'Alta', 'Critica'];
        $estados = ['Pendiente', 'En Revision', 'Solucionado'];

        $descripciones = [
            'Ruido anormal durante el funcionamiento',
            'Pieza suelta en la estructura principal',
            'El equipo no enciende correctamente',
            'Cable de alimentacion danado',
            'Fugas de lubricante en el sistema',
            'Pantalla de control no funciona',
            'Resistencia al movimiento irregular',
            'Pernos de sujecion flojos',
            'Correa de transmision desgastada',
            'Sensor de frecuencia cardiaco no funciona',
        ];

        $reportes = [];
        for ($i = 0; $i < 30; $i++) {
            $idEquipo = $equipos[array_rand($equipos)];
            $dia = rand(0, 172);
            $fecha = date('Y-m-d', strtotime("2026-01-01 + $dia days"));
            $hora = sprintf('%02d:%02d:00', rand(8, 18), rand(0, 59));
            $gravedad = $gravedades[array_rand($gravedades)];

            $reportes[] = [
                'idEquipo' => $idEquipo,
                'carnetEmpleado' => 1001,
                'fechaReporte' => $fecha,
                'horaReporte' => $hora,
                'descripcionFalla' => $descripciones[array_rand($descripciones)],
                'gravedad' => $gravedad,
                'estadoReporte' => $estados[array_rand($estados)],
                'usuarioA' => $adminId,
            ];
        }

        DB::table('TReporteFallas')->insert($reportes);
    }
}

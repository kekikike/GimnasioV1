<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReporteFallaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TReporteFallas')->insert([
            'idEquipo' => 2,
            'carnetEmpleado' => 1001,
            'fechaReporte' => '2024-06-15',
            'horaReporte' => '09:00:00',
            'descripcionFalla' => 'Ruido anormal en el volante de la bicicleta',
            'gravedad' => 'Media',
            'estadoReporte' => 'Pendiente',
            'usuarioA' => $adminId,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SocioSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        $socios = [
            ['idUsuario' => 5, 'codigoAcceso' => 'ACC6700001', 'direccion' => 'Av. Siempre Viva 742', 'contacto' => 'Maria Apaza', 'telfContacto' => 98765433, 'obs' => 'Ninguna', 'asistencias' => 25, 'faltas' => 2, 'strikes' => 0],
            ['idUsuario' => 6, 'codigoAcceso' => 'ACC6700002', 'direccion' => 'Calle Bolivar 456', 'contacto' => 'Luis Garcia', 'telfContacto' => 98765434, 'obs' => 'Asma controlada', 'asistencias' => 30, 'faltas' => 1, 'strikes' => 0],
            ['idUsuario' => 7, 'codigoAcceso' => 'ACC6700003', 'direccion' => 'Av. America 789', 'contacto' => 'Rosa Ruiz', 'telfContacto' => 98765435, 'obs' => 'Ninguna', 'asistencias' => 18, 'faltas' => 3, 'strikes' => 1],
            ['idUsuario' => 8, 'codigoAcceso' => 'ACC6700004', 'direccion' => 'Calle Junin 321', 'contacto' => 'Jorge Herrera', 'telfContacto' => 98765436, 'obs' => 'Lesion de rodilla 2023', 'asistencias' => 40, 'faltas' => 0, 'strikes' => 0],
            ['idUsuario' => 9, 'codigoAcceso' => 'ACC6700005', 'direccion' => 'Av. Heroinas 159', 'contacto' => 'Sofia Rojas', 'telfContacto' => 98765437, 'obs' => 'Hipertension controlada', 'asistencias' => 12, 'faltas' => 4, 'strikes' => 2],
        ];

        foreach ($socios as $s) {
            DB::table('TSocios')->insert([
                'idUsuario' => $s['idUsuario'],
                'codigoAcceso' => $s['codigoAcceso'],
                'direccion' => $s['direccion'],
                'nombreContactoEmergencia' => $s['contacto'],
                'telefonoContactoEmergencia' => $s['telfContacto'],
                'observacionesMedicas' => $s['obs'],
                'estadoSocio' => 'Activo',
                'Asistencias' => $s['asistencias'],
                'Faltas' => $s['faltas'],
                'strikes' => $s['strikes'],
                'usuarioA' => $adminId,
            ]);
        }
    }
}

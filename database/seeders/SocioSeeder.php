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
            ['idUsuario' => 5, 'direccion' => 'Av. Siempre Viva 742', 'contacto' => 'Maria Apaza', 'telfContacto' => 98765433, 'obs' => 'Ninguna', 'strikes' => 0],
            ['idUsuario' => 6, 'direccion' => 'Calle Bolivar 456', 'contacto' => 'Luis Garcia', 'telfContacto' => 98765434, 'obs' => 'Asma controlada', 'strikes' => 0],
            ['idUsuario' => 7, 'direccion' => 'Av. America 789', 'contacto' => 'Rosa Ruiz', 'telfContacto' => 98765435, 'obs' => 'Ninguna', 'strikes' => 1],
            ['idUsuario' => 8, 'direccion' => 'Calle Junin 321', 'contacto' => 'Jorge Herrera', 'telfContacto' => 98765436, 'obs' => 'Lesion de rodilla 2023', 'strikes' => 0],
            ['idUsuario' => 9, 'direccion' => 'Av. Heroinas 159', 'contacto' => 'Sofia Rojas', 'telfContacto' => 98765437, 'obs' => 'Hipertension controlada', 'strikes' => 2],
        ];

        foreach ($socios as $s) {
            DB::table('TSocios')->insert([
                'idUsuario' => $s['idUsuario'],
                'direccion' => $s['direccion'],
                'nombreContactoEmergencia' => $s['contacto'],
                'telefonoContactoEmergencia' => $s['telfContacto'],
                'observacionesMedicas' => $s['obs'],
                'estadoSocio' => 'Activo',
                'strikes' => $s['strikes'],
                'usuarioA' => $adminId,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificacionSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TNotificaciones')->insert([
            'carnetSocio' => 6700001,
            'tipoNotificacion' => 'Recordatorio',
            'mensaje' => 'Su membresía está por vencer el 01/07/2024. Renueve ahora y disfrute de beneficios exclusivos.',
            'canal' => 'Correo',
            'fechaEnvio' => '2024-06-25',
            'estado' => 'Pendiente',
            'usuarioA' => $adminId,
        ]);
    }
}

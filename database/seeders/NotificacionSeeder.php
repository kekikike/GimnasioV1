<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificacionSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        $notificaciones = [
            ['carnetSocio' => 6700001, 'tipo' => 'Recordatorio', 'mensaje' => 'Su membresia esta por vencer el 30/06/2026. Renueve ahora.', 'fecha' => '2026-06-25', 'estado' => 'Enviado'],
            ['carnetSocio' => 6700002, 'tipo' => 'Bienvenida', 'mensaje' => 'Bienvenido a GimnasioV1! Su codigo de acceso es ACC6700002.', 'fecha' => '2026-01-15', 'estado' => 'Enviado'],
            ['carnetSocio' => 6700003, 'tipo' => 'Promocion', 'mensaje' => 'Tenemos un descuento especial en planes premium para usted.', 'fecha' => '2026-03-01', 'estado' => 'Enviado'],
            ['carnetSocio' => 6700004, 'tipo' => 'Alerta', 'mensaje' => 'Su membresia ha vencido el 31/05/2026. Acuda a renovar.', 'fecha' => '2026-06-01', 'estado' => 'Pendiente'],
            ['carnetSocio' => 6700005, 'tipo' => 'Recordatorio', 'mensaje' => 'Tiene 2 strikes acumulados. Recuerde las normas del gimnasio.', 'fecha' => '2026-05-20', 'estado' => 'Enviado'],
            ['carnetSocio' => 6700001, 'tipo' => 'Promocion', 'mensaje' => 'Clase gratis de Yoga este sabado!', 'fecha' => '2026-04-10', 'estado' => 'Enviado'],
            ['carnetSocio' => 6700002, 'tipo' => 'Alerta', 'mensaje' => 'Penalizacion registrada por inasistencia a clase reservada.', 'fecha' => '2026-03-10', 'estado' => 'Enviado'],
            ['carnetSocio' => 6700004, 'tipo' => 'Recordatorio', 'mensaje' => 'Su plan basico se ha completado. Contrate uno nuevo.', 'fecha' => '2026-06-05', 'estado' => 'Pendiente'],
            ['carnetSocio' => 6700003, 'tipo' => 'Bienvenida', 'mensaje' => 'Bienvenido! Su codigo ACC6700003 ya esta activo.', 'fecha' => '2026-02-01', 'estado' => 'Enviado'],
            ['carnetSocio' => 6700005, 'tipo' => 'Promocion', 'mensaje' => 'Refiere a un amigo y obtenga 1 mes gratis!', 'fecha' => '2026-04-01', 'estado' => 'Enviado'],

            // Eddy Vargas (6700001) — notificaciones de prueba
            ['carnetSocio' => 6700001, 'tipo' => 'Recordatorio', 'mensaje' => 'Su membresia vencera en 7 dias. Renueve ahora para no perder el acceso.', 'fecha' => '2026-06-23', 'estado' => 'Enviado'],
            ['carnetSocio' => 6700001, 'tipo' => 'Recordatorio', 'mensaje' => 'Su membresia vence en 3 dias. Evite la suspension de su cuenta.', 'fecha' => '2026-06-27', 'estado' => 'Enviado'],
            ['carnetSocio' => 6700001, 'tipo' => 'Alerta', 'mensaje' => 'Su membresia ha vencido. Acuda a la sucursal para renovar.', 'fecha' => '2026-07-01', 'estado' => 'Pendiente'],
        ];

        foreach ($notificaciones as $n) {
            DB::table('TNotificaciones')->insert([
                'carnetSocio' => $n['carnetSocio'],
                'tipoNotificacion' => $n['tipo'],
                'mensaje' => $n['mensaje'],
                'fechaEnvio' => $n['fecha'],
                'estado' => $n['estado'],
                'usuarioA' => $adminId,
            ]);
        }
    }
}

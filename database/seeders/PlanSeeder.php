<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TPlanes')->insert([
            [
                'nombrePlan' => 'Plan Básico',
                'descripcion' => 'Acceso a instalaciones básicas de 6:00 a 22:00',
                'costoPlan' => 300.00,
                'duracionDias' => 30,
                'usuarioA' => $adminId,
            ],
            [
                'nombrePlan' => 'Plan Premium',
                'descripcion' => 'Acceso completo a instalaciones y clases grupales',
                'costoPlan' => 500.00,
                'duracionDias' => 30,
                'usuarioA' => $adminId,
            ],
            [
                'nombrePlan' => 'Plan VIP',
                'descripcion' => 'Acceso ilimitado con servicios adicionales y entrenador personal',
                'costoPlan' => 800.00,
                'duracionDias' => 30,
                'usuarioA' => $adminId,
            ],
        ]);
    }
}

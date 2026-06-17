<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActividadSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TActividades')->insert([
            ['nombreActividad' => 'Yoga', 'descripcionActividad' => 'Clase de yoga para todos los niveles', 'usuarioA' => $adminId],
            ['nombreActividad' => 'Spinning', 'descripcionActividad' => 'Ciclismo indoor de alta intensidad', 'usuarioA' => $adminId],
            ['nombreActividad' => 'CrossFit', 'descripcionActividad' => 'Entrenamiento funcional de alta intensidad', 'usuarioA' => $adminId],
            ['nombreActividad' => 'Pilates', 'descripcionActividad' => 'Ejercicios de fortalecimiento y flexibilidad', 'usuarioA' => $adminId],
            ['nombreActividad' => 'Zumba', 'descripcionActividad' => 'Baile y fitness al ritmo de la música', 'usuarioA' => $adminId],
        ]);
    }
}

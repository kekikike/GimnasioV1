<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('TRoles')->insert([
            ['nombreRol' => 'Administrador', 'estadoA' => true],
            ['nombreRol' => 'Recepcionista', 'estadoA' => true],
            ['nombreRol' => 'Entrenador', 'estadoA' => true],
            ['nombreRol' => 'Socio', 'estadoA' => true],
        ]);
    }
}

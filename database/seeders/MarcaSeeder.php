<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarcaSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TMarcas')->insert([
            ['nombreMarca' => 'Technogym', 'usuarioA' => $adminId],
            ['nombreMarca' => 'Life Fitness', 'usuarioA' => $adminId],
            ['nombreMarca' => 'Matrix', 'usuarioA' => $adminId],
            ['nombreMarca' => 'Hammer Strength', 'usuarioA' => $adminId],
        ]);
    }
}

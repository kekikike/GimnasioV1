<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SucursalSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TSucursales')->insert([
            'nombre' => 'Sucursal Central',
            'direccion' => 'Av. Principal #123, Col. Centro',
            'telefono' => '7770100',
            'estado' => true,
            'usuarioA' => $adminId,
        ]);
    }
}

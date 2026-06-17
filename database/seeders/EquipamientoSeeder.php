<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipamientoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TEquipamientos')->insert([
            [
                'idSucursal' => 1,
                'idMarca' => 1,
                'nombreEquipo' => 'Cinta de Correr',
                'modelo' => 'Run 700',
                'fechaAdquisicion' => '2024-01-15',
                'estadoEquipo' => 'Operativo',
                'usuarioA' => $adminId,
            ],
            [
                'idSucursal' => 1,
                'idMarca' => 2,
                'nombreEquipo' => 'Bicicleta Estática',
                'modelo' => 'IC5',
                'fechaAdquisicion' => '2024-01-20',
                'estadoEquipo' => 'Operativo',
                'usuarioA' => $adminId,
            ],
        ]);
    }
}

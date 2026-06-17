<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        DB::table('TEmpleados')->insert([
            [
                'carnetEmpleado' => 1001,
                'idUsuario' => 3,
                'idSucursal' => 1,
                'sueldo' => 1500.00,
                'especialidad' => 1,
                'fechaContratoInicio' => '2024-01-15',
                'usuarioA' => $adminId,
            ],
            [
                'carnetEmpleado' => 1002,
                'idUsuario' => 4,
                'idSucursal' => 1,
                'sueldo' => 2000.00,
                'especialidad' => 2,
                'fechaContratoInicio' => '2024-02-01',
                'usuarioA' => $adminId,
            ],
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');
        $favioId = DB::table('TUsuarios')->where('correo', 'favio@gmail.com')->value('idUsuario');

        $registros = [
            ['carnetEmpleado' => 1000, 'idUsuario' => $adminId, 'idSucursal' => 1, 'fechaContratoInicio' => '2024-01-01', 'usuarioA' => $adminId],
            ['carnetEmpleado' => 5001, 'idUsuario' => $favioId, 'idSucursal' => 1, 'fechaContratoInicio' => '2024-01-01', 'usuarioA' => $adminId],
            ['carnetEmpleado' => 1001, 'idUsuario' => 3,        'idSucursal' => 1, 'fechaContratoInicio' => '2024-01-15', 'usuarioA' => $adminId],
            ['carnetEmpleado' => 1002, 'idUsuario' => 4,        'idSucursal' => 1, 'fechaContratoInicio' => '2024-02-01', 'usuarioA' => $adminId],
            ['carnetEmpleado' => 2001, 'idUsuario' => 10,       'idSucursal' => 1, 'fechaContratoInicio' => '2025-06-01', 'usuarioA' => $adminId],
            ['carnetEmpleado' => 2002, 'idUsuario' => 11,       'idSucursal' => 1, 'fechaContratoInicio' => '2025-03-15', 'usuarioA' => $adminId],
            ['carnetEmpleado' => 2003, 'idUsuario' => 12,       'idSucursal' => 1, 'fechaContratoInicio' => '2025-09-01', 'usuarioA' => $adminId],
        ];

        DB::table('TEmpleados')->insert($registros);
    }
}

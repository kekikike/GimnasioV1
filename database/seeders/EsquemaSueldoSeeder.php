<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EsquemaSueldoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        $esquemas = [
            ['carnet' => 1001, 'modalidad' => 'Mensual', 'base' => 1500.00, 'tarifa' => 0],
            ['carnet' => 1001, 'modalidad' => 'Bonificacion', 'base' => 200.00, 'tarifa' => 0],
            ['carnet' => 1001, 'modalidad' => 'Horas Extra', 'base' => 0, 'tarifa' => 25.00],
            ['carnet' => 1001, 'modalidad' => 'Comision', 'base' => 0, 'tarifa' => 50.00],
            ['carnet' => 1002, 'modalidad' => 'Mensual', 'base' => 2000.00, 'tarifa' => 0],
            ['carnet' => 1002, 'modalidad' => 'Bonificacion', 'base' => 300.00, 'tarifa' => 0],
            ['carnet' => 1002, 'modalidad' => 'Horas Extra', 'base' => 0, 'tarifa' => 35.00],
            ['carnet' => 1002, 'modalidad' => 'Comision', 'base' => 0, 'tarifa' => 75.00],
            ['carnet' => 1001, 'modalidad' => 'Mensual', 'base' => 1600.00, 'tarifa' => 0],
            ['carnet' => 1002, 'modalidad' => 'Mensual', 'base' => 2100.00, 'tarifa' => 0],
            ['carnet' => 1001, 'modalidad' => 'Bono Productividad', 'base' => 150.00, 'tarifa' => 0],
            ['carnet' => 1002, 'modalidad' => 'Bono Productividad', 'base' => 200.00, 'tarifa' => 0],
        ];

        foreach ($esquemas as $e) {
            DB::table('TEsquemaSueldos')->insert([
                'carnetEmpleado' => $e['carnet'],
                'modalidadPago' => $e['modalidad'],
                'montoBase' => $e['base'],
                'tarifaHoraOClase' => $e['tarifa'],
                'usuarioA' => $adminId,
            ]);
        }
    }
}

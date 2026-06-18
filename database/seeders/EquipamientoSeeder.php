<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EquipamientoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->where('correo', 'admin@gimnasio.com')->value('idUsuario');

        $equipos = [
            ['nombre' => 'Cinta de Correr', 'modelo' => 'Run 700', 'idMarca' => 1],
            ['nombre' => 'Cinta de Correr', 'modelo' => 'TR 9500', 'idMarca' => 2],
            ['nombre' => 'Cinta de Correr', 'modelo' => 'E1', 'idMarca' => 4],
            ['nombre' => 'Bicicleta Estatica', 'modelo' => 'IC5', 'idMarca' => 2],
            ['nombre' => 'Bicicleta Estatica', 'modelo' => 'Excite Top', 'idMarca' => 1],
            ['nombre' => 'Bicicleta Estatica', 'modelo' => 'UB 100', 'idMarca' => 3],
            ['nombre' => 'Eliptica', 'modelo' => 'Cross Personal', 'idMarca' => 1],
            ['nombre' => 'Eliptica', 'modelo' => 'Eliptical EFX', 'idMarca' => 2],
            ['nombre' => 'Eliptica', 'modelo' => 'E30', 'idMarca' => 3],
            ['nombre' => 'Remo', 'modelo' => 'Skillbike', 'idMarca' => 1],
            ['nombre' => 'Remo', 'modelo' => 'RW100', 'idMarca' => 2],
            ['nombre' => 'Mancuernas', 'modelo' => 'Pro 20kg', 'idMarca' => 4],
            ['nombre' => 'Mancuernas', 'modelo' => 'Adjustable 25kg', 'idMarca' => 4],
            ['nombre' => 'Mancuernas', 'modelo' => 'Rubber Hex 15kg', 'idMarca' => 3],
            ['nombre' => 'Pesa Rusa', 'modelo' => 'Kettlebell 16kg', 'idMarca' => 4],
            ['nombre' => 'Pesa Rusa', 'modelo' => 'Kettlebell 24kg', 'idMarca' => 4],
            ['nombre' => 'Polea Alta', 'modelo' => 'Lat Pulldown', 'idMarca' => 3],
            ['nombre' => 'Prensa de Piernas', 'modelo' => 'Leg Press 45', 'idMarca' => 3],
            ['nombre' => 'Banco Plano', 'modelo' => 'Pro FB', 'idMarca' => 4],
            ['nombre' => 'Banco Inclinado', 'modelo' => 'Adjustable AB', 'idMarca' => 4],
            ['nombre' => 'Sillon de Cuadriceps', 'modelo' => 'Leg Extension', 'idMarca' => 3],
            ['nombre' => 'Sillon de Femoral', 'modelo' => 'Leg Curl', 'idMarca' => 3],
            ['nombre' => 'Maquina Multipower', 'modelo' => 'Smith Machine', 'idMarca' => 2],
            ['nombre' => 'Jaula de Sentadillas', 'modelo' => 'Squat Rack Pro', 'idMarca' => 4],
            ['nombre' => 'Barra Olimpica', 'modelo' => '20kg Olympic', 'idMarca' => 4],
            ['nombre' => 'Barra Olimpica', 'modelo' => '15kg Women', 'idMarca' => 4],
            ['nombre' => 'Disco Peso', 'modelo' => 'Bumper 10kg', 'idMarca' => 4],
            ['nombre' => 'Disco Peso', 'modelo' => 'Bumper 20kg', 'idMarca' => 4],
            ['nombre' => 'Disco Peso', 'modelo' => 'Iron 5kg', 'idMarca' => 4],
            ['nombre' => 'Disco Peso', 'modelo' => 'Iron 15kg', 'idMarca' => 4],
            ['nombre' => 'Cuerda para Saltar', 'modelo' => 'Speed Rope', 'idMarca' => 4],
            ['nombre' => 'Colchoneta Yoga', 'modelo' => 'Pro 6mm', 'idMarca' => 1],
            ['nombre' => 'Pelota Medicinal', 'modelo' => 'Med Ball 6kg', 'idMarca' => 4],
            ['nombre' => 'Pelota Suiza', 'modelo' => 'Exercise Ball 75cm', 'idMarca' => 3],
            ['nombre' => 'TRX', 'modelo' => 'Suspension Pro', 'idMarca' => 2],
            ['nombre' => 'Polea Baja', 'modelo' => 'Low Row', 'idMarca' => 3],
            ['nombre' => 'Escaladora', 'modelo' => 'StairMaster 7000', 'idMarca' => 2],
            ['nombre' => 'Bicicleta Reclinada', 'modelo' => 'Recumbent R30', 'idMarca' => 1],
            ['nombre' => 'Bicicleta Spinning', 'modelo' => 'Spinner Pro', 'idMarca' => 1],
            ['nombre' => 'Bicicleta Spinning', 'modelo' => 'Sprint 8', 'idMarca' => 2],
            ['nombre' => 'Bicicleta Spinning', 'modelo' => 'SB 100', 'idMarca' => 3],
            ['nombre' => 'Maquina de Abdominales', 'modelo' => 'Ab Crunch', 'idMarca' => 3],
            ['nombre' => 'Maquina de Gluteos', 'modelo' => 'Glute Bridge', 'idMarca' => 3],
            ['nombre' => 'Maquina de Hombros', 'modelo' => 'Shoulder Press', 'idMarca' => 3],
            ['nombre' => 'Maquina de Pecho', 'modelo' => 'Chest Press', 'idMarca' => 3],
            ['nombre' => 'Maquina de Espalda', 'modelo' => 'Row Back', 'idMarca' => 3],
            ['nombre' => 'Fitness Bike', 'modelo' => 'Excite Recline', 'idMarca' => 1],
            ['nombre' => 'Escaladora', 'modelo' => 'Gauntlet', 'idMarca' => 3],
            ['nombre' => 'Caminadora Curva', 'modelo' => 'Curve Runner', 'idMarca' => 2],
            ['nombre' => 'Ventilador Aspirador', 'modelo' => 'Industrial Fan', 'idMarca' => 4],
        ];

        $estados = ['Operativo', 'Operativo', 'Operativo', 'En Mantenimiento', 'Fuera de Servicio', 'De Baja'];
        $years = [2024, 2024, 2024, 2025, 2025, 2025, 2026];

        $inserts = [];
        foreach ($equipos as $eq) {
            $year = $years[array_rand($years)];
            $mes = rand(1, 12);
            $dia = rand(1, 28);
            $inserts[] = [
                'idSucursal' => 1,
                'idMarca' => $eq['idMarca'],
                'nombreEquipo' => $eq['nombre'],
                'modelo' => $eq['modelo'],
                'fechaAdquisicion' => sprintf("%d-%02d-%02d", $year, $mes, $dia),
                'estadoEquipo' => $estados[array_rand($estados)],
                'usuarioA' => $adminId,
            ];
        }

        DB::table('TEquipamientos')->insert($inserts);
    }
}

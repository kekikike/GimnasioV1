<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('TUsuarios')->insertGetId([
            'idRol' => 1,
            'nombre1' => 'Admin',
            'apellido1' => 'Sistema',
            'correo' => 'admin@gimnasio.com',
            'telefono' => 12345678,
            'contrasena' => Hash::make('123456'),
            'estado' => true,
            'usuarioA' => null,
        ]);

        DB::table('TRoles')->whereNull('usuarioA')->update(['usuarioA' => $adminId]);

        DB::table('TUsuarios')->insertGetId([
            'idRol' => 1,
            'nombre1' => 'Favio Estefano',
            'apellido1' => 'Sandy Gonzales',
            'correo' => 'favio@gmail.com',
            'telefono' => 23456789,
            'contrasena' => Hash::make('123456'),
            'estado' => true,
            'usuarioA' => $adminId,
        ]);

        DB::table('TUsuarios')->insertGetId([
            'idRol' => 2,
            'nombre1' => 'Juan Enrique',
            'apellido1' => 'Quenallata Escobar',
            'correo' => 'kike@gmail.com',
            'telefono' => 34567890,
            'contrasena' => Hash::make('123456'),
            'estado' => true,
            'usuarioA' => $adminId,
        ]);

        DB::table('TUsuarios')->insert([
            [
                'idRol' => 3,
                'nombre1' => 'Samuel Ignacio',
                'apellido1' => 'Jimenez Aliaga',
                'correo' => 'max@gmail.com',
                'telefono' => 45678901,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 4,
                'nombre1' => 'Eddy Limber',
                'apellido1' => 'Vargas Apaza',
                'correo' => 'eddy@gmail.com',
                'telefono' => 56789012,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 4,
                'nombre1' => 'Maria Fernanda',
                'apellido1' => 'Garcia Lopez',
                'correo' => 'maria@gmail.com',
                'telefono' => 67890123,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 4,
                'nombre1' => 'Charles James',
                'apellido1' => 'Kirk Ruiz',
                'correo' => 'carlos@gmail.com',
                'telefono' => 78901234,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 4,
                'nombre1' => 'Ana Sofia',
                'apellido1' => 'Torrico Herrera',
                'correo' => 'ana@gmail.com',
                'telefono' => 89012345,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 4,
                'nombre1' => 'Pedro Luis',
                'apellido1' => 'Camacho Rojas',
                'correo' => 'pedro@gmail.com',
                'telefono' => 90123456,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 2,
                'nombre1' => 'Carlos',
                'apellido1' => 'Ruiz Martinez',
                'correo' => 'carlos.ruiz@gmail.com',
                'telefono' => 11111111,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 3,
                'nombre1' => 'Lucia',
                'apellido1' => 'Morales Fernandez',
                'correo' => 'lucia@gmail.com',
                'telefono' => 22222222,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 3,
                'nombre1' => 'Roberto',
                'apellido1' => 'Vega Castillo',
                'correo' => 'roberto@gmail.com',
                'telefono' => 33333333,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
        ]);
    }
}



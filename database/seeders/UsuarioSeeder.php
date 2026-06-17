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

        $favioId = DB::table('TUsuarios')->insertGetId([
            'idRol' => 1,
            'nombre1' => 'Favio Estefano',
            'apellido1' => 'Sandy Gonzales',
            'correo' => 'favio@gmail.com',
            'telefono' => 23456789,
            'contrasena' => Hash::make('123456'),
            'estado' => true,
            'usuarioA' => $adminId,
        ]);

        $juanId = DB::table('TUsuarios')->insertGetId([
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
        ]);
    }
}

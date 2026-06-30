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
            'telefono' => 7123456,
            'contrasena' => Hash::make('123456'),
            'estado' => true,
            'usuarioA' => null,
        ]);

        DB::table('TRoles')->whereNull('usuarioA')->update(['usuarioA' => $adminId]);

        DB::table('TUsuarios')->insertGetId([
            'idRol' => 1,
            'nombre1' => 'Favio',
            'nombre2' => 'Estefano',
            'apellido1' => 'Sandy',
            'apellido2' => 'Gonzales',
            'correo' => 'favio@gmail.com',
            'telefono' => 7234567,
            'contrasena' => Hash::make('123456'),
            'estado' => true,
            'usuarioA' => $adminId,
        ]);

        DB::table('TUsuarios')->insertGetId([
            'idRol' => 2,
            'nombre1' => 'Juan',
            'nombre2' => 'Enrique',
            'apellido1' => 'Quenallata',
            'apellido2' => 'Escobar',
            'correo' => 'kike@gmail.com',
            'telefono' => 7345678,
            'contrasena' => Hash::make('123456'),
            'estado' => true,
            'usuarioA' => $adminId,
        ]);

        DB::table('TUsuarios')->insert([
            [
                'idRol' => 3,
                'nombre1' => 'Samuel',
                'nombre2' => 'Ignacio',
                'apellido1' => 'Jimenez',
                'apellido2' => 'Aliaga',
                'correo' => 'max@gmail.com',
                'telefono' => 7456789,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 4,
                'nombre1' => 'Eddy',
                'nombre2' => 'Limber',
                'apellido1' => 'Vargas',
                'apellido2' => 'Apaza',
                'correo' => 'eddy@gmail.com',
                'telefono' => 7567890,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 4,
                'nombre1' => 'Maria',
                'nombre2' => 'Fernanda',
                'apellido1' => 'Garcia',
                'apellido2' => 'Lopez',
                'correo' => 'maria@gmail.com',
                'telefono' => 7678901,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 4,
                'nombre1' => 'Charles',
                'nombre2' => 'James',
                'apellido1' => 'Kirk',
                'apellido2' => 'Ruiz',
                'correo' => 'carlos@gmail.com',
                'telefono' => 7789012,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 4,
                'nombre1' => 'Ana',
                'nombre2' => 'Sofia',
                'apellido1' => 'Torrico',
                'apellido2' => 'Herrera',
                'correo' => 'ana@gmail.com',
                'telefono' => 7890123,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 4,
                'nombre1' => 'Pedro',
                'nombre2' => 'Luis',
                'apellido1' => 'Camacho',
                'apellido2' => 'Rojas',
                'correo' => 'pedro@gmail.com',
                'telefono' => 7901234,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 2,
                'nombre1' => 'Carlos',
                'nombre2' => null,
                'apellido1' => 'Ruiz',
                'apellido2' => 'Martinez',
                'correo' => 'carlos.ruiz@gmail.com',
                'telefono' => 7111111,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 3,
                'nombre1' => 'Lucia',
                'nombre2' => null,
                'apellido1' => 'Morales',
                'apellido2' => 'Fernandez',
                'correo' => 'lucia@gmail.com',
                'telefono' => 7222222,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
            [
                'idRol' => 3,
                'nombre1' => 'Roberto',
                'nombre2' => null,
                'apellido1' => 'Vega',
                'apellido2' => 'Castillo',
                'correo' => 'roberto@gmail.com',
                'telefono' => 7333333,
                'contrasena' => Hash::make('123456'),
                'estado' => true,
                'usuarioA' => $adminId,
            ],
        ]);
    }
}

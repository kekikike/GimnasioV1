<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Sucursal
{
    public static function getAll(): array
    {
        return DB::select('CALL sp_TSucursales_Select()');
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TSucursales_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }

    public static function create(array $data, int $usuarioA, string $direccionIP): int
    {
        $rows = DB::select('CALL sp_TSucursales_Insert(?, ?, ?, ?, ?, ?)', [
            $data['nombre'],
            $data['direccion'],
            $data['telefono'] ?? null,
            $data['estado'] ?? 1,
            $usuarioA,
            $direccionIP,
        ]);
        $id = (int) ($rows[0]->id ?? 0);
        if ($id && isset($data['latitud']) && isset($data['longitud'])) {
            DB::table('TSucursales')->where('idSucursal', $id)->update([
                'latitud' => $data['latitud'],
                'longitud' => $data['longitud'],
            ]);
        }
        return $id;
    }

    public static function update(int $id, array $data, int $usuarioA, string $direccionIP): void
    {
        DB::select('CALL sp_TSucursales_Update(?, ?, ?, ?, ?, ?, ?)', [
            $id,
            $data['nombre'],
            $data['direccion'],
            $data['telefono'] ?? null,
            $data['estado'] ?? 1,
            $usuarioA,
            $direccionIP,
        ]);
        if (isset($data['latitud']) && isset($data['longitud'])) {
            DB::table('TSucursales')->where('idSucursal', $id)->update([
                'latitud' => $data['latitud'],
                'longitud' => $data['longitud'],
            ]);
        }
    }

    public static function delete(int $id, int $usuarioA, string $direccionIP): void
    {
        DB::select('CALL sp_TSucursales_Delete(?, ?, ?)', [$id, $usuarioA, $direccionIP]);
    }
}

<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Equipamiento
{
    public static function getAll(): array
    {
        return DB::select('CALL sp_TEquipamientos_Select()');
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TEquipamientos_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }

    public static function create(array $data, int $usuarioA, string $direccionIP): int
    {
        $rows = DB::select('CALL sp_TEquipamientos_Insert(?, ?, ?, ?, ?, ?, ?, ?)', [
            $data['idSucursal'],
            $data['idMarca'],
            $data['nombreEquipo'],
            $data['modelo'] ?? null,
            $data['fechaAdquisicion'] ?? null,
            $data['estadoEquipo'],
            $usuarioA,
            $direccionIP,
        ]);
        return (int) ($rows[0]->id ?? 0);
    }

    public static function update(int $id, array $data, int $usuarioA, string $direccionIP): void
    {
        DB::select('CALL sp_TEquipamientos_Update(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $id,
            $data['idSucursal'],
            $data['idMarca'],
            $data['nombreEquipo'],
            $data['modelo'] ?? null,
            $data['fechaAdquisicion'] ?? null,
            $data['estadoEquipo'],
            $usuarioA,
            $direccionIP,
        ]);
    }

    public static function delete(int $id, int $usuarioA, string $direccionIP): void
    {
        DB::select('CALL sp_TEquipamientos_Delete(?, ?, ?)', [$id, $usuarioA, $direccionIP]);
    }
}

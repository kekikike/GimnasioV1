<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Mantenimiento
{
    public static function getAll(): array
    {
        return DB::select('CALL sp_TMantenimientoPreventivos_Select()');
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TMantenimientoPreventivos_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }

    public static function create(array $data, int $usuarioA, string $direccionIP): void
    {
        DB::select('CALL sp_TMantenimientoPreventivos_Insert(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $data['idEquipo'],
            $data['fechaProgramada'],
            $data['fechaRealizada'] ?? null,
            $data['descripcionMantenimiento'] ?? null,
            $data['costoMantenimiento'] ?? null,
            $data['tecnicoAsignado'] ?? null,
            $data['estadoMantenimiento'],
            $usuarioA,
            $direccionIP,
        ]);
    }

    public static function update(int $id, array $data, int $usuarioA, string $direccionIP): void
    {
        DB::statement('CALL sp_TMantenimientoPreventivos_Update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $id,
            $data['idEquipo'],
            $data['fechaProgramada'],
            $data['fechaRealizada'] ?? null,
            $data['descripcionMantenimiento'] ?? null,
            $data['costoMantenimiento'] ?? null,
            $data['tecnicoAsignado'] ?? null,
            $data['estadoMantenimiento'],
            $usuarioA,
            $direccionIP,
        ]);
    }

    public static function delete(int $id, int $usuarioA, string $direccionIP): void
    {
        DB::statement('CALL sp_TMantenimientoPreventivos_Delete(?, ?, ?)', [$id, $usuarioA, $direccionIP]);
    }
}

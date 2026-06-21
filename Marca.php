<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Marca
{
    public static function getAll(): array
    {
        return DB::select('CALL sp_TMarcas_Select()');
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TMarcas_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }

    public static function create(array $data, int $usuarioA, string $direccionIP): int
    {
        $rows = DB::select('CALL sp_TMarcas_Insert(?, ?, ?)', [
            $data['nombreMarca'],
            $usuarioA,
            $direccionIP,
        ]);
        return (int) ($rows[0]->id ?? 0);
    }

    public static function update(int $id, array $data, int $usuarioA, string $direccionIP): void
    {
        DB::select('CALL sp_TMarcas_Update(?, ?, ?, ?)', [
            $id,
            $data['nombreMarca'],
            $usuarioA,
            $direccionIP,
        ]);
    }

    public static function delete(int $id, int $usuarioA, string $direccionIP): void
    {
        DB::select('CALL sp_TMarcas_Delete(?, ?, ?)', [$id, $usuarioA, $direccionIP]);
    }
}

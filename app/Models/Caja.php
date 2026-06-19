<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Caja
{
    public static function getAll(): array
    {
        return DB::select('CALL sp_TCajas_Select()');
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TCajas_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }

    public static function create(array $data, int $usuarioA, string $direccionIP): void
    {
        DB::select('CALL sp_TCajas_Insert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $data['idSucursal'],
            $data['carnetEmpleado'],
            $data['fechaApertura'],
            $data['horaApertura'],
            $data['montoApertura'],
            $data['montoCierre'] ?? null,
            $data['montoCierreCalculado'] ?? null,
            $data['diferenciaArqueo'] ?? null,
            $data['estadoCaja'],
            $usuarioA,
            $direccionIP,
        ]);
    }

    public static function update(int $id, array $data, int $usuarioA, string $direccionIP): void
    {
        DB::statement('CALL sp_TCajas_Update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $id,
            $data['idSucursal'],
            $data['carnetEmpleado'],
            $data['fechaApertura'],
            $data['horaApertura'],
            $data['montoApertura'],
            $data['montoCierre'] ?? null,
            $data['montoCierreCalculado'] ?? null,
            $data['diferenciaArqueo'] ?? null,
            $data['estadoCaja'],
            $usuarioA,
            $direccionIP,
        ]);
    }

    public static function delete(int $id, int $usuarioA, string $direccionIP): void
    {
        DB::statement('CALL sp_TCajas_Delete(?, ?, ?)', [$id, $usuarioA, $direccionIP]);
    }
}

<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Recibo
{
    public static function getAll(): array
    {
        return DB::select('CALL sp_TRecibos_Select()');
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TRecibos_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }

    public static function create(array $data, int $usuarioA, string $direccionIP): void
    {
        DB::select('CALL sp_TRecibos_Insert(?, ?, ?, ?, ?, ?, ?, ?)', [
            $data['idCaja'],
            $data['idMembresia'],
            $data['nroRecibo'],
            $data['montoTotal'],
            $data['fechaPago'],
            $data['estadoRecibo'],
            $usuarioA,
            $direccionIP,
        ]);
    }

    public static function update(int $id, array $data, int $usuarioA, string $direccionIP): void
    {
        DB::statement('CALL sp_TRecibos_Update(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $id,
            $data['idCaja'],
            $data['idMembresia'],
            $data['nroRecibo'],
            $data['montoTotal'],
            $data['fechaPago'],
            $data['estadoRecibo'],
            $usuarioA,
            $direccionIP,
        ]);
    }

    public static function delete(int $id, int $usuarioA, string $direccionIP): void
    {
        DB::statement('CALL sp_TRecibos_Delete(?, ?, ?)', [$id, $usuarioA, $direccionIP]);
    }

    public static function getByFilters(array $filters): array
    {
        return DB::select('CALL sp_TRecibos_GetReporteFinanciero(?, ?, ?, ?, ?)', [
            $filters['fecha_desde'] ?? null,
            $filters['fecha_hasta'] ?? null,
            $filters['idSucursal'] ?? null,
            $filters['idMetodoPago'] ?? null,
            $filters['carnetEmpleado'] ?? null,
        ]);
    }
}

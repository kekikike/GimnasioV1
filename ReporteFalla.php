<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class ReporteFalla
{
    public static function getAll(): array
    {
        return DB::select('CALL sp_TReporteFallas_Select()');
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TReporteFallas_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }

    public static function create(array $data, int $usuarioA, string $direccionIP): void
    {
        DB::select('CALL sp_TReporteFallas_Insert(?, ?, ?, ?, ?, ?, ?, ?)', [
            $data['idEquipo'],
            $data['carnetEmpleado'],
            $data['fechaReporte'],
            $data['descripcionFalla'],
            $data['gravedad'],
            $data['estadoReporte'],
            $usuarioA,
            $direccionIP,
        ]);
    }

    public static function update(int $id, array $data, int $usuarioA, string $direccionIP): void
    {
        DB::statement('CALL sp_TReporteFallas_Update(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $id,
            $data['idEquipo'],
            $data['carnetEmpleado'],
            $data['fechaReporte'],
            $data['descripcionFalla'],
            $data['gravedad'],
            $data['estadoReporte'],
            $usuarioA,
            $direccionIP,
        ]);
    }

    public static function delete(int $id, int $usuarioA, string $direccionIP): void
    {
        DB::statement('CALL sp_TReporteFallas_Delete(?, ?, ?)', [$id, $usuarioA, $direccionIP]);
    }
}

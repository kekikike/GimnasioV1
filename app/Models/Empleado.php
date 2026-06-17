<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Empleado
{
    public static function getAll(): array
    {
        return DB::select('CALL sp_TEmpleados_Select()');
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TEmpleados_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }

    public static function count(): int
    {
        return count(self::getAll());
    }
}

<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Rol
{
    public static function getAll(): array
    {
        return DB::select('CALL sp_TRoles_Select()');
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TRoles_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }
}

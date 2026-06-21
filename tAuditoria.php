<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class tAuditoria
{
    public static function getAll(): array
    {
        return DB::select('CALL sp_TAuditorias_Select()');
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TAuditorias_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }
}

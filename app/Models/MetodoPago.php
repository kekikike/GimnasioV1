<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class MetodoPago
{
    public static function getAll(): array
    {
        return DB::select('CALL sp_TMetodoPagos_Select()');
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TMetodoPagos_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }
}

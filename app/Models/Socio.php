<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Socio
{
    public static function getAll(): array
    {
        return DB::select('CALL sp_TSocios_Select()');
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TSocios_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }

    public static function count(): int
    {
        return count(self::getAll());
    }
}

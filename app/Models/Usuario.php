<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Usuario
{
    public static function findByEmail(string $correo): ?object
    {
        $rows = DB::select('CALL sp_TUsuarios_FindByEmail(?)', [$correo]);
        return $rows[0] ?? null;
    }

    public static function getById(int $id): ?object
    {
        $rows = DB::select('CALL sp_TUsuarios_SelectById(?)', [$id]);
        return $rows[0] ?? null;
    }

    public static function getAll(): array
    {
        return DB::select('CALL sp_TUsuarios_Select()');
    }
}

<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Recibo
{
    public static function getAll(): array
    {
        return DB::table('TRecibos')->where('estadoA', 1)->get()->toArray();
    }

    public static function getById(int $id): ?object
    {
        return DB::table('TRecibos')->where('idRecibo', $id)->where('estadoA', 1)->first();
    }

    public static function create(array $data, int $usuarioA, string $direccionIP): int
    {
        return DB::table('TRecibos')->insertGetId([
            'idCaja' => $data['idCaja'],
            'idMembresia' => $data['idMembresia'],
            'montoTotal' => $data['montoTotal'],
            'fechaPago' => $data['fechaPago'],
            'estadoRecibo' => $data['estadoRecibo'],
            'estadoA' => 1,
            'fechaA' => now(),
            'usuarioA' => $usuarioA,
        ]);
    }

    public static function update(int $id, array $data, int $usuarioA, string $direccionIP): void
    {
        DB::table('TRecibos')->where('idRecibo', $id)->update([
            'idCaja' => $data['idCaja'],
            'idMembresia' => $data['idMembresia'],
            'montoTotal' => $data['montoTotal'],
            'fechaPago' => $data['fechaPago'],
            'estadoRecibo' => $data['estadoRecibo'],
            'estadoA' => 1,
            'fechaA' => now(),
            'usuarioA' => $usuarioA,
        ]);
    }

    public static function delete(int $id, int $usuarioA, string $direccionIP): void
    {
        DB::table('TRecibos')->where('idRecibo', $id)->update([
            'estadoA' => 0,
            'usuarioA' => $usuarioA,
            'fechaA' => now(),
        ]);
    }

    public static function getByFilters(array $filters): array
    {
        $query = DB::table('TRecibos')->where('estadoA', 1);

        if (!empty($filters['fecha_desde'])) {
            $query->where('fechaPago', '>=', $filters['fecha_desde']);
        }

        if (!empty($filters['fecha_hasta'])) {
            $query->where('fechaPago', '<=', $filters['fecha_hasta']);
        }

        if (!empty($filters['idSucursal'])) {
            $query->where('idSucursal', $filters['idSucursal']);
        }

        if (!empty($filters['idMetodoPago'])) {
            $query->where('idMetodoPago', $filters['idMetodoPago']);
        }

        if (!empty($filters['carnetEmpleado'])) {
            $query->where('carnetEmpleado', $filters['carnetEmpleado']);
        }

        return $query->get()->toArray();
    }
}

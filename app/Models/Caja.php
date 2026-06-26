<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Caja
{
    public static function getAll(): array
    {
        return DB::table('TCajas')->where('estadoA', 1)->get()->toArray();
    }

    public static function getById(int $id): ?object
    {
        return DB::table('TCajas')->where('idCaja', $id)->where('estadoA', 1)->first();
    }

    public static function create(array $data, int $usuarioA, string $direccionIP): int
    {
        return DB::table('TCajas')->insertGetId([
            'idSucursal' => $data['idSucursal'],
            'carnetEmpleado' => $data['carnetEmpleado'],
            'fechaApertura' => $data['fechaApertura'],
            'horaApertura' => $data['horaApertura'],
            'montoApertura' => $data['montoApertura'],
            'montoCierre' => $data['montoCierre'] ?? null,
            'montoCierreCalculado' => $data['montoCierreCalculado'] ?? null,
            'diferenciaArqueo' => $data['diferenciaArqueo'] ?? null,
            'cierreEstado' => $data['cierreEstado'] ?? null,
            'cierreObservacion' => $data['cierreObservacion'] ?? null,
            'estadoCaja' => $data['estadoCaja'],
            'estadoA' => 1,
            'fechaA' => now(),
            'usuarioA' => $usuarioA,
            'direccionIP' => $direccionIP,
        ]);
    }

    public static function update(int $id, array $data, int $usuarioA, string $direccionIP): void
    {
        DB::table('TCajas')->where('idCaja', $id)->update([
            'idSucursal' => $data['idSucursal'],
            'carnetEmpleado' => $data['carnetEmpleado'],
            'fechaApertura' => $data['fechaApertura'],
            'horaApertura' => $data['horaApertura'],
            'montoApertura' => $data['montoApertura'],
            'montoCierre' => $data['montoCierre'] ?? null,
            'montoCierreCalculado' => $data['montoCierreCalculado'] ?? null,
            'diferenciaArqueo' => $data['diferenciaArqueo'] ?? null,
            'cierreEstado' => $data['cierreEstado'] ?? null,
            'cierreObservacion' => $data['cierreObservacion'] ?? null,
            'estadoCaja' => $data['estadoCaja'],
            'estadoA' => 1,
            'fechaA' => now(),
            'usuarioA' => $usuarioA,
        ]);
    }

    public static function delete(int $id, int $usuarioA, string $direccionIP): void
    {
        DB::table('TCajas')->where('idCaja', $id)->update([
            'estadoA' => 0,
            'usuarioA' => $usuarioA,
            'fechaA' => now(),
        ]);
    }
}

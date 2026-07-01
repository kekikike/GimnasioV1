<?php

namespace App\Http\Controllers;

use App\Models\Equipamiento;
use App\Models\Marca;
use App\Models\Sucursal;
use App\Models\Socio;
use App\Models\Empleado;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $equipos    = Equipamiento::getAll();
        $marcas     = collect(Marca::getAll())->keyBy('idMarca');
        $sucursales = collect(Sucursal::getAll())->keyBy('idSucursal');

        $totalEquipos      = count($equipos);
        $totalSocios       = Socio::count();
        $totalEmpleados    = Empleado::count();
        $equiposRecientes  = array_slice($equipos, 0, 5);

        $alertasProximas = DB::table('tmantenimientopreventivos')
            ->join('tequipamientos', 'tmantenimientopreventivos.idEquipo', '=', 'tequipamientos.idEquipo')
            ->join('tsucursales', 'tequipamientos.idSucursal', '=', 'tsucursales.idSucursal')
            ->select(
                'tmantenimientopreventivos.*',
                'tequipamientos.nombreEquipo',
                'tequipamientos.estadoEquipo',
                'tsucursales.nombre',
                DB::raw('DATEDIFF(tmantenimientopreventivos.fechaProgramada, CURDATE()) as diasRestantes')
            )
            ->where('tmantenimientopreventivos.estadoA', 1)
            ->where('tmantenimientopreventivos.fechaProgramada', '>=', DB::raw('CURDATE()'))
            ->orderBy('tmantenimientopreventivos.fechaProgramada', 'asc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalEquipos', 'totalSocios', 'totalEmpleados', 'equiposRecientes', 'marcas', 'sucursales', 'alertasProximas'
        ));
    }
}

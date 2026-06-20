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

        // Bypass: Calculamos los días restantes directamente en la consulta
        $alertasProximas = DB::table('tmantenimientopreventivos')
            ->join('tequipamientos', 'tmantenimientopreventivos.idEquipo', '=', 'tequipamientos.idEquipo')
            ->select(
                'tmantenimientopreventivos.*', 
                'tequipamientos.nombreEquipo',
                DB::raw('DATEDIFF(tmantenimientopreventivos.fechaProgramada, CURDATE()) as diasRestantes') // ¡Aquí está el cálculo mágico!
            )
            ->where('tmantenimientopreventivos.estadoA', 1)
            ->where('tmantenimientopreventivos.estadoMantenimiento', 'Programado')
            ->orderBy('tmantenimientopreventivos.fechaProgramada', 'asc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalEquipos', 'totalSocios', 'totalEmpleados', 'equiposRecientes', 'marcas', 'sucursales', 'alertasProximas'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Equipamiento;
use App\Models\Marca;
use App\Models\Sucursal;
use App\Models\Socio;
use App\Models\Empleado;

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

        return view('admin.dashboard', compact(
            'totalEquipos', 'totalSocios', 'totalEmpleados', 'equiposRecientes', 'marcas', 'sucursales'
        ));
    }
}

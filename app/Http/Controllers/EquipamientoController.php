<?php

namespace App\Http\Controllers;

use App\Models\Equipamiento;
use App\Models\Marca;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class EquipamientoController extends Controller
{
    public function index()
    {
        $equipos    = Equipamiento::getAll();
        $marcas     = collect(Marca::getAll())->keyBy('idMarca');
        $sucursales = collect(Sucursal::getAll())->keyBy('idSucursal');

        return view('equipamiento.index', compact('equipos', 'marcas', 'sucursales'));
    }

    public function create()
    {
        $marcas     = Marca::getAll();
        $sucursales = Sucursal::getAll();

        return view('equipamiento.create', compact('marcas', 'sucursales'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'idSucursal'       => 'required|integer',
            'idMarca'          => 'required|integer',
            'nombreEquipo'     => 'required|string|max:100',
            'modelo'           => 'nullable|string|max:100',
            'fechaAdquisicion' => 'nullable|date',
            'estadoEquipo'     => 'required|string|max:50',
        ]);

        $usuarioA   = session('usuario')->idUsuario;
        $direccionIP = request()->ip();

        Equipamiento::create($data, $usuarioA, $direccionIP);

        return redirect()->route('equipamiento.index')
            ->with('success', 'Equipo creado exitosamente.');
    }

    public function edit($id)
    {
        $equipo     = Equipamiento::getById((int) $id);
        $marcas     = Marca::getAll();
        $sucursales = Sucursal::getAll();

        return view('equipamiento.edit', compact('equipo', 'marcas', 'sucursales'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'idSucursal'       => 'required|integer',
            'idMarca'          => 'required|integer',
            'nombreEquipo'     => 'required|string|max:100',
            'modelo'           => 'nullable|string|max:100',
            'fechaAdquisicion' => 'nullable|date',
            'estadoEquipo'     => 'required|string|max:50',
        ]);

        $usuarioA   = session('usuario')->idUsuario;
        $direccionIP = request()->ip();

        Equipamiento::update((int) $id, $data, $usuarioA, $direccionIP);

        return redirect()->route('equipamiento.index')
            ->with('success', 'Equipo actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $usuarioA   = session('usuario')->idUsuario;
        $direccionIP = request()->ip();

        Equipamiento::delete((int) $id, $usuarioA, $direccionIP);

        return redirect()->route('equipamiento.index')
            ->with('success', 'Equipo desactivado exitosamente.');
    }
}

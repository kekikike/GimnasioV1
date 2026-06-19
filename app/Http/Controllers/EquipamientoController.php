<?php

namespace App\Http\Controllers;

use App\Models\Equipamiento;
use App\Models\Marca;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipamientoController extends Controller
{
    public function index(Request $request)
    {
        $equipos = Equipamiento::getAll();

        if ($request->filled('estado')) {
            $equipos = array_filter($equipos, function ($eq) use ($request) {
                return $eq->estadoEquipo === $request->estado;
            });
        }

        $marcas     = collect(Marca::getAll())->keyBy('idMarca');
        $sucursales = collect(Sucursal::getAll())->keyBy('idSucursal');

        $tieneRealizado = [];
        foreach ($equipos as $eq) {
            $rows = DB::select('CALL sp_TMantenimientoPreventivos_CountRealizadoByEquipo(?)', [$eq->idEquipo]);
            $tieneRealizado[$eq->idEquipo] = ($rows[0]->c > 0);
        }

        return view('equipamiento.index', compact('equipos', 'marcas', 'sucursales', 'tieneRealizado'));
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

    public function toggleEstado($id)
    {
        $equipo = Equipamiento::getById((int) $id);
        if (!$equipo) {
            return redirect()->route('equipamiento.index')->with('error', 'Equipo no encontrado.');
        }

        if ($equipo->estadoEquipo !== 'En Mantenimiento') {
            return redirect()->route('equipamiento.index')->with('error', 'Accion no valida.');
        }

        $rows = DB::select('CALL sp_TMantenimientoPreventivos_CountRealizadoByEquipo(?)', [(int) $id]);

        if ($rows[0]->c === 0) {
            return redirect()->route('equipamiento.index')
                ->with('error', 'No se puede cambiar a Operativo: el equipo no tiene un mantenimiento marcado como Realizado.');
        }

        $usuarioA    = session('usuario')->idUsuario;
        $direccionIP = request()->ip();
        $data = [
            'idSucursal'       => $equipo->idSucursal,
            'idMarca'          => $equipo->idMarca,
            'nombreEquipo'     => $equipo->nombreEquipo,
            'modelo'           => $equipo->modelo,
            'fechaAdquisicion' => $equipo->fechaAdquisicion,
            'estadoEquipo'     => 'Operativo',
        ];

        Equipamiento::update((int) $id, $data, $usuarioA, $direccionIP);

        return redirect()->route('equipamiento.index')
            ->with('success', 'Equipo cambiado a Operativo.');
    }

    public function iniciarMantenimiento(Request $request, $id)
    {
        $equipo = Equipamiento::getById((int) $id);
        if (!$equipo || $equipo->estadoEquipo !== 'Operativo') {
            return redirect()->route('equipamiento.index')->with('error', 'El equipo debe estar Operativo.');
        }

        $data = $request->validate([
            'descripcionMantenimiento' => 'nullable|string|max:500',
            'tecnicoAsignado'          => 'nullable|string|max:150',
            'costoMantenimiento'       => 'nullable|numeric|min:0',
            'fechaProgramada'          => 'required|date|after:today',
        ]);

        $usuarioA   = session('usuario')->idUsuario;
        $direccionIP = request()->ip();

        DB::beginTransaction();
        try {
            DB::select('CALL sp_TMantenimientoPreventivos_Insert(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                (int) $id,
                $data['fechaProgramada'],
                null,
                $data['descripcionMantenimiento'] ?? null,
                $data['costoMantenimiento'] ?? null,
                $data['tecnicoAsignado'] ?? null,
                'Pendiente',
                $usuarioA,
                $direccionIP,
            ]);

            $updateData = [
                'idSucursal'       => $equipo->idSucursal,
                'idMarca'          => $equipo->idMarca,
                'nombreEquipo'     => $equipo->nombreEquipo,
                'modelo'           => $equipo->modelo,
                'fechaAdquisicion' => $equipo->fechaAdquisicion,
                'estadoEquipo'     => 'En Mantenimiento',
            ];
            Equipamiento::update((int) $id, $updateData, $usuarioA, $direccionIP);

            DB::commit();
            return redirect()->route('equipamiento.index')
                ->with('success', 'Mantenimiento iniciado. Equipo marcado como "En Mantenimiento".');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('equipamiento.index')
                ->with('error', 'Error al iniciar mantenimiento: ' . $e->getMessage());
        }
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

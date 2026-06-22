<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipamiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MantenimientoController extends Controller
{
    public function index(Request $request)
    {
        $estado      = $request->filled('estado') ? $request->estado : null;
        $fecha_desde = $request->filled('fecha_desde') ? $request->fecha_desde : null;
        $fecha_hasta = $request->filled('fecha_hasta') ? $request->fecha_hasta : null;

        $mantenimientos = DB::select('CALL sp_TMantenimientoPreventivos_GetFiltered(?, ?, ?)', [
            $estado, $fecha_desde, $fecha_hasta
        ]);

        return view('admin.mantenimientos.index', compact('mantenimientos'));
    }

    public function update(Request $request, $id)
    {
        $current = DB::select('CALL sp_TMantenimientoPreventivos_SelectById(?)', [(int) $id]);
        if (empty($current)) {
            return redirect()->route('admin.mantenimientos.index', $request->only(['estado', 'fecha_desde', 'fecha_hasta']))
                ->with('error', 'Mantenimiento no encontrado.');
        }

        if (in_array($current[0]->estadoMantenimiento, ['Realizado', 'Cancelado'])) {
            return redirect()->route('admin.mantenimientos.index', $request->only(['estado', 'fecha_desde', 'fecha_hasta']))
                ->with('error', 'No se puede editar un mantenimiento ' . strtolower($current[0]->estadoMantenimiento) . '.');
        }

        $data = $request->validate([
            'descripcionMantenimiento' => 'nullable|string|max:500',
            'tecnicoAsignado'          => 'nullable|string|max:150',
            'costoMantenimiento'       => 'nullable|numeric|min:0',
            'fechaProgramada'          => 'required|date|after:today',
            'fechaRealizada'           => [
                'nullable', 'date',
                function ($attribute, $value, $fail) use ($data) {
                    if ($value && isset($data['fechaProgramada'])) {
                        $min = \Carbon\Carbon::parse($data['fechaProgramada'])->addDays(3);
                        if (\Carbon\Carbon::parse($value)->lt($min)) {
                            $fail('La fecha realizada debe ser al menos 3 dias despues de la fecha programada.');
                        }
                    }
                },
            ],
        ]);

        $estadoMantenimiento = !empty($data['fechaRealizada']) ? 'Realizado' : 'Pendiente';

        $usuarioA   = session('usuario')->idUsuario;
        $direccionIP = request()->ip();

        DB::statement('CALL sp_TMantenimientoPreventivos_Update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            (int) $id,
            $current[0]->idEquipo,
            $data['fechaProgramada'],
            $data['fechaRealizada'] ?? null,
            $data['descripcionMantenimiento'] ?? null,
            $data['costoMantenimiento'] ?? null,
            $data['tecnicoAsignado'] ?? null,
            $estadoMantenimiento,
            $usuarioA,
            $direccionIP,
        ]);

        if ($estadoMantenimiento == 'Realizado') {
            $equipo = Equipamiento::getById((int) $current[0]->idEquipo);
            if ($equipo && $equipo->estadoEquipo != 'Operativo') {
                Equipamiento::update((int) $current[0]->idEquipo, [
                    'idSucursal'       => $equipo->idSucursal,
                    'idMarca'          => $equipo->idMarca,
                    'nombreEquipo'     => $equipo->nombreEquipo,
                    'modelo'           => $equipo->modelo,
                    'fechaAdquisicion' => $equipo->fechaAdquisicion,
                    'estadoEquipo'     => 'Operativo',
                ], $usuarioA, $direccionIP);
            }
        }

        return redirect()->route('admin.mantenimientos.index', $request->only(['estado', 'fecha_desde', 'fecha_hasta']))
            ->with('success', 'Mantenimiento actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $current = DB::select('CALL sp_TMantenimientoPreventivos_SelectById(?)', [(int) $id]);
        if (empty($current)) {
            return redirect()->route('admin.mantenimientos.index')
                ->with('error', 'Mantenimiento no encontrado.');
        }

        if ($current[0]->estadoMantenimiento == 'Realizado') {
            return redirect()->route('admin.mantenimientos.index')
                ->with('error', 'No se puede eliminar un mantenimiento realizado.');
        }

        $usuarioA   = session('usuario')->idUsuario;
        $direccionIP = request()->ip();

        $equipo = Equipamiento::getById((int) $current[0]->idEquipo);
        if ($equipo) {
            Equipamiento::update((int) $current[0]->idEquipo, [
                'idSucursal'       => $equipo->idSucursal,
                'idMarca'          => $equipo->idMarca,
                'nombreEquipo'     => $equipo->nombreEquipo,
                'modelo'           => $equipo->modelo,
                'fechaAdquisicion' => $equipo->fechaAdquisicion,
                'estadoEquipo'     => 'De Baja',
            ], $usuarioA, $direccionIP);
        }

        DB::statement('CALL sp_TMantenimientoPreventivos_Delete(?, ?, ?)', [(int) $id, $usuarioA, $direccionIP]);

        return redirect()->route('admin.mantenimientos.index')
            ->with('success', 'Mantenimiento eliminado. Equipo dado de baja.');
    }

    public function getJson($id)
    {
        $rows = DB::select('CALL sp_TMantenimientoPreventivos_SelectById(?)', [(int) $id]);
        if (empty($rows)) return response()->json(null, 404);
        return response()->json($rows[0]);
    }
}

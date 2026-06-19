<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $data = $request->validate([
            'descripcionMantenimiento' => 'nullable|string|max:500',
            'tecnicoAsignado'          => 'nullable|string|max:150',
            'costoMantenimiento'       => 'nullable|numeric|min:0',
            'fechaProgramada'          => 'required|date|after:today',
            'fechaRealizada'           => 'nullable|date|after:fechaProgramada',
            'estadoMantenimiento'      => 'required|in:Pendiente,Realizado,Cancelado',
        ]);

        if ($request->filled('fechaRealizada') && $request->filled('fechaProgramada')) {
            $prog = \Carbon\Carbon::parse($data['fechaProgramada']);
            $real = \Carbon\Carbon::parse($data['fechaRealizada']);
            if ($real->diffInDays($prog, false) > 7) {
                return redirect()->back()->withInput()->withErrors([
                    'fechaRealizada' => 'La fecha realizada no puede superar una semana despues de la programada.',
                ]);
            }
        }

        $current = DB::select('CALL sp_TMantenimientoPreventivos_SelectById(?)', [(int) $id]);
        if (empty($current)) {
            return redirect()->route('admin.mantenimientos.index', $request->only(['estado', 'fecha_desde', 'fecha_hasta']))
                ->with('error', 'Mantenimiento no encontrado.');
        }

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
            $data['estadoMantenimiento'],
            $usuarioA,
            $direccionIP,
        ]);

        return redirect()->route('admin.mantenimientos.index', $request->only(['estado', 'fecha_desde', 'fecha_hasta']))
            ->with('success', 'Mantenimiento actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $usuarioA   = session('usuario')->idUsuario;
        $direccionIP = request()->ip();

        DB::statement('CALL sp_TMantenimientoPreventivos_Delete(?, ?, ?)', [(int) $id, $usuarioA, $direccionIP]);

        return redirect()->route('admin.mantenimientos.index')
            ->with('success', 'Mantenimiento eliminado (borrado logico).');
    }

    public function getJson($id)
    {
        $rows = DB::select('CALL sp_TMantenimientoPreventivos_SelectById(?)', [(int) $id]);
        if (empty($rows)) return response()->json(null, 404);
        return response()->json($rows[0]);
    }
}

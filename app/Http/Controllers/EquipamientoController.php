<?php

namespace App\Http\Controllers;

use App\Models\Equipamiento;
use App\Models\Marca;
use App\Models\Sucursal;
use App\Models\ReporteFalla;
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
        // RF12: Validación para que el nombre contenga letras.
        $data = $request->validate([
            'idSucursal'       => 'required|integer',
            'idMarca'          => 'required|integer',
            'nombreEquipo'     => ['required', 'string', 'max:100', 'regex:/^(?=.*[a-zA-Z]).+$/'],
            'modelo'           => 'nullable|string|max:100',
            'fechaAdquisicion' => 'nullable|date',
            'estadoEquipo'     => 'required|string|max:50',
        ], [
            'nombreEquipo.regex' => 'El nombre del equipo debe contener al menos una letra.',
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
        // RF12: Validación para que el nombre contenga letras.
        $data = $request->validate([
            'idSucursal'       => 'required|integer',
            'idMarca'          => 'required|integer',
            'nombreEquipo'     => ['required', 'string', 'max:100', 'regex:/^(?=.*[a-zA-Z]).+$/'],
            'modelo'           => 'nullable|string|max:100',
            'fechaAdquisicion' => 'nullable|date',
            'estadoEquipo'     => 'required|string|max:50',
        ], [
            'nombreEquipo.regex' => 'El nombre del equipo debe contener al menos una letra.',
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
        if (!$equipo || $equipo->estadoEquipo == 'De Baja') {
            return back()->with('error', 'No se puede iniciar mantenimiento en un equipo dado de baja.');
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
            return back()->with('success', 'Mantenimiento iniciado. Equipo marcado como "En Mantenimiento".');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al iniciar mantenimiento: ' . $e->getMessage());
        }
    }

    public function reportarFallaForm()
    {
        $equipos = DB::select('CALL sp_TEquipamientos_GetOperativosWithDetails()');
        return view('equipamiento.reportar-falla', compact('equipos'));
    }

    public function reportarFallaStore(Request $request)
    {
        $data = $request->validate([
            'idEquipo'        => 'required|integer|exists:TEquipamientos,idEquipo',
            'descripcionFalla' => 'required|string|max:500',
            'gravedad'         => 'required|in:Baja,Media,Alta,Critica',
        ]);

        $usuario    = session('usuario');
        $empleado   = DB::table('TEmpleados')
            ->where('idUsuario', $usuario->idUsuario)
            ->where('estadoA', 1)
            ->first();
        $carnetEmp  = $empleado?->carnetEmpleado ?? $usuario->idUsuario;
        $direccionIP = $request->ip();

        DB::beginTransaction();
        try {
            DB::select('CALL sp_TReporteFallas_Insert(?, ?, ?, ?, ?, ?, ?, ?)', [
                $data['idEquipo'],
                $carnetEmp,
                date('Y-m-d H:i:s'),
                $data['descripcionFalla'],
                $data['gravedad'],
                'Pendiente',
                $usuario->idUsuario,
                $direccionIP,
            ]);

            $equipo = Equipamiento::getById((int) $data['idEquipo']);
            if ($equipo) {
                Equipamiento::update((int) $data['idEquipo'], [
                    'idSucursal'       => $equipo->idSucursal,
                    'idMarca'          => $equipo->idMarca,
                    'nombreEquipo'     => $equipo->nombreEquipo,
                    'modelo'           => $equipo->modelo,
                    'fechaAdquisicion' => $equipo->fechaAdquisicion,
                    'estadoEquipo'     => 'Fuera de Servicio',
                ], $usuario->idUsuario, $direccionIP);
            }

            DB::commit();
            return redirect()->route('equipamiento.reportar-falla')
                ->with('success', 'Falla reportada. El equipo ha sido marcado como "Fuera de Servicio".');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('equipamiento.reportar-falla')
                ->with('error', 'Error al reportar la falla: ' . $e->getMessage());
        }
    }

    public function fallasSinMantenimiento(Request $request)
    {
        $fechaDesde = $request->input('fecha_desde', '');
        $fechaHasta = $request->input('fecha_hasta', '');

        $equipos = DB::select('CALL sp_TEquipamientos_GetFallasSinMantenimiento(?, ?)', [$fechaDesde, $fechaHasta]);

        return view('equipamiento.fallas-sin-mantenimiento', compact('equipos'));
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

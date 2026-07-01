<?php

namespace App\Http\Controllers;

use App\Models\Equipamiento;
use App\Models\Marca;
use App\Models\Sucursal;
use App\Models\ReporteFalla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EquipamientoController extends Controller
{
    public function index(Request $request)
    {
        $equipos = Equipamiento::getAll();

        if ($request->filled('estado')) {
            $equipos = array_filter($equipos, function ($eq) use ($request) {
                return $eq->estadoEquipo === $request->estado;
            });
            $equipos = array_values($equipos);
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
            'idSucursal.required' => 'La sucursal es obligatoria.',
            'idSucursal.integer'  => 'La sucursal seleccionada no es válida.',
            'idMarca.required'    => 'La marca es obligatoria.',
            'idMarca.integer'     => 'La marca seleccionada no es válida.',
            'nombreEquipo.required' => 'El nombre del equipo es obligatorio.',
            'nombreEquipo.max'    => 'El nombre no debe exceder 100 caracteres.',
            'nombreEquipo.regex'  => 'El nombre del equipo debe contener al menos una letra.',
            'modelo.max'          => 'El modelo no debe exceder 100 caracteres.',
            'fechaAdquisicion.date' => 'La fecha de adquisición no es válida.',
            'estadoEquipo.required' => 'El estado del equipo es obligatorio.',
            'estadoEquipo.max'    => 'El estado no debe exceder 50 caracteres.',
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
            'idSucursal.required' => 'La sucursal es obligatoria.',
            'idSucursal.integer'  => 'La sucursal seleccionada no es válida.',
            'idMarca.required'    => 'La marca es obligatoria.',
            'idMarca.integer'     => 'La marca seleccionada no es válida.',
            'nombreEquipo.required' => 'El nombre del equipo es obligatorio.',
            'nombreEquipo.max'    => 'El nombre no debe exceder 100 caracteres.',
            'nombreEquipo.regex'  => 'El nombre del equipo debe contener al menos una letra.',
            'modelo.max'          => 'El modelo no debe exceder 100 caracteres.',
            'fechaAdquisicion.date' => 'La fecha de adquisición no es válida.',
            'estadoEquipo.required' => 'El estado del equipo es obligatorio.',
            'estadoEquipo.max'    => 'El estado no debe exceder 50 caracteres.',
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

        $validator = Validator::make($request->all(), [
            'descripcionMantenimiento' => 'required|string|max:500',
            'tecnicoAsignado'          => 'required|string|max:100',
            'costoMantenimiento'       => 'required|numeric|min:0',
            'fechaProgramada'          => 'required|date|after:today',
        ], [
            'descripcionMantenimiento.required' => 'La descripción del mantenimiento es obligatoria.',
            'descripcionMantenimiento.max' => 'La descripción no debe exceder 500 caracteres.',
            'tecnicoAsignado.required' => 'El técnico asignado es obligatorio.',
            'tecnicoAsignado.max' => 'El técnico asignado no debe exceder 100 caracteres.',
            'costoMantenimiento.required' => 'El costo estimado es obligatorio.',
            'costoMantenimiento.numeric' => 'El costo estimado debe ser un número.',
            'costoMantenimiento.min' => 'El costo estimado debe ser mayor o igual a 0.',
            'fechaProgramada.required' => 'La fecha de mantenimiento es obligatoria.',
            'fechaProgramada.date' => 'La fecha de mantenimiento no es válida.',
            'fechaProgramada.after' => 'La fecha debe ser posterior a hoy.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator)->with('error', 'Corrija los errores del formulario.')->with('manto_equipo_id', $id);
        }

        $data = $validator->validated();

        $usuarioA   = session('usuario')->idUsuario;
        $direccionIP = request()->ip();

        DB::beginTransaction();
        try {
            DB::select('CALL sp_TMantenimientoPreventivos_Insert(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                (int) $id,
                $data['fechaProgramada'],
                null,
                $data['descripcionMantenimiento'],
                $data['costoMantenimiento'],
                $data['tecnicoAsignado'],
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
        $validator = Validator::make($request->all(), [
            'idEquipo'        => 'required|integer|exists:TEquipamientos,idEquipo',
            'descripcionFalla' => 'required|string|max:255',
            'gravedad'         => 'required|in:Baja,Media,Alta,Critica',
        ], [
            'idEquipo.required' => 'Debe seleccionar un equipo.',
            'idEquipo.exists' => 'El equipo seleccionado no es válido.',
            'descripcionFalla.required' => 'La descripción de la falla es obligatoria.',
            'descripcionFalla.max' => 'La descripción no debe exceder 255 caracteres.',
            'gravedad.required' => 'Debe seleccionar la gravedad de la falla.',
            'gravedad.in' => 'La gravedad seleccionada no es válida.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator)->with('error', 'Corrija los errores del formulario.');
        }

        $data = $validator->validated();

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

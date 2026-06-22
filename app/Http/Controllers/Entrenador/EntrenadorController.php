<?php

namespace App\Http\Controllers\Entrenador;

use App\Http\Controllers\Controller;
use App\Models\Equipamiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntrenadorController extends Controller
{
    public function dashboard()
    {
        return view('entrenador.dashboard');
    }

    public function misClases()
    {
        $usuario = session('usuario');
        $empleado = DB::table('TEmpleados')
            ->where('idUsuario', $usuario->idUsuario)
            ->where('estadoA', 1)
            ->first();
        $carnetEmp = $empleado->carnetEmpleado ?? null;

        if (!$carnetEmp) {
            return response()->json([]);
        }

        $clases = DB::table('TClaseGrupales as cg')
            ->join('TActividades as a', 'cg.idActividad', '=', 'a.idActividad')
            ->join('TSucursales as s', 'cg.idSucursal', '=', 's.idSucursal')
            ->where('cg.carnetEmpleado', $carnetEmp)
            ->where('cg.estadoA', 1)
            ->select(
                'cg.idClaseGrupal',
                'cg.fecha',
                'cg.horaInicio',
                'cg.horaFin',
                'cg.cupoMaximo',
                'cg.estadoClase',
                'a.nombreActividad',
                's.nombre as nombreSucursal'
            )
            ->orderBy('cg.fecha', 'desc')
            ->orderBy('cg.horaInicio', 'desc')
            ->get()
            ->map(function ($clase) {
                $stats = DB::table('TReservas')
                    ->where('idClaseGrupal', $clase->idClaseGrupal)
                    ->where('estadoA', 1)
                    ->selectRaw("COUNT(*) as total")
                    ->selectRaw("SUM(CASE WHEN estadoReserva = 'Reservado' THEN 1 ELSE 0 END) as reservados")
                    ->selectRaw("SUM(CASE WHEN estadoReserva = 'Asistido' THEN 1 ELSE 0 END) as asistieron")
                    ->first();
                $clase->totalReservas = $stats->total ?? 0;
                $clase->reservados = $stats->reservados ?? 0;
                $clase->asistieron = $stats->asistieron ?? 0;
                return $clase;
            });

        return response()->json($clases);
    }

    public function participantes($id)
    {
        $reservas = DB::table('TReservas as r')
            ->join('TSocios as s', 'r.carnetSocio', '=', 's.carnetSocio')
            ->join('TUsuarios as u', 's.idUsuario', '=', 'u.idUsuario')
            ->where('r.idClaseGrupal', $id)
            ->where('r.estadoA', 1)
            ->select(
                'r.idReserva',
                'r.estadoReserva',
                'r.fechaReserva',
                's.fotografiaUrl',
                's.observacionesMedicas',
                'u.nombre1',
                'u.apellido1',
                'u.correo',
                'u.telefono'
            )
            ->orderBy('r.estadoReserva', 'asc')
            ->orderBy('u.apellido1', 'asc')
            ->get();

        return response()->json($reservas);
    }

    public function fallas()
    {
        $usuario = session('usuario');
        $empleado = DB::table('TEmpleados')
            ->where('idUsuario', $usuario->idUsuario)
            ->where('estadoA', 1)
            ->first();
        $carnetEmp = $empleado?->carnetEmpleado;
        $idSucursal = $empleado?->idSucursal;

        $equipos = [];
        if ($idSucursal) {
            $equipos = DB::select('CALL sp_TEquipamientos_GetOperativosBySucursal(?)', [$idSucursal]);
        }

        $historial = [];
        if ($carnetEmp) {
            $historial = DB::select('CALL sp_TReporteFallas_GetByEmpleado(?)', [$carnetEmp]);
        }

        return view('entrenador.fallas', compact('equipos', 'historial'));
    }

    public function reportarFalla(Request $request)
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
        $carnetEmp  = $empleado?->carnetEmpleado;

        if (!$carnetEmp) {
            return redirect()->route('entrenador.fallas')
                ->with('error', 'No se encontró un empleado asociado a su usuario.');
        }

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
                $updateData = [
                    'idSucursal'       => $equipo->idSucursal,
                    'idMarca'          => $equipo->idMarca,
                    'nombreEquipo'     => $equipo->nombreEquipo,
                    'modelo'           => $equipo->modelo,
                    'fechaAdquisicion' => $equipo->fechaAdquisicion,
                    'estadoEquipo'     => 'Fuera de Servicio',
                ];
                Equipamiento::update((int) $data['idEquipo'], $updateData, $usuario->idUsuario, $direccionIP);
            }

            DB::commit();
            return redirect()->route('entrenador.fallas')
                ->with('success', 'Falla reportada. El equipo ha sido marcado como "Fuera de Servicio".');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('entrenador.fallas')
                ->with('error', 'Error al reportar la falla: ' . $e->getMessage());
        }
    }
}

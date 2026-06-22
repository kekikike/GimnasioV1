<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClaseGrupalController extends Controller
{
    public function index()
    {
        $actividades = DB::table('TActividades')
            ->where('estadoA', 1)
            ->where('estado', 1)
            ->get();

        $empleados = DB::table('TEmpleados as e')
            ->join('TUsuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->where('e.estadoA', 1)
            ->select('e.carnetEmpleado', 'u.nombre1', 'u.apellido1')
            ->get();

        $sucursales = DB::table('TSucursales')
            ->where('estadoA', 1)
            ->get();

        return view('admin.clases', compact('actividades', 'empleados', 'sucursales'));
    }

    public function listar()
    {
        $clases = DB::table('TClaseGrupales as cg')
            ->join('TActividades as a', 'cg.idActividad', '=', 'a.idActividad')
            ->join('TEmpleados as e', 'cg.carnetEmpleado', '=', 'e.carnetEmpleado')
            ->join('TUsuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->join('TSucursales as s', 'cg.idSucursal', '=', 's.idSucursal')
            ->where('cg.estadoA', 1)
            ->select(
                'cg.*',
                'a.nombreActividad',
                'u.nombre1',
                'u.apellido1',
                's.nombre as nombreSucursal'
            )
            ->orderBy('cg.fecha', 'desc')
            ->orderBy('cg.horaInicio', 'desc')
            ->get()
            ->map(function ($clase) {
                $reservas = DB::table('TReservas')
                    ->where('idClaseGrupal', $clase->idClaseGrupal)
                    ->where('estadoA', 1)
                    ->selectRaw("COUNT(*) as total, SUM(CASE WHEN estadoReserva = 'Reservado' THEN 1 ELSE 0 END) as reservados")
                    ->first();
                $clase->totalReservas = $reservas->total ?? 0;
                $clase->cuposOcupados = $reservas->reservados ?? 0;
                return $clase;
            });

        return response()->json($clases);
    }

    public function store(Request $request)
    {
        $request->validate([
            'idActividad' => 'required|integer',
            'carnetEmpleado' => 'required|integer',
            'idSucursal' => 'required|integer',
            'fecha' => 'required|date',
            'horaInicio' => 'required',
            'horaFin' => 'required|after:horaInicio',
            'cupoMaximo' => 'required|integer|min:1',
        ]);

        $usuarioA = session('usuario')->idUsuario ?? 1;

        $id = DB::table('TClaseGrupales')->insertGetId([
            'idActividad' => $request->idActividad,
            'carnetEmpleado' => $request->carnetEmpleado,
            'idSucursal' => $request->idSucursal,
            'fecha' => $request->fecha,
            'horaInicio' => $request->horaInicio,
            'horaFin' => $request->horaFin,
            'cupoMaximo' => $request->cupoMaximo,
            'estadoClase' => 'Programada',
            'usuarioA' => $usuarioA,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clase grupal registrada correctamente.',
            'id' => $id,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'idActividad' => 'required|integer',
            'carnetEmpleado' => 'required|integer',
            'idSucursal' => 'required|integer',
            'fecha' => 'required|date',
            'horaInicio' => 'required',
            'horaFin' => 'required|after:horaInicio',
            'cupoMaximo' => 'required|integer|min:1',
            'estadoClase' => 'required|in:Programada,Cursandose,Cancelada',
        ]);

        DB::table('TClaseGrupales')
            ->where('idClaseGrupal', $id)
            ->update([
                'idActividad' => $request->idActividad,
                'carnetEmpleado' => $request->carnetEmpleado,
                'idSucursal' => $request->idSucursal,
                'fecha' => $request->fecha,
                'horaInicio' => $request->horaInicio,
                'horaFin' => $request->horaFin,
                'cupoMaximo' => $request->cupoMaximo,
                'estadoClase' => $request->estadoClase,
            ]);

        if ($request->estadoClase === 'Cancelada') {
            DB::table('TReservas')
                ->where('idClaseGrupal', $id)
                ->where('estadoReserva', 'Reservado')
                ->update(['estadoReserva' => 'Cancelado']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Clase actualizada correctamente.',
        ]);
    }

    public function destroy($id)
    {
        DB::table('TClaseGrupales')
            ->where('idClaseGrupal', $id)
            ->update(['estadoA' => 0, 'estadoClase' => 'Cancelada']);

        DB::table('TReservas')
            ->where('idClaseGrupal', $id)
            ->where('estadoReserva', 'Reservado')
            ->update(['estadoReserva' => 'Cancelado']);

        return response()->json([
            'success' => true,
            'message' => 'Clase cancelada correctamente.',
        ]);
    }

    public function listarReservas($id)
    {
        $reservas = DB::table('TReservas as r')
            ->join('TSocios as s', 'r.carnetSocio', '=', 's.carnetSocio')
            ->join('TUsuarios as u', 's.idUsuario', '=', 'u.idUsuario')
            ->where('r.idClaseGrupal', $id)
            ->where('r.estadoA', 1)
            ->select(
                'r.*',
                's.fotografiaUrl',
                'u.nombre1',
                'u.apellido1',
                'u.correo',
                'u.telefono'
            )
            ->orderBy('r.fechaReserva', 'desc')
            ->get();

        return response()->json($reservas);
    }

    public function marcarAsistencia(Request $request)
    {
        $request->validate([
            'idReserva' => 'required|integer',
            'estado' => 'required|in:Asistido,Penalizado',
        ]);

        DB::table('TReservas')
            ->where('idReserva', $request->idReserva)
            ->update(['estadoReserva' => $request->estado]);

        if ($request->estado === 'Penalizado') {
            $reserva = DB::table('TReservas')
                ->where('idReserva', $request->idReserva)
                ->first();

            if ($reserva) {
                DB::table('TPenalizaciones')->insert([
                    'carnetSocio' => $reserva->carnetSocio,
                    'idReserva' => $request->idReserva,
                    'fecha' => now()->format('Y-m-d'),
                    'estado' => true,
                    'usuarioA' => session('usuario')->idUsuario ?? 1,
                ]);

                DB::table('TSocios')
                    ->where('carnetSocio', $reserva->carnetSocio)
                    ->increment('strikes');

                $socio = DB::table('TSocios')
                    ->where('carnetSocio', $reserva->carnetSocio)
                    ->first();

                if ($socio && $socio->strikes >= 3) {
                    DB::table('TPenalizaciones')->insert([
                        'carnetSocio' => $reserva->carnetSocio,
                        'idReserva' => null,
                        'fecha' => now()->format('Y-m-d'),
                        'estado' => true,
                        'usuarioA' => session('usuario')->idUsuario ?? 1,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Asistencia registrada correctamente.',
        ]);
    }

    public function reporteOcupacion(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->endOfMonth()->format('Y-m-d'));

        $clases = DB::table('TClaseGrupales as cg')
            ->join('TActividades as a', 'cg.idActividad', '=', 'a.idActividad')
            ->join('TSucursales as s', 'cg.idSucursal', '=', 's.idSucursal')
            ->where('cg.estadoA', 1)
            ->whereBetween('cg.fecha', [$desde, $hasta])
            ->select(
                'cg.idClaseGrupal',
                'cg.fecha',
                'cg.horaInicio',
                'cg.horaFin',
                'cg.cupoMaximo',
                'cg.estadoClase',
                'a.nombreActividad',
                's.nombre as sucursal'
            )
            ->orderBy('cg.fecha', 'asc')
            ->orderBy('cg.horaInicio', 'asc')
            ->get()
            ->map(function ($clase) {
                $stats = DB::table('TReservas')
                    ->where('idClaseGrupal', $clase->idClaseGrupal)
                    ->where('estadoA', 1)
                    ->selectRaw("COUNT(*) as total")
                    ->selectRaw("SUM(CASE WHEN estadoReserva = 'Asistido' THEN 1 ELSE 0 END) as asistieron")
                    ->selectRaw("SUM(CASE WHEN estadoReserva = 'Penalizado' THEN 1 ELSE 0 END) as penalizados")
                    ->selectRaw("SUM(CASE WHEN estadoReserva = 'Cancelado' THEN 1 ELSE 0 END) as cancelados")
                    ->first();
                $clase->totalReservas = $stats->total ?? 0;
                $clase->asistieron = $stats->asistieron ?? 0;
                $clase->penalizados = $stats->penalizados ?? 0;
                $clase->cancelados = $stats->cancelados ?? 0;
                $clase->porcentajeOcupacion = $clase->cupoMaximo > 0
                    ? round(($clase->totalReservas / $clase->cupoMaximo) * 100, 1)
                    : 0;
                return $clase;
            });

        return response()->json($clases);
    }
}

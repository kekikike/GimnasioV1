<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClaseGrupalController extends Controller
{
    public function index()
    {
        $data = $this->getFormData();
        return view('admin.clases', $data);
    }

    public function create()
    {
        $data = $this->getFormData();
        return view('admin.clases.create', $data);
    }

    private function getFormData()
    {
        $actividades = DB::table('TActividades')
            ->where('estadoA', 1)
            ->where('estado', 1)
            ->get();

        $empleados = DB::table('TEmpleados as e')
            ->join('TUsuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->where('e.estadoA', 1)
            ->where('u.idRol', 3)
            ->select('e.carnetEmpleado', 'u.nombre1', 'u.apellido1', 'u.idRol')
            ->get();

        $adminSucursalId = $this->getAdminSucursalId();

        $adminSucursalNombre = null;
        if ($adminSucursalId) {
            $suc = DB::table('TSucursales')->where('idSucursal', $adminSucursalId)->first();
            $adminSucursalNombre = $suc->nombre ?? null;
        }

        return compact('actividades', 'empleados', 'adminSucursalId', 'adminSucursalNombre');
    }

    private function getAdminSucursalId()
    {
        $usuarioA = session('usuario')->idUsuario ?? null;
        if (!$usuarioA) return null;
        $empleado = DB::table('TEmpleados')
            ->where('idUsuario', $usuarioA)
            ->where('estadoA', 1)
            ->first();
        return $empleado->idSucursal ?? null;
    }

    public function listar(Request $request)
    {
        $clases = DB::table('TClaseGrupales as cg')
            ->join('TActividades as a', 'cg.idActividad', '=', 'a.idActividad')
            ->join('TEmpleados as e', 'cg.carnetEmpleado', '=', 'e.carnetEmpleado')
            ->join('TUsuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->join('TSucursales as s', 'cg.idSucursal', '=', 's.idSucursal');

        if ($request->estadoClase !== 'Cancelada') {
            $clases->where('cg.estadoA', 1);
        }

        $clases = $clases->select(
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
                $total = DB::table('TReservas')
                    ->where('idClaseGrupal', $clase->idClaseGrupal)
                    ->where('estadoA', 1)
                    ->count();
                $cuposOcupados = DB::table('TReservas')
                    ->where('idClaseGrupal', $clase->idClaseGrupal)
                    ->where('estadoReserva', 'Reservado')
                    ->where('estadoA', 1)
                    ->count();
                $clase->totalReservas = $total;
                $clase->cuposOcupados = $cuposOcupados;
                return $clase;
            });

        return response()->json($clases);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idActividad' => 'required|integer',
            'carnetEmpleado' => 'required|integer',
            'fecha' => 'required|date',
            'horaInicio' => 'required',
            'horaFin' => 'required',
            'cupoMaximo' => 'required|integer|min:1|max:99999',
        ], [
            'idActividad.required' => 'Debe seleccionar una actividad.',
            'carnetEmpleado.required' => 'Debe seleccionar un instructor.',
            'fecha.required' => 'La fecha es obligatoria.',
            'horaInicio.required' => 'La hora de inicio es obligatoria.',
            'horaFin.required' => 'La hora de fin es obligatoria.',
            'cupoMaximo.required' => 'El cupo máximo es obligatorio.',
            'cupoMaximo.integer' => 'El cupo máximo debe ser un número entero.',
            'cupoMaximo.min' => 'El cupo máximo debe ser al menos 1.',
            'cupoMaximo.max' => 'El cupo máximo no debe exceder 99999.',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withInput()->withErrors($validator)->with('error', $validator->errors()->first());
        }

        // Validar duración mínima de 30 minutos
        $diffMinutos = $this->calcularDiferenciaMinutos($request->horaInicio, $request->horaFin);
        if ($diffMinutos < 30) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'La duración mínima de la clase debe ser de 30 minutos.'], 422);
            }
            return redirect()->back()->withInput()->with('error', 'La duración mínima de la clase debe ser de 30 minutos.');
        }

        // Validar que el instructor no tenga otra clase en el mismo horario
        $conflicto = DB::table('TClaseGrupales as cg')
            ->join('TActividades as a', 'cg.idActividad', '=', 'a.idActividad')
            ->where('cg.carnetEmpleado', $request->carnetEmpleado)
            ->where('cg.fecha', $request->fecha)
            ->where('cg.estadoA', 1)
            ->where('cg.estadoClase', '!=', 'Cancelada')
            ->where(function ($q) use ($request) {
                $q->whereRaw('? < cg.horaFin', [$request->horaInicio])
                  ->whereRaw('? > cg.horaInicio', [$request->horaFin]);
            })
            ->select('cg.horaInicio', 'cg.horaFin', 'a.nombreActividad')
            ->first();

        if ($conflicto) {
            $msg = "El instructor ya tiene la clase \"{$conflicto->nombreActividad}\" programada de {$conflicto->horaInicio} a {$conflicto->horaFin} en ese horario.";
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }

        $usuarioA = session('usuario')->idUsuario ?? 1;
        $idSucursal = $this->getAdminSucursalId();
        if (!$idSucursal) {
            $msg = 'No se pudo determinar la sucursal del administrador.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }
        $direccionIP = $request->ip();

        try {
            $result = DB::select(
                'CALL sp_TClaseGrupales_Insert_Validated(?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $request->idActividad,
                    $request->carnetEmpleado,
                    $idSucursal,
                    $request->fecha,
                    $request->horaInicio,
                    $request->horaFin,
                    $request->cupoMaximo,
                    $usuarioA,
                    $direccionIP,
                ]
            );

            $row = $result[0] ?? null;
            $success = $row && (bool) $row->success;
            $message = $row->message ?? 'Clase grupal registrada correctamente.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => $success,
                    'message' => $message,
                    'id' => $row->id ?? null,
                ]);
            }

            if ($success) {
                return redirect()->route('admin.clases.index')
                    ->with('success', $message);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', $message);

        } catch (\Illuminate\Database\QueryException $e) {
            $mensaje = $this->extraerMensajeError($e);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $mensaje]);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', $mensaje);

        } catch (\Exception $e) {
            $msg = 'Error de conexión al registrar la clase.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return redirect()->back()
                ->withInput()
                ->with('error', $msg);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'idActividad' => 'required|integer',
            'carnetEmpleado' => 'required|integer',
            'fecha' => 'required|date',
            'horaInicio' => 'required',
            'horaFin' => 'required',
            'cupoMaximo' => 'required|integer|min:1|max:99999',
            'estadoClase' => 'required|in:Programada,Cursandose,Cancelada,Finalizada',
        ], [
            'idActividad.required' => 'Debe seleccionar una actividad.',
            'carnetEmpleado.required' => 'Debe seleccionar un instructor.',
            'fecha.required' => 'La fecha es obligatoria.',
            'horaInicio.required' => 'La hora de inicio es obligatoria.',
            'horaFin.required' => 'La hora de fin es obligatoria.',
            'cupoMaximo.required' => 'El cupo máximo es obligatorio.',
            'cupoMaximo.integer' => 'El cupo máximo debe ser un número entero.',
            'cupoMaximo.min' => 'El cupo máximo debe ser al menos 1.',
            'cupoMaximo.max' => 'El cupo máximo no debe exceder 99999.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        // Validar duración mínima de 30 minutos
        $diffMinutos = $this->calcularDiferenciaMinutos($request->horaInicio, $request->horaFin);
        if ($diffMinutos < 30) {
            return response()->json(['success' => false, 'message' => 'La duración mínima de la clase debe ser de 30 minutos.'], 422);
        }

        // Validar que el instructor no tenga otra clase en el mismo horario
        $conflicto = DB::table('TClaseGrupales as cg')
            ->join('TActividades as a', 'cg.idActividad', '=', 'a.idActividad')
            ->where('cg.carnetEmpleado', $request->carnetEmpleado)
            ->where('cg.fecha', $request->fecha)
            ->where('cg.idClaseGrupal', '!=', $id)
            ->where('cg.estadoA', 1)
            ->where('cg.estadoClase', '!=', 'Cancelada')
            ->where(function ($q) use ($request) {
                $q->whereRaw('? < cg.horaFin', [$request->horaInicio])
                  ->whereRaw('? > cg.horaInicio', [$request->horaFin]);
            })
            ->select('cg.horaInicio', 'cg.horaFin', 'a.nombreActividad')
            ->first();

        if ($conflicto) {
            return response()->json([
                'success' => false,
                'message' => "El instructor ya tiene la clase \"{$conflicto->nombreActividad}\" programada de {$conflicto->horaInicio} a {$conflicto->horaFin} en ese horario.",
            ], 422);
        }

        $usuarioA = session('usuario')->idUsuario ?? 1;
        $idSucursal = $this->getAdminSucursalId();
        if (!$idSucursal) {
            return response()->json(['success' => false, 'message' => 'No se pudo determinar la sucursal del administrador.'], 422);
        }

        DB::statement(
            'CALL sp_TClaseGrupales_Update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $id,
                $request->idActividad,
                $request->carnetEmpleado,
                $idSucursal,
                $request->fecha,
                $request->horaInicio,
                $request->horaFin,
                $request->cupoMaximo,
                $request->estadoClase,
                $usuarioA,
                $request->ip(),
            ]
        );

        DB::table('TClaseGrupales')
            ->where('idClaseGrupal', $id)
            ->update(['usuarioA' => $usuarioA]);

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
        $clase = DB::table('TClaseGrupales')->where('idClaseGrupal', $id)->first();
        if (!$clase) {
            return response()->json(['success' => false, 'message' => 'Clase no encontrada.'], 404);
        }

        $usuarioA = session('usuario')->idUsuario ?? 1;
        $estadoAnterior = $clase->estadoClase;
        $estadoAAnterior = $clase->estadoA;

        DB::table('TClaseGrupales')
            ->where('idClaseGrupal', $id)
            ->update([
                'estadoClase' => 'Cancelada',
                'estadoA'     => 0,
            ]);

        DB::table('TReservas')
            ->where('idClaseGrupal', $id)
            ->where('estadoReserva', 'Reservado')
            ->update(['estadoReserva' => 'Cancelado']);

        DB::table('tauditorias')->insert([
            'tablaNombre'   => 'TClaseGrupales',
            'registroId'    => $id,
            'accion'        => 'Cancelar',
            'campo'         => 'estadoClase',
            'valorAnterior' => $estadoAnterior,
            'valorNuevo'    => 'Cancelada',
            'usuarioA'      => $usuarioA,
            'fechaA'        => now(),
            'direccionIP'   => request()->ip(),
            'detalles'      => 'Clase cancelada (desactivada) desde el panel de administración',
        ]);

        if ($estadoAAnterior != 0) {
            DB::table('tauditorias')->insert([
                'tablaNombre'   => 'TClaseGrupales',
                'registroId'    => $id,
                'accion'        => 'Desactivar',
                'campo'         => 'estadoA',
                'valorAnterior' => (string) $estadoAAnterior,
                'valorNuevo'    => '0',
                'usuarioA'      => $usuarioA,
                'fechaA'        => now(),
                'direccionIP'   => request()->ip(),
                'detalles'      => 'Clase desactivada automáticamente al cancelar',
            ]);
        }

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

        $participantes = $reservas->filter(fn($r) => in_array($r->estadoReserva, ['Reservado', 'Asistido']))->values();
        $cancelados = $reservas->filter(fn($r) => in_array($r->estadoReserva, ['Cancelado']))->values();
        $penalizados = $reservas->filter(fn($r) => $r->estadoReserva === 'Penalizado')->values();
        $clase = DB::table('TClaseGrupales')->where('idClaseGrupal', $id)->first();
        $cuposOcupados = DB::table('TReservas')
            ->where('idClaseGrupal', $id)
            ->where('estadoReserva', 'Reservado')
            ->where('estadoA', 1)
            ->count();

        return response()->json([
            'participantes' => $participantes,
            'cancelados' => $cancelados,
            'penalizados' => $penalizados,
            'cupoMaximo' => $clase->cupoMaximo ?? 0,
            'cuposOcupados' => $cuposOcupados,
        ]);
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
                $usuarioA = session('usuario')->idUsuario ?? 1;
                $direccionIP = $request->ip();

                try {
                    DB::statement(
                        'CALL sp_TSocios_AplicarStrike(?, ?, ?, ?, ?, @adm_nuevosStrikes, @adm_mensaje)',
                        [
                            $reserva->carnetSocio,
                            $request->idReserva,
                            now()->format('Y-m-d'),
                            $usuarioA,
                            $direccionIP,
                        ]
                    );
                } catch (\Illuminate\Database\QueryException $e) {
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

    private function calcularDiferenciaMinutos($horaInicio, $horaFin)
    {
        $partsInicio = explode(':', $horaInicio);
        $partsFin = explode(':', $horaFin);
        $minutosInicio = (int)$partsInicio[0] * 60 + (int)$partsInicio[1];
        $minutosFin = (int)$partsFin[0] * 60 + (int)$partsFin[1];
        return $minutosFin - $minutosInicio;
    }

    private function extraerMensajeError(\Illuminate\Database\QueryException $e)
    {
        $prev = $e->getPrevious();
        $raw = $prev ? $prev->getMessage() : $e->getMessage();
        if (preg_match('/\d+\s+(.+?)(?:\s*\(Connection:|\s*\(SQL:|\s*$)/s', $raw, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/SQLSTATE\[45000\].*?\[(?:\d+)\]\s*(.*?)(?:\(SQL|$)/i', $e->getMessage(), $m)) {
            return trim($m[1]);
        }
        return 'Error de validación al registrar la clase.';
    }
}

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

    private function actualizarEstadoClases()
    {
        $limiteInicio = now()->subMinutes(5)->format('Y-m-d H:i:s');
        $ahora = now()->format('Y-m-d H:i:s');

        DB::update("
            UPDATE TClaseGrupales
            SET estadoClase = 'Cursandose'
            WHERE estadoClase = 'Programada'
              AND CONCAT(fecha, ' ', horaInicio) <= ?
        ", [$limiteInicio]);

        DB::update("
            UPDATE TClaseGrupales
            SET estadoClase = 'Finalizada'
            WHERE estadoClase IN ('Programada', 'Cursandose')
              AND CONCAT(fecha, ' ', horaFin) < ?
        ", [$ahora]);
    }

    public function misClases()
    {
        $this->actualizarEstadoClases();
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
                $total = DB::table('TReservas')
                    ->where('idClaseGrupal', $clase->idClaseGrupal)
                    ->where('estadoA', 1)
                    ->count();
                $asistieron = DB::table('TReservas')
                    ->where('idClaseGrupal', $clase->idClaseGrupal)
                    ->where('estadoA', 1)
                    ->where('estadoReserva', 'Asistido')
                    ->count();
                $reservados = DB::table('TReservas')
                    ->where('idClaseGrupal', $clase->idClaseGrupal)
                    ->where('estadoReserva', 'Reservado')
                    ->where('estadoA', 1)
                    ->count();
                $clase->totalReservas = $total;
                $clase->reservados = $reservados;
                $clase->asistieron = $asistieron;
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

        $participantes = $reservas->filter(fn($r) => in_array($r->estadoReserva, ['Reservado', 'Asistido']))->values();
        $cancelados = $reservas->filter(fn($r) => $r->estadoReserva === 'Cancelado')->values();
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

    public function asistenciasClase()
    {
        return view('entrenador.asistencias');
    }

    public function clasesHoy()
    {
        $this->actualizarEstadoClases();
        $usuario = session('usuario');
        $empleado = DB::table('TEmpleados')
            ->where('idUsuario', $usuario->idUsuario)
            ->where('estadoA', 1)
            ->first();
        $carnetEmp = $empleado->carnetEmpleado ?? null;

        if (!$carnetEmp) {
            return response()->json([]);
        }

        $hoy = now()->format('Y-m-d');
        $horaActual = now()->format('H:i:s');

        $clases = DB::table('TClaseGrupales as cg')
            ->join('TActividades as a', 'cg.idActividad', '=', 'a.idActividad')
            ->join('TSucursales as s', 'cg.idSucursal', '=', 's.idSucursal')
            ->where('cg.carnetEmpleado', $carnetEmp)
            ->where('cg.estadoA', 1)
            ->where('cg.fecha', $hoy)
            ->where('cg.estadoClase', '!=', 'Cancelada')
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
            ->orderBy('cg.horaInicio', 'asc')
            ->get()
            ->map(function ($clase) use ($horaActual) {
                $total = DB::table('TReservas')
                    ->where('idClaseGrupal', $clase->idClaseGrupal)
                    ->where('estadoA', 1)
                    ->count();
                $asistieron = DB::table('TReservas')
                    ->where('idClaseGrupal', $clase->idClaseGrupal)
                    ->where('estadoA', 1)
                    ->where('estadoReserva', 'Asistido')
                    ->count();
                $reservados = DB::table('TReservas')
                    ->where('idClaseGrupal', $clase->idClaseGrupal)
                    ->where('estadoReserva', 'Reservado')
                    ->where('estadoA', 1)
                    ->count();
                $clase->totalReservas = $total;
                $clase->reservados = $reservados;
                $clase->asistieron = $asistieron;

                if ($horaActual < $clase->horaInicio) {
                    $clase->estadoAsistencia = 'proxima';
                } elseif ($horaActual > $clase->horaFin) {
                    $clase->estadoAsistencia = 'expirada';
                } else {
                    $clase->estadoAsistencia = 'en_curso';
                }

                return $clase;
            });

        return response()->json($clases);
    }

    public function alumnosClase($id)
    {
        $usuario = session('usuario');
        $empleado = DB::table('TEmpleados')
            ->where('idUsuario', $usuario->idUsuario)
            ->where('estadoA', 1)
            ->first();
        $carnetEmp = $empleado->carnetEmpleado ?? null;

        if (!$carnetEmp) {
            return response()->json(['error' => 'Empleado no encontrado.'], 403);
        }

        $clase = DB::table('TClaseGrupales')
            ->where('idClaseGrupal', $id)
            ->where('carnetEmpleado', $carnetEmp)
            ->where('estadoA', 1)
            ->first();

        if (!$clase) {
            return response()->json(['error' => 'Clase no encontrada o no asignada a este entrenador.'], 404);
        }

        // Calcular estadoAsistencia en tiempo real con el servidor
        $horaActual = now()->format('H:i:s');
        if ($horaActual < $clase->horaInicio) {
            $clase->estadoAsistencia = 'proxima';
        } elseif ($horaActual > $clase->horaFin) {
            $clase->estadoAsistencia = 'expirada';
        } else {
            $clase->estadoAsistencia = 'en_curso';
        }

        $alumnos = DB::table('TReservas as r')
            ->join('TSocios as s', 'r.carnetSocio', '=', 's.carnetSocio')
            ->join('TUsuarios as u', 's.idUsuario', '=', 'u.idUsuario')
            ->where('r.idClaseGrupal', $id)
            ->where('r.estadoA', 1)
            ->whereIn('r.estadoReserva', ['Reservado', 'Asistido', 'Penalizado'])
            ->select(
                'r.idReserva',
                'r.estadoReserva',
                'r.fechaReserva',
                's.fotografiaUrl',
                's.observacionesMedicas',
                'u.nombre1',
                'u.nombre2',
                'u.apellido1',
                'u.apellido2',
                'u.correo',
                'u.telefono'
            )
            ->orderBy('u.apellido1', 'asc')
            ->orderBy('u.nombre1', 'asc')
            ->get();

        return response()->json([
            'clase' => $clase,
            'alumnos' => $alumnos,
        ]);
    }

    public function marcarAsistencia(Request $request)
    {
        $request->validate([
            'idReserva' => 'required|integer',
            'estado' => 'required|in:Asistido,Penalizado',
        ]);

        $usuario = session('usuario');
        $usuarioA = $usuario->idUsuario ?? 1;
        $direccionIP = $request->ip();

        try {
            $result = DB::select(
                'CALL sp_TReservas_MarcarAsistencia_Entrenador(?, ?, ?, ?)',
                [
                    $request->idReserva,
                    $request->estado,
                    $usuarioA,
                    $direccionIP,
                ]
            );

            $row = $result[0] ?? null;

            return response()->json([
                'success' => true,
                'message' => $row->message ?? 'Asistencia registrada correctamente.',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            $mensaje = $this->extraerMensajeSP($e);
            return response()->json([
                'success' => false,
                'message' => $mensaje,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión al registrar asistencia.',
            ], 500);
        }
    }

    private function extraerMensajeSP(\Illuminate\Database\QueryException $e): string
    {
        $prev = $e->getPrevious();
        $raw = $prev ? $prev->getMessage() : $e->getMessage();
        if (preg_match('/SQLSTATE\[45000\].*?\[(?:\d+)\]\s*(.*?)(?:\(Connection:|\(SQL:|$)/i', $raw, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/\d+\s+(.+?)(?:\s*\(Connection:|\s*\(SQL:|\s*$)/s', $raw, $m)) {
            return trim($m[1]);
        }
        return 'Error de validación al registrar asistencia.';
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

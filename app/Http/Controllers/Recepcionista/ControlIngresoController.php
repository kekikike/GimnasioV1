<?php

namespace App\Http\Controllers\Recepcionista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ControlIngresoController extends Controller
{
    public function listarTodos()
    {
        $socios = DB::table('TSocios as s')
            ->join('TUsuarios as u', 's.idUsuario', '=', 'u.idUsuario')
            ->where('s.estadoA', 1)
            ->select(
                's.carnetSocio',
                's.direccion',
                's.fotografiaUrl',
                's.nombreContactoEmergencia',
                's.telefonoContactoEmergencia',
                's.observacionesMedicas',
                's.estadoSocio',
                's.strikes',
                'u.nombre1',
                'u.nombre2',
                'u.apellido1',
                'u.apellido2',
                'u.correo',
                'u.telefono'
            )
            ->orderBy('u.nombre1', 'asc')
            ->orderBy('u.apellido1', 'asc')
            ->get()
            ->map(function ($socio) {
                $membresia = DB::table('TMembresias')
                    ->where('carnetSocio', $socio->carnetSocio)
                    ->where('estadoA', 1)
                    ->orderBy('idMembresia', 'desc')
                    ->first();
                $socio->membresiaEstado = $membresia->estadoMembresia ?? null;
                $socio->membresiaFin = $membresia->fechaFinMembresia ?? null;
                $socio->membresiaPlan = null;
                if ($membresia) {
                    $plan = DB::table('TPlanes')->where('idPlan', $membresia->idPlan)->first();
                    $socio->membresiaPlan = $plan->nombrePlan ?? null;
                }
                return $socio;
            });

        return response()->json($socios);
    }

    public function buscarSocio(Request $request)
    {
        $term = $request->get('q', '');
        if (strlen($term) < 1) {
            return response()->json([]);
        }

        $socios = DB::table('TSocios as s')
            ->join('TUsuarios as u', 's.idUsuario', '=', 'u.idUsuario')
            ->where('s.estadoA', 1)
            ->where(function ($q) use ($term) {
                $q->where('s.carnetSocio', 'like', "%{$term}%")
                  ->orWhere('u.nombre1', 'like', "%{$term}%")
                  ->orWhere('u.nombre2', 'like', "%{$term}%")
                  ->orWhere('u.apellido1', 'like', "%{$term}%")
                  ->orWhere('u.apellido2', 'like', "%{$term}%")
                  ->orWhere('u.correo', 'like', "%{$term}%")
                  ->orWhere('u.telefono', 'like', "%{$term}%")
                  ->orWhere(DB::raw("CONCAT(u.nombre1, ' ', u.apellido1)"), 'like', "%{$term}%");
            })
            ->select(
                's.carnetSocio',
                's.idUsuario',
                's.direccion',
                's.fotografiaUrl',
                's.nombreContactoEmergencia',
                's.telefonoContactoEmergencia',
                's.observacionesMedicas',
                's.estadoSocio',
                's.strikes',
                'u.nombre1',
                'u.nombre2',
                'u.apellido1',
                'u.apellido2',
                'u.correo',
                'u.telefono'
            )
            ->limit(10)
            ->get();

        return response()->json($socios);
    }

    public function detalleSocio($carnet)
    {
        $socio = DB::table('TSocios as s')
            ->join('TUsuarios as u', 's.idUsuario', '=', 'u.idUsuario')
            ->where('s.carnetSocio', $carnet)
            ->where('s.estadoA', 1)
            ->select(
                's.carnetSocio',
                's.idUsuario',
                's.direccion',
                's.fotografiaUrl',
                's.nombreContactoEmergencia',
                's.telefonoContactoEmergencia',
                's.observacionesMedicas',
                's.estadoSocio',
                's.strikes',
                'u.nombre1',
                'u.nombre2',
                'u.apellido1',
                'u.apellido2',
                'u.correo',
                'u.telefono'
            )
            ->first();

        if (!$socio) {
            return response()->json(['error' => 'Socio no encontrado'], 404);
        }

        $membresia = DB::table('TMembresias as m')
            ->join('TPlanes as p', 'm.idPlan', '=', 'p.idPlan')
            ->where('m.carnetSocio', $carnet)
            ->where('m.estadoA', 1)
            ->orderBy('m.idMembresia', 'desc')
            ->select(
                'm.idMembresia',
                'm.idPlan',
                'm.fechaInicioMembresia',
                'm.fechaFinMembresia',
                'm.estadoMembresia',
                'p.nombrePlan',
                'p.costoPlan',
                'p.duracionDias'
            )
            ->first();

        $ultimosAccesos = DB::table('TControlAccesos')
            ->where('carnetSocio', $carnet)
            ->where('estadoA', 1)
            ->orderBy('fechaAcceso', 'desc')
            ->orderBy('horaAcceso', 'desc')
            ->limit(5)
            ->get();

        $penalizacionesActivas = DB::table('TPenalizaciones')
            ->where('carnetSocio', $carnet)
            ->where('estado', 1)
            ->where('estadoA', 1)
            ->count();

        $reservasPendientes = DB::table('TReservas')
            ->where('carnetSocio', $carnet)
            ->where('estadoReserva', 'Reservado')
            ->where('estadoA', 1)
            ->count();

        return response()->json([
            'socio' => $socio,
            'membresia' => $membresia,
            'ultimosAccesos' => $ultimosAccesos,
            'penalizacionesActivas' => $penalizacionesActivas,
            'reservasPendientes' => $reservasPendientes,
        ]);
    }

    /**
     * Registrar acceso mediante SP con validación anti-duplicado.
     */
    public function registrarAcceso(Request $request)
    {
        $request->validate([
            'carnetSocio' => 'required|integer',
        ]);

        $carnet = $request->carnetSocio;
        $usuarioA = session('usuario')->idUsuario ?? 1;
        $direccionIP = $request->ip();

        $empleado = DB::table('TEmpleados')
            ->where('idUsuario', $usuarioA)
            ->where('estadoA', 1)
            ->first();
        $idSucursal = $empleado->idSucursal ?? 1;

        try {
            $result = DB::select(
                'CALL sp_TControlAccesos_Registrar(?, ?, ?, ?)',
                [$carnet, $idSucursal, $usuarioA, $direccionIP]
            );

            $row = $result[0] ?? null;

            if (!$row) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al procesar el ingreso.',
                ]);
            }

            return response()->json([
                'success' => (bool) $row->success,
                'bloqueo' => (bool) $row->bloqueo,
                'message' => $row->message,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'bloqueo' => true,
                'message' => $this->extraerMensajeError($e, 'Error al registrar acceso.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'bloqueo' => true,
                'message' => 'Error de conexión al registrar acceso.',
            ]);
        }
    }

    /**
     * Bloquear socio manualmente mediante SP.
     */
    public function bloquearSocio(Request $request)
    {
        $request->validate([
            'carnetSocio' => 'required|integer',
        ]);

        $carnet = $request->carnetSocio;
        $usuarioA = session('usuario')->idUsuario ?? 1;
        $direccionIP = $request->ip();

        try {
            $result = DB::select(
                'CALL sp_TSocios_Bloquear(?, ?, ?)',
                [$carnet, $usuarioA, $direccionIP]
            );

            $row = $result[0] ?? null;

            return response()->json([
                'success' => $row && (bool) $row->success,
                'message' => $row->message ?? 'Socio bloqueado correctamente.',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => $this->extraerMensajeError($e, 'Error al bloquear socio.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión al bloquear socio.',
            ]);
        }
    }

    /**
     * Verificar si el socio tiene reservas de clases grupales para hoy.
     */
    public function reservasHoy($carnet)
    {
        $reservas = DB::table('TReservas as r')
            ->join('TClaseGrupales as cg', 'r.idClaseGrupal', '=', 'cg.idClaseGrupal')
            ->join('TActividades as a', 'cg.idActividad', '=', 'a.idActividad')
            ->join('TEmpleados as e', 'cg.carnetEmpleado', '=', 'e.carnetEmpleado')
            ->join('TUsuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->where('r.carnetSocio', $carnet)
            ->where('r.estadoReserva', 'Reservado')
            ->where('r.estadoA', 1)
            ->where('cg.fecha', now()->format('Y-m-d'))
            ->where('cg.estadoClase', 'Programada')
            ->select(
                'r.idReserva',
                'cg.idClaseGrupal',
                'cg.horaInicio',
                'cg.horaFin',
                'cg.cupoMaximo',
                'a.nombreActividad',
                DB::raw("CONCAT(u.nombre1, ' ', u.apellido1) as instructor")
            )
            ->orderBy('cg.horaInicio', 'asc')
            ->get();

        return response()->json($reservas);
    }

    /**
     * Marcar asistencia a clase desde recepción (flujo integrado).
     */
    public function marcarAsistenciaClase(Request $request)
    {
        $request->validate([
            'idReserva' => 'required|integer',
            'carnetSocio' => 'required|integer',
        ]);

        $usuarioA = session('usuario')->idUsuario ?? 1;
        $direccionIP = $request->ip();

        $empleado = DB::table('TEmpleados')
            ->where('idUsuario', $usuarioA)
            ->where('estadoA', 1)
            ->first();
        $idSucursal = $empleado->idSucursal ?? 1;

        try {
            $result = DB::select(
                'CALL sp_TReservas_MarcarAsistencia_Integrado(?, ?, ?, ?, ?)',
                [$request->idReserva, $request->carnetSocio, $idSucursal, $usuarioA, $direccionIP]
            );

            $row = $result[0] ?? null;

            return response()->json([
                'success' => $row && (bool) $row->success,
                'message' => $row->message ?? 'Asistencia registrada.',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => $this->extraerMensajeError($e, 'Error al marcar asistencia.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión.',
            ]);
        }
    }

    private function extraerMensajeError(\Illuminate\Database\QueryException $e, string $default = 'Error de validación.')
    {
        $prev = $e->getPrevious();
        $raw = $prev ? $prev->getMessage() : $e->getMessage();
        if (preg_match('/\d+\s+(.+?)(?:\s*\(Connection:|\s*\(SQL:|\s*$)/s', $raw, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/SQLSTATE\[45000\].*?\[(?:\d+)\]\s*(.*?)(?:\(SQL|$)/i', $e->getMessage(), $m)) {
            return trim($m[1]);
        }
        return $default;
    }
}

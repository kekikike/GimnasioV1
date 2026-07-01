<?php

namespace App\Http\Controllers\Socio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    private function getSocio()
    {
        $usuario = session('usuario');
        return DB::table('TSocios')
            ->where('idUsuario', $usuario->idUsuario)
            ->where('estadoA', 1)
            ->first();
    }

    public function misReservas()
    {
        $socio = $this->getSocio();
        if (!$socio) {
            return response()->json([]);
        }

        $reservas = DB::table('TReservas as r')
            ->join('TClaseGrupales as cg', 'r.idClaseGrupal', '=', 'cg.idClaseGrupal')
            ->join('TActividades as a', 'cg.idActividad', '=', 'a.idActividad')
            ->join('TSucursales as s', 'cg.idSucursal', '=', 's.idSucursal')
            ->join('TEmpleados as e', 'cg.carnetEmpleado', '=', 'e.carnetEmpleado')
            ->join('TUsuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->where('r.carnetSocio', $socio->carnetSocio)
            ->where('r.estadoA', 1)
            ->select(
                'r.idReserva',
                'r.estadoReserva',
                'r.fechaReserva',
                'cg.idClaseGrupal',
                'cg.fecha',
                'cg.horaInicio',
                'cg.horaFin',
                'cg.cupoMaximo',
                'a.nombreActividad',
                's.nombre as sucursal',
                DB::raw("CONCAT(u.nombre1, ' ', u.apellido1) as instructor")
            )
            ->orderBy('cg.fecha', 'desc')
            ->orderBy('cg.horaInicio', 'desc')
            ->get();

        return response()->json($reservas);
    }

    public function disponibles()
    {
        $clases = DB::table('TClaseGrupales as cg')
            ->join('TActividades as a', 'cg.idActividad', '=', 'a.idActividad')
            ->join('TSucursales as s', 'cg.idSucursal', '=', 's.idSucursal')
            ->join('TEmpleados as e', 'cg.carnetEmpleado', '=', 'e.carnetEmpleado')
            ->join('TUsuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->where('cg.estadoA', 1)
            ->where('cg.estadoClase', 'Programada')
            ->where('cg.fecha', '>=', now()->format('Y-m-d'))
            ->select(
                'cg.idClaseGrupal',
                'cg.fecha',
                'cg.horaInicio',
                'cg.horaFin',
                'cg.cupoMaximo',
                'a.nombreActividad',
                'a.descripcionActividad',
                's.nombre as sucursal',
                DB::raw("CONCAT(u.nombre1, ' ', u.apellido1) as instructor")
            )
            ->orderBy('cg.fecha', 'asc')
            ->orderBy('cg.horaInicio', 'asc')
            ->get()
            ->map(function ($clase) {
                $reservados = DB::table('TReservas')
                    ->where('idClaseGrupal', $clase->idClaseGrupal)
                    ->where('estadoReserva', 'Reservado')
                    ->where('estadoA', 1)
                    ->count();
                $clase->cuposDisponibles = $clase->cupoMaximo - $reservados;
                return $clase;
            });

        return response()->json($clases);
    }

    public function reservar(Request $request)
    {
        $request->validate([
            'idClaseGrupal' => 'required|integer',
        ]);

        $socio = $this->getSocio();
        if (!$socio) {
            return response()->json(['success' => false, 'message' => 'Socio no encontrado.'], 404);
        }

        if ($socio->estadoSocio !== 'Activo') {
            return response()->json(['success' => false, 'message' => 'Tu cuenta no está activa. No puedes realizar reservas.']);
        }

        DB::statement(
            'CALL sp_TSocios_VerificarSuspension(?, @_res_enSuspension, @_res_motivo, @_res_strikes)',
            [$socio->carnetSocio]
        );
        $resSuspension = DB::select('SELECT @_res_enSuspension AS enSuspension')[0] ?? null;

        if ($resSuspension && $resSuspension->enSuspension) {
            return response()->json([
                'success' => false,
                'message' => 'Tu acceso está suspendido por strikes acumulados. No puedes realizar reservas.'
            ]);
        }

        $clase = DB::table('TClaseGrupales')
            ->where('idClaseGrupal', $request->idClaseGrupal)
            ->where('estadoA', 1)
            ->where('estadoClase', 'Programada')
            ->first();

        if (!$clase) {
            return response()->json(['success' => false, 'message' => 'Clase no disponible.']);
        }

        $yaReservado = DB::table('TReservas')
            ->where('idClaseGrupal', $request->idClaseGrupal)
            ->where('carnetSocio', $socio->carnetSocio)
            ->where('estadoReserva', 'Reservado')
            ->where('estadoA', 1)
            ->exists();

        if ($yaReservado) {
            return response()->json(['success' => false, 'message' => 'Ya tienes una reserva activa para esta clase.']);
        }

        // Validar que no tenga otra reserva en el mismo horario
        $conflicto = DB::table('TReservas as r')
            ->join('TClaseGrupales as cg', 'r.idClaseGrupal', '=', 'cg.idClaseGrupal')
            ->join('TActividades as a', 'cg.idActividad', '=', 'a.idActividad')
            ->where('r.carnetSocio', $socio->carnetSocio)
            ->where('r.estadoReserva', 'Reservado')
            ->where('r.estadoA', 1)
            ->where('cg.fecha', $clase->fecha)
            ->where('cg.estadoClase', 'Programada')
            ->where(function ($q) use ($clase) {
                $q->whereRaw('? < cg.horaFin', [$clase->horaInicio])
                  ->whereRaw('? > cg.horaInicio', [$clase->horaFin]);
            })
            ->select('cg.horaInicio', 'cg.horaFin', 'a.nombreActividad')
            ->first();

        if ($conflicto) {
            return response()->json([
                'success' => false,
                'message' => "Ya tienes una reserva en la clase \"{$conflicto->nombreActividad}\" de {$conflicto->horaInicio} a {$conflicto->horaFin} que coincide con este horario.",
            ]);
        }

        $reservados = DB::table('TReservas')
            ->where('idClaseGrupal', $request->idClaseGrupal)
            ->where('estadoReserva', 'Reservado')
            ->where('estadoA', 1)
            ->count();

        if ($reservados >= $clase->cupoMaximo) {
            return response()->json(['success' => false, 'message' => 'La clase está llena. No hay cupos disponibles.']);
        }

        DB::table('TReservas')->insert([
            'idClaseGrupal' => $request->idClaseGrupal,
            'carnetSocio' => $socio->carnetSocio,
            'fechaReserva' => now()->format('Y-m-d H:i:s'),
            'estadoReserva' => 'Reservado',
            'usuarioA' => session('usuario')->idUsuario,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reserva confirmada correctamente.',
        ]);
    }

    public function cancelar(Request $request)
    {
        $request->validate([
            'idReserva' => 'required|integer',
        ]);

        $socio = $this->getSocio();
        if (!$socio) {
            return response()->json(['success' => false, 'message' => 'Socio no encontrado.'], 404);
        }

        $usuarioA = session('usuario')->idUsuario;
        $direccionIP = $request->ip();

        try {
            $result = DB::select(
                'CALL sp_TReservas_Cancelar_Validated(?, ?, ?, ?)',
                [$request->idReserva, $socio->carnetSocio, $usuarioA, $direccionIP]
            );

            $row = $result[0] ?? null;

            return response()->json([
                'success' => $row && (bool) $row->success,
                'message' => $row->message ?? 'Reserva cancelada.',
                'penalizado' => $row->penalizado ?? false,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => $this->extraerMensajeError($e, 'Error al cancelar la reserva.'),
                'penalizado' => false,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de conexión.',
                'penalizado' => false,
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

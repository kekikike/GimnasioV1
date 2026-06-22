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

        if ($socio->strikes >= 3) {
            $penalizacion = DB::table('TPenalizaciones')
                ->where('carnetSocio', $socio->carnetSocio)
                ->where('estado', 1)
                ->where('estadoA', 1)
                ->where('fecha', '>=', now()->subDays(7)->format('Y-m-d'))
                ->exists();

            if ($penalizacion) {
                return response()->json(['success' => false, 'message' => 'Has acumulado 3 strikes. Tu acceso está suspendido por 1 semana.']);
            }
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

        $reserva = DB::table('TReservas as r')
            ->join('TClaseGrupales as cg', 'r.idClaseGrupal', '=', 'cg.idClaseGrupal')
            ->where('r.idReserva', $request->idReserva)
            ->where('r.carnetSocio', $socio->carnetSocio)
            ->where('r.estadoReserva', 'Reservado')
            ->where('r.estadoA', 1)
            ->select('r.*', 'cg.fecha', 'cg.horaInicio')
            ->first();

        if (!$reserva) {
            return response()->json(['success' => false, 'message' => 'Reserva no encontrada o ya cancelada.']);
        }

        $inicioClase = "{$reserva->fecha} {$reserva->horaInicio}";
        $horasRestantes = now()->diffInHours($inicioClase, false);

        $estadoFinal = 'Cancelado';
        $mensaje = 'Reserva cancelada correctamente.';

        if ($horasRestantes < 2 && $horasRestantes >= 0) {
            $estadoFinal = 'Penalizado';
            $mensaje = 'Cancelación fuera del tiempo permitido (mín. 2h antes). Se aplicó un strike.';

            $idUsuario = session('usuario')->idUsuario;

            DB::table('TPenalizaciones')->insert([
                'carnetSocio' => $socio->carnetSocio,
                'idReserva' => $request->idReserva,
                'fecha' => now()->format('Y-m-d'),
                'estado' => true,
                'usuarioA' => $idUsuario,
            ]);

            DB::table('TSocios')
                ->where('carnetSocio', $socio->carnetSocio)
                ->increment('strikes');

            $socioActualizado = DB::table('TSocios')
                ->where('carnetSocio', $socio->carnetSocio)
                ->first();

            if ($socioActualizado && $socioActualizado->strikes >= 3) {
                DB::table('TPenalizaciones')->insert([
                    'carnetSocio' => $socio->carnetSocio,
                    'idReserva' => null,
                    'fecha' => now()->format('Y-m-d'),
                    'estado' => true,
                    'usuarioA' => $idUsuario,
                ]);
                $mensaje .= ' Has acumulado 3 strikes. Acceso suspendido por 1 semana.';
            }
        }

        DB::table('TReservas')
            ->where('idReserva', $request->idReserva)
            ->update(['estadoReserva' => $estadoFinal]);

        return response()->json([
            'success' => true,
            'message' => $mensaje,
            'penalizado' => $estadoFinal === 'Penalizado',
        ]);
    }
}

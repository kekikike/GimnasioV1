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

    public function registrarAcceso(Request $request)
    {
        $request->validate([
            'carnetSocio' => 'required|integer',
        ]);

        $carnet = $request->carnetSocio;
        $usuarioA = session('usuario')->idUsuario ?? 1;

        $socio = DB::table('TSocios')
            ->where('carnetSocio', $carnet)
            ->where('estadoA', 1)
            ->first();

        if (!$socio) {
            return response()->json([
                'success' => false,
                'message' => 'Socio no encontrado.',
            ]);
        }

        $bloqueo = false;
        $motivo = null;

        if ($socio->estadoSocio !== 'Activo') {
            $bloqueo = true;
            $motivo = 'Socio con estado: ' . $socio->estadoSocio;
        }

        if ($socio->strikes >= 3) {
            $penalizacionReciente = DB::table('TPenalizaciones')
                ->where('carnetSocio', $carnet)
                ->where('estado', 1)
                ->where('estadoA', 1)
                ->where('fecha', '>=', now()->subDays(7)->format('Y-m-d'))
                ->first();

            if ($penalizacionReciente) {
                $bloqueo = true;
                $motivo = 'Acceso suspendido por acumular 3 strikes. Penalización vigente hasta: ' . now()->addDays(7)->format('Y-m-d');
            }
        }

        $membresia = DB::table('TMembresias')
            ->where('carnetSocio', $carnet)
            ->where('estadoA', 1)
            ->orderBy('idMembresia', 'desc')
            ->first();

        $membresiaValida = $membresia
            && $membresia->estadoMembresia === 'Activa'
            && $membresia->fechaInicioMembresia <= now()->format('Y-m-d')
            && $membresia->fechaFinMembresia >= now()->format('Y-m-d');

        if (!$membresiaValida) {
            $bloqueo = true;
            $motivo = $motivo ? $motivo . '. ' : '';
            $motivo .= 'Membresía no vigente o vencida.';
        }

        $empleado = DB::table('TEmpleados')
            ->where('idUsuario', $usuarioA)
            ->where('estadoA', 1)
            ->first();
        $idSucursal = $empleado->idSucursal ?? 1;

        DB::table('TControlAccesos')->insert([
            'carnetSocio' => $carnet,
            'idSucursal' => $idSucursal,
            'fechaAcceso' => now()->format('Y-m-d'),
            'horaAcceso' => now()->format('H:i:s'),
            'bloqueo' => $bloqueo,
            'motivoDenegacion' => $motivo,
            'usuarioA' => $usuarioA,
        ]);

        return response()->json([
            'success' => true,
            'bloqueo' => $bloqueo,
            'message' => $bloqueo
                ? 'ACCESO DENEGADO: ' . $motivo
                : 'Ingreso registrado correctamente.',
        ]);
    }
}

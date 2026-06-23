<?php

namespace App\Http\Controllers\Recepcionista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecepcionistaController extends Controller
{
    private function getUsuarioSesion(): ?object
    {
        return session('usuario');
    }

    private function getUsuarioA(): int
    {
        $usuario = $this->getUsuarioSesion();
        return $usuario->idUsuario ?? 1;
    }

    private function getEmpleado()
    {
        $usuarioId = $this->getUsuarioA();
        return DB::table('TEmpleados')->where('idUsuario', $usuarioId)->first();
    }

    public function dashboard()
    {
        return view('recepcionista.dashboard');
    }

    public function caja()
    {
        $metodosPago = DB::select('CALL sp_TMetodoPagos_Select()');
        $empleado = $this->getEmpleado();
        $sucursalNombre = '';
        if ($empleado && $empleado->idSucursal) {
            $suc = DB::table('TSucursales')->where('idSucursal', $empleado->idSucursal)->first();
            $sucursalNombre = $suc ? $suc->nombre : '';
        }
        return view('recepcionista.caja', compact('metodosPago', 'sucursalNombre'));
    }

    public function socios()
    {
        return view('recepcionista.socios');
    }

    public function estado()
    {
        $today = date('Y-m-d');
        $cajaHoy = DB::table('TCajas')
            ->where('fechaApertura', $today)
            ->where('estadoA', 1)
            ->first();

        if (!$cajaHoy) {
            return response()->json(['open' => false, 'today' => $today]);
        }

        return response()->json([
            'open' => $cajaHoy->estadoCaja === 'Abierta',
            'caja' => $cajaHoy,
        ]);
    }

    public function abrir(Request $request)
    {
        $request->validate([
            'montoApertura' => 'required|numeric|min:0',
        ]);

        $today = date('Y-m-d');
        $cajaHoy = DB::table('TCajas')
            ->where('fechaApertura', $today)
            ->where('estadoA', 1)
            ->first();

        if ($cajaHoy) {
            return response()->json(['success' => false, 'message' => 'Ya existe una caja registrada para el dia de hoy (abierta o cerrada). Solo se puede abrir/cerrar una vez por dia.'], 422);
        }

        $empleado = $this->getEmpleado();
        if (!$empleado || !$empleado->idSucursal) {
            return response()->json(['success' => false, 'message' => 'El usuario no esta asociado a un empleado con sucursal valida.'], 422);
        }

        $usuarioA = $this->getUsuarioA();
        $horaApertura = date('H:i:s');

        try {
            $idCaja = DB::table('TCajas')->insertGetId([
                'idSucursal' => $empleado->idSucursal,
                'carnetEmpleado' => $empleado->carnetEmpleado,
                'fechaApertura' => $today,
                'horaApertura' => $horaApertura,
                'montoApertura' => $request->montoApertura,
                'montoCierre' => null,
                'montoCierreCalculado' => null,
                'diferenciaArqueo' => null,
                'estadoCaja' => 'Abierta',
                'estadoA' => 1,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al abrir la caja: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'Caja abierta correctamente.', 'idCaja' => $idCaja]);
    }

    public function cerrar(Request $request, int $id)
    {
        $request->validate([
            'montoCierre' => 'required|numeric|min:0',
        ]);

        $caja = DB::table('TCajas')->where('idCaja', $id)->where('estadoA', 1)->first();
        if (!$caja) {
            return response()->json(['success' => false, 'message' => 'Caja no encontrada.'], 404);
        }
        if ($caja->estadoCaja !== 'Abierta') {
            return response()->json(['success' => false, 'message' => 'La caja ya fue cerrada.'], 422);
        }

        $today = date('Y-m-d');
        $totalRecibos = DB::table('TRecibos')
            ->where('idCaja', $id)
            ->whereDate('fechaPago', $today)
            ->where('estadoA', 1)
            ->sum('montoTotal') ?? 0;

        $totalMantenimientos = DB::table('TMantenimientoPreventivos')
            ->where('fechaRealizada', $today)
            ->where('estadoMantenimiento', 'Realizado')
            ->sum('costoMantenimiento') ?? 0;

        $montoCierreCalculado = $caja->montoApertura + $totalRecibos - $totalMantenimientos;
        $diferenciaArqueo = $request->montoCierre - $montoCierreCalculado;
        $usuarioA = $this->getUsuarioA();

        try {
            DB::table('TCajas')->where('idCaja', $id)->update([
                'montoCierre' => $request->montoCierre,
                'montoCierreCalculado' => $montoCierreCalculado,
                'diferenciaArqueo' => $diferenciaArqueo,
                'estadoCaja' => 'Cerrada',
                'estadoA' => 1,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cerrar la caja: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Caja cerrada correctamente.',
            'montoCierreCalculado' => $montoCierreCalculado,
            'totalRecibos' => $totalRecibos,
            'totalMantenimientos' => $totalMantenimientos,
        ]);
    }

    public function movimientos(Request $request)
    {
        $today = date('Y-m-d');
        $cajaHoy = DB::table('TCajas')
            ->where('fechaApertura', $today)
            ->where('estadoA', 1)
            ->first();

        if (!$cajaHoy) {
            return response()->json(['movimientos' => []]);
        }

        $movimientos = DB::table('TRecibos as r')
            ->join('TCajas as c', 'c.idCaja', '=', 'r.idCaja')
            ->join('TSucursales as s', 's.idSucursal', '=', 'c.idSucursal')
            ->join('TMembresias as m', 'm.idMembresia', '=', 'r.idMembresia')
            ->join('TSocios as so', 'so.carnetSocio', '=', 'm.carnetSocio')
            ->join('TUsuarios as u', 'u.idUsuario', '=', 'so.idUsuario')
            ->where('r.estadoA', 1)
            ->where('r.idCaja', $cajaHoy->idCaja)
            ->select(
                'r.idRecibo', 'r.montoTotal', 'r.fechaPago', 'r.estadoRecibo',
                'c.idCaja', 's.nombre as sucursal',
                'u.nombre1', 'u.apellido1',
                'm.carnetSocio',
                DB::raw('(SELECT GROUP_CONCAT(mp.nombreMetodoPago SEPARATOR ", ") FROM TDetalleMetodoPagos dmp JOIN TMetodoPagos mp ON mp.idMetodoPago = dmp.idMetodoPagoFK WHERE dmp.idRecibo = r.idRecibo) as metodos_pago')
            )
            ->orderBy('r.fechaPago', 'desc')
            ->get();

        $totalMantenimientos = DB::table('TMantenimientoPreventivos')
            ->where('fechaRealizada', $today)
            ->where('estadoMantenimiento', 'Realizado')
            ->sum('costoMantenimiento') ?? 0;

        return response()->json(['movimientos' => $movimientos, 'caja' => $cajaHoy, 'totalMantenimientosHoy' => $totalMantenimientos]);
    }

    public function buscarSocio($carnet)
    {
        try {
            $socio = DB::table('TSocios as s')
                ->join('TUsuarios as u', 's.idUsuario', '=', 'u.idUsuario')
                ->where('s.carnetSocio', $carnet)
                ->where('s.estadoA', 1)
                ->select('s.carnetSocio', 's.estadoSocio', 'u.nombre1', 'u.nombre2', 'u.apellido1', 'u.apellido2')
                ->first();

            if (!$socio) {
                return response()->json(['success' => false, 'message' => 'Socio no encontrado.'], 404);
            }

            $membresiaActiva = DB::table('TMembresias')
                ->where('carnetSocio', $carnet)
                ->where('estadoA', 1)
                ->where('estadoMembresia', 'Activa')
                ->whereDate('fechaFinMembresia', '>=', date('Y-m-d'))
                ->exists();

            return response()->json([
                'success' => true,
                'socio' => $socio,
                'tieneMembresiaActiva' => $membresiaActiva,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al buscar socio.'], 500);
        }
    }

    public function planes()
    {
        $planes = DB::table('TPlanes')
            ->where('estadoA', 1)
            ->select('idPlan', 'nombrePlan', 'costoPlan', 'duracionDias')
            ->get();
        return response()->json($planes);
    }

    public function crearRecibo(Request $request)
    {
        $request->validate([
            'carnetSocio' => 'required|integer|exists:TSocios,carnetSocio',
            'idPlan' => 'required|integer|exists:TPlanes,idPlan',
            'montoTotal' => 'required|numeric|min:0',
            'metodos' => 'required|array|min:1',
            'metodos.*.idMetodoPago' => 'required|integer|exists:TMetodoPagos,idMetodoPago',
            'metodos.*.monto' => 'required|numeric|min:0',
        ]);

        $carnet = $request->carnetSocio;
        $today = date('Y-m-d');
        $usuarioA = $this->getUsuarioA();

        $tieneActiva = DB::table('TMembresias')
            ->where('carnetSocio', $carnet)
            ->where('estadoA', 1)
            ->where('estadoMembresia', 'Activa')
            ->whereDate('fechaFinMembresia', '>=', $today)
            ->exists();

        if ($tieneActiva) {
            return response()->json(['success' => false, 'message' => 'El socio ya tiene una membresia activa. Debe esperar a que venza para comprar una nueva.'], 422);
        }

        $sumaMetodos = collect($request->metodos)->sum('monto');
        if (abs($sumaMetodos - $request->montoTotal) > 0.01) {
            return response()->json(['success' => false, 'message' => 'La suma de los montos de los metodos de pago no coincide con el monto total.'], 422);
        }

        $plan = DB::table('TPlanes')->where('idPlan', $request->idPlan)->first();
        if (!$plan) {
            return response()->json(['success' => false, 'message' => 'Plan no encontrado.'], 404);
        }

        $empleado = $this->getEmpleado();
        $idSucursal = $empleado->idSucursal ?? 1;

        $cajaAbierta = DB::table('TCajas')
            ->where('fechaApertura', $today)
            ->where('estadoCaja', 'Abierta')
            ->where('estadoA', 1)
            ->first();

        if (!$cajaAbierta) {
            return response()->json(['success' => false, 'message' => 'No hay una caja abierta para hoy.'], 422);
        }

        DB::beginTransaction();
        try {
            $idMembresia = DB::table('TMembresias')->insertGetId([
                'idPlan' => $request->idPlan,
                'carnetSocio' => $carnet,
                'idSucursal' => $idSucursal,
                'fechaInicioMembresia' => $today,
                'fechaFinMembresia' => date('Y-m-d', strtotime("$today +{$plan->duracionDias} days")),
                'estadoMembresia' => 'Activa',
                'estadoA' => 1,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);

            $socio = DB::table('TSocios')->where('carnetSocio', $carnet)->first();
            if ($socio && $socio->estadoSocio !== 'Activo') {
                DB::table('TSocios')->where('carnetSocio', $carnet)->update([
                    'estadoSocio' => 'Activo',
                    'fechaA' => now(),
                    'usuarioA' => $usuarioA,
                ]);
            }

            $idRecibo = DB::table('TRecibos')->insertGetId([
                'idCaja' => $cajaAbierta->idCaja,
                'idMembresia' => $idMembresia,
                'montoTotal' => $request->montoTotal,
                'fechaPago' => now(),
                'estadoRecibo' => 'Emitido',
                'estadoA' => 1,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);

            foreach ($request->metodos as $metodo) {
                DB::table('TDetalleMetodoPagos')->insert([
                    'idRecibo' => $idRecibo,
                    'idMetodoPagoFK' => $metodo['idMetodoPago'],
                    'monto' => $metodo['monto'],
                    'estadoA' => 1,
                    'fechaA' => now(),
                    'usuarioA' => $usuarioA,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Recibo #' . $idRecibo . ' registrado correctamente.',
                'idRecibo' => $idRecibo,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar el recibo: ' . $e->getMessage()], 500);
        }
    }

    public function mostrarRecibo(int $id)
    {
        $recibo = DB::table('TRecibos as r')
            ->join('TCajas as c', 'c.idCaja', '=', 'r.idCaja')
            ->join('TSucursales as s', 's.idSucursal', '=', 'c.idSucursal')
            ->join('TMembresias as m', 'm.idMembresia', '=', 'r.idMembresia')
            ->join('TSocios as so', 'so.carnetSocio', '=', 'm.carnetSocio')
            ->join('TUsuarios as u', 'u.idUsuario', '=', 'so.idUsuario')
            ->where('r.idRecibo', $id)
            ->select(
                'r.idRecibo', 'r.montoTotal', 'r.fechaPago', 'r.estadoRecibo',
                'c.idCaja', 'c.idSucursal', 's.nombre as sucursal',
                'u.nombre1', 'u.apellido1',
                'm.carnetSocio', 'm.idMembresia'
            )
            ->first();

        if (!$recibo) {
            return response()->json(['success' => false, 'message' => 'Recibo no encontrado.'], 404);
        }

        $metodos = DB::table('TDetalleMetodoPagos as dmp')
            ->join('TMetodoPagos as mp', 'mp.idMetodoPago', '=', 'dmp.idMetodoPagoFK')
            ->where('dmp.idRecibo', $id)
            ->select('mp.nombreMetodoPago', 'dmp.monto')
            ->get();

        return response()->json([
            'success' => true,
            'recibo' => $recibo,
            'metodos' => $metodos,
        ]);
    }
}

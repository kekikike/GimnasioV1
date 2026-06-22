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

    public function dashboard()
    {
        return view('recepcionista.dashboard');
    }

    public function caja()
    {
        $metodosPago = DB::select('CALL sp_TMetodoPagos_Select()');
        $sucursales  = DB::select('CALL sp_TSucursales_Select()');
        $membresias  = DB::select('CALL sp_TMembresias_Select()');

        return view('recepcionista.caja', compact('metodosPago', 'sucursales', 'membresias'));
    }

    public function socios()
    {
        return view('recepcionista.socios');
    }

    // Métodos para caja - con restricción de una sola vez por día
    public function estado()
    {
        $today = date('Y-m-d');
        $testMode = config('app.caja_test_mode', true);
        $cajaHoy = DB::table('TCajas')
            ->where('fechaApertura', $today)
            ->where('estadoCaja', 'Abierta')
            ->where('estadoA', 1)
            ->first();

        if (!$cajaHoy) {
            return response()->json(['open' => false, 'today' => $today]);
        }

        return response()->json([
            'open' => true,
            'caja' => $cajaHoy,
        ]);
    }

    public function abrir(Request $request)
    {
        $request->validate([
            'idSucursal' => 'required|integer|exists:TSucursales,idSucursal',
            'montoApertura' => 'required|numeric|min:0',
        ]);

        $today = date('Y-m-d');
        $testMode = config('app.caja_test_mode', true);
        
        // Verificar si ya existe una caja abierta hoy
        $cajaAbierta = DB::table('TCajas')
            ->where('fechaApertura', $today)
            ->where('estadoCaja', 'Abierta')
            ->where('estadoA', 1)
            ->first();

        if ($cajaAbierta) {
            return response()->json(['success' => false, 'message' => 'Ya existe una caja abierta para el día de hoy. Solo puede abrir caja una vez.'], 422);
        }

        // Si NO estamos en modo pruebas, mantener la verificación de caja cerrada previa
        if (!$testMode) {
            $cajaCerrada = DB::table('TCajas')
                ->where('fechaApertura', $today)
                ->where('estadoCaja', 'Cerrada')
                ->where('estadoA', 1)
                ->first();

            if ($cajaCerrada) {
                return response()->json(['success' => false, 'message' => 'La caja ya fue cerrada hoy. Solo puede abrir y cerrar caja una vez por día.'], 422);
            }
        }

        $usuario = $this->getUsuarioSesion();
        $usuarioId = $usuario->idUsuario ?? $this->getUsuarioA();
        
        // Buscar el carnetEmpleado asociado al usuario actual
        $empleado = DB::table('TEmpleados')
            ->where('idUsuario', $usuarioId)
            ->first(['carnetEmpleado']);
        
        if (!$empleado) {
            if ($testMode) {
                // En modo pruebas, usar idUsuario como carnetEmpleado si no hay empleado asociado
                $carnetEmpleado = $usuarioId;
            } else {
                return response()->json(['success' => false, 'message' => 'Usuario no asociado a un empleado válido.'], 422);
            }
        } else {
            $carnetEmpleado = $empleado->carnetEmpleado;
        }
        $usuarioA = $this->getUsuarioA();
        $horaApertura = date('H:i:s');

        try {
            $idCaja = DB::table('TCajas')->insertGetId([
                'idSucursal' => $request->idSucursal,
                'carnetEmpleado' => $carnetEmpleado,
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
            'montoCierreCalculado' => 'required|numeric|min:0',
        ]);

        $caja = DB::table('TCajas')->where('idCaja', $id)->where('estadoA', 1)->first();
        if (!$caja) {
            return response()->json(['success' => false, 'message' => 'Caja no encontrada.'], 404);
        }

        if ($caja->estadoCaja !== 'Abierta') {
            return response()->json(['success' => false, 'message' => 'La caja ya fue cerrada o no está en estado abierto.'], 422);
        }

        $diferenciaArqueo = $request->montoCierre - $request->montoCierreCalculado;
        $usuarioA = $this->getUsuarioA();

        try {
            DB::table('TCajas')->where('idCaja', $id)->update([
                'montoCierre' => $request->montoCierre,
                'montoCierreCalculado' => $request->montoCierreCalculado,
                'diferenciaArqueo' => $diferenciaArqueo,
                'estadoCaja' => 'Cerrada',
                'estadoA' => 1,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cerrar la caja: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'Caja cerrada correctamente.']);
    }

    public function movimientos(Request $request)
    {
        $today = date('Y-m-d');
        $cajaHoy = DB::table('TCajas')
            ->where('fechaApertura', $today)
            ->where('estadoA', 1)
            ->orderByDesc('idCaja')
            ->first();

        if (!$cajaHoy) {
            return response()->json(['movimientos' => []]);
        }

                $movimientos = DB::select(
                        'SELECT r.idRecibo, r.nroRecibo, r.montoTotal, r.fechaPago, r.estadoRecibo,
                                        c.idCaja, c.idSucursal, s.nombre AS sucursal,
                                        e.carnetEmpleado AS cajaCarnet, u.nombre1, u.apellido1,
                                        mp.nombreMetodoPago, dmp.monto AS montoMetodo,
                                        m.carnetSocio
                         FROM TRecibos r
                         INNER JOIN TCajas c ON c.idCaja = r.idCaja
                         INNER JOIN TSucursales s ON s.idSucursal = c.idSucursal
                         INNER JOIN TDetalleMetodoPagos dmp ON dmp.idRecibo = r.idRecibo
                         INNER JOIN TMetodoPagos mp ON mp.idMetodoPago = dmp.idMetodoPagoFK
                         INNER JOIN TMembresias m ON m.idMembresia = r.idMembresia
                         LEFT JOIN TEmpleados e ON e.carnetEmpleado = c.carnetEmpleado
                         LEFT JOIN TUsuarios u ON u.idUsuario = e.idUsuario
                         WHERE r.estadoA = 1
                             AND r.idCaja = ?
                         ORDER BY r.fechaPago DESC',
                        [$cajaHoy->idCaja]
                );

        return response()->json(['movimientos' => $movimientos, 'caja' => $cajaHoy]);
    }

    public function crearRecibo(Request $request)
    {
        $request->validate([
            'idCaja' => 'required|integer|exists:TCajas,idCaja',
            'idMembresia' => 'required|integer|exists:TMembresias,idMembresia',
            'idMetodoPago' => 'required|integer|exists:TMetodoPagos,idMetodoPago',
            'nroRecibo' => 'required|string|max:50',
            'montoTotal' => 'required|numeric|min:0',
            'fechaPago' => 'required|date',
            'montoMetodo' => 'required|numeric|min:0',
        ]);

        $usuarioA = $this->getUsuarioA();

        DB::beginTransaction();
        try {
            $idRecibo = DB::table('TRecibos')->insertGetId([
                'idCaja' => $request->idCaja,
                'idMembresia' => $request->idMembresia,
                'nroRecibo' => $request->nroRecibo,
                'montoTotal' => $request->montoTotal,
                'fechaPago' => $request->fechaPago,
                'estadoRecibo' => 'Emitido',
                'estadoA' => 1,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);

            DB::table('TDetalleMetodoPagos')->insert([
                'idRecibo' => $idRecibo,
                'idMetodoPagoFK' => $request->idMetodoPago,
                'monto' => $request->montoMetodo,
                'estadoA' => 1,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Recibo registrado correctamente.', 'idRecibo' => $idRecibo]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar el recibo: ' . $e->getMessage()], 500);
        }
    }

    public function mostrarRecibo(int $id)
    {
        $recibo = DB::select(
            'SELECT r.idRecibo, r.nroRecibo, r.montoTotal, r.fechaPago, r.estadoRecibo,
                    c.idCaja, s.nombre AS sucursal,
                    u.nombre1, u.apellido1,
                    mp.nombreMetodoPago, dmp.monto AS montoMetodo,
                    m.carnetSocio, m.idMembresia
             FROM TRecibos r
             INNER JOIN TCajas c ON c.idCaja = r.idCaja
             INNER JOIN TSucursales s ON s.idSucursal = c.idSucursal
             INNER JOIN TDetalleMetodoPagos dmp ON dmp.idRecibo = r.idRecibo
             INNER JOIN TMetodoPagos mp ON mp.idMetodoPago = dmp.idMetodoPagoFK
             INNER JOIN TMembresias m ON m.idMembresia = r.idMembresia
             LEFT JOIN TUsuarios u ON u.idUsuario = c.carnetEmpleado
             WHERE r.estadoA = 1
               AND r.idRecibo = ?
             LIMIT 1',
            [$id]
        );

        if (empty($recibo)) {
            return response()->json(['success' => false, 'message' => 'Recibo no encontrado.'], 404);
        }

        return response()->json(['success' => true, 'recibo' => $recibo[0]]);
    }
}

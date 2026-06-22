<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index()
    {
        $metodosPago = DB::select('CALL sp_TMetodoPagos_Select()');
        $sucursales  = DB::select('CALL sp_TSucursales_Select()');
        $empleados   = DB::select('CALL sp_TUsuarios_GetCajeros()');

        return view('admin.reportes', compact('metodosPago', 'sucursales', 'empleados'));
    }

    public function reporteFinanciero(Request $request)
    {
        $ingresos = DB::select('CALL sp_TRecibos_GetReporteFinanciero(?, ?, ?, ?, ?)', [
            $request->filled('fecha_desde') ? $request->fecha_desde : null,
            $request->filled('fecha_hasta') ? $request->fecha_hasta : null,
            $request->filled('idSucursal') ? $request->idSucursal : null,
            $request->filled('idMetodoPago') ? $request->idMetodoPago : null,
            $request->filled('carnetEmpleado') ? $request->carnetEmpleado : null,
        ]);
        $totalGeneral = array_sum(array_column($ingresos, 'montoTotal'));

        return response()->json(compact('ingresos', 'totalGeneral'));
    }

    public function reporteEquipos()
    {
        $operativos             = DB::select('CALL sp_TEquipamientos_GetByEstado(?)', ['Operativo']);
        $enMantenimiento        = DB::select('CALL sp_TEquipamientos_GetByEstado(?)', ['En Mantenimiento']);
        $fueraServicio          = DB::select('CALL sp_TEquipamientos_GetByEstado(?)', ['Fuera de Servicio']);
        $historialFallas        = DB::select('CALL sp_TReporteFallas_GetHistorial(?)', [50]);
        $historialMantenimientos = DB::select('CALL sp_TMantenimientoPreventivos_GetHistorial(?)', [50]);

        return response()->json(compact(
            'operativos', 'enMantenimiento', 'fueraServicio',
            'historialFallas', 'historialMantenimientos'
        ));
    }

    public function reporteMembresias(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', date('Y-m-d', strtotime('-6 months')));
        $fechaFin = $request->get('fecha_fin', date('Y-m-d'));

        $membresias = DB::table('TMembresias as m')
            ->join('TPlanes as p', 'm.idPlan', '=', 'p.idPlan')
            ->select(
                'p.idPlan',
                'p.nombrePlan',
                'p.costoPlan',
                DB::raw('COUNT(*) as total_vendidas'),
                DB::raw('SUM(p.costoPlan) as ingresos_totales')
            )
            ->whereBetween('m.fechaInicioMembresia', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->groupBy('p.idPlan', 'p.nombrePlan', 'p.costoPlan')
            ->orderBy('total_vendidas', 'desc')
            ->get();

        $totalGeneral = $membresias->sum('ingresos_totales');
        $totalMembresias = $membresias->sum('total_vendidas');

        return response()->json(compact('membresias', 'totalGeneral', 'totalMembresias', 'fechaInicio', 'fechaFin'));
    }

    public function reporteRenovaciones(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', date('Y-m-d', strtotime('-6 months')));
        $fechaFin = $request->get('fecha_fin', date('Y-m-d'));

        $renovaciones = DB::table('TMembresias as m')
            ->join('TSocios as s', 'm.carnetSocio', '=', 's.carnetSocio')
            ->join('TUsuarios as u', 's.idUsuario', '=', 'u.idUsuario')
            ->join('TPlanes as p', 'm.idPlan', '=', 'p.idPlan')
            ->select(
                's.carnetSocio',
                DB::raw("CONCAT(u.nombre1, ' ', u.apellido1) as nombre_socio"),
                'p.nombrePlan',
                'm.fechaInicioMembresia',
                'm.fechaFinMembresia',
                'm.estadoMembresia',
                DB::raw('(SELECT COUNT(*) FROM TMembresias m2 WHERE m2.carnetSocio = m.carnetSocio AND m2.idMembresia < m.idMembresia) + 1 as num_membresia')
            )
            ->where(function($q) {
                $q->whereExists(function($sub) {
                    $sub->select(DB::raw(1))
                        ->from('TMembresias as m3')
                        ->whereColumn('m3.carnetSocio', 'm.carnetSocio')
                        ->whereColumn('m3.idMembresia', '<', 'm.idMembresia');
                });
            })
            ->whereBetween('m.fechaInicioMembresia', [$fechaInicio, $fechaFin . ' 23:59:59'])
            ->orderBy('m.carnetSocio')
            ->orderBy('m.fechaInicioMembresia')
            ->get();

        $totalRenovaciones = $renovaciones->count();
        $sociosUnicos = $renovaciones->unique('carnetSocio')->count();

        return response()->json(compact('renovaciones', 'totalRenovaciones', 'sociosUnicos', 'fechaInicio', 'fechaFin'));
    }
}

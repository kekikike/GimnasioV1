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
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Eloquent\Socio;
use App\Models\Membresia;
use App\Models\Asistencia;
use App\Models\Pago;
use App\Models\Clase;
use App\Models\Equipo;
use App\Models\Incidencia;
use App\Models\Reserva;
use App\Models\Plan;
use App\Models\Eloquent\Usuario;
use App\Models\Eloquent\Empleado;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    // =========================================
    // VISTA PRINCIPAL DE REPORTES
    // =========================================
    public function index()
    {
        return response()->json(['message' => 'Reporte API - use /reportes/{socios|financiero|asistencia|clases|equipamiento}?json=1']);
    }

    // =========================================
    // REPORTE DE SOCIOS (RF-44)
    // =========================================
    public function socios(Request $request)
    {
        $estado = $request->get('estado', 'todos');
        $nombre = $request->get('nombre', '');

        $query = Socio::with(['usuario', 'membresia']);

        if ($estado == 'activos') {
            $query->whereHas('membresia', function($q) {
                $q->where('estadoMembresia', 'Activa');
            });
        } elseif ($estado == 'inactivos') {
            $query->whereDoesntHave('membresia', function($q) {
                $q->where('estadoMembresia', 'Activa');
            });
        }

        if ($nombre !== '') {
            $query->where(function($q) use ($nombre) {
                $q->where('carnetSocio', 'like', "%{$nombre}%")
                  ->orWhereHas('usuario', function($uq) use ($nombre) {
                      $uq->where('nombre1', 'like', "%{$nombre}%")
                         ->orWhere('nombre2', 'like', "%{$nombre}%")
                         ->orWhere('apellido1', 'like', "%{$nombre}%")
                         ->orWhere('apellido2', 'like', "%{$nombre}%")
                         ->orWhere('correo', 'like', "%{$nombre}%")
                         ->orWhere('telefono', 'like', "%{$nombre}%");
                  });
            });
        }

        $socios = $query->get();

        $totalSocios = $socios->count();
        $conMembresia = $socios->filter(function($s) {
            return $s->membresia && $s->membresia->estadoMembresia == 'Activa';
        })->count();
        $sinMembresia = $totalSocios - $conMembresia;

        return response()->json(compact('socios', 'totalSocios', 'conMembresia', 'sinMembresia', 'estado', 'nombre'));
    }

    // =========================================
    // DETALLE DE SOCIO (membresías, clases)
    // =========================================
    public function detalle($carnet)
    {
        $socio = Socio::with(['usuario', 'membresia.plan'])
            ->where('carnetSocio', $carnet)
            ->firstOrFail();

        $membresias = \App\Models\Membresia::with('plan')
            ->where('carnetSocio', $carnet)
            ->orderBy('fechaInicioMembresia', 'desc')
            ->get();

        $clasesPasadas = Reserva::with(['clase.actividad'])
            ->where('carnetSocio', $carnet)
            ->where('estadoReserva', 'Asistido')
            ->orderBy('fechaReserva', 'desc')
            ->get();

        $clasesFuturas = Reserva::with(['clase.actividad'])
            ->where('carnetSocio', $carnet)
            ->where('fechaReserva', '>=', Carbon::now())
            ->whereIn('estadoReserva', ['Reservado'])
            ->orderBy('fechaReserva', 'asc')
            ->get();

        return response()->json(compact('socio', 'membresias', 'clasesPasadas', 'clasesFuturas'));
    }

    // =========================================
    // REPORTE FINANCIERO (RF-45)
    // =========================================
    public function financiero(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->format('Y-m-d'));
        $estadoCaja = $request->get('estado_caja');
        $idSucursal = $request->get('id_sucursal');

        $query = DB::table('TCajas as c')
            ->leftJoin('TSucursales as s', 'c.idSucursal', '=', 's.idSucursal')
            ->select(
                'c.idCaja',
                'c.idSucursal',
                's.nombre as sucursalNombre',
                'c.fechaApertura',
                'c.horaApertura',
                'c.montoApertura',
                'c.montoCierre',
                'c.montoCierreCalculado',
                'c.diferenciaArqueo',
                'c.estadoCaja',
                'c.carnetEmpleado',
                'c.fechaA',
                'c.usuarioA'
            )
            ->whereBetween('c.fechaApertura', [$fechaInicio, $fechaFin]);

        if ($estadoCaja) {
            $query->where('c.estadoCaja', $estadoCaja);
        }

        if ($idSucursal) {
            $query->where('c.idSucursal', $idSucursal);
        }

        $pagos = $query->orderBy('c.fechaApertura', 'desc')->get();

        $totalIngresos = $pagos->sum('montoApertura');
        $totalTransacciones = $pagos->count();

        $ingresosPorEstado = $pagos->groupBy('estadoCaja')->map(function($item) {
            return $item->sum('montoApertura');
        });

        if ($ingresosPorEstado->isEmpty()) {
            $ingresosPorEstado = collect(['Sin datos' => 0]);
        }

        $ingresosDiarios = $pagos->groupBy(function($pago) {
            return Carbon::parse($pago->fechaApertura)->format('Y-m-d');
        })->map(function($item) {
            return $item->sum('montoApertura');
        });

        return response()->json(compact('pagos', 'totalIngresos', 'totalTransacciones', 'ingresosPorEstado', 'ingresosDiarios', 'fechaInicio', 'fechaFin', 'estadoCaja', 'idSucursal'));
    }

    // =========================================
    // DETALLE FINANCIERO (membresías + salidas de una caja)
    // =========================================
    public function financieroDetalle($idCaja)
    {
        $caja = DB::table('TCajas as c')
            ->leftJoin('TSucursales as s', 'c.idSucursal', '=', 's.idSucursal')
            ->select('c.*', 's.nombre as sucursalNombre')
            ->where('c.idCaja', $idCaja)
            ->first();

        if (!$caja) {
            abort(404, 'Caja no encontrada');
        }

        $membresias = DB::table('TRecibos as r')
            ->join('TMembresias as m', 'r.idMembresia', '=', 'm.idMembresia')
            ->join('TPlanes as p', 'm.idPlan', '=', 'p.idPlan')
            ->join('TSocios as so', 'm.carnetSocio', '=', 'so.carnetSocio')
            ->leftJoin('TUsuarios as u', 'so.idUsuario', '=', 'u.idUsuario')
            ->select(
                'r.idRecibo',
                'r.montoTotal',
                'r.fechaPago',
                'r.estadoRecibo',
                'm.idMembresia',
                'm.carnetSocio',
                'm.fechaInicioMembresia',
                'm.fechaFinMembresia',
                'm.estadoMembresia',
                'p.nombrePlan',
                'p.costoPlan',
                DB::raw("CONCAT(u.nombre1, ' ', u.apellido1) as nombreSocio")
            )
            ->where('r.idCaja', $idCaja)
            ->get();

        $totalMembresias = $membresias->sum('montoTotal');

        $salidas = DB::table('TSalidas')
            ->where('idCaja', $idCaja)
            ->where('estadoA', 1)
            ->get();

        $totalSalidas = $salidas->sum('costo');

        return response()->json(compact(
            'caja', 'membresias', 'totalMembresias',
            'salidas', 'totalSalidas'
        ));
    }

    // =========================================
    // REPORTE DE ASISTENCIA (RF-46)
    // =========================================
    public function asistencia(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->format('Y-m-d'));
        $nombre = $request->get('nombre', '');

        $query = DB::table('TAsistenciasPersonal as ap')
            ->join('TEmpleados as e', 'ap.carnetEmpleado', '=', 'e.carnetEmpleado')
            ->join('TUsuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->select(
                'ap.idAsistencia',
                'ap.carnetEmpleado',
                DB::raw("CONCAT(u.nombre1, ' ', u.apellido1) as nombreEmpleado"),
                'ap.fechaHoraEntrada',
                'ap.fechaHoraSalida',
                'ap.estadoAsistencia',
                'u.nombre1',
                'u.nombre2',
                'u.apellido1',
                'u.apellido2'
            )
            ->whereBetween('ap.fechaHoraEntrada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->where('ap.estadoA', 1);

        if ($nombre !== '') {
            $query->where(function($q) use ($nombre) {
                $q->where('ap.carnetEmpleado', 'like', "%{$nombre}%")
                  ->orWhere('u.nombre1', 'like', "%{$nombre}%")
                  ->orWhere('u.nombre2', 'like', "%{$nombre}%")
                  ->orWhere('u.apellido1', 'like', "%{$nombre}%")
                  ->orWhere('u.apellido2', 'like', "%{$nombre}%");
            });
        }

        $asistencias = $query->orderBy('ap.fechaHoraEntrada', 'desc')->get();

        // Mapear horarios esperados por empleado+día
        $carnets = $asistencias->pluck('carnetEmpleado')->unique();
        $horarios = DB::table('THorarioLaborales')
            ->whereIn('carnetEmpleado', $carnets)
            ->where('estadoA', 1)
            ->select('carnetEmpleado', 'diaSemana', 'horaEntradaEsperada', 'horaSalidaEsperada')
            ->get()
            ->groupBy('carnetEmpleado');

        $diasES = ['Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'];

        $asistencias = $asistencias->map(function($a) use ($horarios, $diasES) {
            $fecha = Carbon::parse($a->fechaHoraEntrada);
            $diaSemana = $diasES[$fecha->format('l')] ?? '';
            $empleadoHorarios = isset($horarios[$a->carnetEmpleado]) ? $horarios[$a->carnetEmpleado] : collect();
            $delDia = $empleadoHorarios->where('diaSemana', $diaSemana)->values();
            $a->diaSemana = $diaSemana;
            if ($delDia->count() > 0) {
                $a->esperadoEntrada = $delDia->pluck('horaEntradaEsperada')->implode(', ');
                $a->esperadoSalida = $delDia->pluck('horaSalidaEsperada')->implode(', ');
                $a->turnos = $delDia->map(function($t) {
                    return ['entrada' => substr($t->horaEntradaEsperada, 0, 5), 'salida' => substr($t->horaSalidaEsperada, 0, 5)];
                });
            } else {
                $a->esperadoEntrada = '—';
                $a->esperadoSalida = '—';
                $a->turnos = [];
            }
            return $a;
        });

        $totalAsistencias = $asistencias->count();

        $asistenciasPorDia = $asistencias->groupBy(function($a) {
            return Carbon::parse($a->fechaHoraEntrada)->format('Y-m-d');
        })->map(function($item) {
            return $item->count();
        });

        if ($asistenciasPorDia->isEmpty()) {
            $asistenciasPorDia = collect(['Sin datos' => 0]);
        }

        return response()->json(compact('asistencias', 'totalAsistencias', 'asistenciasPorDia', 'fechaInicio', 'fechaFin', 'nombre'));
    }

    // =========================================
    // DETALLE DE CLASE (socios que reservaron)
    // =========================================
    public function claseDetalle($idClase)
    {
        $clase = Clase::with(['actividad', 'instructor.usuario'])->findOrFail($idClase);

        $reservas = Reserva::with(['socio.usuario'])
            ->where('idClaseGrupal', $idClase)
            ->orderBy('fechaReserva')
            ->get();

        $socios = $reservas->map(function($r) {
            $s = $r->socio;
            return [
                'idReserva' => $r->idReserva,
                'carnetSocio' => $s ? $s->carnetSocio : null,
                'nombreSocio' => $s && $s->usuario ? ($s->usuario->nombre1 . ' ' . $s->usuario->apellido1) : 'N/A',
                'fechaReserva' => $r->fechaReserva,
                'estadoReserva' => $r->estadoReserva,
            ];
        });

        $nombreInstructor = 'Sin instructor';
        if ($clase->instructor && $clase->instructor->usuario) {
            $nombreInstructor = ($clase->instructor->usuario->nombre1 ?? '') . ' ' . ($clase->instructor->usuario->apellido1 ?? '');
        }

        return response()->json([
            'clase' => [
                'idClaseGrupal' => $clase->idClaseGrupal,
                'nombreActividad' => $clase->actividad->nombreActividad ?? 'Sin nombre',
                'instructor' => $nombreInstructor,
                'fecha' => $clase->fecha,
                'horaInicio' => $clase->horaInicio,
                'horaFin' => $clase->horaFin,
                'capacidad' => $clase->cupoMaximo ?? 0,
                'totalReservas' => $reservas->count(),
                'asistieron' => $reservas->where('estadoReserva', 'Asistido')->count(),
            ],
            'socios' => $socios,
        ]);
    }

    // =========================================
    // REPORTE DE CLASES GRUPALES (RF-47)
    // =========================================
    public function clases(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->format('Y-m-d'));
        $instructor = $request->get('instructor', '');

        $query = Clase::with(['instructor.usuario', 'reservas', 'actividad'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin]);

        if ($instructor !== '') {
            $query->whereHas('instructor.usuario', function($q) use ($instructor) {
                $q->where('nombre1', 'like', "%{$instructor}%")
                  ->orWhere('nombre2', 'like', "%{$instructor}%")
                  ->orWhere('apellido1', 'like', "%{$instructor}%")
                  ->orWhere('apellido2', 'like', "%{$instructor}%");
            });
        }

        $clases = $query->get();

        $estadisticas = $clases->map(function($clase) {
            $nombreInstructor = 'Sin instructor';
            if ($clase->instructor && $clase->instructor->usuario) {
                $nombreInstructor = ($clase->instructor->usuario->nombre1 ?? '') . ' ' . ($clase->instructor->usuario->apellido1 ?? '');
            }

            return [
                'idClaseGrupal' => $clase->idClaseGrupal,
                'nombre' => $clase->actividad->nombreActividad ?? 'Sin nombre',
                'instructor' => $nombreInstructor,
                'fecha' => $clase->fecha,
                'horaInicio' => $clase->horaInicio,
                'horaFin' => $clase->horaFin,
                'capacidad' => $clase->cupoMaximo ?? 0,
                'reservados' => $clase->reservas->count(),
                'asistieron' => $clase->reservas->where('estadoReserva', 'Asistido')->count(),
                'ocupacion' => $clase->cupoMaximo > 0 ? round(($clase->reservas->count() / $clase->cupoMaximo) * 100, 2) : 0
            ];
        });

        return response()->json(compact('estadisticas', 'fechaInicio', 'fechaFin', 'instructor'));
    }

    // =========================================
    // REPORTE DE EQUIPAMIENTO (RF-48)
    // =========================================
    public function equipamiento(Request $request)
    {
        $estado = $request->get('estado');
        $nombre = $request->get('nombre', '');

        $query = Equipo::with('incidencias');

        if ($estado) {
            $query->where('estadoEquipo', $estado);
        }

        if ($nombre !== '') {
            $query->where('nombreEquipo', 'like', "%{$nombre}%");
        }

        $equipos = $query->get();

        // Si no hay equipos, crear un array vacío
        if ($equipos->isEmpty()) {
            $equipos = collect([]);
        }

        $estadisticas = [
            'total' => $equipos->count(),
            'operativos' => $equipos->where('estadoEquipo', 'Operativo')->count(),
            'mantenimiento' => $equipos->where('estadoEquipo', 'En Mantenimiento')->count(),
            'fuera_servicio' => $equipos->where('estadoEquipo', 'Fuera de Servicio')->count(),
            'fallas_recientes' => Incidencia::where('fechaReporte', '>=', Carbon::now()->subDays(30))->count()
        ];

        return response()->json(compact('equipos', 'estadisticas'));
    }

    // =========================================
    // REPORTE DE DESEMPEÑO DE PERSONAL (RF-8)
    // =========================================
    public function personalDesempeno(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $empleadoId = $request->get('empleado_id');

        $query = DB::table('TAsistenciasPersonal as ap')
            ->join('TEmpleados as e', 'ap.carnetEmpleado', '=', 'e.carnetEmpleado')
            ->join('TUsuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->select(
                'ap.idAsistencia',
                'ap.carnetEmpleado',
                'u.nombre1',
                'u.apellido1',
                'ap.fechaHoraEntrada',
                'ap.fechaHoraSalida'
            )
            ->whereBetween('ap.fechaHoraEntrada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->where('ap.estadoA', 1);

        if ($empleadoId) {
            $query->where('ap.carnetEmpleado', $empleadoId);
        }

        $asistencias = $query->orderBy('ap.fechaHoraEntrada', 'desc')->get();

        return response()->json($asistencias);
    }

    public function generarPDF(Request $request)
    {
        $html = $request->input('html');
        $nombreArchivo = $request->input('nombreArchivo', 'reporte_' . now()->format('Y-m-d'));
        $titulo = $request->input('titulo', 'Reporte');

        $fullHtml = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . e($titulo) . '</title>
        <style>
            body{font-family:Inter,DejaVu Sans,sans-serif;padding:20px;}
            table{width:100%;border-collapse:collapse;}
            th{text-align:left;padding:0.75rem;font-size:0.8rem;font-weight:600;color:#64748b;border-bottom:2px solid #e2e8f0;text-transform:uppercase;}
            td{padding:0.75rem;font-size:0.85rem;color:#1e293b;border-bottom:1px solid #f1f5f9;}
            .badge{display:inline-block;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.75rem;font-weight:600;}
            .badge-green,.badge-success{background:#dcfce7;color:#166534;}
            .badge-amber,.badge-warning{background:#fef3c7;color:#92400e;}
            .badge-red,.badge-danger{background:#fee2e2;color:#991b1b;}
            .badge-blue{background:#dbeafe;color:#1e40af;}
            .badge-gray{background:#f1f5f9;color:#475569;}
            .stat-card{text-align:center;padding:1rem;display:inline-block;margin:0.5rem;}
            .stat-card .number{font-size:2rem;font-weight:700;color:#0f172a;}
            .stat-card .label{font-size:0.8rem;color:#64748b;}
            .socio-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;}
            .socio-header .nombre{font-size:1.1rem;font-weight:700;}
            .socio-header .carnet{color:#64748b;font-size:0.85rem;}
            h4{font-size:0.9rem;font-weight:600;margin:1.25rem 0 0.5rem 0;color:#1e293b;}
            h2{margin-bottom:1rem;}
            .no-print{display:none!important;}
            .row-clickable{cursor:default;}
        </style></head><body><h2>' . e($titulo) . '</h2>' . $html . '</body></html>';

        $pdf = Pdf::loadHTML($fullHtml);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download($nombreArchivo . '.pdf');
    }
}
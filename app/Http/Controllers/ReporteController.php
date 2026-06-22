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
use App\Models\Eloquent\Usuario;
use App\Models\Eloquent\Empleado;
use Carbon\Carbon;

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

        $socios = $query->get();

        $totalSocios = $socios->count();
        $conMembresia = $socios->filter(function($s) {
            return $s->membresia && $s->membresia->estadoMembresia == 'Activa';
        })->count();
        $sinMembresia = $totalSocios - $conMembresia;

        return response()->json(compact('socios', 'totalSocios', 'conMembresia', 'sinMembresia', 'estado'));
    }

    // =========================================
    // REPORTE FINANCIERO (RF-45)
    // =========================================
    public function financiero(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->format('Y-m-d'));
        $estadoCaja = $request->get('estado_caja');

        $query = Pago::whereBetween('fechaApertura', [$fechaInicio, $fechaFin]);

        if ($estadoCaja) {
            $query->where('estadoCaja', $estadoCaja);
        }

        $pagos = $query->get();
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

        return response()->json(compact('pagos', 'totalIngresos', 'totalTransacciones', 'ingresosPorEstado', 'ingresosDiarios', 'fechaInicio', 'fechaFin', 'estadoCaja'));
    }

    // =========================================
    // REPORTE DE ASISTENCIA (RF-46)
    // =========================================
    public function asistencia(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->format('Y-m-d'));

        $asistencias = DB::table('TAsistenciasPersonal as ap')
            ->join('TEmpleados as e', 'ap.carnetEmpleado', '=', 'e.carnetEmpleado')
            ->join('TUsuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->select(
                'ap.idAsistencia',
                'ap.carnetEmpleado',
                DB::raw("CONCAT(u.nombre1, ' ', u.apellido1) as nombreEmpleado"),
                'ap.fechaHoraEntrada',
                'ap.fechaHoraSalida',
                'ap.estadoAsistencia'
            )
            ->whereBetween('ap.fechaHoraEntrada', [$fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59'])
            ->where('ap.estadoA', 1)
            ->orderBy('ap.fechaHoraEntrada', 'desc')
            ->get();

        $totalAsistencias = $asistencias->count();

        $asistenciasPorDia = $asistencias->groupBy(function($a) {
            return Carbon::parse($a->fechaHoraEntrada)->format('Y-m-d');
        })->map(function($item) {
            return $item->count();
        });

        if ($asistenciasPorDia->isEmpty()) {
            $asistenciasPorDia = collect(['Sin datos' => 0]);
        }

        return response()->json(compact('asistencias', 'totalAsistencias', 'asistenciasPorDia', 'fechaInicio', 'fechaFin'));
    }

    // =========================================
    // REPORTE DE CLASES GRUPALES (RF-47)
    // =========================================
    public function clases(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->format('Y-m-d'));

        $clases = Clase::with(['instructor.usuario', 'reservas', 'actividad'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->get();

        $estadisticas = $clases->map(function($clase) {
            $nombreInstructor = 'Sin instructor';
            if ($clase->instructor && $clase->instructor->usuario) {
                $nombreInstructor = ($clase->instructor->usuario->nombre1 ?? '') . ' ' . ($clase->instructor->usuario->apellido1 ?? '');
            }

            return [
                'nombre' => $clase->actividad->nombreActividad ?? 'Sin nombre',
                'instructor' => $nombreInstructor,
                'fecha' => $clase->fecha,
                'capacidad' => $clase->cupoMaximo ?? 0,
                'reservados' => $clase->reservas->count(),
                'asistieron' => $clase->reservas->where('estadoReserva', 'Asistido')->count(),
                'ocupacion' => $clase->cupoMaximo > 0 ? round(($clase->reservas->count() / $clase->cupoMaximo) * 100, 2) : 0
            ];
        });

        return response()->json(compact('estadisticas', 'fechaInicio', 'fechaFin'));
    }

    // =========================================
    // REPORTE DE EQUIPAMIENTO (RF-48)
    // =========================================
    public function equipamiento(Request $request)
    {
        $estado = $request->get('estado');

        $query = Equipo::with('incidencias');

        if ($estado) {
            $query->where('estadoEquipo', $estado);
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
}
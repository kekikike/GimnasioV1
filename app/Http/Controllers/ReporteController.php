<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        return view('reportes.index');
    }

    // =========================================
    // REPORTE DE SOCIOS (RF-44)
    // =========================================
    public function socios(Request $request)
    {
        $estado = $request->get('estado', 'todos');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        $query = Socio::with(['usuario', 'membresia']);

        if ($estado == 'activos') {
            $query->whereHas('membresia', function($q) {
                $q->where('estadoMembresia', 'Activa')
                  ->where('fechaFinMembresia', '>=', Carbon::now()->format('Y-m-d'));
            });
        } elseif ($estado == 'inactivos') {
            $query->whereHas('membresia', function($q) {
                $q->where('estadoMembresia', 'Inactiva');
            });
        } elseif ($estado == 'vencidos') {
            $query->whereHas('membresia', function($q) {
                $q->where('fechaFinMembresia', '<', Carbon::now()->format('Y-m-d'));
            });
        }

        $socios = $query->get();

        $totalSocios = $socios->count();
        $activos = $socios->filter(function($s) {
            return $s->membresia && $s->membresia->estadoMembresia == 'Activa' && $s->membresia->fechaFinMembresia >= Carbon::now()->format('Y-m-d');
        })->count();
        $inactivos = $socios->filter(function($s) {
            return !$s->membresia || $s->membresia->estadoMembresia != 'Activa' || $s->membresia->fechaFinMembresia < Carbon::now()->format('Y-m-d');
        })->count();

        return view('reportes.socios', compact('socios', 'totalSocios', 'activos', 'inactivos', 'estado'));
    }

    // =========================================
    // REPORTE FINANCIERO (RF-45)
    // =========================================
    public function financiero(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->format('Y-m-d'));
        $metodoPago = $request->get('metodo_pago');

        $query = Pago::whereBetween('fechaApertura', [$fechaInicio, $fechaFin]);

        $pagos = $query->get();
        $totalIngresos = $pagos->sum('montoApertura');
        $totalTransacciones = $pagos->count();

        // Agrupar por método de pago (usamos estadoCaja como categoría)
        $ingresosPorMetodo = $pagos->groupBy('estadoCaja')->map(function($item) {
            return $item->sum('montoApertura');
        });

        // Si no hay datos, crear un array vacío para evitar error
        if ($ingresosPorMetodo->isEmpty()) {
            $ingresosPorMetodo = collect(['Sin datos' => 0]);
        }

        $ingresosDiarios = $pagos->groupBy(function($pago) {
            return Carbon::parse($pago->fechaApertura)->format('Y-m-d');
        })->map(function($item) {
            return $item->sum('montoApertura');
        });

        return view('reportes.financiero', compact(
            'pagos',
            'totalIngresos',
            'totalTransacciones',
            'ingresosPorMetodo',
            'ingresosDiarios',
            'fechaInicio',
            'fechaFin'
        ));
    }

    // =========================================
    // REPORTE DE ASISTENCIA (RF-46)
    // =========================================
    public function asistencia(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->format('Y-m-d'));
        $socioId = $request->get('socio_id');

        $query = Asistencia::whereBetween('fecha', [$fechaInicio, $fechaFin]);

        $asistencias = $query->get();
        $totalAsistencias = $asistencias->count();

        $asistenciasPorDia = $asistencias->groupBy(function($a) {
            return Carbon::parse($a->fecha)->format('Y-m-d');
        })->map(function($item) {
            return $item->count();
        });

        // Si no hay datos, crear un array vacío para evitar error
        if ($asistenciasPorDia->isEmpty()) {
            $asistenciasPorDia = collect(['Sin datos' => 0]);
        }

        $socios = Socio::with('usuario')->get();

        return view('reportes.asistencia', compact(
            'asistencias',
            'totalAsistencias',
            'asistenciasPorDia',
            'socios',
            'fechaInicio',
            'fechaFin'
        ));
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

        return view('reportes.clases', compact('estadisticas', 'fechaInicio', 'fechaFin'));
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

        return view('reportes.equipamiento', compact('equipos', 'estadisticas'));
    }

    // =========================================
    // REPORTE DE DESEMPEÑO DE PERSONAL (RF-8)
    // =========================================
    public function personalDesempeno(Request $request)
    {
        $fechaInicio = $request->get('fecha_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $fechaFin = $request->get('fecha_fin', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $empleadoId = $request->get('empleado_id');

        // Obtener todos los empleados para el filtro del frontend
        $empleados = Empleado::with('usuario')->whereHas('usuario', function ($q) {
            $q->where('estadoA', 1);
        })->get();

        $query = DB::table('tasistenciaspersonal as ap')
            ->join('templeados as e', 'ap.carnetEmpleado', '=', 'e.carnetEmpleado')
            ->join('tusuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->select(
                'ap.idAsistencia',
                'ap.carnetEmpleado',
                'u.nombre1',
                'u.apellido1',
                'ap.fechaHoraEntrada',
                'ap.fechaHoraSalida'
            )
            ->whereBetween('ap.fechaHoraEntrada', [$fechaInicio . " 00:00:00", $fechaFin . " 23:59:59"]);

        if ($empleadoId) {
            $query->where('ap.carnetEmpleado', $empleadoId);
        }

        $asistencias = $query->orderBy('ap.fechaHoraEntrada', 'desc')->get();

        // Aquí se podrían agregar más cálculos: horas trabajadas, retardos, etc.

        return view('reportes.personal_desempeno', compact('asistencias', 'empleados', 'fechaInicio', 'fechaFin', 'empleadoId'));
    }
}
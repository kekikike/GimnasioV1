<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    private function getHorario($carnetEmpleado, $timestamp = null)
    {
        $fechaReferencia = $timestamp ? Carbon::parse($timestamp) : Carbon::now();
        $dias = [
            'Monday'    => 'Lunes',
            'Tuesday'   => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday'  => 'Jueves',
            'Friday'    => 'Viernes',
            'Saturday'  => 'Sábado',
            'Sunday'    => 'Domingo',
        ];
        $diaSemana = $dias[$fechaReferencia->format('l')];

        $horarios = DB::table('thorariolaborales')
            ->where('carnetEmpleado', $carnetEmpleado)
            ->where('diaSemana', $diaSemana)
            ->where('estadoA', 1)
            ->get();

        if ($horarios->isEmpty()) {
            return null;
        }

        $mejorTurno = null;
        $menorDiferencia = PHP_INT_MAX;

        foreach ($horarios as $horario) {
            // Comparamos la hora de entrada del turno con la hora de referencia
            $horaTurno = Carbon::parse($horario->horaEntradaEsperada)->setDateFrom($fechaReferencia);
            $diferenciaAbs = abs($fechaReferencia->getTimestamp() - $horaTurno->getTimestamp());

            if ($diferenciaAbs < $menorDiferencia) {
                $menorDiferencia = $diferenciaAbs;
                $mejorTurno = $horario;
            }
        }

        // Para entradas, no asociar si el turno más cercano está a más de 4 horas de distancia.
        if ($timestamp === null && $menorDiferencia > (4 * 3600)) {
            return null;
        }

        return $mejorTurno;
    }

    public function registrarEntrada(Request $request)
    {
        $request->validate(['carnetEmpleado' => 'required|string|exists:templeados,carnetEmpleado']);
        $usuarioA = Auth::id() ?? 1;

        try {
            $entradaExistente = DB::table('tasistenciaspersonal')
                ->where('carnetEmpleado', $request->carnetEmpleado)
                ->whereDate('fechaHoraEntrada', Carbon::today()->toDateString())
                ->whereNull('fechaHoraSalida')
                ->exists();

            if ($entradaExistente) {
                return response()->json(['success' => false, 'message' => 'Ya existe una entrada registrada sin salida para hoy.'], 409);
            }

            $horario = $this->getHorario($request->carnetEmpleado);
            $ahora = Carbon::now();

            $estadoAsistencia = 'Puntual';
            if ($horario) {
                $horaEntradaEsperada = Carbon::parse($horario->horaEntradaEsperada);
                $tolerancia = $horaEntradaEsperada->copy()->addMinutes(5);
                if ($ahora->gt($tolerancia)) {
                    $estadoAsistencia = 'Tardanza';
                }
            }

            DB::table('tasistenciaspersonal')->insert([
                'carnetEmpleado' => $request->carnetEmpleado,
                'fechaHoraEntrada' => $ahora,
                'estadoAsistencia' => $estadoAsistencia,
                'usuarioA' => $usuarioA,
                'fechaA' => now(),
                'estadoA' => 1
            ]);

            if ($horario) {
                $horaEntradaEsperada = Carbon::parse($horario->horaEntradaEsperada);
                $msg = $ahora->lte($horaEntradaEsperada)
                    ? 'Entrada registrada - Llegaste a tiempo.'
                    : ($estadoAsistencia === 'Tardanza'
                        ? 'Entrada registrada - Llegaste tarde (mas de 5 min de tolerancia).'
                        : 'Entrada registrada exitosamente.');
            } else {
                $msg = 'Entrada registrada exitosamente.';
            }

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function registrarSalida(Request $request)
    {
        $request->validate(['carnetEmpleado' => 'required|string|exists:templeados,carnetEmpleado']);
        $usuarioA = Auth::id() ?? 1;

        try {
            $asistenciaAbierta = DB::table('tasistenciaspersonal')
                ->where('carnetEmpleado', $request->carnetEmpleado)
                ->whereNull('fechaHoraSalida')
                ->orderBy('fechaHoraEntrada', 'desc')
                ->first();

            if (!$asistenciaAbierta) {
                return response()->json(['success' => false, 'message' => 'No se encontro una entrada abierta para registrar la salida.'], 404);
            }

            $ahora = Carbon::now();
            // Buscamos el horario correspondiente a la hora de entrada que se registró
            $horario = $this->getHorario($request->carnetEmpleado, $asistenciaAbierta->fechaHoraEntrada);

            $estadoSalida = $asistenciaAbierta->estadoAsistencia;
            $msg = 'Salida registrada correctamente.';

            if ($horario) {
                $horaSalidaEsperada = Carbon::parse($horario->horaSalidaEsperada);

                if ($ahora->lt($horaSalidaEsperada->copy()->subMinutes(10))) {
                    $estadoSalida = 'Falta';
                    $msg = 'Salida registrada - Te fuiste antes de tiempo, se registro como Falta.';
                }
            }

            DB::table('tasistenciaspersonal')
                ->where('idAsistencia', $asistenciaAbierta->idAsistencia)
                ->update([
                    'fechaHoraSalida' => $ahora,
                    'estadoAsistencia' => $estadoSalida,
                    'usuarioA' => $usuarioA,
                    'fechaA' => now()
                ]);

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}

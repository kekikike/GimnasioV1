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
            $horaTurno = Carbon::parse($horario->horaEntradaEsperada)->setDateFrom($fechaReferencia);
            $diferenciaAbs = abs($fechaReferencia->getTimestamp() - $horaTurno->getTimestamp());

            if ($diferenciaAbs < $menorDiferencia) {
                $menorDiferencia = $diferenciaAbs;
                $mejorTurno = $horario;
            }
        }

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

            $estadoEntrada = null;
            $msg = 'Entrada registrada exitosamente.';

            if ($horario) {
                $horaEntradaEsperada = Carbon::parse($horario->horaEntradaEsperada);
                $tolerancia = $horaEntradaEsperada->copy()->addMinutes(5);

                if ($ahora->lte($tolerancia)) {
                    $estadoEntrada = 'puntual';
                    $msg = 'Entrada registrada - Llegaste a tiempo.';
                } else {
                    $estadoEntrada = 'tarde';
                    $msg = 'Entrada registrada - Llegaste tarde.';
                }

                $msg .= ' Turno: ' . substr($horario->horaEntradaEsperada, 0, 5) . ' - ' . substr($horario->horaSalidaEsperada, 0, 5);
            }

            DB::table('tasistenciaspersonal')->insert([
                'carnetEmpleado' => $request->carnetEmpleado,
                'idHorario' => $horario ? $horario->idHorario : null,
                'fechaHoraEntrada' => $ahora,
                'estadoEntrada' => $estadoEntrada,
                'estadoSalida' => null,
                'estadoAsistencia' => $estadoEntrada ?? 'Puntual',
                'usuarioA' => $usuarioA,
                'fechaA' => now(),
                'estadoA' => 1
            ]);

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
            $estadoSalida = null;
            $msg = 'Salida registrada correctamente.';

            if ($asistenciaAbierta->idHorario) {
                $horario = DB::table('thorariolaborales')->where('idHorario', $asistenciaAbierta->idHorario)->first();

                if ($horario) {
                    $horaSalidaEsperada = Carbon::parse($horario->horaSalidaEsperada);
                    $diferenciaMinutos = $ahora->diffInMinutes($horaSalidaEsperada, false);

                    if ($diferenciaMinutos < -10) {
                        $estadoSalida = 'salió antes';
                        $msg = 'Salida registrada - Te fuiste antes de la hora.';
                    } elseif ($diferenciaMinutos > 30) {
                        $estadoSalida = 'salió tarde';
                        $msg = 'Salida registrada - Te fuiste despues de la hora.';
                    } else {
                        $estadoSalida = 'puntual';
                        $msg = 'Salida registrada - A tiempo.';
                    }

                    $msg .= ' Turno: ' . substr($horario->horaEntradaEsperada, 0, 5) . ' - ' . substr($horario->horaSalidaEsperada, 0, 5);
                }
            }

            DB::table('tasistenciaspersonal')
                ->where('idAsistencia', $asistenciaAbierta->idAsistencia)
                ->update([
                    'fechaHoraSalida' => $ahora,
                    'estadoSalida' => $estadoSalida,
                    'estadoAsistencia' => $estadoSalida ?? $asistenciaAbierta->estadoEntrada ?? 'Puntual',
                    'usuarioA' => $usuarioA,
                    'fechaA' => now()
                ]);

            return response()->json(['success' => true, 'message' => $msg]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}

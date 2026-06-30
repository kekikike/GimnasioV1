<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    private function getHorario($carnetEmpleado, $fechaReferencia = null)
    {
        $fechaReferencia = $fechaReferencia ? Carbon::parse($fechaReferencia) : Carbon::now();
        $dias = [
            'Monday'    => 'Lunes', 'Tuesday'   => 'Martes', 'Wednesday' => 'Miércoles',
            'Thursday'  => 'Jueves', 'Friday'    => 'Viernes', 'Saturday'  => 'Sábado', 'Sunday'    => 'Domingo',
        ];
        $diaSemana = $dias[$fechaReferencia->format('l')];

        $horarios = DB::table('thorariolaborales')
            ->where('carnetEmpleado', $carnetEmpleado)
            ->where('diaSemana', $diaSemana)
            ->where('estadoA', 1)
            ->orderBy('horaEntradaEsperada')
            ->get();

        if ($horarios->isEmpty()) {
            return ['siguiente' => null, 'anterior' => null];
        }

        $siguiente = null;
        $anterior = null;
        $toleranciaSegundos = 3600;

        foreach ($horarios as $horario) {
            $horaEntrada = Carbon::parse($horario->horaEntradaEsperada)->setDateFrom($fechaReferencia);
            $diferenciaSegundos = $horaEntrada->timestamp - $fechaReferencia->timestamp;

            if ($diferenciaSegundos >= -$toleranciaSegundos) {
                $siguiente = $horario;
                break;
            }
            $anterior = $horario;
        }

        return ['siguiente' => $siguiente, 'anterior' => $anterior];
    }

    public function registrarEntrada(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'carnetEmpleado' => 'required|string|exists:templeados,carnetEmpleado',
        ], [
            'carnetEmpleado.required' => 'El número de carnet es obligatorio.',
            'carnetEmpleado.exists' => 'El carnet ingresado no corresponde a un empleado registrado.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first('carnetEmpleado')], 422);
        }

        $usuarioA = Auth::id() ?? 1;
        $hoy = Carbon::today()->toDateString();

        try {
            $horarios = $this->getHorario($request->carnetEmpleado);
            $siguiente = $horarios['siguiente'];
            $anterior = $horarios['anterior'];

            if ($anterior) {
                $tieneAsistencia = DB::table('tasistenciaspersonal')
                    ->where('carnetEmpleado', $request->carnetEmpleado)
                    ->where('idHorario', $anterior->idHorario)
                    ->whereDate('fechaHoraEntrada', $hoy)
                    ->exists();

                if (!$tieneAsistencia) {
                    DB::table('tasistenciaspersonal')->insert([
                        'carnetEmpleado' => $request->carnetEmpleado,
                        'idHorario' => $anterior->idHorario,
                        'fechaHoraEntrada' => Carbon::parse($anterior->horaEntradaEsperada),
                        'estadoEntrada' => 'falta',
                        'estadoSalida' => 'falta',
                        'estadoAsistencia' => 'falta',
                        'usuarioA' => $usuarioA,
                        'fechaA' => now(),
                        'estadoA' => 1,
                    ]);
                }
            }

            if ($siguiente) {
                $existeHoy = DB::table('tasistenciaspersonal')
                    ->where('carnetEmpleado', $request->carnetEmpleado)
                    ->where('idHorario', $siguiente->idHorario)
                    ->whereDate('fechaHoraEntrada', $hoy)
                    ->exists();

                if ($existeHoy) {
                    return response()->json(['success' => false, 'message' => 'Ya existe un registro de asistencia para este turno el día de hoy.'], 409);
                }

                $ahora = Carbon::now();
                $horaEntradaEsperada = Carbon::parse($siguiente->horaEntradaEsperada);
                $tolerancia = $horaEntradaEsperada->copy()->addMinutes(5);

                if ($ahora->lte($tolerancia)) {
                    $estadoEntrada = 'puntual';
                    $estadoAsistencia = 'presente';
                    $msg = 'Entrada registrada - Llegaste a tiempo.';
                } else {
                    $estadoEntrada = 'tardanza';
                    $estadoAsistencia = 'tardanza';
                    $msg = 'Entrada registrada - Llegaste tarde.';
                }

                $msg .= ' Turno: ' . substr($siguiente->horaEntradaEsperada, 0, 5) . ' - ' . substr($siguiente->horaSalidaEsperada, 0, 5);

                DB::table('tasistenciaspersonal')->insert([
                    'carnetEmpleado' => $request->carnetEmpleado,
                    'idHorario' => $siguiente->idHorario,
                    'fechaHoraEntrada' => $ahora,
                    'estadoEntrada' => $estadoEntrada,
                    'estadoSalida' => null,
                    'estadoAsistencia' => $estadoAsistencia,
                    'usuarioA' => $usuarioA,
                    'fechaA' => now(),
                    'estadoA' => 1,
                ]);

                return response()->json(['success' => true, 'message' => $msg, 'estado' => $estadoEntrada]);
            }

            return response()->json(['success' => false, 'message' => 'Todos tus turnos de hoy ya pasaron.'], 400);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function registrarSalida(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'carnetEmpleado' => 'required|string|exists:templeados,carnetEmpleado',
        ], [
            'carnetEmpleado.required' => 'El número de carnet es obligatorio.',
            'carnetEmpleado.exists' => 'El carnet ingresado no corresponde a un empleado registrado.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first('carnetEmpleado')], 422);
        }

        $usuarioA = Auth::id() ?? 1;

        try {
            $asistenciaAbierta = DB::table('tasistenciaspersonal')
                ->where('carnetEmpleado', $request->carnetEmpleado)
                ->whereNull('fechaHoraSalida')
                ->orderBy('fechaHoraEntrada', 'desc')
                ->first();

            if (!$asistenciaAbierta) {
                return response()->json(['success' => false, 'message' => 'No se encontró una entrada abierta para registrar la salida.'], 404);
            }

            $ahora = Carbon::now();
            $estadoSalida = null;
            $msg = 'Salida registrada correctamente.';

            $horario = null;
            if ($asistenciaAbierta->idHorario) {
                $horario = DB::table('thorariolaborales')->where('idHorario', $asistenciaAbierta->idHorario)->first();
            }

            if (!$horario) {
                $horarios = $this->getHorario($request->carnetEmpleado, $asistenciaAbierta->fechaHoraEntrada);
                $horario = $horarios['siguiente'];
            }

            if ($horario) {
                $horaSalidaEsperada = Carbon::parse($horario->horaSalidaEsperada);
                $minutosAntes = ($horaSalidaEsperada->timestamp - $ahora->timestamp) / 60;

                if ($minutosAntes > 10) {
                    $estadoSalida = 'temprano';
                    $msg = 'Salida registrada - Te fuiste antes de la hora.';
                } else {
                    $estadoSalida = 'puntual';
                    $msg = 'Salida registrada - A tiempo.';
                }

                $msg .= ' Turno: ' . substr($horario->horaEntradaEsperada, 0, 5) . ' - ' . substr($horario->horaSalidaEsperada, 0, 5);
            }

            DB::table('tasistenciaspersonal')
                ->where('idAsistencia', $asistenciaAbierta->idAsistencia)
                ->update([
                    'fechaHoraSalida' => $ahora,
                    'estadoSalida' => $estadoSalida,
                    'estadoAsistencia' => $estadoSalida === 'temprano' ? 'temprano' : 'presente',
                    'usuarioA' => $usuarioA,
                    'fechaA' => now(),
                ]);

            return response()->json(['success' => true, 'message' => $msg, 'estado' => $estadoSalida]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}

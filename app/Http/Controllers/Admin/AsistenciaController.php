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
            return null;
        }

        // Primero buscar un horario donde la hora actual esté DENTRO del rango
        foreach ($horarios as $horario) {
            $horaInicio = Carbon::parse($horario->horaEntradaEsperada)->setDateFrom($fechaReferencia);
            $horaFin = Carbon::parse($horario->horaSalidaEsperada)->setDateFrom($fechaReferencia);
            if ($fechaReferencia->between($horaInicio, $horaFin)) {
                return $horario;
            }
        }

        // Si no, buscar el futuro más cercano, o el pasado más cercano
        $futuro = null;
        $pasado = null;

        foreach ($horarios as $horario) {
            $horaInicio = Carbon::parse($horario->horaEntradaEsperada)->setDateFrom($fechaReferencia);
            $diff = $horaInicio->timestamp - $fechaReferencia->timestamp;

            if ($diff >= 0) {
                if ($futuro === null || $diff < $futuro['diff']) {
                    $futuro = ['horario' => $horario, 'diff' => $diff];
                }
            } else {
                if ($pasado === null || $diff > $pasado['diff']) {
                    $pasado = ['horario' => $horario, 'diff' => $diff];
                }
            }
        }

        return $futuro ? $futuro['horario'] : ($pasado ? $pasado['horario'] : null);
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
            $adminSucursal = DB::table('templeados')->where('idUsuario', $usuarioA)->value('idSucursal');
            $empSucursal = DB::table('templeados')->where('carnetEmpleado', $request->carnetEmpleado)->value('idSucursal');

            if ($adminSucursal !== $empSucursal) {
                return response()->json(['success' => false, 'message' => 'El empleado no pertenece a tu sucursal.'], 403);
            }

            $horario = $this->getHorario($request->carnetEmpleado);

            if (!$horario) {
                return response()->json(['success' => false, 'message' => 'No se encontró un horario laboral para el día de hoy.'], 404);
            }

            $ahora = Carbon::now();
            $horaInicio = Carbon::parse($horario->horaEntradaEsperada)->setDateFrom($ahora);
            $horaFin = Carbon::parse($horario->horaSalidaEsperada)->setDateFrom($ahora);

            if (!$ahora->between($horaInicio, $horaFin)) {
                return response()->json(['success' => false, 'message' => 'Fuera del horario laboral.'], 400);
            }

            $existeHoy = DB::table('tasistenciaspersonal')
                ->where('carnetEmpleado', $request->carnetEmpleado)
                ->where('idHorario', $horario->idHorario)
                ->whereDate('fechaHoraEntrada', $hoy)
                ->exists();

            if ($existeHoy) {
                return response()->json(['success' => false, 'message' => 'Ya existe un registro de asistencia para este turno el día de hoy.'], 409);
            }

            $tolerancia = $horaInicio->copy()->addMinutes(5);

            if ($ahora->lte($tolerancia)) {
                $estadoEntrada = 'puntual';
                $estadoAsistencia = 'presente';
                $msg = 'Entrada registrada - Llegaste a tiempo.';
            } else {
                $estadoEntrada = 'tardanza';
                $estadoAsistencia = 'presente';
                $msg = 'Entrada registrada - Llegaste tarde.';
            }

            $msg .= ' Turno: ' . substr($horario->horaEntradaEsperada, 0, 5) . ' - ' . substr($horario->horaSalidaEsperada, 0, 5);

            // Auto-marcar como falta los turnos anteriores del mismo día no registrados
            $diasMap = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
            $diaSemana = $diasMap[$ahora->format('l')];
            $todosHorarios = DB::table('thorariolaborales')
                ->where('carnetEmpleado', $request->carnetEmpleado)
                ->where('diaSemana', $diaSemana)
                ->where('estadoA', 1)
                ->orderBy('horaEntradaEsperada')
                ->get();

            foreach ($todosHorarios as $h) {
                if ($h->horaEntradaEsperada >= $horario->horaEntradaEsperada) break;

                $yaRegistrado = DB::table('tasistenciaspersonal')
                    ->where('carnetEmpleado', $request->carnetEmpleado)
                    ->where('idHorario', $h->idHorario)
                    ->whereDate('fechaHoraEntrada', $hoy)
                    ->exists();

                if (!$yaRegistrado) {
                    DB::table('tasistenciaspersonal')->insert([
                        'carnetEmpleado' => $request->carnetEmpleado,
                        'idHorario' => $h->idHorario,
                        'fechaHoraEntrada' => $hoy . ' ' . $h->horaEntradaEsperada,
                        'estadoEntrada' => null,
                        'estadoSalida' => null,
                        'estadoAsistencia' => 'falta',
                        'usuarioA' => $usuarioA,
                        'fechaA' => now(),
                        'estadoA' => 1,
                    ]);
                }
            }

            DB::table('tasistenciaspersonal')->insert([
                'carnetEmpleado' => $request->carnetEmpleado,
                'idHorario' => $horario->idHorario,
                'fechaHoraEntrada' => $ahora,
                'estadoEntrada' => $estadoEntrada,
                'estadoSalida' => null,
                'estadoAsistencia' => $estadoAsistencia,
                'usuarioA' => $usuarioA,
                'fechaA' => now(),
                'estadoA' => 1,
            ]);

            return response()->json(['success' => true, 'message' => $msg, 'estado' => $estadoEntrada]);

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
        $hoy = Carbon::today()->toDateString();

        try {
            $adminSucursal = DB::table('templeados')->where('idUsuario', $usuarioA)->value('idSucursal');
            $empSucursal = DB::table('templeados')->where('carnetEmpleado', $request->carnetEmpleado)->value('idSucursal');

            if ($adminSucursal !== $empSucursal) {
                return response()->json(['success' => false, 'message' => 'El empleado no pertenece a tu sucursal.'], 403);
            }

            $horario = $this->getHorario($request->carnetEmpleado);

            if (!$horario) {
                return response()->json(['success' => false, 'message' => 'No se encontró un horario laboral para el día de hoy.'], 404);
            }

            $ahora = Carbon::now();
            $horaInicio = Carbon::parse($horario->horaEntradaEsperada)->setDateFrom($ahora);

            if ($ahora->lt($horaInicio)) {
                return response()->json(['success' => false, 'message' => 'Fuera del horario laboral.'], 400);
            }

            $entrada = DB::table('tasistenciaspersonal')
                ->where('carnetEmpleado', $request->carnetEmpleado)
                ->where('idHorario', $horario->idHorario)
                ->whereDate('fechaHoraEntrada', $hoy)
                ->first();

            if (!$entrada) {
                return response()->json(['success' => false, 'message' => 'No marcó asistencia.'], 400);
            }

            if ($entrada->fechaHoraSalida) {
                return response()->json(['success' => false, 'message' => 'Ya registró su salida para este turno.'], 409);
            }

            $estadoSalida = null;
            $msg = 'Salida registrada correctamente.';

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

            DB::table('tasistenciaspersonal')
                ->where('idAsistencia', $entrada->idAsistencia)
                ->update([
                    'fechaHoraSalida' => $ahora,
                    'estadoSalida' => $estadoSalida,
                    'estadoAsistencia' => 'presente',
                    'usuarioA' => $usuarioA,
                    'fechaA' => now(),
                ]);

            return response()->json(['success' => true, 'message' => $msg, 'estado' => $estadoSalida]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}

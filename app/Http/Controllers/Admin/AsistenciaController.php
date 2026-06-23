<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
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

            $dias = [
                'Monday'    => 'Lunes',
                'Tuesday'   => 'Martes',
                'Wednesday' => 'Miercoles',
                'Thursday'  => 'Jueves',
                'Friday'    => 'Viernes',
                'Saturday'  => 'Sabado',
                'Sunday'    => 'Domingo',
            ];
            $diaSemanaActual = $dias[Carbon::now()->format('l')];

            $horario = DB::table('thorariolaborales')
                ->where('carnetEmpleado', $request->carnetEmpleado)
                ->where('diaSemana', $diaSemanaActual)
                ->where('estadoA', 1)
                ->first();

            $estadoAsistencia = 'Puntual';
            if ($horario) {
                $horaEntradaEsperada = Carbon::parse($horario->horaEntradaEsperada)->addMinutes(5);
                if (Carbon::now()->gt($horaEntradaEsperada)) {
                    $estadoAsistencia = 'Tardanza';
                }
            }

            DB::table('tasistenciaspersonal')->insert([
                'carnetEmpleado' => $request->carnetEmpleado,
                'fechaHoraEntrada' => Carbon::now(),
                'estadoAsistencia' => $estadoAsistencia,
                'usuarioA' => $usuarioA,
                'fechaA' => now(),
                'estadoA' => 1
            ]);

            return response()->json(['success' => true, 'message' => 'Entrada registrada exitosamente.']);
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

            DB::table('tasistenciaspersonal')->where('idAsistencia', $asistenciaAbierta->idAsistencia)->update([
                'fechaHoraSalida' => Carbon::now(),
                'usuarioA' => $usuarioA,
                'fechaA' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Salida registrada correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}

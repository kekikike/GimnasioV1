<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HorarioController extends Controller
{
    public function index()
    {
        $usuario = session('usuario');
        $query = DB::table('templeados as e')
            ->join('tusuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->where('e.estadoA', 1)
            ->where('u.idRol', '!=', 1)
            ->select('e.carnetEmpleado', 'u.nombre1', 'u.apellido1', 'e.idSucursal');

        if ($usuario->idRol != 1) {
            $empleado = DB::table('templeados')->where('idUsuario', $usuario->idUsuario)->first();
            if ($empleado) {
                $query->where('e.idSucursal', $empleado->idSucursal);
            }
        }

        $empleados = $query->get();
        return view('admin.horarios', compact('empleados'));
    }

    public function listar($carnetEmpleado)
    {
        $horarios = DB::table('thorariolaborales')
            ->where('carnetEmpleado', $carnetEmpleado)
            ->where('estadoA', 1)
            ->select('idHorario', 'carnetEmpleado', 'diaSemana', 'horaEntradaEsperada as horaEntrada', 'horaSalidaEsperada as horaSalida')
            ->get();
        return response()->json($horarios);
    }

    public function store(Request $request)
    {
        $usuario = session('usuario');
        if ($usuario->idRol != 1) {
            $this->authorizeSucursal($usuario, $request->carnetEmpleado);
        }

        $validator = Validator::make($request->all(), [
            'carnetEmpleado' => 'required|numeric|exists:templeados,carnetEmpleado',
            'diaSemana' => [
                'required',
                'string',
                'in:Lunes,Martes,Miercoles,Jueves,Viernes,Sabado,Domingo',
                Rule::unique('thorariolaborales')->where(function ($query) use ($request) {
                    return $query->where('carnetEmpleado', $request->carnetEmpleado)
                                 ->where('estadoA', 1);
                }),
            ],
            'horaEntrada' => 'required|date_format:H:i',
            'horaSalida' => 'required|date_format:H:i|after:horaEntrada',
        ], [
            'carnetEmpleado.exists' => 'El empleado seleccionado no es valido.',
            'diaSemana.in' => 'El dia de la semana no es valido. Use: Lunes, Martes, Miercoles, etc.',
            'diaSemana.unique' => 'Este empleado ya tiene un horario asignado para ese dia.',
            'horaSalida.after' => 'La hora de salida debe ser posterior a la de entrada.'
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        $usuarioA = Auth::id() ?? 1;

        DB::table('thorariolaborales')->insert([
            'carnetEmpleado' => $request->carnetEmpleado,
            'diaSemana' => $request->diaSemana,
            'horaEntradaEsperada' => $request->horaEntrada,
            'horaSalidaEsperada' => $request->horaSalida,
            'estadoA' => 1,
            'usuarioA' => $usuarioA,
            'fechaA' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Horario registrado exitosamente.']);
    }

    public function update(Request $request, $id)
    {
        $horarioActual = DB::table('thorariolaborales')->where('idHorario', $id)->first();
        if (!$horarioActual) {
            return response()->json(['success' => false, 'message' => 'El horario que intentas editar no existe.'], 404);
        }

        $usuario = session('usuario');
        if ($usuario->idRol != 1) {
            $this->authorizeSucursal($usuario, $horarioActual->carnetEmpleado);
        }

        $validator = Validator::make($request->all(), [
            'diaSemana' => [
                'required',
                'string',
                'in:Lunes,Martes,Miercoles,Jueves,Viernes,Sabado,Domingo',
                Rule::unique('thorariolaborales')->where(function ($query) use ($horarioActual) {
                    return $query->where('carnetEmpleado', $horarioActual->carnetEmpleado)
                                 ->where('estadoA', 1);
                })->ignore($id, 'idHorario'),
            ],
            'horaEntrada' => 'required|date_format:H:i',
            'horaSalida' => 'required|date_format:H:i|after:horaEntrada',
        ], [
            'diaSemana.in' => 'El dia de la semana no es valido. Use: Lunes, Martes, Miercoles, etc.',
            'diaSemana.unique' => 'Este empleado ya tiene un horario asignado para ese dia.',
            'horaSalida.after' => 'La hora de salida debe ser posterior a la de entrada.'
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        $usuarioA = Auth::id() ?? 1;

        DB::table('thorariolaborales')->where('idHorario', $id)->update([
            'diaSemana' => $request->diaSemana,
            'horaEntradaEsperada' => $request->horaEntrada,
            'horaSalidaEsperada' => $request->horaSalida,
            'usuarioA' => $usuarioA,
            'fechaA' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Horario actualizado.']);
    }

    public function destroy(Request $request, $id)
    {
        $horario = DB::table('thorariolaborales')->where('idHorario', $id)->first();
        if (!$horario) {
            return response()->json(['success' => false, 'message' => 'El horario no existe.'], 404);
        }

        $usuario = session('usuario');
        if ($usuario->idRol != 1) {
            $this->authorizeSucursal($usuario, $horario->carnetEmpleado);
        }

        $usuarioA = Auth::id() ?? 1;
        DB::table('thorariolaborales')->where('idHorario', $id)->update([
            'estadoA' => 0,
            'usuarioA' => $usuarioA,
            'fechaA' => now()
        ]);
        return response()->json(['success' => true, 'message' => 'Horario dado de baja.']);
    }

    private function authorizeSucursal($usuario, $carnetEmpleado)
    {
        $empleado = DB::table('templeados')->where('idUsuario', $usuario->idUsuario)->first();
        $target = DB::table('templeados')->where('carnetEmpleado', $carnetEmpleado)->first();
        if (!$empleado || !$target || $empleado->idSucursal != $target->idSucursal) {
            abort(403, 'No tienes permiso para gestionar horarios de este empleado.');
        }
    }
}

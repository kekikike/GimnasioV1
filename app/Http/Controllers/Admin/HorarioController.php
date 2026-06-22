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
        $empleados = DB::table('templeados as e')
            ->join('tusuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->where('e.estadoA', 1)
            ->select('e.carnetEmpleado', 'u.nombre1', 'u.apellido1')
            ->get();
        return view('admin.horarios', compact('empleados'));
    }

    public function listar($carnetEmpleado)
    {
        // Tabla correcta: thorariolaborales
        $horarios = DB::table('thorariolaborales')
            ->where('carnetEmpleado', $carnetEmpleado)
            ->where('estadoA', 1)
            ->select('idHorario', 'carnetEmpleado', 'diaSemana', 'horaEntradaEsperada as horaEntrada', 'horaSalidaEsperada as horaSalida')
            ->get();
        return response()->json($horarios);
    }

    public function store(Request $request)
    {
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
            'carnetEmpleado.exists' => 'El empleado seleccionado no es válido.',
            'diaSemana.in' => 'El día de la semana no es válido. Use: Lunes, Martes, Miercoles, etc.',
            'diaSemana.unique' => 'Este empleado ya tiene un horario asignado para ese día.',
            'horaSalida.after' => 'La hora de salida debe ser posterior a la de entrada.'
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        $usuarioA = Auth::id() ?? 1;

        DB::table('thorariolaborales')->insert([
            'carnetEmpleado' => $request->carnetEmpleado,
            'diaSemana' => $request->diaSemana,
            'horaEntradaEsperada' => $request->horaEntrada, // Nombre real de la BD
            'horaSalidaEsperada' => $request->horaSalida,   // Nombre real de la BD
            'estadoA' => 1,
            'usuarioA' => $usuarioA,
            'fechaA' => now(),
        ]);
        return response()->json(['success' => true, 'message' => '✅ Horario registrado exitosamente.']);
    }

    public function update(Request $request, $id)
    {
        // Obtenemos el carnet del empleado desde el horario que se está editando
        $horarioActual = DB::table('thorariolaborales')->where('idHorario', $id)->first();
        if (!$horarioActual) {
            return response()->json(['success' => false, 'message' => 'El horario que intentas editar no existe.'], 404);
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
            'diaSemana.in' => 'El día de la semana no es válido. Use: Lunes, Martes, Miercoles, etc.',
            'diaSemana.unique' => 'Este empleado ya tiene un horario asignado para ese día.',
            'horaSalida.after' => 'La hora de salida debe ser posterior a la de entrada.'
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        $usuarioA = Auth::id() ?? 1;

        DB::table('thorariolaborales')->where('idHorario', $id)->update([
            'diaSemana' => $request->diaSemana,
            'horaEntradaEsperada' => $request->horaEntrada,
            'horaSalidaEsperada' => $request->horaSalida,
            'usuarioA' => $usuarioA, // En este proyecto, se usa usuarioA y fechaA para modificar
            'fechaA' => now(),   // En un proyecto nuevo, serían usuarioM y fechaM
        ]);
        return response()->json(['success' => true, 'message' => '✅ Horario actualizado.']);
    }

    public function destroy(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        DB::table('thorariolaborales')->where('idHorario', $id)->update([
            'estadoA' => 0,
            'usuarioA' => $usuarioA,
            'fechaA' => now()
        ]);
        return response()->json(['success' => true, 'message' => 'Horario dado de baja.']);
    }
}
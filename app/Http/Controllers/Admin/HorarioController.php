<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

        // Lógica de seguridad para limitar por sucursal
        if ($usuario && $usuario->idRol != 1) {
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
        // Se quitaron las reglas estrictas de date_format y exists que causaban el falso 422
        $validator = Validator::make($request->all(), [
            'carnetEmpleado' => 'required',
            'diaSemana'      => 'required',
            'horaEntrada'    => 'required',
            'horaSalida'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Convertimos las horas a tiempo real para compararlas sin importar el formato del navegador
        $tEntrada = strtotime($request->horaEntrada);
        $tSalida = strtotime($request->horaSalida);

        if ($tSalida <= $tEntrada) {
            return response()->json(['success' => false, 'errors' => ['horaSalida' => ['La hora de salida debe ser posterior a la de entrada.']]], 422);
        }

        // Formateamos para que la base de datos lo entienda perfecto (H:i:s)
        $horaEntradaDB = date('H:i:s', $tEntrada);
        $horaSalidaDB = date('H:i:s', $tSalida);

        $choque = DB::table('thorariolaborales')
            ->where('carnetEmpleado', $request->carnetEmpleado)
            ->where('diaSemana', $request->diaSemana)
            ->where('estadoA', 1)
            ->where('horaEntradaEsperada', '<', $horaSalidaDB)
            ->where('horaSalidaEsperada', '>', $horaEntradaDB)
            ->exists();

        if ($choque) {
            return response()->json(['success' => false, 'errors' => ['horaEntrada' => ['Este turno choca o se cruza con otro horario que ya le asignaste este día.']]], 422);
        }

        $usuarioA = Auth::id() ?? 1;

        DB::table('thorariolaborales')->insert([
            'carnetEmpleado'      => $request->carnetEmpleado,
            'diaSemana'           => $request->diaSemana,
            'horaEntradaEsperada' => $horaEntradaDB,
            'horaSalidaEsperada'  => $horaSalidaDB,
            'estadoA'             => 1,
            'usuarioA'            => $usuarioA,
            'fechaA'              => now(),
        ]);
        
        return response()->json(['success' => true, 'message' => '✅ Turno registrado exitosamente.']);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'carnetEmpleado' => 'required',
            'diaSemana'      => 'required',
            'horaEntrada'    => 'required',
            'horaSalida'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $tEntrada = strtotime($request->horaEntrada);
        $tSalida = strtotime($request->horaSalida);

        if ($tSalida <= $tEntrada) {
            return response()->json(['success' => false, 'errors' => ['horaSalida' => ['La hora de salida debe ser posterior a la de entrada.']]], 422);
        }

        $horaEntradaDB = date('H:i:s', $tEntrada);
        $horaSalidaDB = date('H:i:s', $tSalida);

        $choque = DB::table('thorariolaborales')
            ->where('idHorario', '!=', $id)
            ->where('carnetEmpleado', $request->carnetEmpleado)
            ->where('diaSemana', $request->diaSemana)
            ->where('estadoA', 1)
            ->where('horaEntradaEsperada', '<', $horaSalidaDB)
            ->where('horaSalidaEsperada', '>', $horaEntradaDB)
            ->exists();

        if ($choque) {
            return response()->json(['success' => false, 'errors' => ['horaEntrada' => ['Este cambio choca con otro turno existente para este día.']]], 422);
        }

        $usuarioA = Auth::id() ?? 1;

        DB::table('thorariolaborales')->where('idHorario', $id)->update([
            'diaSemana'           => $request->diaSemana,
            'horaEntradaEsperada' => $horaEntradaDB,
            'horaSalidaEsperada'  => $horaSalidaDB,
            'usuarioA'            => $usuarioA,
            'fechaA'              => now(),
        ]);
        
        return response()->json(['success' => true, 'message' => '✅ Horario actualizado con éxito.']);
    }

    public function destroy(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        DB::table('thorariolaborales')->where('idHorario', $id)->update([
            'estadoA'  => 0,
            'usuarioA' => $usuarioA,
            'fechaA'   => now()
        ]);
        return response()->json(['success' => true, 'message' => '🗑️ Turno eliminado correctamente.']);
    }
}
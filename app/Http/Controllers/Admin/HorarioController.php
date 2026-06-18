<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HorarioController extends Controller
{
    // Asumimos que existirá una vista para gestionar horarios
    public function index()
    {
        // Podrías necesitar listar empleados para seleccionar uno
        $empleados = DB::select('CALL sp_TEmpleados_Select()');
        return view('admin.horarios', compact('empleados'));
    }

    // Listar horarios de un empleado específico
    public function listar($idEmpleado)
    {
        // Debes crear el SP: sp_THorarios_SelectPorEmpleado(?)
        $horarios = DB::select('CALL sp_THorarios_SelectPorEmpleado(?)', [$idEmpleado]);
        return response()->json($horarios);
    }

    // Registrar un nuevo horario
    public function store(Request $request)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        // Debes crear el SP: sp_THorarios_Insert(?, ?, ?, ?, ?, ?)
        DB::statement('CALL sp_THorarios_Insert(?, ?, ?, ?, ?, ?)', [
            $request->idEmpleado,
            $request->diaSemana,
            $request->horaEntrada,
            $request->horaSalida,
            $usuarioA,
            $ip
        ]);

        return response()->json(['success' => true, 'message' => 'Horario registrado.']);
    }

    // Actualizar un horario existente
    public function update(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        try {
            // Debes crear el SP: sp_THorarios_Update(?, ?, ?, ?, ?, ?)
            DB::statement('CALL sp_THorarios_Update(?, ?, ?, ?, ?, ?)', [
                $id, // id del horario a actualizar
                $request->diaSemana,
                $request->horaEntrada,
                $request->horaSalida,
                $usuarioA,
                $ip
            ]);
            return response()->json(['success' => true, 'message' => 'Horario actualizado.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }


    // Eliminar un horario
    public function destroy(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        try {
            // Debes crear el SP: sp_THorarios_Delete(?, ?, ?)
            DB::statement('CALL sp_THorarios_Delete(?, ?, ?)', [$id, $usuarioA, $ip]);
            return response()->json(['success' => true, 'message' => 'Horario eliminado.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AsistenciaController extends Controller
{
    // Marcar la hora de entrada de un empleado
    public function registrarEntrada(Request $request)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        // Debes crear el SP: sp_TAsistenciasPersonal_Insert(?, ?, ?)
        // Este SP debería registrar la hora de entrada actual.
        DB::statement('CALL sp_TAsistenciasPersonal_Insert(?, ?, ?)', [
            $request->idEmpleado, // ID del empleado que marca asistencia
            $usuarioA,
            $ip
        ]);

        return response()->json(['success' => true, 'message' => 'Entrada registrada.']);
    }

    // Marcar la hora de salida
    public function registrarSalida(Request $request, $idAsistencia)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        // Debes crear el SP: sp_TAsistenciasPersonal_UpdateSalida(?, ?, ?)
        // Este SP busca el registro de asistencia por su ID y actualiza la hora de salida.
        DB::statement('CALL sp_TAsistenciasPersonal_UpdateSalida(?, ?, ?)', [
            $idAsistencia,
            $usuarioA,
            $ip
        ]);

        return response()->json(['success' => true, 'message' => 'Salida registrada.']);
    }
}
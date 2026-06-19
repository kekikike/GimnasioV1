<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PersonalController extends Controller
{
    // 1. Cargar la interfaz gráfica
    public function index()
    {
        $roles = DB::select('CALL sp_TRoles_Select()');
        $sucursales = DB::select('CALL sp_TSucursales_Select()');
        return view('admin.personal', compact('roles', 'sucursales'));
    }

    // 2. Listar
    public function listar()
    {
        $empleados = DB::select('CALL sp_TEmpleados_GetAllWithDetails()');
        return response()->json($empleados);
    }

    // 3. Registrar (Con Bypass al SP de la Base de Datos)
    public function store(Request $request)
    {
        $usuarioA = Auth::id() ?? 1; 
        $ip = $request->ip();

        DB::beginTransaction(); 
        try {
            // A. Crear TUsuarios
            $usuario = DB::select('CALL sp_TUsuarios_Insert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->idRol,
                $request->nombre1,
                null, 
                $request->apellido1,
                null, 
                $request->correo,
                $request->telefono,
                bcrypt($request->contrasena), 
                1, 
                $usuarioA,
                $ip
            ]);

            $idUsuario = $usuario[0]->idUsuario ?? $usuario[0]->id ?? 0;

            // B. Crear TEmpleados via SP
            DB::select('CALL sp_TEmpleados_Insert(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->carnetEmpleado,
                $idUsuario,
                $request->idSucursal,
                $request->sueldo,
                $request->especialidad ?? 'General',
                $request->fechaContratoInicio,
                null,
                $usuarioA,
                $ip
            ]);

            DB::commit(); 
            return response()->json(['success' => true, 'message' => '✅ Personal registrado exitosamente.']);
            
        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }

    // 4. Actualizar
    public function update(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        DB::beginTransaction();
        try {
            $usuarioActual = DB::select('CALL sp_TUsuarios_SelectById(?)', [$request->idUsuario])[0] ?? null;
            $contrasena = $request->contrasena ? bcrypt($request->contrasena) : $usuarioActual->contrasena;

            DB::statement('CALL sp_TUsuarios_Update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->idUsuario,
                $request->idRol,
                $request->nombre1,
                null,
                $request->apellido1,
                null,
                $request->correo,
                $request->telefono,
                $contrasena,
                1,
                $usuarioA,
                $ip
            ]);

            DB::statement('CALL sp_TEmpleados_Update(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $id, 
                $request->idUsuario,
                $request->idSucursal,
                $request->sueldo,
                'General', // especialidad
                $request->fechaContratoInicio,
                null, 
                $usuarioA,
                $ip
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => '✅ Información del empleado actualizada.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }

    // 5. Eliminar
    public function destroy(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        try {
            DB::statement('CALL sp_TEmpleados_Delete(?, ?, ?)', [$id, $usuarioA, $ip]);
            return response()->json(['success' => true, 'message' => 'Empleado dado de baja.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }
}
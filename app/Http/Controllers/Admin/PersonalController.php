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

    // 2. Listar uniendo las tablas (Nombres de tabla en minúsculas adaptados a migraciones)
    public function listar()
    {
        $empleados = DB::select("
            SELECT e.carnetEmpleado, e.idUsuario, e.idSucursal, e.sueldo, e.fechaContratoInicio,
                   u.idRol, u.nombre1, u.apellido1, u.correo, u.telefono,
                   r.nombreRol, s.nombre as nombreSucursal
            FROM templeados e
            INNER JOIN tusuarios u ON e.idUsuario = u.idUsuario
            INNER JOIN troles r ON u.idRol = r.idRol
            INNER JOIN tsucursales s ON e.idSucursal = s.idSucursal
            WHERE e.estadoA = 1
        ");
        return response()->json($empleados);
    }

    // 3. Registrar (Con Bypass protegido para migraciones)
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

            if ($idUsuario == 0) {
                throw new \Exception("No se pudo obtener el ID del usuario.");
            }

            // B. Crear TEmpleados (Añadido el campo 'fechaA' obligatorio por migraciones)
            DB::table('templeados')->insert([
                'carnetEmpleado'      => $request->carnetEmpleado,
                'idUsuario'           => $idUsuario,
                'idSucursal'          => $request->idSucursal,
                'sueldo'              => $request->sueldo,
                'especialidad'        => 1, 
                'fechaContratoInicio' => $request->fechaContratoInicio,
                'fechaContratoFin'    => null,
                'estadoA'             => 1,
                'fechaA'              => now(), // <--- Soluciona el error de DEFAULT
                'usuarioA'            => $usuarioA
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
            $usuarioActual = DB::table('tusuarios')->where('idUsuario', $request->idUsuario)->first();
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
                1, // <--- ¡Cambiamos 'General' por 1 para que las migraciones de Kike no exploten!
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

    // 5. Eliminar (Dar de baja)
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
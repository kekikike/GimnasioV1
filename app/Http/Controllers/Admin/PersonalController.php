<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PersonalController extends Controller
{
    public function index()
    {
        $roles = DB::select('CALL sp_TRoles_Select()');
        $sucursales = DB::select('CALL sp_TSucursales_Select()');
        return view('admin.personal', compact('roles', 'sucursales'));
    }

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

    public function store(Request $request)
    {
        $socioRoleId = DB::table('troles')->where('nombreRol', 'Socio')->value('idRol');

        // Validaciones estrictas RF3
        $validator = Validator::make($request->all(), [
            'idRol'               => ['required', 'integer', Rule::notIn([$socioRoleId])],
            'nombre1'             => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellido1'           => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'correo'              => 'required|email|unique:tusuarios,correo',
            'telefono'            => 'required|numeric|digits_between:7,15',
            'contrasena'          => 'required|string|min:8',
            'carnetEmpleado'      => 'required|numeric|max:2147483647|unique:templeados,carnetEmpleado', // Se cambia a numeric y se limita al máximo de un INT
            'idSucursal'          => 'required|integer|exists:tsucursales,idSucursal',
            'sueldo'              => 'required|numeric|min:0',
            'fechaContratoInicio' => 'required|date|before_or_equal:today', // No puede ser en el futuro
        ], [
            'idRol.not_in' => 'No se puede registrar un Socio desde este formulario.',
            'nombre1.regex' => 'El nombre solo puede contener letras y espacios.',
            'apellido1.regex' => 'El apellido solo puede contener letras y espacios.',
            'correo.unique' => 'Este correo electrónico ya está en uso.',
            'contrasena.confirmed' => 'Las contraseñas no coinciden.',
            'telefono.digits_between' => 'El teléfono debe tener entre 7 y 15 dígitos.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'carnetEmpleado.unique' => 'Este carnet ya está registrado en el sistema.',
            'carnetEmpleado.max' => 'El número de carnet es demasiado grande para el sistema.',
            'fechaContratoInicio.before_or_equal' => 'La fecha de inicio no puede ser en el futuro.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $usuarioA = Auth::id() ?? 1; 
        $ip = $request->ip();

        DB::beginTransaction(); 
        try {
            // Se reemplaza el Stored Procedure por un Insert directo de Laravel para mayor fiabilidad y mantenibilidad.
            // El SP `sp_TUsuarios_Insert` no devolvía un ID de forma consistente.
            $idUsuario = DB::table('tusuarios')->insertGetId([
                'idRol'      => $request->idRol,
                'nombre1'    => $request->nombre1,
                'nombre2'    => null,
                'apellido1'  => $request->apellido1,
                'apellido2'  => null,
                'correo'     => $request->correo,
                'telefono'   => $request->telefono,
                'contrasena' => bcrypt($request->contrasena),
                'estadoA'    => 1,
                'usuarioA'   => $usuarioA,
                'fechaA'     => now(),
                // El SP original recibía una IP, que probablemente se usaba para auditoría.
                // Si la tabla 'tusuarios' tiene una columna para IP, se debe añadir aquí.
            ]);

            if (!$idUsuario) throw new \Exception("No se pudo crear el registro de usuario.");

            DB::table('templeados')->insert([
                'carnetEmpleado'      => $request->carnetEmpleado,
                'idUsuario'           => $idUsuario,
                'idSucursal'          => $request->idSucursal,
                'sueldo'              => $request->sueldo,
                'especialidad'        => 1, 
                'fechaContratoInicio' => $request->fechaContratoInicio,
                'fechaContratoFin'    => null,
                'estadoA'             => 1,
                'fechaA'              => now(),
                'usuarioA'            => $usuarioA
            ]);

            DB::commit(); 
            return response()->json(['success' => true, 'message' => '✅ Personal registrado exitosamente.']);
            
        } catch (\Exception $e) {
            DB::rollBack(); 
            \Log::error('Error en PersonalController@store: ' . $e->getMessage() . ' en la línea ' . $e->getLine());
            // Proporcionar un mensaje de error más descriptivo
            $errorMessage = 'Error al registrar al personal. ';
            // En modo debug, mostrar el error real para facilitar la depuración
            if (config('app.debug')) { $errorMessage .= 'Detalle: ' . $e->getMessage(); }
            return response()->json(['success' => false, 'message' => $errorMessage], 500);
        }
    }

    // 4. Actualizar información del empleado
    public function update(Request $request, $id)
    {
        // Solución al texto vacío: Si la contraseña viene vacía, la volvemos NULL para que 'nullable' actúe perfectamente
        if ($request->input('contrasena') === '') {
            $request->merge([
                'contrasena' => null, 
                'contrasena_confirmation' => null
            ]);
        }

        $socioRoleId = DB::table('troles')->where('nombreRol', 'Socio')->value('idRol');

        $validator = Validator::make($request->all(), [
            'idUsuario'           => 'required|integer|exists:tusuarios,idUsuario',
            'idRol'               => ['required', 'integer', Rule::notIn([$socioRoleId])],
            'nombre1'             => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellido1'           => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'correo'              => 'required|email|unique:tusuarios,correo,' . $request->idUsuario . ',idUsuario',
            'telefono'            => 'required|numeric|digits_between:7,15',
            'contrasena'          => 'nullable|string|min:8',
            'idSucursal'          => 'required|integer|exists:tsucursales,idSucursal',
            'sueldo'              => 'required|numeric|min:0',
            'fechaContratoInicio' => 'required|date|before_or_equal:today',
        ], [
            'idRol.not_in' => 'No se puede asignar el rol de Socio a un empleado.',
            'nombre1.regex' => 'El nombre solo puede contener letras.',
            'apellido1.regex' => 'El apellido solo puede contener letras.',
            'correo.unique' => 'El correo electrónico ya está en uso.',
            'telefono.digits_between' => 'El teléfono debe tener entre 7 y 15 dígitos.',
            'fechaContratoInicio.before_or_equal' => 'La fecha no puede ser en el futuro.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        DB::beginTransaction();
        try {
            $usuarioActual = DB::table('tusuarios')->where('idUsuario', $request->idUsuario)->first();

            // Se reemplaza el SP por un Update directo para consistencia y claridad.
            $updateData = [
                'idRol'      => $request->idRol,
                'nombre1'    => $request->nombre1,
                'apellido1'  => $request->apellido1,
                'correo'     => $request->correo,
                'telefono'   => $request->telefono,
                'usuarioA'   => $usuarioA,
                'fechaA'     => now(),
            ];

            // Actualizar la contraseña solo si se proporciona una nueva
            if ($request->filled('contrasena')) {
                $updateData['contrasena'] = bcrypt($request->contrasena);
            }

            DB::table('tusuarios')->where('idUsuario', $request->idUsuario)->update($updateData);


            // CORRECCIÓN DE COLUMNAS: Cambiamos usuarioM y fechaM por usuarioA y fechaA para acoplarnos a las tablas de Kike
            DB::table('templeados')->where('carnetEmpleado', $id)->update([
                'idSucursal'          => $request->idSucursal,
                'sueldo'              => $request->sueldo,
                'especialidad'        => $request->especialidad ?? 1,
                'fechaContratoInicio' => $request->fechaContratoInicio,
                'usuarioA'            => $usuarioA, // <-- Corregido
                'fechaA'              => now(),     // <-- Corregido
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => '✅ Información del empleado actualizada.']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en PersonalController@update: ' . $e->getMessage() . ' en la línea ' . $e->getLine());
            // Proporcionar un mensaje de error más descriptivo
            $errorMessage = 'Error interno al actualizar. ';
            if (config('app.debug')) { $errorMessage .= 'Detalle: ' . $e->getMessage(); }
            return response()->json(['success' => false, 'message' => $errorMessage], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        try {
            DB::statement('CALL sp_TEmpleados_Delete(?, ?, ?)', [$id, $usuarioA, $ip]);
            return response()->json(['success' => true, 'message' => 'Empleado dado de baja.']);
        } catch (\Exception $e) {
            \Log::error('Error en PersonalController@destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al dar de baja.'], 500);
        }
    }
}
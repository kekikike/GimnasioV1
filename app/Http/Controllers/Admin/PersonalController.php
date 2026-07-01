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
        $usuario = session('usuario');
        $adminCarnet = DB::table('templeados')->where('idUsuario', $usuario->idUsuario)->value('carnetEmpleado');
        return view('admin.personal', compact('roles', 'sucursales', 'adminCarnet'));
    }

    public function listar()
    {
        $empleados = DB::select("
            SELECT e.carnetEmpleado, e.idUsuario, e.idSucursal, e.fechaContratoInicio, e.fechaContratoFin,
                   u.idRol, u.nombre1, u.nombre2, u.apellido1, u.apellido2, u.correo, u.telefono,
                   r.nombreRol, s.nombre as nombreSucursal
            FROM templeados e
            INNER JOIN tusuarios u ON e.idUsuario = u.idUsuario
            INNER JOIN troles r ON u.idRol = r.idRol
            INNER JOIN tsucursales s ON e.idSucursal = s.idSucursal
            WHERE e.estadoA = 1 AND e.carnetEmpleado != '1000'
        ");
        return response()->json($empleados);
    }

    public function listarInactivos()
    {
        $empleados = DB::select("
            SELECT e.carnetEmpleado, e.idUsuario, e.idSucursal, e.fechaContratoInicio, e.fechaContratoFin,
                   u.idRol, u.nombre1, u.nombre2, u.apellido1, u.apellido2, u.correo, u.telefono,
                   r.nombreRol, s.nombre as nombreSucursal
            FROM templeados e
            INNER JOIN tusuarios u ON e.idUsuario = u.idUsuario
            INNER JOIN troles r ON u.idRol = r.idRol
            INNER JOIN tsucursales s ON e.idSucursal = s.idSucursal
            WHERE e.estadoA = 0 AND e.carnetEmpleado != '1000'
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
            'telefono'            => 'required|numeric|digits_between:7,8',
            'contrasena'          => 'required|string|min:8',
            'carnetEmpleado'      => 'required|numeric|max:2147483647|unique:templeados,carnetEmpleado', // Se cambia a numeric y se limita al máximo de un INT
            'idSucursal'          => 'required|integer|exists:tsucursales,idSucursal',
            'fechaContratoInicio' => 'required|date|before_or_equal:today', // No puede ser en el futuro
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'idRol.not_in' => 'No se puede registrar un Socio desde este formulario.',
            'nombre1.required' => 'El primer nombre es obligatorio.',
            'nombre1.regex' => 'El nombre solo puede contener letras y espacios.',
            'nombre1.max' => 'El nombre no debe exceder 50 caracteres.',
            'apellido1.required' => 'El apellido paterno es obligatorio.',
            'apellido1.regex' => 'El apellido solo puede contener letras y espacios.',
            'apellido1.max' => 'El apellido no debe exceder 50 caracteres.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Ingrese un correo electrónico válido.',
            'correo.unique' => 'Este correo electrónico ya está en uso.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.numeric' => 'El teléfono solo debe contener números.',
            'telefono.digits_between' => 'El teléfono debe tener entre 7 y 8 dígitos.',
            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'carnetEmpleado.required' => 'El número de carnet es obligatorio.',
            'carnetEmpleado.numeric' => 'El carnet solo debe contener números.',
            'carnetEmpleado.unique' => 'Este carnet ya está registrado en el sistema.',
            'carnetEmpleado.max' => 'El número de carnet es demasiado grande para el sistema.',
            'idSucursal.required' => 'Debe seleccionar una sucursal.',
            'idSucursal.exists' => 'La sucursal seleccionada no es válida.',
            'fechaContratoInicio.required' => 'La fecha de inicio es obligatoria.',
            'fechaContratoInicio.date' => 'Ingrese una fecha válida.',
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
                'nombre2'    => $request->nombre2,
                'apellido1'  => $request->apellido1,
                'apellido2'  => $request->apellido2,
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

            $carnet = $request->carnetEmpleado;

            DB::table('templeados')->insert([
                'carnetEmpleado'      => $carnet,
                'idUsuario'           => $idUsuario,
                'idSucursal'          => $request->idSucursal,
                'fechaContratoInicio' => $request->fechaContratoInicio,
                'fechaContratoFin'    => null,
                'estadoA'             => 1,
                'fechaA'              => now(),
                'usuarioA'            => $usuarioA
            ]);

            DB::table('tauditorias')->insert([
                'tablaNombre' => 'templeados',
                'registroId' => $carnet,
                'accion' => 'I',
                'campo' => 'estadoA',
                'valorAnterior' => null,
                'valorNuevo' => '1',
                'usuarioA' => $usuarioA,
                'fechaA' => now(),
                'direccionIP' => $ip,
                'detalles' => "Registro de empleado: {$request->nombre1} {$request->apellido1}, carnet {$carnet}",
            ]);

            DB::commit(); 
            return response()->json(['success' => true, 'message' => 'Personal registrado exitosamente.']);
            
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
            'telefono'            => 'required|numeric|digits_between:7,8',
            'contrasena'          => 'nullable|string|min:8',
            'idSucursal'          => 'required|integer|exists:tsucursales,idSucursal',
            'fechaContratoInicio' => 'required|date|before_or_equal:today',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'idUsuario.required' => 'Error de referencia del usuario.',
            'idUsuario.exists' => 'El usuario de referencia no existe.',
            'idRol.not_in' => 'No se puede asignar el rol de Socio a un empleado.',
            'nombre1.required' => 'El primer nombre es obligatorio.',
            'nombre1.regex' => 'El nombre solo puede contener letras.',
            'nombre1.max' => 'El nombre no debe exceder 50 caracteres.',
            'apellido1.required' => 'El apellido paterno es obligatorio.',
            'apellido1.regex' => 'El apellido solo puede contener letras.',
            'apellido1.max' => 'El apellido no debe exceder 50 caracteres.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Ingrese un correo electrónico válido.',
            'correo.unique' => 'El correo electrónico ya está en uso.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.numeric' => 'El teléfono solo debe contener números.',
            'telefono.digits_between' => 'El teléfono debe tener entre 7 y 8 dígitos.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'idSucursal.required' => 'Debe seleccionar una sucursal.',
            'idSucursal.exists' => 'La sucursal seleccionada no es válida.',
            'fechaContratoInicio.required' => 'La fecha de inicio es obligatoria.',
            'fechaContratoInicio.date' => 'Ingrese una fecha válida.',
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
                'nombre2'    => $request->nombre2,
                'apellido1'  => $request->apellido1,
                'apellido2'  => $request->apellido2,
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


            $oldEmpleado = DB::table('templeados')->where('carnetEmpleado', $id)->first();

            DB::table('templeados')->where('carnetEmpleado', $id)->update([
                'idSucursal'          => $request->idSucursal,
                'fechaContratoInicio' => $request->fechaContratoInicio,
                'usuarioA'            => $usuarioA,
                'fechaA'              => now(),
            ]);

            $cambios = [];
            if ($oldEmpleado->idSucursal != $request->idSucursal) {
                $cambios[] = 'idSucursal';
            }
            if ($oldEmpleado->fechaContratoInicio != $request->fechaContratoInicio) {
                $cambios[] = 'fechaContratoInicio';
            }
            if ($usuarioActual->idRol != $request->idRol) {
                $cambios[] = 'idRol';
            }

            if (!empty($cambios)) {
                DB::table('tauditorias')->insert([
                    'tablaNombre' => 'templeados',
                    'registroId' => $id,
                    'accion' => 'U',
                    'campo' => implode('|', $cambios),
                    'valorAnterior' => null,
                    'valorNuevo' => null,
                    'usuarioA' => $usuarioA,
                    'fechaA' => now(),
                    'direccionIP' => $ip,
                    'detalles' => "Actualizacion de empleado carnet {$id}",
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Informacion del empleado actualizada.']);
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
            $empleado = DB::table('templeados')->where('carnetEmpleado', $id)->first();
            if (!$empleado) {
                return response()->json(['success' => false, 'message' => 'Empleado no encontrado.'], 404);
            }

            // Verificar si tiene clases programadas o en curso
            $clasesFuturas = DB::table('TClaseGrupales')
                ->where('carnetEmpleado', $id)
                ->whereIn('estadoClase', ['Programada', 'Cursandose'])
                ->where('estadoA', 1)
                ->count();

            if ($clasesFuturas > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede dar de baja al empleado porque tiene {$clasesFuturas} clase(s) programada(s) o en curso."
                ], 422);
            }

            DB::table('templeados')->where('carnetEmpleado', $id)->update([
                'estadoA' => 0,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);

            DB::table('tusuarios')->where('idUsuario', $empleado->idUsuario)->update([
                'estadoA' => 0,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);

            DB::table('tauditorias')->insert([
                'tablaNombre' => 'templeados',
                'registroId' => $id,
                'accion' => 'D',
                'campo' => 'estadoA',
                'valorAnterior' => '1',
                'valorNuevo' => '0',
                'usuarioA' => $usuarioA,
                'fechaA' => now(),
                'direccionIP' => $ip,
                'detalles' => "Baja de empleado carnet {$id}",
            ]);

            return response()->json(['success' => true, 'message' => 'Empleado dado de baja.']);
        } catch (\Exception $e) {
            \Log::error('Error en PersonalController@destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al dar de baja.'], 500);
        }
    }

    public function acabarContrato(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        try {
            $empleado = DB::table('templeados')->where('carnetEmpleado', $id)->first();
            if (!$empleado) {
                return response()->json(['success' => false, 'message' => 'Empleado no encontrado.'], 404);
            }

            // Verificar si tiene clases programadas o en curso
            $clasesFuturas = DB::table('TClaseGrupales')
                ->where('carnetEmpleado', $id)
                ->whereIn('estadoClase', ['Programada', 'Cursandose'])
                ->where('estadoA', 1)
                ->count();

            if ($clasesFuturas > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede finalizar el contrato porque el empleado tiene {$clasesFuturas} clase(s) programada(s) o en curso."
                ], 422);
            }

            DB::table('templeados')->where('carnetEmpleado', $id)->update([
                'fechaContratoFin' => now()->toDateString(),
                'estadoA' => 0,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);

            DB::table('tusuarios')->where('idUsuario', $empleado->idUsuario)->update([
                'estadoA' => 0,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);

            DB::table('tauditorias')->insert([
                'tablaNombre' => 'templeados',
                'registroId' => $id,
                'accion' => 'U',
                'campo' => 'fechaContratoFin|estadoA',
                'valorAnterior' => ($empleado->fechaContratoFin ?? 'null') . '|1',
                'valorNuevo' => now()->toDateString() . '|0',
                'usuarioA' => $usuarioA,
                'fechaA' => now(),
                'direccionIP' => $ip,
                'detalles' => "Finalizacion de contrato empleado carnet {$id}",
            ]);

            return response()->json(['success' => true, 'message' => 'Contrato finalizado exitosamente.']);
        } catch (\Exception $e) {
            \Log::error('Error en PersonalController@acabarContrato: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al finalizar contrato.'], 500);
        }
    }

    public function reactivar(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        try {
            $empleado = DB::table('templeados')->where('carnetEmpleado', $id)->first();
            if (!$empleado) {
                return response()->json(['success' => false, 'message' => 'Empleado no encontrado.'], 404);
            }

            DB::table('templeados')->where('carnetEmpleado', $id)->update([
                'fechaContratoFin' => null,
                'estadoA' => 1,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);

            DB::table('tusuarios')->where('idUsuario', $empleado->idUsuario)->update([
                'estadoA' => 1,
                'fechaA' => now(),
                'usuarioA' => $usuarioA,
            ]);

            DB::table('tauditorias')->insert([
                'tablaNombre' => 'templeados',
                'registroId' => $id,
                'accion' => 'U',
                'campo' => 'fechaContratoFin|estadoA',
                'valorAnterior' => ($empleado->fechaContratoFin ?? 'null') . '|0',
                'valorNuevo' => 'null|1',
                'usuarioA' => $usuarioA,
                'fechaA' => now(),
                'direccionIP' => $ip,
                'detalles' => "Reactivacion de empleado carnet {$id}",
            ]);

            return response()->json(['success' => true, 'message' => 'Empleado reactivado exitosamente.']);
        } catch (\Exception $e) {
            \Log::error('Error en PersonalController@reactivar: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al reactivar empleado.'], 500);
        }
    }
}
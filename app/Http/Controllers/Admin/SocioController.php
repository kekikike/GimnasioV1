<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SocioController extends Controller
{
    public function index()
    {
        $sucursales = DB::select('CALL sp_TSucursales_Select()');
        return view('admin.socios', compact('sucursales'));
    }

    public function listar(Request $request)
    {
        try {
            // Como ya no hay codigoAcceso, enviamos el carnetSocio camuflado como codigoAcceso para que la vista de Vue no se rompa
            $query = DB::table('tsocios as s')
                ->join('tusuarios as u', 's.idUsuario', '=', 'u.idUsuario')
                ->select(
                    's.carnetSocio', 's.idUsuario', 's.estadoSocio', 's.carnetSocio AS codigoAcceso', 
                    'u.nombre1', 'u.apellido1', 'u.correo', 'u.telefono', 
                    's.direccion', 's.nombreContactoEmergencia as contacto_emergencia_nombre', 
                    's.telefonoContactoEmergencia as contacto_emergencia_telefono', 's.fotografiaUrl as foto_url'
                )
                ->where('u.estadoA', 1);

            if ($request->has('carnetSocio') && $request->carnetSocio != '') {
                $query->where('s.carnetSocio', 'LIKE', '%' . $request->carnetSocio . '%');
            }

            return response()->json($query->get());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al listar: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'carnetSocio'  => 'required|numeric|max:2147483647|confirmed|unique:tsocios,carnetSocio',
            'nombre1'      => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellido1'    => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'correo'       => 'required|email|unique:tusuarios,correo',
            'telefono'     => 'required|numeric|digits_between:7,15',
            'contrasena'   => 'required|string|min:8|confirmed',
            'direccion'    => 'nullable|string|max:255',
            'contacto_emergencia_nombre' => 'nullable|string|max:100',
            'contacto_emergencia_telefono' => 'nullable|numeric|digits_between:7,15',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
            'idPlan'       => 'required|integer|exists:tplanes,idPlan',
            'idSucursal'   => 'required|integer|exists:tsucursales,idSucursal',
        ], [
            'carnetSocio.max' => 'El número de carnet es demasiado grande para el sistema.',
            'carnetSocio.confirmed' => 'Los números de carnet no coinciden.',
            'carnetSocio.unique' => 'Este carnet ya está registrado.',
            'nombre1.regex' => 'El nombre solo puede contener letras y espacios.',
            'apellido1.regex' => 'El apellido solo puede contener letras y espacios.',
            'correo.unique' => 'Este correo ya está en uso.',
            'telefono.digits_between' => 'El teléfono debe tener entre 7 y 15 dígitos.',
            'contrasena.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        DB::beginTransaction();
        try {
            $rol = DB::table('troles')->where('nombreRol', 'Socio')->first();
            if (!$rol) {
                $idRol = DB::table('troles')->insertGetId(['nombreRol' => 'Socio', 'fechaA' => now(), 'usuarioA' => $usuarioA]);
            } else {
                $idRol = $rol->idRol;
            }

            // Se reemplaza el Stored Procedure por un Insert directo de Laravel para mayor fiabilidad.
            // El SP `sp_TUsuarios_Insert` no devolvía un ID de forma consistente.
            $idUsuario = DB::table('tusuarios')->insertGetId([
                'idRol'      => $idRol,
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
            ]);

            if (!$idUsuario) throw new \Exception("Error al crear el registro de usuario.");

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $ext = $request->file('foto')->getClientOriginalExtension();
                $nombreFoto = 'S-' . $request->carnetSocio . '.' . $ext;
                $fotoPath = $request->file('foto')->storeAs('fotos_socios', $nombreFoto, 'public');
            }

            // CORRECCIÓN: Eliminamos el 'codigoAcceso' porque Kike usa el CI para esto ahora
            DB::table('tsocios')->insert([
                'carnetSocio'  => $request->carnetSocio, 
                'idUsuario'    => $idUsuario,
                'direccion'    => $request->direccion,
                'nombreContactoEmergencia' => $request->contacto_emergencia_nombre,
                'telefonoContactoEmergencia' => $request->contacto_emergencia_telefono,
                'fotografiaUrl'=> $fotoPath,
                'estadoSocio'  => 'Activo', 
                'fechaA'       => now(), 
                'usuarioA'     => $usuarioA
            ]);

            $plan = DB::table('tplanes')->where('idPlan', $request->idPlan)->first();
            $duracion = $plan ? $plan->duracionDias : 30;

            DB::table('tmembresias')->insert([
                'idPlan'               => $request->idPlan,
                'carnetSocio'          => $request->carnetSocio, 
                'idSucursal'           => $request->idSucursal,
                'fechaInicioMembresia' => now()->format('Y-m-d'),
                'fechaFinMembresia'    => now()->addDays($duracion)->format('Y-m-d'),
                'estadoMembresia'      => 'Activa',
                'estadoA'              => 1,
                'fechaA'               => now(), 
                'usuarioA'             => $usuarioA
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => '✅ Socio registrado con éxito. El código de acceso es su CI: ' . $request->carnetSocio]);

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($fotoPath)) Storage::disk('public')->delete($fotoPath);
            \Log::error('Error en SocioController@store: ' . $e->getMessage() . ' en la línea ' . $e->getLine());
            $errorMessage = 'Error al registrar al socio. ';
            // En modo debug, mostrar el error real para facilitar la depuración
            if (config('app.debug')) { $errorMessage .= 'Detalle: ' . $e->getMessage(); }
            return response()->json(['success' => false, 'message' => $errorMessage], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if ($request->input('contrasena') === 'null' || $request->input('contrasena') === '') {
            $request->merge(['contrasena' => null, 'contrasena_confirmation' => null]);
        }

        $validator = Validator::make($request->all(), [
            'idUsuario'    => 'required|integer|exists:tusuarios,idUsuario',
            'nombre1'      => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellido1'    => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'correo'       => 'required|email|unique:tusuarios,correo,' . $request->idUsuario . ',idUsuario',
            'telefono'     => 'required|numeric|digits_between:7,15',
            'contrasena'   => 'nullable|string|min:8|confirmed',
            'direccion'    => 'nullable|string|max:255',
            'contacto_emergencia_nombre' => 'nullable|string|max:100',
            'contacto_emergencia_telefono' => 'nullable|numeric|digits_between:7,15',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        DB::beginTransaction();
        try {
            // Se obtiene el rol 'Socio' dinámicamente para evitar usar un ID hardcodeado como '4'.
            $rolSocio = DB::table('troles')->where('nombreRol', 'Socio')->first();
            if (!$rolSocio) {
                throw new \Exception("El rol 'Socio' no se encuentra en la base de datos.");
            }

            $usuarioActual = DB::table('tusuarios')->where('idUsuario', $request->idUsuario)->first();

            // Se reemplaza el SP por un Update directo para consistencia y claridad.
            $updateData = [
                'idRol'      => $rolSocio->idRol,
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

            $socioActual = DB::table('tsocios')->where('carnetSocio', $id)->first();
            $fotoPath = $socioActual->fotografiaUrl ?? null;
            
            if ($request->hasFile('foto')) {
                if ($fotoPath) Storage::disk('public')->delete($fotoPath);
                $ext = $request->file('foto')->getClientOriginalExtension();
                $nombreFoto = 'S-' . $id . '.' . $ext;
                $fotoPath = $request->file('foto')->storeAs('fotos_socios', $nombreFoto, 'public');
            }

            DB::table('tsocios')->where('carnetSocio', $id)->update([
                'direccion' => $request->direccion,
                'nombreContactoEmergencia' => $request->contacto_emergencia_nombre,
                'telefonoContactoEmergencia' => $request->contacto_emergencia_telefono,
                'fotografiaUrl' => $fotoPath,
                'fechaA' => now(), 
                'usuarioA' => $usuarioA
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => '✅ Información del socio actualizada.']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en SocioController@update: ' . $e->getMessage() . ' en la línea ' . $e->getLine());
            $errorMessage = 'Error al actualizar al socio. ';
            // En modo debug, mostrar el error real para facilitar la depuración
            if (config('app.debug')) { $errorMessage .= 'Detalle: ' . $e->getMessage(); }
            return response()->json(['success' => false, 'message' => $errorMessage], 500);
        }
    }

    public function congelar(Request $request, $id)
    {
        try {
            $socio = DB::table('tsocios')->where('carnetSocio', $id)->first();
            if (!$socio) return response()->json(['success' => false, 'message' => 'Socio no encontrado.'], 404);

            $nuevoEstado = ($socio->estadoSocio === 'Activo') ? 'Congelado' : 'Activo';
            DB::table('tsocios')->where('carnetSocio', $id)->update(['estadoSocio' => $nuevoEstado, 'fechaA' => now()]);

            $estadoMembresia = ($nuevoEstado === 'Activo') ? 'Activa' : 'Congelada';
            DB::table('tmembresias')->where('carnetSocio', $id)->where('estadoMembresia', '!=', 'Vencida')
                ->update(['estadoMembresia' => $estadoMembresia, 'fechaA' => now()]);

            $texto = $nuevoEstado == 'Congelado' ? 'Congelado' : 'Activo';
            return response()->json(['success' => true, 'message' => '✅ El estado del socio ahora es: ' . $texto]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno.'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip(); // Asegurarse de capturar la IP al inicio
        
        try {
            $socio = DB::table('tsocios')->where('carnetSocio', $id)->first();
            if ($socio) {
                DB::table('tusuarios')->where('idUsuario', $socio->idUsuario)->update(['estadoA' => 0, 'fechaA' => now(), 'usuarioA' => $usuarioA]);
            }
            DB::table('tmembresias')->where('carnetSocio', $id)->update(['estadoA' => 0, 'fechaA' => now()]);
            
            // 🔥 CÓDIGO DE AUDITORÍA INYECTADO AQUÍ 🔥
            DB::table('tauditorias')->insert([
                'tablaNombre'   => 'tsocios',
                'registroId'    => $id,
                'accion'        => 'DELETE', // Baja lógica
                'campo'         => 'estadoA',
                'valorAnterior' => '1',
                'valorNuevo'    => '0',
                'usuarioA'      => $usuarioA,
                'fechaA'        => now(),
                'direccionIP'   => $ip,
                'detalles'      => 'Baja manual de Socio y su Membresía desde el Panel'
            ]);

            return response()->json(['success' => true, 'message' => 'Socio dado de baja exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno.'], 500);
        }
    }
}
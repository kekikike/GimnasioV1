<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
            $query = DB::table('tsocios as s')
                ->join('tusuarios as u', 's.idUsuario', '=', 'u.idUsuario')
                ->leftJoin('tmembresias as m', function ($join) {
                    $join->on('s.carnetSocio', '=', 'm.carnetSocio')
                         ->where('m.estadoA', 1)
                         ->whereRaw('m.idMembresia = (SELECT MAX(m2.idMembresia) FROM tmembresias m2 WHERE m2.carnetSocio = s.carnetSocio AND m2.estadoA = 1)');
                })
                ->select(
                    's.carnetSocio', 's.idUsuario', 's.estadoSocio', 's.carnetSocio AS codigoAcceso',
                    'u.nombre1', 'u.nombre2', 'u.apellido1', 'u.apellido2', 'u.correo', 'u.telefono',
                    's.direccion', 's.observacionesMedicas',
                    's.nombreContactoEmergencia as contacto_emergencia_nombre',
                    's.telefonoContactoEmergencia as contacto_emergencia_telefono', 's.fotografiaUrl as foto_url',
                    'm.estadoMembresia', 'm.fechaFinMembresia', 'm.fechaCongelamiento'
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
            'carnetSocio'      => 'required|numeric|max:2147483647|unique:tsocios,carnetSocio',
            'nombre1'          => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'nombre2'          => 'nullable|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellidoPaterno'  => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellidoMaterno'  => 'nullable|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'correo'           => 'required|email|unique:tusuarios,correo',
            'telefono'         => 'required|numeric|digits_between:7,8',
            'contrasena'       => 'required|string|min:8',
            'direccion'        => 'nullable|string|max:255',
            'contacto_emergencia_nombre' => 'nullable|string|max:100',
            'contacto_emergencia_telefono' => 'nullable|numeric|digits_between:7,8',
            'observacionesMedicas' => 'nullable|string|max:255',
            'foto'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'carnetSocio.required' => 'El carnet de socio es obligatorio.',
            'carnetSocio.numeric' => 'El carnet de socio debe ser numerico.',
            'carnetSocio.unique' => 'Este carnet de socio ya esta registrado.',
            'nombre1.required' => 'El primer nombre es obligatorio.',
            'nombre1.regex' => 'El primer nombre solo puede contener letras.',
            'apellidoPaterno.required' => 'El apellido paterno es obligatorio.',
            'apellidoPaterno.regex' => 'El apellido paterno solo puede contener letras.',
            'correo.required' => 'El correo electronico es obligatorio.',
            'correo.email' => 'El correo electronico no es valido.',
            'correo.unique' => 'Este correo electronico ya esta registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.numeric' => 'El teléfono debe ser numérico.',
            'telefono.digits_between' => 'El teléfono debe tener entre 7 y 8 dígitos.',
            'contrasena.required' => 'La contraseña es obligatoria.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'contacto_emergencia_telefono.numeric' => 'El teléfono de emergencia debe ser numérico.',
            'contacto_emergencia_telefono.digits_between' => 'El teléfono de emergencia debe tener entre 7 y 8 dígitos.',
            'foto.image' => 'La foto debe ser una imagen.',
            'foto.mimes' => 'La foto debe ser JPG o PNG.',
            'foto.max' => 'La foto no debe superar 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $usuarioA = Auth::id() ?? 1;

        DB::beginTransaction();
        try {
            $rol = DB::table('troles')->where('nombreRol', 'Socio')->first();
            $idRol = $rol ? $rol->idRol : DB::table('troles')->insertGetId(['nombreRol' => 'Socio', 'fechaA' => now(), 'usuarioA' => $usuarioA]);

            $idUsuario = DB::table('tusuarios')->insertGetId([
                'idRol'      => $idRol,
                'nombre1'    => $request->nombre1,
                'nombre2'    => $request->nombre2,
                'apellido1'  => $request->apellidoPaterno,
                'apellido2'  => $request->apellidoMaterno,
                'correo'     => $request->correo,
                'telefono'   => $request->telefono,
                'contrasena' => bcrypt($request->contrasena),
                'estadoA'    => 1,
                'usuarioA'   => $usuarioA,
                'fechaA'     => now(),
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $ext = $request->file('foto')->getClientOriginalExtension();
                $nombreFoto = 'S-' . $request->carnetSocio . '.' . $ext;
                $fotoPath = $request->file('foto')->storeAs('fotos_socios', $nombreFoto, 'public');
            }

            DB::table('tsocios')->insert([
                'carnetSocio'  => $request->carnetSocio, 
                'idUsuario'    => $idUsuario,
                'direccion'    => $request->direccion,
                'observacionesMedicas' => $request->observacionesMedicas ?: 'Ninguna',
                'nombreContactoEmergencia' => $request->contacto_emergencia_nombre,
                'telefonoContactoEmergencia' => $request->contacto_emergencia_telefono,
                'fotografiaUrl'=> $fotoPath,
                'estadoSocio'  => 'Activo', 
                'strikes'      => 0,
                'fechaA'       => now(), 
                'usuarioA'     => $usuarioA
            ]);

            DB::table('tauditorias')->insert([
                'tablaNombre'   => 'tsocios',
                'registroId'    => $request->carnetSocio,
                'accion'        => 'I',
                'campo'         => 'carnetSocio|idUsuario|direccion|estadoSocio',
                'valorAnterior' => '|||',
                'valorNuevo'    => implode('|', [$request->carnetSocio, $idUsuario, $request->direccion ?? '', 'Activo']),
                'usuarioA'      => $usuarioA,
                'fechaA'        => now(),
                'direccionIP'   => $request->ip(),
                'detalles'      => 'Insercion de Socio'
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Socio registrado con exito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al registrar socio.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if ($request->input('contrasena') === 'null' || $request->input('contrasena') === '') {
            $request->merge(['contrasena' => null, 'contrasena_confirmation' => null]);
        }

        $validator = Validator::make($request->all(), [
            'idUsuario'        => 'required|integer|exists:tusuarios,idUsuario',
            'nombre1'          => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'nombre2'          => 'nullable|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellidoPaterno'  => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellidoMaterno'  => 'nullable|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'correo'           => 'required|email|unique:tusuarios,correo,' . $request->idUsuario . ',idUsuario',
            'telefono'         => 'required|numeric|digits_between:7,8',
            'contrasena'       => 'nullable|string|min:8',
            'direccion'        => 'nullable|string|max:255',
            'contacto_emergencia_nombre' => 'nullable|string|max:100',
            'contacto_emergencia_telefono' => 'nullable|numeric|digits_between:7,8',
            'observacionesMedicas' => 'nullable|string|max:255',
            'foto'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'nombre1.required' => 'El primer nombre es obligatorio.',
            'nombre1.regex' => 'El primer nombre solo puede contener letras.',
            'apellidoPaterno.required' => 'El apellido paterno es obligatorio.',
            'apellidoPaterno.regex' => 'El apellido paterno solo puede contener letras.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El correo electrónico no es válido.',
            'correo.unique' => 'Este correo electrónico ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.numeric' => 'El teléfono debe ser numérico.',
            'telefono.digits_between' => 'El teléfono debe tener entre 7 y 8 dígitos.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'contacto_emergencia_telefono.numeric' => 'El teléfono de emergencia debe ser numérico.',
            'contacto_emergencia_telefono.digits_between' => 'El teléfono de emergencia debe tener entre 7 y 8 dígitos.',
            'foto.image' => 'La foto debe ser una imagen.',
            'foto.mimes' => 'La foto debe ser JPG o PNG.',
            'foto.max' => 'La foto no debe superar 2MB.',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        $usuarioA = Auth::id() ?? 1;

        $usuarioViejo = DB::table('tusuarios')->where('idUsuario', $request->idUsuario)->first();
        $socioViejo = DB::table('tsocios')->where('carnetSocio', $id)->first();

        DB::beginTransaction();
        try {
            $updateData = [
                'nombre1'    => $request->nombre1,
                'nombre2'    => $request->nombre2,
                'apellido1'  => $request->apellidoPaterno,
                'apellido2'  => $request->apellidoMaterno,
                'correo'     => $request->correo,
                'telefono'   => $request->telefono,
                'usuarioA'   => $usuarioA,
                'fechaA'     => now(),
            ];

            if ($request->filled('contrasena')) {
                $updateData['contrasena'] = bcrypt($request->contrasena);
            }

            DB::table('tusuarios')->where('idUsuario', $request->idUsuario)->update($updateData);

            $fotoPath = $socioViejo->fotografiaUrl ?? null;
            
            if ($request->hasFile('foto')) {
                if ($fotoPath) Storage::disk('public')->delete($fotoPath);
                $ext = $request->file('foto')->getClientOriginalExtension();
                $fotoPath = $request->file('foto')->storeAs('fotos_socios', 'S-' . $id . '.' . $ext, 'public');
            }

            $socioUpdateData = [
                'direccion' => $request->direccion,
                'observacionesMedicas' => $request->observacionesMedicas ?: 'Ninguna',
                'nombreContactoEmergencia' => $request->contacto_emergencia_nombre,
                'telefonoContactoEmergencia' => $request->contacto_emergencia_telefono,
                'fotografiaUrl' => $fotoPath,
                'fechaA' => now(), 
                'usuarioA' => $usuarioA
            ];

            DB::table('tsocios')->where('carnetSocio', $id)->update($socioUpdateData);

            $campos = [];
            $viejos = [];
            $nuevos = [];
            foreach (['nombre1', 'nombre2', 'apellido1' => 'apellidoPaterno', 'apellido2' => 'apellidoMaterno', 'correo', 'telefono'] as $campoDb => $campoReq) {
                $reqKey = is_numeric($campoDb) ? $campoReq : $campoDb;
                $dbKey = is_numeric($campoDb) ? $campoReq : $campoDb;
                if (($usuarioViejo->$dbKey ?? '') !== ($request->$reqKey ?? '')) {
                    $campos[] = $dbKey;
                    $viejos[] = $usuarioViejo->$dbKey ?? '';
                    $nuevos[] = $request->$reqKey ?? '';
                }
            }
            if (($socioViejo->direccion ?? '') !== ($request->direccion ?? '')) {
                $campos[] = 'direccion'; $viejos[] = $socioViejo->direccion ?? ''; $nuevos[] = $request->direccion ?? '';
            }
            if (($socioViejo->nombreContactoEmergencia ?? '') !== ($request->contacto_emergencia_nombre ?? '')) {
                $campos[] = 'nombreContactoEmergencia'; $viejos[] = $socioViejo->nombreContactoEmergencia ?? ''; $nuevos[] = $request->contacto_emergencia_nombre ?? '';
            }
            if (($socioViejo->telefonoContactoEmergencia ?? '') !== ($request->contacto_emergencia_telefono ?? '')) {
                $campos[] = 'telefonoContactoEmergencia'; $viejos[] = $socioViejo->telefonoContactoEmergencia ?? ''; $nuevos[] = $request->contacto_emergencia_telefono ?? '';
            }
            if (($socioViejo->observacionesMedicas ?? '') !== ($request->observacionesMedicas ?? '')) {
                $campos[] = 'observacionesMedicas'; $viejos[] = $socioViejo->observacionesMedicas ?? ''; $nuevos[] = $request->observacionesMedicas ?? '';
            }

            if (!empty($campos)) {
                DB::table('tauditorias')->insert([
                    'tablaNombre'   => 'tsocios',
                    'registroId'    => $id,
                    'accion'        => 'U',
                    'campo'         => implode('|', $campos),
                    'valorAnterior' => implode('|', $viejos),
                    'valorNuevo'    => implode('|', $nuevos),
                    'usuarioA'      => $usuarioA,
                    'fechaA'        => now(),
                    'direccionIP'   => $request->ip(),
                    'detalles'      => 'Actualizacion de Socio'
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Informacion del socio actualizada.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar.'], 500);
        }
    }

    public function congelarMembresia(Request $request, $carnet)
    {
        try {
            $request->validate([
                'fechaCongelamiento' => 'required|date|after:today',
            ]);

            $membresia = DB::table('tmembresias')
                ->where('carnetSocio', $carnet)
                ->where('estadoMembresia', 'Activa')
                ->where('estadoA', 1)
                ->orderBy('idMembresia', 'DESC')
                ->first();

            if (!$membresia) {
                return response()->json(['success' => false, 'message' => 'No hay membresía activa para congelar.'], 422);
            }

            $diasCongelados = max(0, (int)((strtotime($request->fechaCongelamiento) - strtotime('today')) / 86400));
            $nuevaFechaFin = date('Y-m-d', strtotime($membresia->fechaFinMembresia . " + {$diasCongelados} days"));
            $usuarioA = Auth::id() ?? 1;

            DB::table('tmembresias')
                ->where('idMembresia', $membresia->idMembresia)
                ->update([
                    'fechaFinMembresia'  => DB::raw("DATE_ADD(fechaFinMembresia, INTERVAL {$diasCongelados} DAY)"),
                    'estadoMembresia'    => 'Congelada',
                    'fechaCongelamiento' => $request->fechaCongelamiento,
                    'fechaA'             => now(),
                    'usuarioA'           => $usuarioA,
                ]);

            DB::table('tsocios')
                ->where('carnetSocio', $carnet)
                ->update(['estadoSocio' => 'Congelado', 'fechaA' => now()]);

            DB::table('tauditorias')->insert([
                'tablaNombre'   => 'tmembresias',
                'registroId'    => $membresia->idMembresia,
                'accion'        => 'U',
                'campo'         => 'estadoMembresia|fechaCongelamiento|fechaFinMembresia',
                'valorAnterior' => "Activa||{$membresia->fechaFinMembresia}",
                'valorNuevo'    => "Congelada|{$request->fechaCongelamiento}|{$nuevaFechaFin}",
                'usuarioA'      => $usuarioA,
                'fechaA'        => now(),
                'direccionIP'   => $request->ip(),
                'detalles'      => 'Congelamiento de Membresia'
            ]);

            DB::table('tauditorias')->insert([
                'tablaNombre'   => 'tsocios',
                'registroId'    => $carnet,
                'accion'        => 'U',
                'campo'         => 'estadoSocio',
                'valorAnterior' => 'Activo',
                'valorNuevo'    => 'Congelado',
                'usuarioA'      => $usuarioA,
                'fechaA'        => now(),
                'direccionIP'   => $request->ip(),
                'detalles'      => 'Congelamiento de Socio por Membresia'
            ]);

            return response()->json(['success' => true, 'message' => "Membresia congelada hasta el {$request->fechaCongelamiento}. Se agregaron {$diasCongelados} dia(s) al vencimiento."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al congelar: ' . $e->getMessage()], 500);
        }
    }

    public function activarMembresia(Request $request, $carnet)
    {
        try {
            $membresia = DB::table('tmembresias')
                ->where('carnetSocio', $carnet)
                ->where('estadoMembresia', 'Congelada')
                ->where('estadoA', 1)
                ->orderBy('idMembresia', 'DESC')
                ->first();

            if (!$membresia) {
                return response()->json(['success' => false, 'message' => 'No hay membresia congelada para activar.'], 422);
            }

            $usuarioA = Auth::id() ?? 1;

            DB::table('tmembresias')
                ->where('idMembresia', $membresia->idMembresia)
                ->update([
                    'estadoMembresia'    => 'Activa',
                    'fechaCongelamiento' => null,
                    'fechaA'             => now(),
                    'usuarioA'           => $usuarioA,
                ]);

            DB::table('tsocios')
                ->where('carnetSocio', $carnet)
                ->update(['estadoSocio' => 'Activo', 'fechaA' => now()]);

            DB::table('tauditorias')->insert([
                'tablaNombre'   => 'tmembresias',
                'registroId'    => $membresia->idMembresia,
                'accion'        => 'U',
                'campo'         => 'estadoMembresia|fechaCongelamiento',
                'valorAnterior' => "Congelada|{$membresia->fechaCongelamiento}",
                'valorNuevo'    => 'Activa|',
                'usuarioA'      => $usuarioA,
                'fechaA'        => now(),
                'direccionIP'   => $request->ip(),
                'detalles'      => 'Activacion de Membresia'
            ]);

            return response()->json(['success' => true, 'message' => 'Membresia activada correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al activar: ' . $e->getMessage()], 500);
        }
    }

    public function notificaciones($id)
    {
        try {
            $notificaciones = DB::table('tnotificaciones')
                ->where('idUsuario', $id)
                ->where('estadoA', 1)
                ->orderBy('fechaEnvio', 'DESC')
                ->get(['idNotificacion', 'tipoNotificacion', 'mensaje', 'fechaEnvio', 'estado']);
            return response()->json($notificaciones);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar notificaciones: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();
        
        try {
            $socio = DB::table('tsocios')->where('carnetSocio', $id)->first();
            if ($socio) {
                DB::table('tusuarios')->where('idUsuario', $socio->idUsuario)->update(['estadoA' => 0, 'fechaA' => now(), 'usuarioA' => $usuarioA]);
                DB::table('tsocios')->where('carnetSocio', $id)->update(['estadoA' => 0, 'fechaA' => now(), 'usuarioA' => $usuarioA]);
            }
            DB::table('tmembresias')->where('carnetSocio', $id)->update(['estadoA' => 0, 'fechaA' => now()]);
            
            DB::table('tauditorias')->insert([
                'tablaNombre'   => 'tsocios',
                'registroId'    => $id,
                'accion'        => 'D',
                'campo'         => 'estadoA',
                'valorAnterior' => '1',
                'valorNuevo'    => '0',
                'usuarioA'      => $usuarioA,
                'fechaA'        => now(),
                'direccionIP'   => $ip,
                'detalles'      => 'Baja manual de Socio'
            ]);

            return response()->json(['success' => true, 'message' => 'Socio dado de baja exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno.'], 500);
        }
    }
}
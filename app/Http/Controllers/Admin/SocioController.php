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
                ->select(
                    's.carnetSocio', 's.idUsuario', 's.estadoSocio', 's.carnetSocio AS codigoAcceso',
                    'u.nombre1', 'u.nombre2', 'u.apellido1', 'u.apellido2', 'u.correo', 'u.telefono',
                    's.direccion', 's.nombreContactoEmergencia as contacto_emergencia_nombre',
                    's.telefonoContactoEmergencia as contacto_emergencia_telefono', 's.fotografiaUrl as foto_url'
                )
                ->where('u.estadoA', 1);

            if ($request->has('carnetSocio') && $request->carnetSocio != '') {
                $query->where('s.carnetSocio', 'LIKE', '%' . $request->carnetSocio . '%');
            }

            $socios = $query->get();
            $socios->transform(function ($s) {
                $m = DB::table('tmembresias')
                    ->where('carnetSocio', $s->carnetSocio)
                    ->where('estadoA', 1)
                    ->orderBy('idMembresia', 'desc')
                    ->first(['estadoMembresia', 'fechaCongelamiento']);
                $s->estadoMembresia = $m->estadoMembresia ?? null;
                $s->fechaCongelamiento = $m->fechaCongelamiento ?? null;
                return $s;
            });
            return response()->json($socios);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al listar: ' . $e->getMessage()], 500);
        }
    }

    private function errorFriendly($e)
    {
        $msg = $e->getMessage();
        if (str_contains($msg, 'Out of range') || str_contains($msg, 'out of range')) {
            return 'El valor ingresado es demasiado grande. Revise el CI (max. 10 digitos) y el telefono (7-8 digitos).';
        }
        if (str_contains($msg, 'Data too long')) {
            return 'El valor ingresado excede la longitud maxima permitida.';
        }
        if (str_contains($msg, 'Duplicate entry')) {
            return 'El CI ingresado ya esta registrado.';
        }
        return $msg;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'carnetSocio'  => 'required|string|max:10|confirmed|unique:tsocios,carnetSocio',
            'nombre1'       => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'nombre2'       => 'nullable|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellidoPaterno' => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellidoMaterno' => 'nullable|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'correo'       => 'required|email|unique:tusuarios,correo',
            'telefono'     => 'required|numeric|digits_between:7,8',
            'contrasena'   => 'required|string|min:8|confirmed',
            'direccion'    => 'nullable|string|max:255',
            'contacto_emergencia_nombre' => 'nullable|string|max:100',
            'contacto_emergencia_telefono' => 'nullable|numeric|digits_between:7,8',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'idSucursal'   => 'required|integer|exists:tsucursales,idSucursal',
        ], [
            'carnetSocio.required' => 'El CI es requerido.',
            'carnetSocio.max' => 'El CI debe tener maximo 10 digitos.',
            'carnetSocio.confirmed' => 'La confirmacion del CI no coincide.',
            'carnetSocio.unique' => 'El CI ya esta registrado.',
            'nombre1.required' => 'El primer nombre es requerido.',
            'nombre1.regex' => 'El nombre solo puede contener letras.',
            'nombre2.regex' => 'El nombre solo puede contener letras.',
            'apellidoPaterno.required' => 'El apellido paterno es requerido.',
            'apellidoPaterno.regex' => 'El apellido paterno solo puede contener letras.',
            'apellidoMaterno.regex' => 'El apellido materno solo puede contener letras.',
            'correo.required' => 'El correo es requerido.',
            'correo.email' => 'Ingrese un correo valido.',
            'correo.unique' => 'El correo ya esta registrado.',
            'telefono.required' => 'El telefono es requerido.',
            'telefono.numeric' => 'El telefono debe ser numerico.',
            'telefono.digits_between' => 'El telefono debe tener entre 7 y 8 digitos.',
            'contrasena.required' => 'La contrasena es requerida.',
            'contrasena.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'contrasena.confirmed' => 'Las contrasenas no coinciden.',
            'idSucursal.required' => 'Seleccione una sucursal.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validacion.', 'errors' => $validator->errors()], 422);
        }

        $nombre1 = $request->nombre1;
        $nombre2 = $request->nombre2 ?? '';
        $apellido1 = $request->apellidoPaterno;
        $apellido2 = $request->apellidoMaterno ?? '';

        $telefono = $request->telefono;
        if (!preg_match('/^[67]\d{6,7}$/', $telefono)) {
            return response()->json(['success' => false, 'message' => 'Error de validacion.', 'errors' => ['telefono' => ['El telefono debe comenzar con 6 o 7 y tener 7-8 digitos.']]], 422);
        }

        if ($request->contacto_emergencia_telefono) {
            $telEmergencia = $request->contacto_emergencia_telefono;
            if (!preg_match('/^[67]\d{6,7}$/', $telEmergencia)) {
                return response()->json(['success' => false, 'message' => 'Error de validacion.', 'errors' => ['contacto_emergencia_telefono' => ['El telefono de emergencia debe comenzar con 6 o 7 y tener 7-8 digitos.']]], 422);
            }
        }

        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        DB::beginTransaction();
        try {
            $rol = DB::table('troles')->where('nombreRol', 'Socio')->first();
            $idRol = $rol ? ($rol->idRol ?? $rol->id) : DB::table('troles')->insertGetId(['nombreRol' => 'Socio', 'fechaA' => now(), 'usuarioA' => $usuarioA]);

            $usuario = DB::select('CALL sp_TUsuarios_Insert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $idRol, $nombre1, $nombre2 ?: null, $apellido1, $apellido2 ?: null,
                $request->correo, $telefono, bcrypt($request->contrasena), 1, $usuarioA, $ip
            ]);

            $idUsuario = $usuario[0]->idUsuario ?? $usuario[0]->id ?? 0;
            if ($idUsuario == 0) throw new \Exception("Error al crear usuario.");

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
                'nombreContactoEmergencia' => $request->contacto_emergencia_nombre,
                'telefonoContactoEmergencia' => $request->contacto_emergencia_telefono,
                'fotografiaUrl'=> $fotoPath,
                'estadoSocio'  => 'Activo',
                'fechaA'       => now(),
                'usuarioA'     => $usuarioA
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Socio registrado con exito. Su codigo de acceso es su CI: ' . $request->carnetSocio]);

        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($fotoPath)) Storage::disk('public')->delete($fotoPath);
            return response()->json(['success' => false, 'message' => $this->errorFriendly($e)], 500);
        }
    }

    public function update(Request $request, $id)
    {
        if ($request->input('contrasena') === 'null' || $request->input('contrasena') === '') {
            $request->merge(['contrasena' => null, 'contrasena_confirmation' => null]);
        }

        $validator = Validator::make($request->all(), [
            'idUsuario'    => 'required|integer|exists:tusuarios,idUsuario',
            'nombre1'       => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'nombre2'       => 'nullable|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellidoPaterno' => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellidoMaterno' => 'nullable|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'correo'       => 'required|email|unique:tusuarios,correo,' . $request->idUsuario . ',idUsuario',
            'telefono'     => 'required|numeric|digits_between:7,8',
            'contrasena'   => 'nullable|string|min:8|confirmed',
            'direccion'    => 'nullable|string|max:255',
            'contacto_emergencia_nombre' => 'nullable|string|max:100',
            'contacto_emergencia_telefono' => 'nullable|numeric|digits_between:7,8',
            'foto'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'nombres.required' => 'Los nombres son requeridos.',
            'nombres.regex' => 'Los nombres solo pueden contener letras.',
            'apellidoPaterno.required' => 'El apellido paterno es requerido.',
            'apellidoPaterno.regex' => 'El apellido paterno solo puede contener letras.',
            'apellidoMaterno.regex' => 'El apellido materno solo puede contener letras.',
            'telefono.numeric' => 'El telefono debe ser numerico.',
            'telefono.digits_between' => 'El telefono debe tener entre 7 y 8 digitos.',
            'contrasena.min' => 'La contrasena debe tener al menos 8 caracteres.',
            'contrasena.confirmed' => 'Las contrasenas no coinciden.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validacion.', 'errors' => $validator->errors()], 422);
        }

        $nombre1 = $request->nombre1;
        $nombre2 = $request->nombre2 ?? '';
        $apellido1 = $request->apellidoPaterno;
        $apellido2 = $request->apellidoMaterno ?? '';

        $telefono = $request->telefono;
        if (!preg_match('/^[67]\d{6,7}$/', $telefono)) {
            return response()->json(['success' => false, 'message' => 'Error de validacion.', 'errors' => ['telefono' => ['El telefono debe comenzar con 6 o 7 y tener 7-8 digitos.']]], 422);
        }

        if ($request->contacto_emergencia_telefono) {
            $telEmergencia = $request->contacto_emergencia_telefono;
            if (!preg_match('/^[67]\d{6,7}$/', $telEmergencia)) {
                return response()->json(['success' => false, 'message' => 'Error de validacion.', 'errors' => ['contacto_emergencia_telefono' => ['El telefono de emergencia debe comenzar con 6 o 7 y tener 7-8 digitos.']]], 422);
            }
        }

        $apellido1 = $request->apellidoPaterno;
        $apellido2 = $request->apellidoMaterno ?? '';

        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        DB::beginTransaction();
        try {
            $usuarioActual = DB::table('tusuarios')->where('idUsuario', $request->idUsuario)->first();
            $contrasena = $request->filled('contrasena') ? bcrypt($request->contrasena) : $usuarioActual->contrasena;

            DB::statement('CALL sp_TUsuarios_Update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->idUsuario, 4, $nombre1, $nombre2 ?: null, $apellido1, $apellido2 ?: null,
                $request->correo, $telefono, $contrasena, 1, $usuarioA, $ip
            ]);

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
            return response()->json(['success' => true, 'message' => 'Informacion del socio actualizada.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $this->errorFriendly($e)], 500);
        }
    }

    public function congelarMembresia($carnet)
    {
        try {
            $membresia = DB::table('tmembresias')
                ->where('carnetSocio', $carnet)
                ->where('estadoA', 1)
                ->orderBy('idMembresia', 'desc')
                ->first();

            if (!$membresia) {
                return response()->json(['success' => false, 'message' => 'No hay membresia activa para este socio.'], 404);
            }

            if ($membresia->estadoMembresia === 'Congelada') {
                return response()->json(['success' => false, 'message' => 'La membresia ya esta congelada.'], 400);
            }

            if ($membresia->estadoMembresia !== 'Activa') {
                return response()->json(['success' => false, 'message' => 'Solo se puede congelar una membresia activa.'], 400);
            }

            DB::table('tmembresias')
                ->where('idMembresia', $membresia->idMembresia)
                ->update([
                    'estadoMembresia' => 'Congelada',
                    'fechaCongelamiento' => now(),
                    'fechaA' => now(),
                    'usuarioA' => Auth::id() ?? 1,
                ]);

            DB::table('tsocios')
                ->where('carnetSocio', $carnet)
                ->update(['estadoSocio' => 'Congelado']);

            return response()->json(['success' => true, 'message' => 'Membresia congelada exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno.'], 500);
        }
    }

    public function activarMembresia($carnet)
    {
        try {
            $membresia = DB::table('tmembresias')
                ->where('carnetSocio', $carnet)
                ->where('estadoA', 1)
                ->where('estadoMembresia', 'Congelada')
                ->orderBy('idMembresia', 'desc')
                ->first();

            if (!$membresia) {
                return response()->json(['success' => false, 'message' => 'No hay membresia congelada para este socio.'], 404);
            }

            if (!$membresia->fechaCongelamiento) {
                return response()->json(['success' => false, 'message' => 'La membresia no tiene fecha de congelamiento registrada.'], 400);
            }

            $diasCongelado = now()->diffInDays(\Carbon\Carbon::parse($membresia->fechaCongelamiento));

            DB::table('tmembresias')
                ->where('idMembresia', $membresia->idMembresia)
                ->update([
                    'fechaFinMembresia' => \Carbon\Carbon::parse($membresia->fechaFinMembresia)->addDays($diasCongelado)->format('Y-m-d'),
                    'estadoMembresia' => 'Activa',
                    'fechaCongelamiento' => null,
                    'fechaA' => now(),
                    'usuarioA' => Auth::id() ?? 1,
                ]);

            DB::table('tsocios')
                ->where('carnetSocio', $carnet)
                ->update(['estadoSocio' => 'Activo']);

            return response()->json(['success' => true, 'message' => "Membresia activada. Se agregaron $diasCongelado dias a la fecha de fin."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno.'], 500);
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
            return response()->json(['success' => true, 'message' => 'El estado del socio ahora es: ' . $texto]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno.'], 500);
        }
    }

    public function notificaciones($carnet)
    {
        try {
            $notis = DB::table('tnotificaciones')
                ->where('carnetSocio', $carnet)
                ->where('estadoA', 1)
                ->orderBy('fechaEnvio', 'desc')
                ->orderBy('idNotificacion', 'desc')
                ->get();
            return response()->json($notis);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al obtener notificaciones.'], 500);
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
            }
            DB::table('tmembresias')->where('carnetSocio', $id)->update(['estadoA' => 0, 'fechaA' => now()]);

            DB::table('tauditorias')->insert([
                'tablaNombre'   => 'tsocios',
                'registroId'    => $id,
                'accion'        => 'DELETE',
                'campo'         => 'estadoA',
                'valorAnterior' => '1',
                'valorNuevo'    => '0',
                'usuarioA'      => $usuarioA,
                'fechaA'        => now(),
                'direccionIP'   => $ip,
                'detalles'      => 'Baja manual de Socio y su Membresia desde el Panel'
            ]);

            return response()->json(['success' => true, 'message' => 'Socio dado de baja exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno.'], 500);
        }
    }
}

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
                ->leftJoin('tmembresias as m', function($join) {
                    // Unimos con la última membresía activa o vencida del socio
                    $join->on('s.carnetSocio', '=', 'm.carnetSocio')
                         ->where('m.estadoA', '=', 1)
                         ->whereRaw('m.idMembresia = (SELECT MAX(idMembresia) FROM tmembresias WHERE carnetSocio = s.carnetSocio)');
                })
                ->select(
                    's.carnetSocio', 's.idUsuario', 's.estadoSocio', 's.carnetSocio AS codigoAcceso', 
                    'u.nombre1', 'u.nombre2', 'u.apellido1', 'u.apellido2', 'u.correo', 'u.telefono', 
                    's.direccion', 's.nombreContactoEmergencia as contacto_emergencia_nombre', 
                    's.telefonoContactoEmergencia as contacto_emergencia_telefono', 's.fotografiaUrl as foto_url',
                    'm.estadoMembresia' // Recuperamos el estado para el botón de Congelar
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
            'telefono'         => 'required|numeric|digits_between:7,15',
            'contrasena'       => 'required|string|min:8',
            'direccion'        => 'nullable|string|max:255',
            'contacto_emergencia_nombre' => 'nullable|string|max:100',
            'contacto_emergencia_telefono' => 'nullable|numeric|digits_between:7,15',
            'foto'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048', 
            'idPlan'           => 'required|integer|exists:tplanes,idPlan',
            'idSucursal'       => 'required|integer|exists:tsucursales,idSucursal',
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
                'nombreContactoEmergencia' => $request->contacto_emergencia_nombre,
                'telefonoContactoEmergencia' => $request->contacto_emergencia_telefono,
                'fotografiaUrl'=> $fotoPath,
                'estadoSocio'  => 'Activo', 
                'strikes'      => 0,
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
            return response()->json(['success' => true, 'message' => '✅ Socio registrado con éxito.']);
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
            'telefono'         => 'required|numeric|digits_between:7,15',
            'contrasena'       => 'nullable|string|min:8',
            'foto'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        $usuarioA = Auth::id() ?? 1;

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

            $socioActual = DB::table('tsocios')->where('carnetSocio', $id)->first();
            $fotoPath = $socioActual->fotografiaUrl ?? null;
            
            if ($request->hasFile('foto')) {
                if ($fotoPath) Storage::disk('public')->delete($fotoPath);
                $ext = $request->file('foto')->getClientOriginalExtension();
                $fotoPath = $request->file('foto')->storeAs('fotos_socios', 'S-' . $id . '.' . $ext, 'public');
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
            return response()->json(['success' => false, 'message' => 'Error al actualizar.'], 500);
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

            return response()->json(['success' => true, 'message' => '✅ El estado del socio y membresía ahora es: ' . $nuevoEstado]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno.'], 500);
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
                'detalles'      => 'Baja manual de Socio'
            ]);

            return response()->json(['success' => true, 'message' => 'Socio dado de baja exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error interno.'], 500);
        }
    }
    public function notificaciones($id)
    {
        try {
            // Buscamos las notificaciones del socio en la base de datos
            $notificaciones = DB::table('tnotificaciones')
                ->where('carnetSocio', $id)
                ->orderBy('fechaEnvio', 'desc')
                ->get();
                
            return response()->json($notificaciones);
        } catch (\Exception $e) {
            // Si la tabla 'tnotificaciones' no existe en la BD de Kike, devolvemos un array vacío para no romper la vista
            return response()->json([]);
        }
    }
}
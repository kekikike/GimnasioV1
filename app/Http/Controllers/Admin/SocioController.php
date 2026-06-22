<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SocioController extends Controller
{
    public function index()
    {
        $sucursales = DB::select('CALL sp_TSucursales_Select()');
        return view('admin.socios', compact('sucursales'));
    }

    public function listar()
    {
        try {
            $socios = DB::select("
                SELECT s.carnetSocio, s.idUsuario, s.estadoSocio,
                       s.carnetSocio AS codigoAcceso,
                       u.nombre1, u.apellido1, u.correo, u.telefono
                FROM tsocios s
                INNER JOIN tusuarios u ON s.idUsuario = u.idUsuario
                WHERE u.estadoA = 1 
            ");
            return response()->json($socios);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error en consulta SQL: ' . $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        DB::beginTransaction();
        try {
            $rol = DB::table('troles')->where('nombreRol', 'Socio')->first();
            $idRol = $rol ? ($rol->idRol ?? $rol->id) : DB::table('troles')->insertGetId(['nombreRol' => 'Socio', 'fechaA' => now()]);

            $usuario = DB::select('CALL sp_TUsuarios_Insert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $idRol, $request->nombre1, null, $request->apellido1, null, 
                $request->correo, $request->telefono, bcrypt($request->contrasena), 1, $usuarioA, $ip
            ]);

            $idUsuario = $usuario[0]->idUsuario ?? $usuario[0]->id ?? 0;

            if ($idUsuario == 0) throw new \Exception("No se pudo obtener el ID del usuario.");

            // Bypass TSocios (Este ya pasó exitosamente)
            DB::table('tsocios')->insert([
                'carnetSocio'  => $request->carnetSocio, 
                'idUsuario'    => $idUsuario,
                'estadoSocio'  => 'Activo', 
                'fechaA'       => now(), 
                'usuarioA'     => $usuarioA
            ]);

            // Bypass TMembresias (¡Aquí corregimos los nombres de las fechas!)
            if ($request->idPlan) {
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
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => '✅ Socio registrado con éxito. Carnet: ' . $request->carnetSocio]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        DB::beginTransaction();
        try {
            $usuarioActual = DB::table('tusuarios')->where('idUsuario', $request->idUsuario)->first();
            $contrasena = $request->contrasena ? bcrypt($request->contrasena) : $usuarioActual->contrasena;

            DB::statement('CALL sp_TUsuarios_Update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->idUsuario, 4, $request->nombre1, null, $request->apellido1, null,
                $request->correo, $request->telefono, $contrasena, 1, $usuarioA, $ip
            ]);

            DB::table('tsocios')->where('carnetSocio', $id)->update([
                'fechaA' => now(),
                'usuarioA' => $usuarioA
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => '✅ Información del socio actualizada.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }

    public function congelar(Request $request, $id)
    {
        try {
            $socio = DB::table('tsocios')->where('carnetSocio', $id)->first();
            if (!$socio) {
                return response()->json(['success' => false, 'message' => 'Socio no encontrado.']);
            }

            $nuevoEstado = ($socio->estadoSocio === 'Activo') ? 'Congelado' : 'Activo';

            DB::table('tsocios')->where('carnetSocio', $id)->update([
                'estadoSocio' => $nuevoEstado, 
                'fechaA' => now()
            ]);

            $estadoMembresia = ($nuevoEstado === 'Activo') ? 'Activa' : 'Congelada';
            DB::table('tmembresias')
                ->where('carnetSocio', $id)
                ->where('estadoMembresia', '!=', 'Vencida')
                ->update(['estadoMembresia' => $estadoMembresia, 'fechaA' => now()]);

            $texto = $nuevoEstado == 'Congelado' ? 'Congelado' : 'Activo';
            return response()->json(['success' => true, 'message' => '✅ El estado del socio ahora es: ' . $texto]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        try {
            $socio = DB::table('tsocios')->where('carnetSocio', $id)->first();
            if ($socio) {
                DB::table('tusuarios')->where('idUsuario', $socio->idUsuario)->update(['estadoA' => 0, 'fechaA' => now(), 'usuarioA' => $usuarioA]);
            }
            
            DB::table('tmembresias')->where('carnetSocio', $id)->update(['estadoA' => 0, 'fechaA' => now()]);
            return response()->json(['success' => true, 'message' => 'Socio dado de baja exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }
}
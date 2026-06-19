<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SocioController extends Controller
{
    // 1. Cargar vista con sucursales
    public function index()
    {
        $sucursales = DB::select('CALL sp_TSucursales_Select()');
        return view('admin.socios', compact('sucursales'));
    }

    public function listar()
    {
        $socios = DB::select('CALL sp_TSocios_GetAllWithUsers()');
        return response()->json($socios);
    }

    // 3. Registrar: Usuario + Socio + Membresía (RF-09, RF-11, RF-12)
    public function store(Request $request)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        DB::beginTransaction();
        try {
            $roles = DB::select('CALL sp_TRoles_Select()');
            $rol = collect($roles)->firstWhere('nombreRol', 'Socio');
            $idRol = $rol ? $rol->idRol : 4;

            // A. Crear usuario (11 parámetros)
            $usuario = DB::select('CALL sp_TUsuarios_Insert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $idRol, 
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

            // B. Crear Socio (9 parámetros)
            $socio = DB::select('CALL sp_TSocios_Insert(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                0,
                $idUsuario,
                $request->direccion ?? 'Sin especificar',
                null, // foto
                $request->nombreContactoEmergencia ?? 'Sin especificar',
                $request->telefonoContactoEmergencia ?? 0,
                'Ninguna', // obs. médicas
                'Activo', // estadoSocio
                0, // strikes
                $usuarioA,
                $ip
            ]);

            $carnetSocio = $socio[0]->id ?? $socio[0]->carnetSocio ?? 0;

            // C. Asignar Membresía Inicial (8 parámetros)
            if ($request->idPlan && $carnetSocio > 0) {
                $plan = DB::select('CALL sp_TPlanes_SelectById(?)', [$request->idPlan])[0] ?? null;
                $duracion = $plan ? $plan->duracionDias : 30;

                $fechaInicio = now()->format('Y-m-d');
                $fechaFin = now()->addDays($duracion)->format('Y-m-d');

                DB::select('CALL sp_TMembresias_Insert(?, ?, ?, ?, ?, ?, ?, ?)', [
                    $request->idPlan,
                    $carnetSocio,
                    $request->idSucursal,
                    $fechaInicio,
                    $fechaFin,
                    'Activa',
                    $usuarioA,
                    $ip
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Socio registrado con éxito.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }

    // 4. Actualizar Socio (RF-10)
    public function update(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        DB::beginTransaction();
        try {
            // Actualizar TUsuarios
            $usuarioActual = DB::select('CALL sp_TUsuarios_SelectById(?)', [$request->idUsuario])[0] ?? null;
            $contrasena = $request->contrasena ? bcrypt($request->contrasena) : $usuarioActual->contrasena;

            DB::statement('CALL sp_TUsuarios_Update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $request->idUsuario,
                4, // idRol Socio
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

            // Actualizar TSocios
            $socioActual = DB::select('CALL sp_TSocios_SelectById(?)', [$id])[0] ?? null;

            DB::statement('CALL sp_TSocios_Update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $id,
                $request->idUsuario,
                $request->direccion ?? 'Sin especificar',
                null,
                $request->nombreContactoEmergencia ?? 'Sin especificar',
                $request->telefonoContactoEmergencia ?? 0,
                'Ninguna',
                'Activo',
                0,
                $usuarioA,
                $ip
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Información del socio actualizada.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }

    // 5. Eliminar Socio
    public function destroy(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();

        try {
            DB::statement('CALL sp_TSocios_Delete(?, ?, ?)', [$id, $usuarioA, $ip]);
            return response()->json(['success' => true, 'message' => 'Socio dado de baja.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }
    // 6. Congelar / Activar Socio (RF-14)
    public function congelar(Request $request, $id)
    {
        try {
            $socio = DB::select('CALL sp_TSocios_SelectById(?)', [$id])[0] ?? null;
            if (!$socio) return response()->json(['success' => false, 'message' => 'Socio no encontrado.'], 404);

            $nuevoEstadoSocio = ($socio->estadoSocio === 'Activo') ? 'Congelado' : 'Activo';
            $estadoMembresia  = ($nuevoEstadoSocio === 'Activo') ? 'Activa' : 'Congelada';

            DB::select('CALL sp_TSocios_Update(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $id,
                $socio->idUsuario,
                $socio->direccion ?? 'Sin especificar',
                null,
                $socio->nombreContactoEmergencia ?? 'Sin especificar',
                $socio->telefonoContactoEmergencia ?? 0,
                'Ninguna',
                $nuevoEstadoSocio,
                0,
                $usuarioA,
                $ip
            ]);

            $todas = DB::select('CALL sp_TMembresias_Select()');
            $membresias = array_filter($todas, function ($m) use ($id) {
                return (int) $m->carnetSocio === (int) $id && $m->estadoMembresia !== 'Vencida';
            });
            foreach ($membresias as $m) {
                DB::select('CALL sp_TMembresias_Update(?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                    $m->idMembresia,
                    $m->idPlan,
                    $id,
                    $m->idSucursal,
                    $m->fechaInicioMembresia,
                    $m->fechaFinMembresia,
                    $estadoMembresia,
                    $usuarioA,
                    $ip,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Estado del socio actualizado a: ' . $nuevoEstadoSocio]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }
}
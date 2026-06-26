<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PerfilController extends Controller
{
    private function loadProfile()
    {
        $usuario = session('usuario');
        if (!$usuario) return null;

        $empleado = DB::table('TEmpleados as e')
            ->join('TUsuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->join('TSucursales as s', 'e.idSucursal', '=', 's.idSucursal')
            ->select(
                'e.carnetEmpleado',
                'e.idSucursal',
                'e.fechaContratoInicio',
                'u.idUsuario',
                'u.nombre1',
                'u.nombre2',
                'u.apellido1',
                'u.apellido2',
                'u.correo',
                'u.telefono',
                'u.idRol',
                's.nombre as nombreSucursal'
            )
            ->where('e.idUsuario', $usuario->idUsuario)
            ->first();

        return $empleado;
    }

    public function perfil()
    {
        $data = $this->loadProfile();
        $usuario = session('usuario');

        if (!$data) {
            return view('admin.perfil', [
                'data' => null,
                'usuario' => $usuario,
            ]);
        }

        return view('admin.perfil', compact('data', 'usuario'));
    }

    public function updatePerfil(Request $request)
    {
        $usuario = session('usuario');
        if (!$usuario) return response()->json(['success' => false, 'message' => 'No autorizado'], 401);

        $data = $this->loadProfile();
        if (!$data) return response()->json(['success' => false, 'message' => 'Empleado no encontrado'], 404);

        if ($request->input('contrasena') === 'null' || $request->input('contrasena') === '') {
            $request->merge(['contrasena' => null, 'contrasena_confirmation' => null]);
        }

        $validator = Validator::make($request->all(), [
            'nombre1'          => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'nombre2'          => 'nullable|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellido1'        => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellido2'        => 'nullable|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'correo'           => 'required|email|unique:tusuarios,correo,' . $usuario->idUsuario . ',idUsuario',
            'telefono'         => 'required|numeric|digits_between:7,15',
            'contrasena'       => 'nullable|string|min:8|confirmed',
        ], [
            'contrasena.confirmed' => 'Las contraseñas no coinciden.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'correo.unique' => 'Este correo ya pertenece a otra persona.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $updateData = [
                'nombre1'   => $request->nombre1,
                'nombre2'   => $request->nombre2,
                'apellido1' => $request->apellido1,
                'apellido2' => $request->apellido2,
                'correo'    => $request->correo,
                'telefono'  => $request->telefono,
                'fechaA'    => now(),
            ];

            if ($request->filled('contrasena')) {
                $updateData['contrasena'] = bcrypt($request->contrasena);
            }

            DB::table('tusuarios')->where('idUsuario', $usuario->idUsuario)->update($updateData);

            $usuario->nombre1 = $request->nombre1;
            $usuario->apellido1 = $request->apellido1;
            session(['usuario' => $usuario]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Perfil actualizado con éxito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar.'], 500);
        }
    }
}

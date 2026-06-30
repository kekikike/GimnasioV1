<?php

namespace App\Http\Controllers\Socio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SocioPortalController extends Controller
{
    private function loadSocio()
    {
        $usuario = session('usuario');
        $socio = DB::select('CALL sp_TSocios_GetByUserId(?)', [$usuario->idUsuario]);
        $socio = $socio[0] ?? null;
        view()->share('fotografiaUrl', $socio->fotografiaUrl ?? null);
        return $socio;
    }

    public function dashboard()
    {
        $usuario = session('usuario');
        $socio = $this->loadSocio();

        if (!$socio) {
            return view('socio.dashboard', [
                'socio' => null,
                'membresia' => null,
                'accesos' => [],
                'reservas' => [],
                'clases' => [],
            ]);
        }

        $carnet = $socio->carnetSocio;
        $membresia = DB::select('CALL sp_TMembresias_GetActiveBySocio(?)', [$carnet]);
        $membresia = $membresia[0] ?? null;
        $accesos = DB::select('CALL sp_TControlAccesos_GetBySocio(?)', [$carnet]);
        $reservas = DB::select('CALL sp_TReservas_GetBySocio(?)', [$carnet]);
        $clases = DB::select('CALL sp_TClaseGrupales_GetAvailable()');

        return view('socio.dashboard', compact('socio', 'membresia', 'accesos', 'reservas', 'clases'));
    }

    public function perfil()
    {
        $socio = $this->loadSocio();
        return view('socio.perfil', compact('socio'));
    }

    public function updatePerfil(Request $request)
    {
        $usuario = session('usuario');
        if (!$usuario) return response()->json(['success' => false, 'message' => 'No autorizado'], 401);

        $socio = $this->loadSocio();
        if (!$socio) return response()->json(['success' => false, 'message' => 'Socio no encontrado'], 404);

        if ($request->input('contrasena') === 'null' || $request->input('contrasena') === '') {
            $request->merge(['contrasena' => null, 'contrasena_confirmation' => null]);
        }

        $validator = Validator::make($request->all(), [
            'nombre1'          => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'nombre2'          => 'nullable|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellido1'        => 'required|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'apellido2'        => 'nullable|string|regex:/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]+$/|max:50',
            'correo'           => 'required|email|unique:tusuarios,correo,' . $usuario->idUsuario . ',idUsuario',
            'telefono'         => ['required', 'numeric', 'digits_between:7,8', 'regex:/^[67]\d+$/'],
            'direccion'        => 'nullable|string|max:255',
            'contacto_emergencia_nombre' => 'nullable|string|max:100',
            'contacto_emergencia_telefono' => 'nullable|numeric|digits_between:7,8',
            'contrasena'       => 'nullable|string|min:8|confirmed',
        ], [
            'nombre1.required' => 'El primer nombre es obligatorio.',
            'nombre1.regex' => 'El nombre solo puede contener letras.',
            'nombre1.max' => 'El nombre no debe exceder 50 caracteres.',
            'apellido1.required' => 'El apellido paterno es obligatorio.',
            'apellido1.regex' => 'El apellido solo puede contener letras.',
            'apellido1.max' => 'El apellido no debe exceder 50 caracteres.',
            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'Ingrese un correo válido (debe contener @ y .).',
            'correo.unique' => 'Este correo ya pertenece a otra persona.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.numeric' => 'El teléfono solo debe contener números.',
            'telefono.digits_between' => 'El teléfono debe tener entre 7 y 8 dígitos.',
            'telefono.regex' => 'El teléfono debe comenzar con 6 o 7.',
            'contrasena.confirmed' => 'Las contraseñas no coinciden.',
            'contrasena.min' => 'La contraseña debe tener al menos 8 caracteres.',
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

            DB::table('tsocios')->where('carnetSocio', $socio->carnetSocio)->update([
                'direccion'                  => $request->direccion,
                'nombreContactoEmergencia'   => $request->contacto_emergencia_nombre,
                'telefonoContactoEmergencia' => $request->contacto_emergencia_telefono,
                'fechaA'                     => now()
            ]);

            // Actualizamos la sesión para que el nombre de la esquina superior derecha cambie automáticamente
            $usuario->nombre1 = $request->nombre1;
            $usuario->apellido1 = $request->apellido1;
            session(['usuario' => $usuario]);

            DB::commit();
            return response()->json(['success' => true, 'message' => '✅ Tu perfil ha sido actualizado con éxito.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar.'], 500);
        }
    }

    public function asistencias()
    {
        $socio = $this->loadSocio();
        $accesos = $socio ? DB::select('CALL sp_TControlAccesos_GetBySocio(?)', [$socio->carnetSocio]) : [];
        return view('socio.asistencias', compact('accesos'));
    }

    public function reservas()
    {
        $this->loadSocio();
        return view('socio.reservas');
    }

    public function notificaciones()
    {
        $socio = $this->loadSocio();
        $notificaciones = [];
        if ($socio) {
            $notificaciones = DB::table('tnotificaciones')
                ->where('idUsuario', $socio->idUsuario)
                ->where('estadoA', 1)
                ->orderBy('fechaEnvio', 'desc')
                ->orderBy('idNotificacion', 'desc')
                ->get();
        }
        return view('socio.notificaciones', compact('notificaciones'));
    }

    public function historialMembresias()
    {
        $socio = $this->loadSocio();
        if (!$socio) return view('socio.historial-membresias', ['membresias' => []]);
        
        $membresias = DB::table('TMembresias as m')
            ->join('TPlanes as p', 'm.idPlan', '=', 'p.idPlan')
            ->select('m.*', 'p.nombrePlan', 'p.costoPlan', 'p.duracionDias')
            ->where('m.carnetSocio', $socio->carnetSocio)
            ->orderBy('m.fechaInicioMembresia', 'desc')
            ->get();
        return view('socio.historial-membresias', compact('membresias'));
    }
}
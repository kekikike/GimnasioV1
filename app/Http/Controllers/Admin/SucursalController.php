<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SucursalController extends Controller
{
    // 1. Cargar la interfaz gráfica (Blade)
    public function index()
    {
        return view('admin.sucursales');
    }

    // 2. Devolver los datos a Vue en formato JSON
    public function listar()
    {
        $sucursales = Sucursal::getAll();
        return response()->json($sucursales);
    }

    // 3. Guardar nueva sucursal
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:tsucursales,nombre',
            'direccion' => 'required|string|max:255',
            'telefono' => ['required', 'numeric', 'digits_between:7,8', 'regex:/^[67]\d{6,7}$/'],
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
        ], [
            'telefono.digits_between' => 'El telefono debe tener entre 7 y 8 digitos.',
            'telefono.regex' => 'El telefono debe comenzar con 6 o 7.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $usuarioA = Auth::id() ?? 1;
        $direccionIP = $request->ip();

        $data = $validator->validated();
        $data['estado'] = 1;

        Sucursal::create($data, $usuarioA, $direccionIP);

        return response()->json(['success' => true, 'message' => 'Sucursal registrada exitosamente.']);
    }

    // 4. Actualizar sucursal
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:tsucursales,nombre,' . $id . ',idSucursal',
            'direccion' => 'required|string|max:255',
            'telefono' => ['required', 'numeric', 'digits_between:7,8', 'regex:/^[67]\d{6,7}$/'],
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
        ], [
            'telefono.digits_between' => 'El telefono debe tener entre 7 y 8 digitos.',
            'telefono.regex' => 'El telefono debe comenzar con 6 o 7.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $usuarioA = Auth::id() ?? 1;
        $direccionIP = $request->ip();

        $data = $validator->validated();
        $data['estado'] = 1;

        Sucursal::update($id, $data, $usuarioA, $direccionIP);

        return response()->json(['success' => true, 'message' => 'Sucursal actualizada.']);
    }

    // 5. Eliminar (Dar de baja)
    public function destroy(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $direccionIP = $request->ip();

        Sucursal::delete($id, $usuarioA, $direccionIP);

        return response()->json(['success' => true, 'message' => 'Sucursal dada de baja exitosamente.']);
    }

    // -----------------------------------------------------
    // ⬇️ NUEVAS FUNCIONES PARA EL PANEL DE RESTAURACIÓN ⬇️
    // -----------------------------------------------------

    public function listarInactivas()
    {
        // Traemos directamente las sucursales con estadoA = 0
        $inactivas = DB::table('tsucursales')
            ->where('estadoA', 0)
            ->get();
        return response()->json($inactivas);
    }

    public function restaurar(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $direccionIP = $request->ip();

        // 1. Volvemos a cambiar el estado a 1 (Activo)
        DB::table('tsucursales')
            ->where('idSucursal', $id)
            ->update([
                'estadoA' => 1,
                'usuarioA' => $usuarioA,
                'fechaA' => now()
            ]);

        // 2. Guardamos el rastro en Auditoría
        DB::table('tauditorias')->insert([
            'tablaNombre'   => 'tsucursales',
            'registroId'    => $id,
            'accion'        => 'RESTORE',
            'campo'         => 'estadoA',
            'valorAnterior' => '0',
            'valorNuevo'    => '1',
            'usuarioA'      => $usuarioA,
            'fechaA'        => now(),
            'direccionIP'   => $direccionIP,
            'detalles'      => 'Reactivación de Sucursal dada de baja'
        ]);

        return response()->json(['success' => true, 'message' => 'Sucursal reactivada con exito.']);
    }
}
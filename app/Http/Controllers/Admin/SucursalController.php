<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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
        // RF1: Validación de teléfono y otros campos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:tsucursales,nombre',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|numeric|digits_between:7,15',
        ], [
            'telefono.digits_between' => 'El teléfono debe tener entre 7 y 15 dígitos.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $usuarioA = Auth::id() ?? 1; // Usuario para auditoría
        $direccionIP = $request->ip(); // Capturamos la IP de la computadora

        $data = $validator->validated();
        $data['estado'] = 1;

        // Usamos la función create() tal cual la programó Kike
        Sucursal::create($data, $usuarioA, $direccionIP);

        return response()->json(['success' => true, 'message' => 'Sucursal registrada exitosamente.']);
    }

    // 4. Actualizar sucursal
    public function update(Request $request, $id)
    {
        // RF2: Validación de teléfono y otros campos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:tsucursales,nombre,' . $id . ',idSucursal',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|numeric|digits_between:7,15',
        ], [
            'telefono.digits_between' => 'El teléfono debe tener entre 7 y 15 dígitos.',
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

        return response()->json(['success' => true, 'message' => 'Sucursal eliminada.']);
    }
}
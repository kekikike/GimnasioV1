<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sucursal;
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
        $usuarioA = Auth::id() ?? 1; // Usuario para auditoría
        $direccionIP = $request->ip(); // Capturamos la IP de la computadora

        $data = [
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'estado' => 1
        ];

        // Usamos la función create() tal cual la programó Kike
        Sucursal::create($data, $usuarioA, $direccionIP);

        return response()->json(['success' => true, 'message' => 'Sucursal registrada exitosamente.']);
    }

    // 4. Actualizar sucursal
    public function update(Request $request, $id)
    {
        $usuarioA = Auth::id() ?? 1;
        $direccionIP = $request->ip();

        $data = [
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'estado' => 1
        ];

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
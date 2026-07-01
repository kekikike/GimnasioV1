<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Marca;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarcaController extends Controller
{
    public function index()
    {
        return view('admin.marcas');
    }

    public function listar()
    {
        $marcas = Marca::getAll();
        return response()->json($marcas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombreMarca' => 'required|string|max:100|unique:tmarcas,nombreMarca',
        ], [
            'nombreMarca.required' => 'El nombre de la marca es obligatorio.',
            'nombreMarca.string' => 'El nombre de la marca debe ser texto.',
            'nombreMarca.max' => 'El nombre de la marca no debe exceder los 100 caracteres.',
            'nombreMarca.unique' => 'Ya existe una marca con ese nombre.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $usuarioA = Auth::id() ?? 1;
        $direccionIP = $request->ip();

        $data = $validator->validated();
        Marca::create($data, $usuarioA, $direccionIP);

        return response()->json(['success' => true, 'message' => 'Marca registrada exitosamente.']);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombreMarca' => 'required|string|max:100|unique:tmarcas,nombreMarca,' . $id . ',idMarca',
        ], [
            'nombreMarca.required' => 'El nombre de la marca es obligatorio.',
            'nombreMarca.string' => 'El nombre de la marca debe ser texto.',
            'nombreMarca.max' => 'El nombre de la marca no debe exceder los 100 caracteres.',
            'nombreMarca.unique' => 'Ya existe una marca con ese nombre.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Error de validación.', 'errors' => $validator->errors()], 422);
        }

        $usuarioA = Auth::id() ?? 1;
        $direccionIP = $request->ip();

        $data = $validator->validated();
        Marca::update($id, $data, $usuarioA, $direccionIP);

        return response()->json(['success' => true, 'message' => 'Marca actualizada exitosamente.']);
    }

    public function destroy(Request $request, $id)
    {
        $equiposActivos = DB::table('TEquipamientos')
            ->where('idMarca', $id)
            ->where('estadoA', 1)
            ->count();

        if ($equiposActivos > 0) {
            return response()->json([
                'success' => false,
                'message' => "No se puede eliminar la marca porque tiene {$equiposActivos} equipo(s) activo(s) asociado(s)."
            ], 422);
        }

        $usuarioA = Auth::id() ?? 1;
        $direccionIP = $request->ip();

        Marca::delete($id, $usuarioA, $direccionIP);

        return response()->json(['success' => true, 'message' => 'Marca eliminada exitosamente.']);
    }
}

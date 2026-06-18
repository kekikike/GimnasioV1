<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    public function index() {
        return view('admin.planes');
    }

    public function listar() {
        return response()->json(DB::select('CALL sp_TPlanes_Select()'));
    }

    public function store(Request $request) {
        $usuarioA = Auth::id() ?? 1;
        try {
            DB::statement('CALL sp_TPlanes_Insert(?, ?, ?, ?, ?, ?)', [
                $request->nombrePlan, $request->descripcion, $request->costoPlan, $request->duracionDias, $usuarioA, $request->ip()
            ]);
            return response()->json(['success' => true, 'message' => '✅ Plan de membresía creado.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id) {
        $usuarioA = Auth::id() ?? 1;
        try {
            DB::statement('CALL sp_TPlanes_Update(?, ?, ?, ?, ?, ?, ?)', [
                $id, $request->nombrePlan, $request->descripcion, $request->costoPlan, $request->duracionDias, $usuarioA, $request->ip()
            ]);
            return response()->json(['success' => true, 'message' => '✅ Plan actualizado.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request, $id) {
        try {
            DB::statement('CALL sp_TPlanes_Delete(?, ?, ?)', [$id, Auth::id() ?? 1, $request->ip()]);
            return response()->json(['success' => true, 'message' => 'Plan eliminado.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
        }
    }
}   
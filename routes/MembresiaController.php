<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MembresiaController extends Controller
{
    /**
     * Suspende o congela una membresía. (RF-14)
     *
     * @param \Illuminate\Http\Request $request
     * @param int $idMembresia
     * @return \Illuminate\Http\JsonResponse
     */
    public function suspender(Request $request, $id)
    {
        $request->validate([
            'dias_suspension' => 'required|integer|min:1',
            'motivo' => 'nullable|string|max:255',
        ]);

        $usuarioA = Auth::id() ?? 1;
        $ip = $request->ip();
        $diasSuspension = $request->dias_suspension;

        DB::beginTransaction();
        try {
            $membresia = DB::select('CALL sp_TMembresias_SelectById(?)', [(int) $id])[0] ?? null;

            if (!$membresia) {
                return response()->json(['success' => false, 'message' => 'Membresía no encontrada.'], 404);
            }

            $fechaFinActual = Carbon::parse($membresia->fechaFinMembresia);
            $nuevaFechaFin = $fechaFinActual->addDays($diasSuspension);

            // 3. Actualizar la membresía con la nueva fecha de fin y un estado 'Suspendida'.
            // Asumimos un SP para esta lógica: sp_TMembresias_Suspender(idMembresia, nuevaFechaFin, estado, motivo, usuarioA, ip)
            DB::statement('CALL sp_TMembresias_Suspender(?, ?, ?, ?, ?, ?)', [
                $id,
                $nuevaFechaFin->toDateString(),
                'Suspendida',
                $request->motivo ?? 'Suspensión solicitada por el administrador.',
                $usuarioA,
                $ip
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => "Membresía suspendida por {$diasSuspension} días. Nueva fecha de vencimiento: " . $nuevaFechaFin->format('d/m/Y')]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al suspender la membresía: ' . $e->getMessage()]);
        }
    }
}
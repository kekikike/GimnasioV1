<?php

namespace App\Http\Controllers\Entrenador;

use App\Http\Controllers\Controller;
use App\Models\Equipamiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntrenadorController extends Controller
{
    public function dashboard()
    {
        return view('entrenador.dashboard');
    }

    public function fallas()
    {
        $equipos = DB::select('CALL sp_TEquipamientos_GetOperativosWithDetails()');

        return view('entrenador.fallas', compact('equipos'));
    }

    public function reportarFalla(Request $request)
    {
        $data = $request->validate([
            'idEquipo'        => 'required|integer|exists:TEquipamientos,idEquipo',
            'descripcionFalla' => 'required|string|max:500',
            'gravedad'         => 'required|in:Baja,Media,Alta,Critica',
        ]);

        $usuario    = session('usuario');
        $carnetEmp  = $usuario->carnetEmpleado ?? $usuario->idUsuario;
        $direccionIP = $request->ip();

        DB::beginTransaction();
        try {
            DB::select('CALL sp_TReporteFallas_Insert(?, ?, NOW(), ?, ?, ?, ?, ?)', [
                $data['idEquipo'],
                $carnetEmp,
                $data['descripcionFalla'],
                $data['gravedad'],
                'Pendiente',
                $usuario->idUsuario,
                $direccionIP,
            ]);

            $equipo = Equipamiento::getById((int) $data['idEquipo']);
            if ($equipo) {
                $updateData = [
                    'idSucursal'       => $equipo->idSucursal,
                    'idMarca'          => $equipo->idMarca,
                    'nombreEquipo'     => $equipo->nombreEquipo,
                    'modelo'           => $equipo->modelo,
                    'fechaAdquisicion' => $equipo->fechaAdquisicion,
                    'estadoEquipo'     => 'Fuera de Servicio',
                ];
                Equipamiento::update((int) $data['idEquipo'], $updateData, $usuario->idUsuario, $direccionIP);
            }

            DB::commit();
            return redirect()->route('entrenador.fallas')
                ->with('success', 'Falla reportada. El equipo ha sido marcado como "Fuera de Servicio".');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('entrenador.fallas')
                ->with('error', 'Error al reportar la falla: ' . $e->getMessage());
        }
    }
}

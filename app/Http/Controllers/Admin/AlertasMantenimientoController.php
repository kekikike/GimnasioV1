<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AlertasMantenimientoController extends Controller
{
    public function index()
    {
        $alertas = DB::select('CALL sp_TMantenimientoPreventivos_GetAlertasPendientes()');
        $todos = DB::select('CALL sp_TMantenimientoPreventivos_GetResumen()');

        $resumen = [
            'pendientes' => count($alertas),
            'realizados' => 0,
            'vencidas'   => 0,
        ];
        foreach ($todos as $t) {
            if ($t->estadoMantenimiento == 'Realizado') $resumen['realizados'] = (int) $t->cantidad;
        }

        foreach ($alertas as $a) {
            if ($a->diasRestantes < 0) $resumen['vencidas']++;
        }

        return view('admin.alertas-mantenimiento', compact('alertas', 'resumen'));
    }
}

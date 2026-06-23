<?php

namespace App\Http\Controllers\Socio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $this->loadSocio();
        return view('socio.perfil');
    }

    public function asistencias()
    {
        $socio = $this->loadSocio();

        $accesos = [];
        if ($socio) {
            $accesos = DB::select('CALL sp_TControlAccesos_GetBySocio(?)', [$socio->carnetSocio]);
        }

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
                ->where('carnetSocio', $socio->carnetSocio)
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
        if (!$socio) {
            return view('socio.historial-membresias', ['membresias' => []]);
        }
        $membresias = DB::table('TMembresias as m')
            ->join('TPlanes as p', 'm.idPlan', '=', 'p.idPlan')
            ->select('m.*', 'p.nombrePlan', 'p.costoPlan', 'p.duracionDias')
            ->where('m.carnetSocio', $socio->carnetSocio)
            ->orderBy('m.fechaInicioMembresia', 'desc')
            ->get();
        return view('socio.historial-membresias', compact('membresias'));
    }
}

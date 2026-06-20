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
        $this->loadSocio();
        return view('socio.asistencias');
    }

    public function reservas()
    {
        $this->loadSocio();
        return view('socio.reservas');
    }
}

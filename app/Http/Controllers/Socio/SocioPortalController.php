<?php

namespace App\Http\Controllers\Socio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SocioPortalController extends Controller
{
    public function dashboard()
    {
        return view('socio.dashboard');
    }

    public function perfil()
    {
        return view('socio.perfil');
    }

    public function asistencias()
    {
        return view('socio.asistencias');
    }

    public function reservas()
    {
        return view('socio.reservas');
    }
}

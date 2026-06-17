<?php

namespace App\Http\Controllers\Entrenador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EntrenadorController extends Controller
{
    public function dashboard()
    {
        return view('entrenador.dashboard');
    }

    public function fallas()
    {
        return view('entrenador.fallas');
    }
}

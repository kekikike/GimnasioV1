<?php

namespace App\Http\Controllers\Recepcionista;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RecepcionistaController extends Controller
{
    public function dashboard()
    {
        return view('recepcionista.dashboard');
    }

    public function caja()
    {
        return view('recepcionista.caja');
    }

    public function socios()
    {
        return view('recepcionista.socios');
    }
}

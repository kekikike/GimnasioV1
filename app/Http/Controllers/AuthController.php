<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('usuario')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'correo'     => 'required|email',
            'contrasena' => 'required',
        ]);

        $usuario = Usuario::findByEmail($request->correo);

        if (!$usuario || !Hash::check($request->contrasena, $usuario->contrasena)) {
            return back()->withErrors(['correo' => 'Credenciales incorrectas.'])->withInput();
        }

        session(['usuario' => $usuario]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        session()->forget('usuario');
        return redirect()->route('login');
    }
}

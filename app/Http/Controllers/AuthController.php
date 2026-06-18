<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function redirectByRole(): string
    {
        $usuario = session('usuario');
        return match ((int) $usuario->idRol) {
            1 => route('dashboard'),
            2 => route('recepcionista.dashboard'),
            3 => route('entrenador.dashboard'),
            4 => route('socio.dashboard'),
            default => route('login'),
        };
    }

    public function showLogin()
    {
        if (session()->has('usuario')) {
            return redirect()->to($this->redirectByRole());
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

        return redirect()->to($this->redirectByRole());
    }

    public function logout(Request $request)
    {
        session()->forget('usuario');
        return redirect()->route('login');
    }
}

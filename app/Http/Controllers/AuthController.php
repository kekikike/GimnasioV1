<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'correo'     => 'required|email',
            'contrasena' => 'required',
        ]);

        $usuario = Usuario::findByEmail($request->correo);

        if (!$usuario) {
            // Verificar si el usuario est� inactivo con contrato finalizado
            $inactivo = DB::select('
                SELECT u.*, e.fechaContratoFin
                FROM TUsuarios u
                LEFT JOIN TEmpleados e ON u.idUsuario = e.idUsuario
                WHERE u.correo = ? COLLATE utf8mb4_unicode_ci AND u.estadoA = 0
                LIMIT 1
            ', [$request->correo]);

            if (!empty($inactivo) && !empty($inactivo[0]->fechaContratoFin)) {
                return back()->withErrors(['correo' => 'Usuario no disponible.'])->withInput();
            }

            return back()->withErrors(['correo' => 'Credenciales incorrectas.'])->withInput();
        }

        if (!Hash::check($request->contrasena, $usuario->contrasena)) {
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

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $usuario = session('usuario');

        if (!in_array((string) $usuario->idRol, $roles, true)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está logueado
        if (!session('usuario_logueado')) {
            return redirect()->route('auth.login')
                ->with('error', 'Debes iniciar sesión para acceder a esta área.');
        }

        // Verificar si el usuario es administrador
        if (!session('es_admin')) {
            return redirect()->route('quinielas.index')
                ->with('error', 'No tienes permisos para acceder a esta área de administración.');
        }

        return $next($request);
    }
}

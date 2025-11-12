<?php

namespace App\Http\Controllers;

use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Procesar el login
     */
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'contrasena' => 'required'
        ]);

        // Buscar usuario por correo
        $usuario = Registro::where('correo', $request->correo)->first();

        if ($usuario && $usuario->contrasena === $request->contrasena) {
            // Login exitoso
            session([
                'registro_id' => $usuario->id,
                'usuario_registrado' => $usuario->nombre,
                'usuario_logueado' => true,
                'es_admin' => $usuario->es_admin,
            ]);

            // Redirigir según el tipo de usuario
            if ($usuario->es_admin) {
                return Redirect::route('admin.dashboard')
                    ->with('success', '¡Bienvenido Administrador!');
            }

            return Redirect::route('quinielas.index')
                ->with('success', '¡Bienvenido ' . $usuario->nombre . '!');
        }

        // Login fallido
        return back()->withErrors([
            'correo' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ]);
    }

    /**
     * Cerrar sesión
     */
    public function logout()
    {
        session()->forget(['registro_id', 'usuario_registrado', 'usuario_logueado', 'es_admin']);
        
        return Redirect::route('auth.login')
            ->with('success', 'Has cerrado sesión correctamente.');
    }

    /**
     * Dashboard del usuario
     */
    public function dashboard(): View
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return Redirect::route('auth.login');
        }

        $usuario = Registro::find($usuarioId);
        $chats = $usuario->chats()
            ->with(['usuarios', 'ultimoMensaje.emisor'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return view('auth.dashboard', compact('usuario', 'chats'));
    }
}

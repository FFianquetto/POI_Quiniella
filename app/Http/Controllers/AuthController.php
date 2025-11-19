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

        // Obtener puntos acumulados globales del usuario mediante consulta MySQL directa
        // Calcular automáticamente sumando puntos del Mundial + Quinielas tradicionales
        $puntosAcumulados = 0;
        $partidosAcertados = 0;
        
        // 1. Puntos del Mundial (desde user_total_points o world_cup_user_points)
        $puntosMundial = 0;
        $partidosMundial = 0;
        
        if (\Illuminate\Support\Facades\Schema::hasTable('user_total_points')) {
            $userPoint = \Illuminate\Support\Facades\DB::table('user_total_points')
                ->where('registro_id', $usuarioId)
                ->first();
            if ($userPoint) {
                // Si ya tiene puntos guardados, obtenerlos
                $puntosMundial = $userPoint->puntos_totales ?? 0;
                $partidosMundial = $userPoint->partidos_acertados ?? 0;
            }
        }
        
        // Si no tiene puntos en user_total_points, calcular desde world_cup_user_points
        if ($puntosMundial == 0 && \Illuminate\Support\Facades\Schema::hasTable('world_cup_user_points')) {
            $puntosMundial = \Illuminate\Support\Facades\DB::table('world_cup_user_points')
                ->where('registro_id', $usuarioId)
                ->sum('puntos_totales') ?? 0;
            
            $partidosMundial = \Illuminate\Support\Facades\DB::table('world_cup_user_points')
                ->where('registro_id', $usuarioId)
                ->sum('apuestas_acertadas') ?? 0;
        }
        
        // 2. Puntos de quinielas tradicionales (desde predicciones)
        $puntosQuinielasTradicionales = 0;
        $partidosQuinielasTradicionales = 0;
        
        if (\Illuminate\Support\Facades\Schema::hasTable('predicciones')) {
            $puntosQuinielasTradicionales = \Illuminate\Support\Facades\DB::table('predicciones')
                ->where('registro_id', $usuarioId)
                ->where('puntos_obtenidos', '>', 0)
                ->sum('puntos_obtenidos') ?? 0;
            
            $partidosQuinielasTradicionales = \Illuminate\Support\Facades\DB::table('predicciones')
                ->where('registro_id', $usuarioId)
                ->where('acierto', true)
                ->count();
        }
        
        // 3. Sumar totales
        $puntosAcumulados = $puntosMundial + $puntosQuinielasTradicionales;
        $partidosAcertados = $partidosMundial + $partidosQuinielasTradicionales;
        
        // 4. Actualizar user_total_points con el total calculado (para futuras consultas más rápidas)
        if (\Illuminate\Support\Facades\Schema::hasTable('user_total_points')) {
            \Illuminate\Support\Facades\DB::table('user_total_points')
                ->updateOrInsert(
                    ['registro_id' => $usuarioId],
                    [
                        'puntos_totales' => $puntosAcumulados,
                        'partidos_acertados' => $partidosAcertados,
                        'ultima_actualizacion' => now(),
                        'updated_at' => now(),
                        'created_at' => \Illuminate\Support\Facades\DB::table('user_total_points')
                            ->where('registro_id', $usuarioId)
                            ->value('created_at') ?? now()
                    ]
                );
        }

        // Obtener puntos de la última quiniela jugada mediante consulta MySQL directa
        $puntosUltimaQuiniela = 0;
        $nombreUltimaQuiniela = null;
        $fechaUltimaQuiniela = null;
        
        if (\Illuminate\Support\Facades\Schema::hasTable('world_cup_user_points') && 
            \Illuminate\Support\Facades\Schema::hasTable('world_cup_tournaments')) {
            
            // Consulta MySQL para obtener el último torneo en el que participó el usuario
            // Ordenar por fecha_calculo más reciente para obtener la última quiniela jugada
            $ultimaQuiniela = \Illuminate\Support\Facades\DB::table('world_cup_user_points as wup')
                ->join('world_cup_tournaments as wct', 'wup.tournament_id', '=', 'wct.id')
                ->where('wup.registro_id', $usuarioId)
                ->whereNotNull('wup.fecha_calculo')
                ->select(
                    'wup.puntos_totales',
                    'wup.fecha_calculo',
                    'wup.tournament_id',
                    'wct.created_at as tournament_created_at'
                )
                ->orderBy('wup.fecha_calculo', 'desc')
                ->orderBy('wup.updated_at', 'desc')
                ->first();
            
            if ($ultimaQuiniela) {
                $fechaUltimaQuiniela = $ultimaQuiniela->fecha_calculo;
                
                // Consulta MySQL para sumar todos los puntos del usuario en ese torneo (última quiniela)
                $puntosTotalesTorneo = \Illuminate\Support\Facades\DB::table('world_cup_user_points')
                    ->where('registro_id', $usuarioId)
                    ->where('tournament_id', $ultimaQuiniela->tournament_id)
                    ->sum('puntos_totales');
                
                if ($puntosTotalesTorneo > 0) {
                    $puntosUltimaQuiniela = $puntosTotalesTorneo;
                    // Generar nombre del torneo basado en la fecha de creación
                    $fechaTorneo = $ultimaQuiniela->tournament_created_at;
                    $nombreUltimaQuiniela = 'Mundial ' . \Carbon\Carbon::parse($fechaTorneo)->format('Y');
                }
            }
        }

        return view('auth.dashboard', compact(
            'usuario', 
            'chats', 
            'puntosAcumulados', 
            'partidosAcertados',
            'puntosUltimaQuiniela',
            'nombreUltimaQuiniela',
            'fechaUltimaQuiniela'
        ));
    }
}

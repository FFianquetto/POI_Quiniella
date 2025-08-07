<?php

namespace App\Http\Controllers;

use App\Models\Quiniela;
use App\Models\Partido;
use App\Models\ParticipanteQuiniela;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class QuinielaController extends Controller
{
    public function index(): View
    {
        $quinielas = Quiniela::with(['partido.equipoLocal', 'partido.equipoVisitante', 'participantes'])
            ->where('estado', 'activa')
            ->orderBy('fecha_limite')
            ->paginate(10);
            
        $usuarioId = session('registro_id');
        
        return view('quiniela.index', compact('quinielas', 'usuarioId'));
    }

    public function show(Quiniela $quiniela): View
    {
        $quiniela->load(['partido.equipoLocal', 'partido.equipoVisitante', 'participantes.usuario', 'predicciones.usuario']);
        
        $usuarioId = session('registro_id');
        $participante = $quiniela->participantes()->where('registro_id', $usuarioId)->first();
        $miPrediccion = null;
        
        if ($participante) {
            $miPrediccion = $quiniela->predicciones()->where('registro_id', $usuarioId)->first();
        }
        
        $estadisticas = $quiniela->getEstadisticasApuestas();

        return view('quiniela.show', compact('quiniela', 'participante', 'miPrediccion', 'estadisticas', 'usuarioId'));
    }

    public function participar(Request $request, Quiniela $quiniela): RedirectResponse
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return back()->with('error', 'Debes iniciar sesión para participar.');
        }
        
        if (!$quiniela->puedeParticipar($usuarioId)) {
            return back()->with('error', 'No puedes participar en esta quiniela.');
        }

        ParticipanteQuiniela::create([
            'quiniela_id' => $quiniela->id,
            'registro_id' => $usuarioId,
            'puntos_totales' => 0,
            'pagado' => true,
        ]);

        return redirect()->route('quinielas.show', $quiniela)
            ->with('success', 'Te has unido a la quiniela exitosamente.');
    }

    public function hacerPrediccion(Request $request, Quiniela $quiniela): RedirectResponse
    {
        $request->validate([
            'prediccion' => 'required|in:local,visitante,empate',
        ]);

        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return back()->with('error', 'Debes iniciar sesión para hacer predicciones.');
        }
        
        // Verificar que el usuario participe en la quiniela
        $participante = $quiniela->participantes()->where('registro_id', $usuarioId)->first();
        if (!$participante) {
            return back()->with('error', 'Debes participar en la quiniela para hacer predicciones.');
        }

        // Verificar que no haya hecho ya una predicción
        $prediccionExistente = $quiniela->predicciones()
            ->where('registro_id', $usuarioId)
            ->first();

        if ($prediccionExistente) {
            $prediccionExistente->update(['prediccion' => $request->prediccion]);
            $mensaje = 'Predicción actualizada exitosamente.';
        } else {
            $quiniela->predicciones()->create([
                'registro_id' => $usuarioId,
                'partido_id' => $quiniela->partido_id,
                'prediccion' => $request->prediccion,
            ]);
            $mensaje = 'Predicción guardada exitosamente.';
        }

        return back()->with('success', $mensaje);
    }
}

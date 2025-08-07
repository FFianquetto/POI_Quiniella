<?php

namespace App\Http\Controllers;

use App\Models\Quiniela;
use App\Models\Partido;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $quinielas = Quiniela::with(['partido.equipoLocal', 'partido.equipoVisitante', 'participantes'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        $partidos = Partido::with(['equipoLocal', 'equipoVisitante'])
            ->orderBy('fecha_hora')
            ->paginate(10);
            
        return view('admin.dashboard', compact('quinielas', 'partidos'));
    }

    public function quinielas(): View
    {
        $quinielas = Quiniela::with(['partido.equipoLocal', 'partido.equipoVisitante', 'participantes'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('admin.quinielas', compact('quinielas'));
    }

    public function definirResultado(Request $request, Quiniela $quiniela): RedirectResponse
    {
        $request->validate([
            'resultado_final' => 'required|in:local,visitante,empate',
        ]);

        $quiniela->resultado_final = $request->resultado_final;
        $quiniela->estado = 'finalizada';
        $quiniela->save();

        // Calcular ganadores
        $ganadores = $quiniela->calcularGanadores();

        return back()->with('success', 'Resultado definido y ganadores calculados exitosamente. ' . count($ganadores) . ' ganadores.');
    }

    public function crearQuiniela(Request $request): RedirectResponse
    {
        $request->validate([
            'partido_id' => 'required|exists:partidos,id',
            'fecha_limite' => 'required|date|after:now',
            'puntos_ganador' => 'required|integer|min:1',
        ]);

        // Verificar que no exista ya una quiniela para este partido
        $quinielaExistente = Quiniela::where('partido_id', $request->partido_id)->first();
        if ($quinielaExistente) {
            return back()->with('error', 'Ya existe una quiniela para este partido.');
        }

        $partido = Partido::find($request->partido_id);
        
        Quiniela::create([
            'nombre' => 'Quiniela: ' . $partido->equipoLocal->nombre . ' vs ' . $partido->equipoVisitante->nombre,
            'descripcion' => 'Adivina el resultado del partido entre ' . $partido->equipoLocal->nombre . ' y ' . $partido->equipoVisitante->nombre,
            'partido_id' => $request->partido_id,
            'fecha_limite' => $request->fecha_limite,
            'estado' => 'activa',
            'puntos_ganador' => $request->puntos_ganador,
        ]);

        return back()->with('success', 'Quiniela creada exitosamente.');
    }
}

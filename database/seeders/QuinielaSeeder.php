<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiniela;
use App\Models\Partido;
use Carbon\Carbon;

class QuinielaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear algunos partidos de ejemplo si no existen
        $partidos = Partido::all();
        
        if ($partidos->count() === 0) {
            // Crear partidos de ejemplo
            $equipos = \App\Models\Equipo::all();
            
            for ($i = 0; $i < 5; $i++) {
                $equipoLocal = $equipos->random();
                $equipoVisitante = $equipos->where('id', '!=', $equipoLocal->id)->random();
                
                Partido::create([
                    'equipo_local_id' => $equipoLocal->id,
                    'equipo_visitante_id' => $equipoVisitante->id,
                    'fecha_hora' => Carbon::now()->addDays($i + 1)->setTime(20, 0),
                    'estadio' => 'Estadio ' . ($i + 1),
                    'liga' => $equipoLocal->liga,
                    'estado' => 'programado',
                ]);
            }
            
            $partidos = Partido::all();
        }

        // Crear quinielas para cada partido
        foreach ($partidos as $partido) {
            Quiniela::create([
                'nombre' => 'Quiniela: ' . $partido->equipoLocal->nombre . ' vs ' . $partido->equipoVisitante->nombre,
                'descripcion' => 'Adivina el resultado del partido entre ' . $partido->equipoLocal->nombre . ' y ' . $partido->equipoVisitante->nombre,
                'partido_id' => $partido->id,
                'fecha_limite' => $partido->fecha_hora->subHour(),
                'estado' => 'activa',
                'puntos_ganador' => 10,
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chat;
use App\Models\Mensaje;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios
        $juan = \App\Models\Registro::where('correo', 'juan@example.com')->first();
        $maria = \App\Models\Registro::where('correo', 'maria@example.com')->first();
        $carlos = \App\Models\Registro::where('correo', 'carlos@example.com')->first();

        if ($juan && $maria && $carlos) {
            // Crear chat entre Juan y María
            $chat1 = Chat::encontrarOcrearChat($juan->id, $maria->id);
            
            // Agregar algunos mensajes de ejemplo
            Mensaje::create([
                'chat_id' => $chat1->id,
                'registro_id_emisor' => $juan->id,
                'contenido' => '¡Hola María! ¿Cómo estás?',
                'tipo' => 'texto',
                'leido' => true,
            ]);

            Mensaje::create([
                'chat_id' => $chat1->id,
                'registro_id_emisor' => $maria->id,
                'contenido' => '¡Hola Juan! Muy bien, gracias. ¿Y tú?',
                'tipo' => 'texto',
                'leido' => true,
            ]);

            Mensaje::create([
                'chat_id' => $chat1->id,
                'registro_id_emisor' => $juan->id,
                'contenido' => 'Perfecto! ¿Ya viste las quinielas disponibles?',
                'tipo' => 'texto',
                'leido' => false,
            ]);

            // Crear chat entre María y Carlos
            $chat2 = Chat::encontrarOcrearChat($maria->id, $carlos->id);
            
            Mensaje::create([
                'chat_id' => $chat2->id,
                'registro_id_emisor' => $maria->id,
                'contenido' => '¡Hola Carlos! ¿Te gusta el fútbol?',
                'tipo' => 'texto',
                'leido' => true,
            ]);

            Mensaje::create([
                'chat_id' => $chat2->id,
                'registro_id_emisor' => $carlos->id,
                'contenido' => '¡Claro! Soy fanático del fútbol. ¿Tú también?',
                'tipo' => 'texto',
                'leido' => true,
            ]);

            // Crear chat entre Juan y Carlos
            $chat3 = Chat::encontrarOcrearChat($juan->id, $carlos->id);
            
            Mensaje::create([
                'chat_id' => $chat3->id,
                'registro_id_emisor' => $carlos->id,
                'contenido' => '¡Hola Juan! ¿Ya participaste en alguna quiniela?',
                'tipo' => 'texto',
                'leido' => true,
            ]);

            Mensaje::create([
                'chat_id' => $chat3->id,
                'registro_id_emisor' => $juan->id,
                'contenido' => '¡Sí! Ya participé en varias. Es muy divertido.',
                'tipo' => 'texto',
                'leido' => false,
            ]);

            // Agregar mensajes multimedia de ejemplo
            Mensaje::create([
                'chat_id' => $chat1->id,
                'registro_id_emisor' => $maria->id,
                'contenido' => 'Mira esta foto del estadio',
                'tipo' => 'imagen',
                'archivo_url' => '/storage/chat_archivos/ejemplo_estadio.jpg',
                'archivo_nombre' => 'estadio.jpg',
                'leido' => true,
            ]);

            Mensaje::create([
                'chat_id' => $chat2->id,
                'registro_id_emisor' => $carlos->id,
                'contenido' => 'Escucha este audio sobre el partido',
                'tipo' => 'audio',
                'archivo_url' => '/storage/chat_archivos/ejemplo_audio.mp3',
                'archivo_nombre' => 'comentario_partido.mp3',
                'leido' => false,
            ]);
        }
    }
}

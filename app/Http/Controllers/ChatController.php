<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Mensaje;
use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * Mostrar lista de chats del usuario
     */
    public function index(): View
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }

        $usuario = Registro::find($usuarioId);
        $chats = $usuario->chats()
            ->with(['usuarios', 'ultimoMensaje.emisor'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('chat.index', compact('chats', 'usuario'));
    }

    /**
     * Mostrar un chat específico
     */
    public function show(Chat $chat): View|RedirectResponse
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }

        if (!$chat->tieneUsuario($usuarioId)) {
            return redirect()->route('chat.index')->with('error', 'No tienes acceso a este chat.');
        }

        $usuario = Registro::find($usuarioId);
        $mensajes = $chat->mensajes()->with('emisor')->get();
        
        // Si es un chat grupal, redirigir a la vista específica de grupo
        if ($chat->esGrupal()) {
            return redirect()->route('chat.grupo.show', $chat->id);
        }
        
        $otroUsuario = $chat->otroUsuario($usuarioId);

        // Marcar mensajes como leídos
        $chat->mensajes()
            ->where('registro_id_emisor', '!=', $usuarioId)
            ->where('leido', false)
            ->update(['leido' => true]);

        // Marcar mensajes como entregados
        $chat->mensajes()
            ->where('registro_id_emisor', '!=', $usuarioId)
            ->where('entregado', false)
            ->update([
                'entregado' => true,
                'entregado_at' => now(),
            ]);

        return view('chat.show', compact('chat', 'mensajes', 'usuario', 'otroUsuario'));
    }

    /**
     * Crear o abrir chat con otro usuario
     */
    public function crearChat(Request $request): RedirectResponse
    {
        $request->validate([
            'usuario_id' => 'required|exists:registros,id'
        ]);

        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }

        if ($usuarioId == $request->usuario_id) {
            return back()->with('error', 'No puedes chatear contigo mismo.');
        }

        $chat = Chat::encontrarOcrearChat($usuarioId, $request->usuario_id);

        return redirect()->route('chat.show', $chat);
    }

    /**
     * Enviar mensaje
     */
    public function enviarMensaje(Request $request, Chat $chat): RedirectResponse
    {
        $request->validate([
            'contenido' => 'required_without:archivo|string|max:1000',
            'tipo' => 'in:texto,imagen,video,audio,archivo',
            'archivo' => 'nullable|file|max:10240', // 10MB máximo
        ]);

        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }

        if (!$chat->tieneUsuario($usuarioId)) {
            return back()->with('error', 'No tienes acceso a este chat.');
        }

        $mensajeData = [
            'chat_id' => $chat->id,
            'registro_id_emisor' => $usuarioId,
            'contenido' => $request->contenido ?? '',
            'tipo' => $request->tipo ?? 'texto',
            'leido' => false,
            'entregado' => false,
        ];

        // Manejar archivo subido
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo');
            
            // Validar tipo de archivo
            $extension = strtolower($archivo->getClientOriginalExtension());
            $allowedTypes = config('chat.allowed_file_types');
            $tipoValido = false;
            
            foreach ($allowedTypes as $tipo => $extensiones) {
                if (in_array($extension, $extensiones)) {
                    $mensajeData['tipo'] = $tipo;
                    $tipoValido = true;
                    break;
                }
            }
            
            if (!$tipoValido) {
                return back()->with('error', 'Tipo de archivo no permitido: ' . $extension);
            }
            
            // Generar nombre único para el archivo
            $nombreArchivo = time() . '_' . uniqid() . '_' . $archivo->getClientOriginalName();
            
            // Guardar archivo
            $rutaCompleta = $archivo->storeAs('chat_archivos', $nombreArchivo, 'public');
            
            if (!$rutaCompleta) {
                return back()->with('error', 'Error al guardar el archivo.');
            }
            
            // Generar URL pública para el archivo
            $mensajeData['archivo_url'] = asset('storage/' . $rutaCompleta);
            $mensajeData['archivo_nombre'] = $archivo->getClientOriginalName();
        }

        $mensaje = $chat->mensajes()->create($mensajeData);

        return back()->with('success', 'Mensaje enviado.');
    }

    /**
     * Buscar usuarios para chatear
     */
    public function buscarUsuarios(): View
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }

        $usuario = Registro::find($usuarioId);
        $usuarios = Registro::where('id', '!=', $usuarioId)
            ->where('nombre', 'like', '%' . request('q', '') . '%')
            ->paginate(10);

        return view('chat.buscar', compact('usuarios', 'usuario'));
    }

    /**
     * Iniciar videollamada
     */
    public function iniciarVideollamada(Request $request, Chat $chat): \Illuminate\Http\JsonResponse
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        if (!$chat->tieneUsuario($usuarioId)) {
            return response()->json(['error' => 'No tienes acceso a este chat'], 403);
        }

        // Generar ID único para la videollamada
        $callId = uniqid('call_', true);
        
        // En una implementación real, aquí guardarías la información de la videollamada
        // y notificarías al otro usuario a través de WebSockets
        
        return response()->json([
            'success' => true,
            'call_id' => $callId,
            'chat_id' => $chat->id
        ]);
    }

    /**
     * Registrar llamada perdida en el chat
     */
    public function registrarLlamadaPerdida(Request $request, Chat $chat): \Illuminate\Http\JsonResponse
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        if (!$chat->tieneUsuario($usuarioId)) {
            return response()->json(['error' => 'No tienes acceso a este chat'], 403);
        }

        // Obtener el otro usuario
        $otroUsuario = $chat->otroUsuario($usuarioId);
        if (!$otroUsuario) {
            return response()->json(['error' => 'No se encontró el otro usuario'], 404);
        }

        // Crear mensaje de llamada perdida
        // El mensaje se crea como si lo enviara el sistema, pero se muestra como del usuario que rechazó
        $mensaje = $chat->mensajes()->create([
            'registro_id_emisor' => $otroUsuario->id, // El que rechazó
            'contenido' => 'Llamada perdida',
            'tipo' => 'llamada_perdida', // Tipo especial para llamadas perdidas
            'leido' => false,
            'entregado' => true,
            'entregado_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Llamada perdida registrada',
            'mensaje_id' => $mensaje->id
        ]);
    }

    /**
     * Manejar señalización WebRTC
     */
    public function señalizacion(Request $request, Chat $chat): \Illuminate\Http\JsonResponse
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        if (!$chat->tieneUsuario($usuarioId)) {
            return response()->json(['error' => 'No tienes acceso a este chat'], 403);
        }

        $request->validate([
            'tipo' => 'required|in:offer,answer,ice-candidate,call-rejected',
            'datos' => 'required|array',
            'call_id' => 'nullable|string',
            'usuario_id' => 'required|integer'
        ]);

        // Obtener el otro usuario del chat
        $otroUsuario = $chat->otroUsuario($usuarioId);
        if (!$otroUsuario) {
            return response()->json(['error' => 'No se encontró el otro usuario'], 404);
        }

        // Guardar el mensaje de señalización en cache para que el otro usuario lo recupere
        $cacheKey = "videollamada_signaling_{$chat->id}_{$otroUsuario->id}";
        $mensajes = \Illuminate\Support\Facades\Cache::get($cacheKey, []);
        
        $mensajes[] = [
            'tipo' => $request->tipo,
            'datos' => $request->datos,
            'call_id' => $request->call_id,
            'from_usuario_id' => $usuarioId,
            'timestamp' => now()->toIso8601String()
        ];
        
        // Guardar en cache por 5 minutos
        \Illuminate\Support\Facades\Cache::put($cacheKey, $mensajes, 300);
        
        // Log para debugging en producción (solo en modo debug)
        if (config('app.debug')) {
            \Log::info('Señalización WebRTC', [
                'chat_id' => $chat->id,
                'usuario_id' => $usuarioId,
                'tipo' => $request->tipo,
                'otro_usuario_id' => $otroUsuario->id
            ]);
        }
        
        return response()->json([
            'success' => true,
            'mensaje' => 'Señalización procesada'
        ]);
    }

    /**
     * Obtener mensajes de señalización pendientes (polling tradicional)
     */
    public function obtenerSeñalizacion(Request $request, Chat $chat): \Illuminate\Http\JsonResponse
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        if (!$chat->tieneUsuario($usuarioId)) {
            return response()->json(['error' => 'No tienes acceso a este chat'], 403);
        }

        // Obtener mensajes de señalización para este usuario
        $cacheKey = "videollamada_signaling_{$chat->id}_{$usuarioId}";
        $mensajes = \Illuminate\Support\Facades\Cache::get($cacheKey, []);
        
        // Limpiar los mensajes después de leerlos
        \Illuminate\Support\Facades\Cache::forget($cacheKey);
        
        // Log para debugging
        if (config('app.debug') && count($mensajes) > 0) {
            \Log::info('Señalización recuperada', [
                'chat_id' => $chat->id,
                'usuario_id' => $usuarioId,
                'mensajes_count' => count($mensajes)
            ]);
        }
        
        return response()->json([
            'success' => true,
            'mensajes' => $mensajes
        ]);
    }

    /**
     * Server-Sent Events para señalización en tiempo real
     */
    public function señalizacionStream(Request $request, Chat $chat)
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return response('No autorizado', 401);
        }

        if (!$chat->tieneUsuario($usuarioId)) {
            return response('No tienes acceso a este chat', 403);
        }

        // Configurar headers para SSE
        return response()->stream(function () use ($chat, $usuarioId) {
            $cacheKey = "videollamada_signaling_{$chat->id}_{$usuarioId}";
            $lastCheck = now();
            $timeout = 60; // 60 segundos de timeout
            $startTime = time();
            $heartbeatInterval = 5; // Enviar heartbeat cada 5 segundos
            
            // Enviar comentario inicial para establecer conexión
            echo ": connected\n\n";
            ob_flush();
            flush();
            
            while (true) {
                // Verificar timeout
                if (time() - $startTime > $timeout) {
                    echo "data: " . json_encode(['type' => 'timeout']) . "\n\n";
                    ob_flush();
                    flush();
                    break;
                }
                
                // Verificar si hay mensajes nuevos
                $mensajes = \Illuminate\Support\Facades\Cache::get($cacheKey, []);
                
                if (!empty($mensajes)) {
                    // Enviar todos los mensajes pendientes
                    foreach ($mensajes as $mensaje) {
                        echo "data: " . json_encode($mensaje) . "\n\n";
                        ob_flush();
                        flush();
                    }
                    
                    // Limpiar los mensajes después de enviarlos
                    \Illuminate\Support\Facades\Cache::forget($cacheKey);
                    break; // Cerrar la conexión después de enviar los mensajes
                }
                
                // Enviar heartbeat cada 5 segundos para mantener la conexión viva
                if (now()->diffInSeconds($lastCheck) >= $heartbeatInterval) {
                    echo ": heartbeat\n\n";
                    ob_flush();
                    flush();
                    $lastCheck = now();
                }
                
                // Esperar 500ms antes de la siguiente verificación
                usleep(500000);
                
                // Verificar si la conexión sigue viva
                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Deshabilitar buffering en Nginx
        ]);
    }

    /**
     * Obtener mensajes pendientes para el usuario (modo nube)
     */
    public function recibirPendientes(Request $request): \Illuminate\Http\JsonResponse
    {
        $usuarioId = session('registro_id');

        if (!$usuarioId) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $mensajesPendientes = Mensaje::with(['chat', 'emisor'])
            ->where('entregado', false)
            ->where('registro_id_emisor', '!=', $usuarioId)
            ->whereHas('chat', function ($query) use ($usuarioId) {
                $query->whereHas('usuarios', function ($subQuery) use ($usuarioId) {
                    $subQuery->where('registro_id', $usuarioId);
                });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $ahora = now();

        if ($mensajesPendientes->isNotEmpty()) {
            Mensaje::whereIn('id', $mensajesPendientes->pluck('id'))
                ->update([
                    'entregado' => true,
                    'entregado_at' => $ahora,
                ]);

            $mensajesPendientes->each(function (Mensaje $mensaje) use ($ahora) {
                $mensaje->entregado = true;
                $mensaje->entregado_at = $ahora;
            });
        }

        $data = $mensajesPendientes->map(function (Mensaje $mensaje) {
            return [
                'id' => $mensaje->id,
                'chat_id' => $mensaje->chat_id,
                'contenido' => $mensaje->contenido,
                'tipo' => $mensaje->tipo,
                'archivo_url' => $mensaje->archivo_url,
                'archivo_nombre' => $mensaje->archivo_nombre,
                'creado_en' => optional($mensaje->created_at)->toIso8601String(),
                'entregado_en' => optional($mensaje->entregado_at)->toIso8601String(),
                'emisor' => [
                    'id' => optional($mensaje->emisor)->id,
                    'nombre' => optional($mensaje->emisor)->nombre,
                ],
            ];
        });

        return response()->json(['data' => $data]);
    }
}

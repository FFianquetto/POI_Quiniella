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
            'tipo' => 'required|in:offer,answer,ice-candidate',
            'datos' => 'required|array'
        ]);

        // En una implementación real, aquí procesarías la señalización
        // y la enviarías al otro usuario a través de WebSockets
        
        return response()->json([
            'success' => true,
            'mensaje' => 'Señalización procesada'
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

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
    public function show(Chat $chat): View
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }

        if (!$chat->tieneUsuario($usuarioId)) {
            return redirect()->route('chats.index')->with('error', 'No tienes acceso a este chat.');
        }

        $usuario = Registro::find($usuarioId);
        $mensajes = $chat->mensajes()->with('emisor')->get();
        $otroUsuario = $chat->otroUsuario($usuarioId);

        // Marcar mensajes como leídos
        $chat->mensajes()
            ->where('registro_id_emisor', '!=', $usuarioId)
            ->where('leido', false)
            ->update(['leido' => true]);

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

        return redirect()->route('chats.show', $chat);
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

        $usuarios = Registro::where('id', '!=', $usuarioId)
            ->orderBy('nombre')
            ->get();

        return view('chat.buscar', compact('usuarios'));
    }
}

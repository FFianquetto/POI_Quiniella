<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\ChatGrupoTarea;
use App\Models\Registro;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatGrupoController extends Controller
{
    /**
     * Mostrar formulario para crear un nuevo grupo
     */
    public function create()
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }
        
        $usuarios = Registro::where('id', '!=', $usuarioId)->get();
        return view('chat.grupo.create', compact('usuarios'));
    }

    /**
     * Crear un nuevo grupo
     */
    public function store(Request $request)
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }
        
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'miembros' => 'required|array|min:2', // Mínimo 2 miembros + creador = 3 total
            'miembros.*' => 'exists:registros,id'
        ], [
            'nombre.required' => 'El nombre del grupo es obligatorio',
            'nombre.max' => 'El nombre del grupo no puede exceder 255 caracteres',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres',
            'miembros.required' => 'Debe seleccionar al menos 2 miembros',
            'miembros.min' => 'Debe seleccionar al menos 2 miembros',
            'miembros.*.exists' => 'Uno o más usuarios seleccionados no existen'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $chat = Chat::crearGrupo(
                $request->nombre,
                $request->descripcion,
                $usuarioId,
                $request->miembros
            );

            return redirect()->route('chat.grupo.show', $chat->id)
                ->with('success', 'Grupo creado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mostrar información del grupo
     */
    public function show($id)
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }
        
        $chat = Chat::with([
                        'usuarios',
                        'administradores',
                        'creador',
                        'mensajes.emisor',
                        'tareas.asignadoA',
                        'tareas.creador',
                        'tareas.completadoPor',
                    ])
                    ->findOrFail($id);

        if (!$chat->esGrupal()) {
            abort(404);
        }

        if (!$chat->tieneUsuario($usuarioId)) {
            abort(403);
        }

        // Marcar mensajes como leídos
        $chat->mensajes()
            ->where('registro_id_emisor', '!=', $usuarioId)
            ->where('leido', false)
            ->update(['leido' => true]);

        $tareasPendientes = $chat->tareas
            ->where('estado', ChatGrupoTarea::ESTADO_PENDIENTE)
            ->sortBy('created_at');

        $tareasCompletadas = $chat->tareas
            ->where('estado', ChatGrupoTarea::ESTADO_COMPLETADA)
            ->sortByDesc('completado_at');

        return view('chat.grupo.show', compact('chat', 'tareasPendientes', 'tareasCompletadas'));
    }

    /**
     * Mostrar formulario para editar grupo
     */
    public function edit($id)
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }
        
        $chat = Chat::findOrFail($id);

        if (!$chat->esGrupal()) {
            abort(404);
        }

        if (!$chat->esAdministrador($usuarioId)) {
            abort(403);
        }

        $usuariosDisponibles = Registro::whereNotIn('id', $chat->usuarios->pluck('id'))
                                     ->get();

        return view('chat.grupo.edit', compact('chat', 'usuariosDisponibles'));
    }

    /**
     * Actualizar información del grupo
     */
    public function update(Request $request, $id)
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }
        
        $chat = Chat::findOrFail($id);

        if (!$chat->esGrupal()) {
            abort(404);
        }

        if (!$chat->esAdministrador($usuarioId)) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500'
        ], [
            'nombre.required' => 'El nombre del grupo es obligatorio',
            'nombre.max' => 'El nombre del grupo no puede exceder 255 caracteres',
            'descripcion.max' => 'La descripción no puede exceder 500 caracteres'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $chat->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion
        ]);

        return redirect()->route('chat.grupo.show', $chat->id)
            ->with('success', 'Información del grupo actualizada');
    }

    /**
     * Agregar miembros al grupo
     */
    public function agregarMiembros(Request $request, $id)
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }
        
        $chat = Chat::findOrFail($id);

        if (!$chat->esGrupal()) {
            abort(404);
        }

        if (!$chat->esAdministrador($usuarioId)) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'miembros' => 'required|array|min:1',
            'miembros.*' => 'exists:registros,id'
        ], [
            'miembros.required' => 'Debe seleccionar al menos un miembro',
            'miembros.min' => 'Debe seleccionar al menos un miembro',
            'miembros.*.exists' => 'Uno o más usuarios seleccionados no existen'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            $chat->agregarMiembros($request->miembros);
            return redirect()->back()
                ->with('success', 'Miembros agregados exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remover miembros del grupo
     */
    public function removerMiembros(Request $request, $id)
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }
        
        $chat = Chat::findOrFail($id);

        if (!$chat->esGrupal()) {
            abort(404);
        }

        if (!$chat->esAdministrador($usuarioId)) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'miembros' => 'required|array|min:1',
            'miembros.*' => 'exists:registros,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            $chat->removerMiembros($request->miembros);
            return redirect()->back()
                ->with('success', 'Miembros removidos exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Promover usuario a administrador
     */
    public function promoverAdministrador(Request $request, $id)
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }
        
        $chat = Chat::findOrFail($id);

        if (!$chat->esGrupal()) {
            abort(404);
        }

        if (!$chat->esAdministrador($usuarioId)) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|exists:registros,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            $chat->promoverAdministrador($request->usuario_id);
            return redirect()->back()
                ->with('success', 'Usuario promovido a administrador');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Degradar administrador a miembro común
     */
    public function degradarAdministrador(Request $request, $id)
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }
        
        $chat = Chat::findOrFail($id);

        if (!$chat->esGrupal()) {
            abort(404);
        }

        if (!$chat->esAdministrador($usuarioId)) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|exists:registros,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        try {
            $chat->degradarAdministrador($request->usuario_id);
            return redirect()->back()
                ->with('success', 'Administrador degradado a miembro común');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Abandonar grupo
     */
    public function abandonar($id)
    {
        $usuarioId = session('registro_id');
        
        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }
        
        $chat = Chat::findOrFail($id);

        if (!$chat->esGrupal()) {
            abort(404);
        }

        if (!$chat->tieneUsuario($usuarioId)) {
            abort(403);
        }

        // No permitir que el creador abandone el grupo
        if ($chat->creador_id == $usuarioId) {
            return redirect()->back()
                ->withErrors(['error' => 'El creador del grupo no puede abandonarlo']);
        }

        $chat->usuarios()->detach($usuarioId);
        $chat->administradores()->detach($usuarioId);

        return redirect()->route('chat.index')
            ->with('success', 'Has abandonado el grupo');
    }

    /**
     * Crear una tarea dentro del grupo
     */
    public function crearTarea(Request $request, $id)
    {
        $usuarioId = session('registro_id');

        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }

        $chat = Chat::with('usuarios')->findOrFail($id);

        if (!$chat->esGrupal() || !$chat->tieneUsuario($usuarioId)) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'asignado_a' => 'nullable|exists:registros,id',
        ], [
            'titulo.required' => 'El título de la tarea es obligatorio.',
            'titulo.max' => 'El título no puede exceder 255 caracteres.',
            'descripcion.max' => 'La descripción no puede exceder 2000 caracteres.',
            'asignado_a.exists' => 'El usuario seleccionado no es válido.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('chat.grupo.show', $id)
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->filled('asignado_a') && !$chat->usuarios->contains('id', $request->asignado_a)) {
            return redirect()->route('chat.grupo.show', $id)
                ->withErrors(['asignado_a' => 'El usuario asignado debe ser miembro del grupo.'])
                ->withInput();
        }

        $chat->tareas()->create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'creado_por' => $usuarioId,
            'asignado_a' => $request->asignado_a,
            'estado' => ChatGrupoTarea::ESTADO_PENDIENTE,
        ]);

        // Redirigir a la misma página del chat para recargar
        return redirect()->route('chat.grupo.show', $id)->with('success', 'Tarea creada exitosamente.');
    }

    /**
     * Marcar una tarea como completada
     */
    public function completarTarea(Request $request, $chatId, $tareaId)
    {
        $usuarioId = session('registro_id');

        if (!$usuarioId) {
            return redirect()->route('auth.login');
        }

        $chat = Chat::with('usuarios')->findOrFail($chatId);

        if (!$chat->esGrupal() || !$chat->tieneUsuario($usuarioId)) {
            abort(403);
        }

        $tarea = $chat->tareas()->findOrFail($tareaId);

        if ($tarea->estaCompletada()) {
            return redirect()->route('chat.grupo.show', $chatId)->with('info', 'La tarea ya fue completada.');
        }

        // Permitimos completar a cualquier miembro del grupo
        $tarea->marcarComoCompletada($usuarioId);

        // Redirigir a la misma página del chat para recargar
        return redirect()->route('chat.grupo.show', $chatId)->with('success', 'Tarea marcada como completada.');
    }
}

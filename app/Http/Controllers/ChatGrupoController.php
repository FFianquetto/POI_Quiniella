<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
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
        
        $chat = Chat::with(['usuarios', 'administradores', 'creador', 'mensajes.emisor'])
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

        return view('chat.grupo.show', compact('chat'));
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
}

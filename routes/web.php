<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PublicacioneController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuinielaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\TorneoController;
use App\Http\Controllers\ChatGrupoController;

Route::get('/', function () {
    return redirect()->route('auth.login');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('auth.login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('auth.dashboard');

// Rutas de recursos
Route::resource('registros', RegistroController::class);
Route::resource('publicaciones', PublicacioneController::class);
Route::resource('comentarios', ComentarioController::class);

// Rutas adicionales para comentarios
Route::get('/comentarios/conversacion/{usuario1}/{usuario2}', [ComentarioController::class, 'conversacion'])->name('comentarios.conversacion');

// Rutas para quinielas (formato casa de apuestas)
Route::get('/quinielas', [QuinielaController::class, 'index'])->name('quinielas.index');
Route::get('/quinielas/{quiniela}', [QuinielaController::class, 'show'])->name('quinielas.show');
Route::post('/quinielas/{quiniela}/participar', [QuinielaController::class, 'participar'])->name('quinielas.participar');
Route::post('/quinielas/{quiniela}/prediccion', [QuinielaController::class, 'hacerPrediccion'])->name('quinielas.prediccion');
Route::post('/quinielas/mundial/apostar', [QuinielaController::class, 'apostarMundial'])->name('quinielas.mundial.apostar');

// Rutas para administración
Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/quinielas', [AdminController::class, 'quinielas'])->name('admin.quinielas');
    Route::post('/quinielas/{quiniela}/resultado', [AdminController::class, 'definirResultado'])->name('admin.quinielas.resultado');
    Route::post('/quinielas/crear', [AdminController::class, 'crearQuiniela'])->name('admin.quinielas.crear');
    
});

// Rutas para el sistema de chats
Route::get('/chats', [ChatController::class, 'index'])->name('chat.index');
Route::get('/chats/buscar', [ChatController::class, 'buscarUsuarios'])->name('chat.buscar');
Route::get('/chats/{chat}', [ChatController::class, 'show'])->name('chat.show');
Route::post('/chats/crear', [ChatController::class, 'crearChat'])->name('chat.crear');
Route::post('/chats/{chat}/mensaje', [ChatController::class, 'enviarMensaje'])->name('chat.mensaje');
Route::get('/chats/pendientes', [ChatController::class, 'recibirPendientes'])->name('chat.pendientes');

// Rutas para chats grupales
Route::prefix('chat/grupo')->name('chat.grupo.')->group(function () {
    Route::get('/crear', [ChatGrupoController::class, 'create'])->name('create');
    Route::post('/crear', [ChatGrupoController::class, 'store'])->name('store');
    Route::get('/{id}', [ChatGrupoController::class, 'show'])->name('show');
    Route::get('/{id}/editar', [ChatGrupoController::class, 'edit'])->name('edit');
    Route::put('/{id}', [ChatGrupoController::class, 'update'])->name('update');
    Route::post('/{id}/agregar-miembros', [ChatGrupoController::class, 'agregarMiembros'])->name('agregar-miembros');
    Route::post('/{id}/remover-miembros', [ChatGrupoController::class, 'removerMiembros'])->name('remover-miembros');
    Route::post('/{id}/promover-admin', [ChatGrupoController::class, 'promoverAdministrador'])->name('promover-admin');
    Route::post('/{id}/degradar-admin', [ChatGrupoController::class, 'degradarAdministrador'])->name('degradar-admin');
    Route::post('/{id}/tareas', [ChatGrupoController::class, 'crearTarea'])->name('tareas.crear');
    Route::post('/{chat}/tareas/{tarea}/completar', [ChatGrupoController::class, 'completarTarea'])->name('tareas.completar');
    Route::delete('/{id}/abandonar', [ChatGrupoController::class, 'abandonar'])->name('abandonar');
});

// Rutas para videollamadas
Route::post('/chats/{chat}/videollamada/iniciar', [ChatController::class, 'iniciarVideollamada'])->name('chat.videollamada.iniciar');
Route::post('/chats/{chat}/videollamada/señalizacion', [ChatController::class, 'señalizacion'])->name('chat.videollamada.señalizacion');

// Rutas para el simulador de torneo
Route::get('/torneo', [TorneoController::class, 'index'])->name('torneo.index');
Route::post('/torneo/generate', [TorneoController::class, 'generate'])->name('torneo.generate');
Route::post('/torneo/simulate-match', [TorneoController::class, 'simulateMatch'])->name('torneo.simulate-match');
Route::get('/torneo/stats', [TorneoController::class, 'getStats'])->name('torneo.stats');
Route::post('/torneo/store', [TorneoController::class, 'store'])->name('torneo.store');
Route::patch('/torneo/{uuid}', [TorneoController::class, 'update'])->name('torneo.update');

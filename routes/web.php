<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PublicacioneController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\QuinielaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ComentarioController;

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

// Rutas para equipos (solo ver información)
Route::get('/equipos', [EquipoController::class, 'index'])->name('equipos.index');
Route::get('/equipos/{equipo}', [EquipoController::class, 'show'])->name('equipos.show');

// Rutas para quinielas (formato casa de apuestas)
Route::get('/quinielas', [QuinielaController::class, 'index'])->name('quinielas.index');
Route::get('/quinielas/{quiniela}', [QuinielaController::class, 'show'])->name('quinielas.show');
Route::post('/quinielas/{quiniela}/participar', [QuinielaController::class, 'participar'])->name('quinielas.participar');
Route::post('/quinielas/{quiniela}/prediccion', [QuinielaController::class, 'hacerPrediccion'])->name('quinielas.prediccion');

// Rutas para administración
Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/quinielas', [AdminController::class, 'quinielas'])->name('admin.quinielas');
    Route::post('/quinielas/{quiniela}/resultado', [AdminController::class, 'definirResultado'])->name('admin.quinielas.resultado');
    Route::post('/quinielas/crear', [AdminController::class, 'crearQuiniela'])->name('admin.quinielas.crear');
});

// Rutas para el sistema de chats
Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
Route::get('/chats/buscar', [ChatController::class, 'buscarUsuarios'])->name('chats.buscar');
Route::get('/chats/{chat}', [ChatController::class, 'show'])->name('chats.show');
Route::post('/chats/crear', [ChatController::class, 'crearChat'])->name('chats.crear');
Route::post('/chats/{chat}/mensaje', [ChatController::class, 'enviarMensaje'])->name('chats.mensaje');

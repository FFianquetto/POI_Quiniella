<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\PublicacioneController;
Route::get('/', function () {
    return view('welcome');
});

// Rutas para registros
Route::resource('registros', RegistroController::class);

// Rutas para comentarios
Route::resource('comentarios', ComentarioController::class);

// Rutas para comentarios
Route::resource('publicaciones', PublicacioneController::class);


// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

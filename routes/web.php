<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\PublicacioneController;
Route::get('/', function () {
    return redirect()->route('registros.index');
});

Route::resource('registros', RegistroController::class);

Route::resource('comentarios', ComentarioController::class);

Route::resource('publicaciones', PublicacioneController::class);

//Todo conectadito y funcionando

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

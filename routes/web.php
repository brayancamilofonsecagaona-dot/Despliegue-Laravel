<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RecuperacionController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TareaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Registro
Route::get('/registro', [RegisterController::class, 'create'])->name('register');
Route::post('/registro', [RegisterController::class, 'store']);

// Login
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);

// Cierre de sesión (redirige al login)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// CA 2.5 y 2.6: ruta privada y protegida
Route::get('/privada', function () {
    return view('privada');
})->middleware('auth')->name('privada');

// Recuperación de contraseña
Route::get('/recuperacion', function () {
    return view('recuperacion');
})->name('password.request');
Route::post('/recuperacion', [RecuperacionController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [RecuperacionController::class, 'showResetForm'])->name('password.reset');
Route::post('/update-password', [RecuperacionController::class, 'updatePassword'])->name('password.update');

// Tareas (solo usuarios autenticados)
Route::middleware('auth')->group(function () {
    // Debe ir antes del resource para que "calendario" no se confunda con {tarea}
    Route::get('tareas/calendario', [TareaController::class, 'calendario'])->name('tareas.calendario');
    Route::resource('tareas', TareaController::class);
});
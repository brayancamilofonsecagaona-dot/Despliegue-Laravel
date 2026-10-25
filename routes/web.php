<?php

use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/registro', [RegisterController::class, 'create'])->name('register');
Route::post('/registro', [RegisterController::class, 'store']);
Route:: get('/login', [LoginController::class, 'create'])->name ('login');
Route::post('/login', [LoginController::class, 'store']);

//CA 2.5 y 2.6: Ruta privada y protegida
Route::get('/privada', function(){
    return view ('privada');})-> middleware('auth'); //filtro de seguridad

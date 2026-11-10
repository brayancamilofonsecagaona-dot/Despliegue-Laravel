<?php

use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RecuperacionController;
use App\Http\Controllers\LoginController;

Route::get('/', function () {
    return view('welcome');
});

//La ruta que recibe el nombre de un registro
Route::get('/registro', [RegisterController::class, 'create'])->name('register');
//La ruta que guarda los datos del registro
Route::post('/registro', [RegisterController::class, 'store']);
//La ruta que recibe el nombre de un login
Route:: get('/login', [LoginController::class, 'create'])->name ('login');
//La ruta que recibe el almacenamiento de los datos del login
Route::post('/login', [LoginController::class, 'store']);
//La ruta que recibe el cierre de sesion y redirige al login
Route::post('/logout',[LoginController::class,'logout'])->name('logout');
//CA 2.5 y 2.6: Ruta privada y protegida
Route::get('/privada', function(){
    return view ('privada');})-> middleware('auth'); //filtro de seguridad
Route::get('/recuperacion', function() {
    return view('recuperacion');
})->name('password.request');
Route::post('/recuperacion', [RecuperacionController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [RecuperacionController::class, 'showResetForm'])->name('password.reset');
Route::post('/update-password', [RecuperacionController::class, 'updatePassword'])->name('password.update');
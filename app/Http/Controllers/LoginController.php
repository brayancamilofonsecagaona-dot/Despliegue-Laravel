<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth; //esta herramienta es para poder hacer la autenticacion

class LoginController extends Controller
{
    public function create(){//para mostrar la vista

    return view ('login');
    
    }
    public function store ( Request $request){
        //validar los campos vacios 
        $credentials = $request->validate(['email'=>['required','email'], 'password'=>'required',
        ]);

        //intentar inciar sesion y redirigir
         if (Auth::attempt($credentials)){
           $request->session()->regenerate(); //regenera sesion 
        return redirect()->intended('/privada');

         }else{
    
        return back()->withErrors(['credenciales' => 'Correo o contraseña incorrectos'])->onlyInput('email');
    }
    }
}


<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 

class RecuperacionController extends Controller
{

    public function create()
    {
        return view('recuperacion');
    }


    public function sendResetLinkEmail(Request $request)
    {
        // 1. Validamos los campos. Esto genera automáticamente excepciones que el test puede capturar
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // 2. Opcional para testing riguroso: Verificar si el correo realmente existe en tu Postgres
        $userExists = User::where('email', $request->email)->exists();

        if (!$userExists) {
            // Retorna un error de validación testeable mediante assertSessionHasErrors
            return back()->withErrors([
                'email' => 'No encontramos ningún usuario con ese correo electrónico.'
            ])->onlyInput('email');
        }

        // 3. Simulamos el éxito guardando el mensaje en la sesión de forma estructurada
        return back()->with('status', '¡Te hemos enviado por correo el enlace para restablecer tu contraseña!');
    }
}

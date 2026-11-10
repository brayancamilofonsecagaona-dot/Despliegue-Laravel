<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class RecuperacionController extends Controller
{
    public function create()
    {
        return view('recuperacion');
    }

    public function sendResetLinkEmail(Request $request)
    {
        // CA-03.1: Validar formato de correo
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        // CA-03.3: Si la cuenta existe, creamos el token seguro
        if ($user) {
            $token = Str::random(60);

            // Guardamos el token en la tabla nativa de Laravel para restablecer contraseñas
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => bcrypt($token),
                    'created_at' => Carbon::now() // Esto servirá para la expiración de 30 min (CA-03.4)
                ]
            );

            // Aquí enviarías el correo usando un Mailable de Laravel
            // Mail::to($user->email)->send(new ResetPasswordNotification($token));
        }

        // CA-03.2: RESPUESTA GENÉRICA (Siempre dice que se envió, exista o no el usuario)
        return back()->with('status', 'Si el correo electrónico coincide con una cuenta registrada, recibirás un enlace para restablecer tu contraseña.');
    }
}
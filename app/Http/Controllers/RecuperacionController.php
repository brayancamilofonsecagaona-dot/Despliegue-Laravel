<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RecuperacionController extends Controller
{
    /**
     * Muestra la vista estética para solicitar la recuperación.
     */
    public function create()
    {
        return view('recuperacion');
    }

    /**
     * CA-03.1, CA-03.2, CA-03.3, CA-03.4
     * Procesa la solicitud inicial y envía el correo con el token seguro.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // CA-03.3: Generamos un token seguro de un solo uso
            $token = Str::random(60);

            // Se almacena en la tabla nativa intermedia de Laravel
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => bcrypt($token),
                    'created_at' => Carbon::now() // Para calcular la expiración de 30 mins (CA-03.4)
                ]
            );

            // Construimos la URL segura adjuntando el token y el email correspondiente
            $link = url('/reset-password/' . $token . '?email=' . urlencode($user->email));

            // Envío en formato HTML directamente hacia Mailpit / Mailtrap
            Mail::send([], [], function ($message) use ($user, $link) {
                $message->to($user->email)
                        ->subject('Recuperación de Contraseña')
                        ->html("<h3>Hola, estás recibiendo este correo porque solicitaste restablecer tu contraseña.</h3>
                                <p>Haz clic en el siguiente enlace para continuar (Este enlace expira en 30 minutos):</p>
                                <p><a href='{$link}' style='background: #111; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block;'>Restablecer Contraseña</a></p>");
            });
        }

        // CA-03.2: Respuesta estrictamente genérica por motivos de ciberseguridad
        return back()->with('status', 'Si el correo electrónico coincide con una cuenta registrada, recibirás un enlace para restablecer tu contraseña.');
    }


    public function showResetForm($token, Request $request)
    {
        return view('reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

  
    public function updatePassword(Request $request)
    {
        // CA-03.5: Validación rigurosa de robustez de contraseña
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',      // Al menos una minúscula
                'regex:/[A-Z]/',      // Al menos una mayúscula
                'regex:/[0-9]/',      // Al menos un número
            ],
        ], [
            'password.regex' => 'La contraseña debe cumplir con los requisitos (Mayúscula, minúscula y número).',
            'password.min' => 'La contraseña debe contener un mínimo de 8 caracteres.'
        ]);

        // Buscamos el registro del token correspondiente al email
        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        // CA-03.4: Validar existencia del token, autenticidad y expiración tras 30 minutos
        if (!$record || !Hash::check($request->token, $record->token) || Carbon::parse($record->created_at)->addMinutes(30)->isPast()) {
            return back()->withErrors(['email' => 'El enlace de recuperación es inválido o ha expirado (límite de 30 minutos).']);
        }

        // CA-03.7: Se actualiza la contraseña del usuario encriptándola con bcrypt
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = bcrypt($request->password);
            $user->save();

            // CA-03.6: Se elimina el token de la tabla intermedia invalidándolo permanentemente
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        }

        // Redirección final directa a la pantalla de Login con mensaje flash exitoso
        return redirect('/login')->with('status', '¡Tu contraseña ha sido actualizada con éxito! Ya puedes iniciar sesión con tus nuevas credenciales.');
    }
}
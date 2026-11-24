<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Mail\ResetPasswordMail; // <-- IMPORTANTE: Importamos tu clase de correo
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RecuperacionController extends Controller
{
    public function create()
    {
        return view('recuperacion');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Str::random(60);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => bcrypt($token),
                    'created_at' => Carbon::now()
                ]
            );

            $link = url('/reset-password/' . $token . '?email=' . urlencode($user->email));

            // ENVIAR USANDO TU CLASE REAL (Evita que se quede congelado)
            Mail::to($user->email)->send(new ResetPasswordMail($link));
        }

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
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',      
                'regex:/[A-Z]/',      
                'regex:/[0-9]/',      
            ],
        ], [
            'password.regex' => 'La contraseña debe cumplir con los requisitos (Mayúscula, minúscula y número).',
            'password.min' => 'La contraseña debe contener un mínimo de 8 caracteres.'
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token) || Carbon::parse($record->created_at)->addMinutes(30)->isPast()) {
            return back()->withErrors(['email' => 'El enlace de recuperación es inválido o ha expirado (límite de 30 minutos).']);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = bcrypt($request->password);
            $user->save();

            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        }

        return redirect('/login')->with('status', '¡Tu contraseña ha sido actualizada con éxito! Ya puedes iniciar sesión con tus nuevas credenciales.');
    }
}

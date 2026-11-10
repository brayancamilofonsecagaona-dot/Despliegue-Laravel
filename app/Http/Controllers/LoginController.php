<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // herramienta para hacer la autenticacion
use Illuminate\Support\Facades\RateLimiter; // herramienta para controlar los intentos fallidos
use Illuminate\Support\Str; // necesaria para procesar la clave unica del usuario
use Illuminate\Validation\ValidationException; // necesaria para lanzar el error de bloqueo estructurado

class LoginController extends Controller
{
    public function create()
    {
        // para mostrar la vista
        return view('login');
    }

    public function store(Request $request)
    {
        // validar los campos vacios 
        $credentials = $request->validate([
            'email' => ['required', 'email'], 
            'password' => 'required',
        ]);

        // Creamos una clave única para identificar los intentos por correo e IP combinados
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        // Máximo de intentos permitidos
        $maxAttempts = 5;

        // [HU-05] COMPROBAR EL BLOQUEO: Si supera los 5 intentos consecutivos
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            // Presenta un mensaje claro y comprensible sin revelar datos técnicos sensibles
            throw ValidationException::withMessages([
                'credenciales' => "Demasiados intentos de inicio de sesión. Por favor, intente de nuevo en {$minutes} minutos."
            ]);
        }

        // intentar iniciar sesion y redirigir
        if (Auth::attempt($credentials)) {
            // Si el inicio es exitoso, limpiamos el historial de intentos fallidos de inmediato
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate(); // regenera sesion 
            return redirect()->intended('/privada');
        }

        // [HU-05] REGISTRAR INTENTO FALLIDO: Suma 1 al contador. 
        // El bloqueo se mantiene estricto durante un rango de 15 minutos (900 segundos)
        RateLimiter::hit($throttleKey, 900);

        // Calcular cuántos intentos le quedan disponibles al usuario
        $attemptsLeft = RateLimiter::retriesLeft($throttleKey, $maxAttempts);

        // Personalización del mensaje según los intentos que restan
        if ($attemptsLeft > 0) {
            $mensajeError = "Correo o contraseña incorrectos. Te quedan {$attemptsLeft} intentos antes del bloqueo temporal.";
        } else {
            $mensajeError = "Correo o contraseña incorrectos. Tu cuenta ha sido bloqueada temporalmente por 15 minutos.";
        }

        // Redirigir al login con el mensaje dinámico de intentos
        return back()->withErrors(['credenciales' => $mensajeError])->onlyInput('email');
    }

    // Funcion para cerrar sesion y redirigir al login
    public function logout(Request $request)
    {
        Auth::logout();
        
        // Buenas prácticas para limpiar el estado de pruebas e invalidar la sesión actual
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f4f5; display: flex; justify-content: center; padding: 40px 16px; }
        .card { background: #fff; padding: 28px; border-radius: 10px; width: 100%; max-width: 400px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        h1 { margin-top: 0; font-size: 1.4rem; color: #18181b; }
        label { display: block; margin-top: 14px; font-weight: 600; font-size: .9rem; color: #4b5563; }
        input { width: 100%; padding: 9px; margin-top: 4px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .error { color: #b91c1c; font-size: .85rem; margin-top: 4px; font-weight: 500; }
        button { margin-top: 24px; width: 100%; padding: 10px; background: #111; color: #fff; border: 0; border-radius: 6px; cursor: pointer; font-size: 1rem; font-weight: bold; }
        button:hover { background: #27272a; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Restablecer Contraseña</h1>

        <!-- Alertas generales de error de expiración de token o email -->
        @error('email')
            <div class="error" style="background: #fef2f2; border: 1px solid #fca5a5; padding: 10px; border-radius: 6px; margin-bottom: 12px;">
                {{ $message }}
            </div>
        @enderror

        <form method="POST" action="{{ route('password.update') }}" novalidate>
            @csrf
            <!-- Campos ocultos necesarios para procesar la validación segura -->
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <label for="password">Nueva Contraseña</label>
            <input id="password" type="password" name="password" placeholder="Mínimo 8 caracteres, mayúscula, minúscula y número" required>
            @error('password')
                <div class="error"> {{ $message }}</div>
            @enderror

            <label for="password_confirmation">Confirmar Contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Repite la contraseña exactamente" required>

            <button type="submit">Actualizar Contraseña</button>
        </form>
    </div>
</body>
</html>
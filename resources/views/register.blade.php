<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f4f5; display: flex; justify-content: center; padding: 40px 16px; }
        .card { background: #fff; padding: 28px; border-radius: 10px; width: 100%; max-width: 400px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        h1 { margin-top: 0; font-size: 1.4rem; }
        label { display: block; margin-top: 14px; font-weight: 600; font-size: .9rem; }
        input { width: 100%; padding: 9px; margin-top: 4px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        .error { color: #b91c1c; font-size: .85rem; margin-top: 4px; }
        .success { background: #dcfce7; color: #166534; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        button { margin-top: 20px; width: 100%; padding: 10px; background: #111; color: #fff; border: 0; border-radius: 6px; cursor: pointer; font-size: 1rem; }
        small { color: #666; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Crear cuenta</h1>

        @if (session('status'))
            <div class="success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            <label for="name">Nombre</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name">
            @error('name') <div class="error">{{ $message }}</div> @enderror

            <label for="email">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email">
            @error('email') <div class="error">{{ $message }}</div> @enderror

            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" autocomplete="new-password">
            <small>Mínimo 8 caracteres, con mayúscula, minúscula y número.</small>
            @error('password') <div class="error">{{ $message }}</div> @enderror

            <label for="password_confirmation">Confirmar contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
            @error('password_confirmation') <div class="error">{{ $message }}</div> @enderror

            <button type="submit">Registrarme</button>
        </form>
    </div>
</body>
</html>
<DOCTYPE = html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña</title>
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
        <h1>Recuperar contraseña</h1>

        @if (session('status'))
            <div class="success">{{ session('status') }}</div>
        @endif
        @error('email')
           <div class="error">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf

            <label for="email">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email">
            @error('email') <div class="error">{{ $message }}</div> @enderror

            <button type="submit">Enviar enlace de recuperación</button>
        </form>
    </div>
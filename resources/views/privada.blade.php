<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área privada</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f4f5; display: flex; justify-content: center; padding: 40px 16px; }
        .card { background: #fff; padding: 28px; border-radius: 10px; width: 100%; max-width: 400px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        h1 { margin-top: 0; font-size: 1.4rem; }
        p { color: #333; line-height: 1.5; }
    </style>
</head>
<body>
    <!-- Clase que muestra la bienvinida al usuario recordado su correo y nombre -->
    <div class="card">
        <h1>Área privada</h1>
        <p>¡Bienvenido, <strong>{{ auth()->user()->name }}</strong>!</p>
        <p>Has iniciado sesión con el correo: {{ auth()->user()->email }}</p>
        <!-- Formato del boton de cierre de sesion -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="background-color: #dc3545; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold;">
                Cerrar sesión
            </button>
        </form>
    </div>
</body>
</html>
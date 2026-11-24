<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo') | Tareas</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f4f5; display: flex; justify-content: center; padding: 40px 16px; }
        .card { background: #fff; padding: 28px; border-radius: 10px; width: 100%; max-width: 560px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        h1 { margin-top: 0; font-size: 1.4rem; }
        label { display: block; margin-top: 14px; font-weight: 600; font-size: .9rem; }
        input, textarea, select { width: 100%; padding: 9px; margin-top: 4px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font: inherit; }
        .error { color: #b91c1c; font-size: .85rem; margin-top: 4px; }
        .success { background: #dcfce7; color: #166534; padding: 10px; border-radius: 6px; margin-bottom: 12px; }
        button, .btn { display: inline-block; margin-top: 16px; padding: 9px 14px; background: #111; color: #fff; border: 0; border-radius: 6px; cursor: pointer; font-size: .95rem; text-decoration: none; }
        .item { border: 1px solid #e4e4e7; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .badge { background: #e4e4e7; border-radius: 999px; padding: 2px 8px; font-size: .75rem; margin-left: 6px; }
        .acciones { display: flex; gap: 14px; align-items: center; margin-top: 8px; }
        .acciones form { margin: 0; }
        .link { background: none; color: #b91c1c; padding: 0; margin: 0; text-decoration: underline; }
        small { color: #666; }
    </style>
</head>
<body>
    <div class="card">
        @if (session('status'))
            <div class="success">{{ session('status') }}</div>
        @endif
        @yield('contenido')
    </div>
</body>
</html>
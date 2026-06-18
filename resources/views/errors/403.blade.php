<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 - Acceso Denegado</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,600,700" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: white; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.06); padding: 3rem; text-align: center; max-width: 480px; }
        .code { font-size: 5rem; font-weight: 800; color: #f43f5e; line-height: 1; }
        .title { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 1rem 0 0.5rem; }
        .msg { color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem; }
        .btn { display: inline-block; padding: 0.6rem 1.5rem; background: #f43f5e; color: white; text-decoration: none; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; }
        .btn:hover { background: #e11d48; }
    </style>
</head>
<body>
    <div class="card">
        <div class="code">403</div>
        <div class="title">Acceso Denegado</div>
        <div class="msg">No tienes permisos para acceder a esta sección.</div>
        <a href="{{ route('login') }}" class="btn">Ir al Login</a>
    </div>
</body>
</html>

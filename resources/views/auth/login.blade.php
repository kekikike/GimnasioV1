<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - GimnasioV1</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    {{-- Styles already inline --}}
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: white; border-radius: 1rem; padding: 2.5rem; width: 100%; max-width: 420px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .login-card h1 { font-size: 1.5rem; font-weight: 700; color: #0f172a; text-align: center; }
        .login-card .subtitle { color: #64748b; text-align: center; margin-bottom: 2rem; font-size: 0.9rem; }
        .login-card .brand { text-align: center; font-size: 2.5rem; margin-bottom: 0.5rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; }
        .form-control { width: 100%; padding: 0.7rem 0.9rem; border: 1.5px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.9rem; transition: all 0.2s; outline: none; }
        .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .btn { width: 100%; padding: 0.75rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.2s; background: #3b82f6; color: white; }
        .btn:hover { background: #2563eb; }
        .error { background: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 0.5rem; font-size: 0.85rem; margin-bottom: 1rem; text-align: center; }
        .footer-text { text-align: center; margin-top: 1.5rem; font-size: 0.8rem; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">🏋️</div>
        <h1>GimnasioV1</h1>
        <p class="subtitle">Inicia sesión para acceder al sistema</p>

        @if($errors->any())
            <div class="error">{{ $errors->first('correo') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control" value="{{ old('correo') }}" required autofocus placeholder="admin@gimnasio.com">
            </div>
            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>
        <div class="footer-text">Sistema de Gestión de Gimnasio &copy; {{ date('Y') }}</div>
    </div>
</body>
</html>

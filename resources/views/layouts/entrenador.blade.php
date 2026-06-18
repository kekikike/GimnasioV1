<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Entrenador') - GimnasioV1</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; min-height: 100vh; }
        .sidebar { width: 240px; background: #0f172a; color: #e2e8f0; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; height: 100vh; z-index: 50; }
        .sidebar .brand { padding: 1.5rem; border-bottom: 1px solid #1e293b; }
        .sidebar .brand h1 { font-size: 1.1rem; font-weight: 700; color: #f8fafc; }
        .sidebar .brand span { color: #8b5cf6; }
        .sidebar .nav { padding: 1rem 0; flex: 1; }
        .sidebar .nav a { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; color: #94a3b8; text-decoration: none; transition: all 0.2s; font-size: 0.9rem; font-weight: 500; }
        .sidebar .nav a:hover, .sidebar .nav a.active { background: #1e293b; color: #f8fafc; }
        .sidebar .nav a.active { border-right: 3px solid #8b5cf6; }
        .sidebar .user-info { padding: 1rem 1.5rem; border-top: 1px solid #1e293b; font-size: 0.85rem; }
        .sidebar .user-info .name { color: #f8fafc; font-weight: 600; }
        .sidebar .user-info .role { color: #94a3b8; font-size: 0.8rem; }
        .main-content { margin-left: 240px; flex: 1; padding: 2rem; min-height: 100vh; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .topbar h2 { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
        .card { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); padding: 1.5rem; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-primary { background: #8b5cf6; color: white; }
        .btn-primary:hover { background: #7c3aed; }
        .btn-danger { background: #ef4444; color: white; }
        .alert { padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><h1><svg fill="none" stroke="#8b5cf6" viewBox="0 0 24 24" width="22" height="22" stroke-width="2" style="vertical-align:middle;margin-right:6px;"><rect x="3" y="8" width="18" height="8" rx="2"/><rect x="5" y="5" width="2" height="14" rx="1"/><rect x="17" y="5" width="2" height="14" rx="1"/></svg><span>Gimnasio</span>V1</h1></div>
        <nav class="nav">
            <a href="{{ route('entrenador.dashboard') }}" class="{{ request()->routeIs('entrenador.dashboard') ? 'active' : '' }}">
                Mi Agenda
            </a>
            <a href="{{ route('entrenador.fallas') }}" class="{{ request()->routeIs('entrenador.fallas') ? 'active' : '' }}">
                Reportar Fallas
            </a>
        </nav>
        <div class="user-info">
            <div class="name">{{ session('usuario')->nombre1 ?? 'Usuario' }}</div>
            <div class="role">Entrenador</div>
        </div>
    </aside>
    <div class="main-content">
        <div class="topbar">
            <h2>@yield('title', 'Entrenador')</h2>
            <a href="{{ route('logout') }}" class="btn btn-danger btn-sm" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Salir</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @yield('content')
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Mi Cuenta') - GimnasioV1</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; min-height: 100vh; }
        .sidebar { width: 240px; background: #0f172a; color: #e2e8f0; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; height: 100vh; z-index: 50; }
        .sidebar .brand { padding: 1.5rem; border-bottom: 1px solid #1e293b; }
        .sidebar .brand h1 { font-size: 1.1rem; font-weight: 700; color: #f8fafc; }
        .sidebar .brand span { color: #f43f5e; }
        .sidebar .nav { padding: 1rem 0; flex: 1; }
        .sidebar .nav a { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; color: #94a3b8; text-decoration: none; transition: all 0.2s; font-size: 0.9rem; font-weight: 500; }
        .sidebar .nav a:hover, .sidebar .nav a.active { background: #1e293b; color: #f8fafc; }
        .sidebar .nav a.active { border-right: 3px solid #f43f5e; }
        .sidebar .user-info { padding: 1rem 1.5rem; border-top: 1px solid #1e293b; font-size: 0.85rem; }
        .sidebar .user-info .name { color: #f8fafc; font-weight: 600; }
        .sidebar .user-info .role { color: #94a3b8; font-size: 0.8rem; }
        .main-content { margin-left: 240px; flex: 1; padding: 2rem; min-height: 100vh; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .topbar h2 { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
        .card { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); padding: 1.5rem; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-primary { background: #f43f5e; color: white; }
        .btn-primary:hover { background: #e11d48; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .alert { padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .user-photo { width: 48px; height: 48px; border-radius: 0.5rem; overflow: hidden; flex-shrink: 0; background: #1e293b; }
        .user-photo img { width: 100%; height: 100%; object-fit: cover; object-position: top; }
        .foto-socio { width: 160px; height: 160px; border-radius: 0.75rem; overflow: hidden; margin: 0 auto; border: 3px solid #e2e8f0; background: #f8fafc; }
        .foto-socio img { width: 100%; height: 100%; object-fit: cover; object-position: top; }
        .stat-card { text-align: center; padding: 1.5rem; }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #0f172a; }
        .stat-card .label { font-size: 0.85rem; color: #64748b; margin-top: 0.25rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; }
        .section-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #f1f5f9; display: flex; align-items: center; gap: 0.5rem; }
        .info-row { display: flex; padding: 0.5rem 0; border-bottom: 1px solid #f8fafc; }
        .info-row .label { width: 140px; font-weight: 600; color: #64748b; font-size: 0.85rem; }
        .info-row .value { flex: 1; color: #0f172a; font-size: 0.9rem; }
        .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        th { text-align: left; padding: 0.6rem 0.75rem; background: #f8fafc; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
        td { padding: 0.6rem 0.75rem; border-bottom: 1px solid #f1f5f9; color: #0f172a; }
        .empty-msg { color: #94a3b8; font-size: 0.9rem; text-align: center; padding: 2rem; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><h1><svg fill="none" stroke="#f43f5e" viewBox="0 0 24 24" width="22" height="22" stroke-width="2" style="vertical-align:middle;margin-right:6px;"><rect x="3" y="8" width="18" height="8" rx="2"/><rect x="5" y="5" width="2" height="14" rx="1"/><rect x="17" y="5" width="2" height="14" rx="1"/></svg><span>Gimnasio</span>V1</h1></div>
        <nav class="nav">
            <a href="{{ route('socio.dashboard') }}" class="{{ request()->routeIs('socio.dashboard') ? 'active' : '' }}">
                Inicio
            </a>
            <a href="{{ route('socio.perfil') }}" class="{{ request()->routeIs('socio.perfil') ? 'active' : '' }}">
                Mi Perfil
            </a>
            <a href="{{ route('socio.asistencias') }}" class="{{ request()->routeIs('socio.asistencias') ? 'active' : '' }}">
                Asistencias
            </a>
            <a href="{{ route('socio.reservas.index') }}" class="{{ request()->routeIs('socio.reservas*') ? 'active' : '' }}">
                Mis Reservas
            </a>
        </nav>
        <div class="user-info">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.5rem;">
                <div class="user-photo">
                    <img src="{{ asset('storage/' . ($fotografiaUrl ?? 'fotos_socios/default.jpeg')) }}" alt="Foto"
                         onerror="this.style.display='none'">
                </div>
                <div>
                    <div class="name">{{ session('usuario')->nombre1 ?? 'Socio' }}</div>
                    <div class="role">Socio</div>
                </div>
            </div>
            <div style="margin-top:0.5rem;">
                <a href="{{ route('logout') }}" class="btn btn-danger btn-sm" style="width:100%;justify-content:center;" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Salir</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
            </div>
        </div>
    </aside>
    <div class="main-content">
        <div class="topbar">
            <h2>@yield('title', 'Mi Cuenta')</h2>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @yield('content')
    </div>
</body>
</html>

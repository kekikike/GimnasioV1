<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gimnasio') - Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    {{-- Styles already inline --}}
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #0f172a; color: #e2e8f0; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; height: 100vh; z-index: 50; }
        .sidebar .brand { padding: 1.5rem; border-bottom: 1px solid #1e293b; }
        .sidebar .brand h1 { font-size: 1.25rem; font-weight: 700; color: #f8fafc; }
        .sidebar .brand span { color: #3b82f6; }
        .sidebar .nav { padding: 1rem 0; flex: 1; }
        .sidebar .nav a { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; color: #94a3b8; text-decoration: none; transition: all 0.2s; font-size: 0.9rem; font-weight: 500; }
        .sidebar .nav a:hover, .sidebar .nav a.active { background: #1e293b; color: #f8fafc; }
        .sidebar .nav a.active { border-right: 3px solid #3b82f6; }
        .sidebar .nav a svg { width: 20px; height: 20px; flex-shrink: 0; }
        .sidebar .user-info { padding: 1rem 1.5rem; border-top: 1px solid #1e293b; font-size: 0.85rem; }
        .sidebar .user-info .name { color: #f8fafc; font-weight: 600; }
        .sidebar .user-info .role { color: #94a3b8; font-size: 0.8rem; }
        .user-photo { width: 48px; height: 48px; border-radius: 0.5rem; overflow: hidden; flex-shrink: 0; background: #1e293b; }
        .user-photo img { width: 100%; height: 100%; object-fit: cover; object-position: top; }
        .main-content { margin-left: 260px; flex: 1; padding: 2rem; min-height: 100vh; width: calc(100% - 260px); }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .topbar h2 { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
        .topbar .user-badge { display: flex; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 1rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .card { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-warning:hover { background: #d97706; }
        .btn-sm { padding: 0.4rem 0.75rem; font-size: 0.8rem; }
        .btn-outline { background: transparent; border: 1.5px solid #e2e8f0; color: #475569; }
        .btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }
        .alert { padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 0.75rem 1rem; font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid #e2e8f0; }
        td { padding: 0.75rem 1rem; font-size: 0.9rem; color: #334155; border-bottom: 1px solid #f1f5f9; }
        tr:hover td { background: #f8fafc; }
        .badge { display: inline-flex; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; }
        .form-control { width: 100%; padding: 0.6rem 0.85rem; border: 1.5px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.9rem; transition: border-color 0.2s; outline: none; background: white; }
        .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 0.75rem center; padding-right: 2rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; }
        .stat-card { padding: 1.5rem; text-align: center; }
        .stat-card .number { font-size: 2rem; font-weight: 700; color: #0f172a; }
        .stat-card .label { font-size: 0.85rem; color: #64748b; margin-top: 0.25rem; }
        .stat-card .icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; }
        .page-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .action-group { display: flex; gap: 0.5rem; }
        .empty-state { text-align: center; padding: 3rem 1rem; color: #94a3b8; }
        .empty-state svg { width: 64px; height: 64px; margin: 0 auto 1rem; opacity: 0.4; }
        .modal-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.6); z-index:1000; display:flex; align-items:center; justify-content:center; }
        .modal-content { background:white; border-radius:0.75rem; width:100%; max-width:640px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
        .modal-header { display:flex; justify-content:space-between; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid #e2e8f0; }
        .modal-header h3 { font-size:1.1rem; font-weight:600; color:#0f172a; }
        .modal-close { background:none; border:none; font-size:1.5rem; color:#64748b; cursor:pointer; padding:0; line-height:1; }
        .modal-close:hover { color:#0f172a; }
        .modal-content .form-group { padding:0 1.5rem; margin-bottom:1rem; }
        .modal-content .grid-2 { padding:0 1.5rem; }
        .modal-footer { display:flex; gap:0.75rem; justify-content:flex-end; padding:1.25rem 1.5rem; border-top:1px solid #e2e8f0; margin-top:1rem; }
        @media print {
            .sidebar { display:none !important; }
            .main-content { margin-left:0 !important; width:100% !important; padding:1rem !important; }
            .topbar { display:none !important; }
            .no-print { display:none !important; }
            .action-print { display:none !important; }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand">
            <h1><svg fill="none" stroke="#f8fafc" viewBox="0 0 24 24" width="22" height="22" stroke-width="2" style="vertical-align:middle;margin-right:6px;"><rect x="3" y="8" width="18" height="8" rx="2"/><rect x="5" y="5" width="2" height="14" rx="1"/><rect x="17" y="5" width="2" height="14" rx="1"/></svg><span>Gimnasio</span>V1</h1>
        </div>
        <nav class="nav">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.sucursales.index') }}" class="{{ request()->routeIs('admin.sucursales.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Sucursales
            </a>
            <a href="{{ route('admin.personal.index') }}" class="{{ request()->routeIs('admin.personal.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                Personal
            </a>
            <a href="{{ route('admin.socios.index') }}" class="{{ request()->routeIs('admin.socios.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Socios
            </a>
            <a href="{{ route('admin.planes.index') }}" class="{{ request()->routeIs('admin.planes.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Planes de Membresía
            </a>
            <a href="{{ route('admin.horarios.index') }}" class="{{ request()->routeIs('admin.horarios.index') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Gestión de Horarios
            </a>

            <a href="{{ route('admin.asistencias.index') }}" class="{{ request()->routeIs('admin.asistencias.index') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Reloj de Asistencias
            </a>
            <a href="{{ route('admin.clases.index') }}" class="{{ request()->routeIs('admin.clases.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Clases
            </a>
            <a href="{{ route('admin.caja') }}" class="{{ request()->routeIs('admin.caja') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Caja
            </a>
            <a href="{{ route('equipamiento.index') }}" class="{{ request()->routeIs('equipamiento.index') || request()->routeIs('equipamiento.create') || request()->routeIs('equipamiento.edit') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Equipamiento
            </a>
            <a href="{{ route('equipamiento.reportar-falla') }}" class="{{ request()->routeIs('equipamiento.reportar-falla') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                Reportar Fallas
            </a>
            <a href="{{ route('equipamiento.fallas-sin-mantenimiento') }}" class="{{ request()->routeIs('equipamiento.fallas-sin-mantenimiento') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Fallas x Mantenimiento
            </a>
            <a href="{{ route('admin.mantenimientos.index') }}" class="{{ request()->routeIs('admin.mantenimientos.*') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Mantenimientos
            </a>
            <a href="{{ route('admin.alertas') }}" class="{{ request()->routeIs('admin.alertas') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                Alertas
            </a>
            <a href="{{ route('admin.reportes') }}" class="{{ request()->routeIs('admin.reportes') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Reportes
            </a>
            <a href="{{ route('admin.auditoria') }}" class="{{ request()->routeIs('admin.auditoria') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Auditoría
            </a>
        </nav>
        <div class="user-info">
            <div class="name">{{ session('usuario')->nombre1 }} {{ session('usuario')->apellido1 }}</div>
            <div class="role">{{ session('usuario')->nombreRol ?? 'Usuario' }}</div>
            <div style="margin-top:0.75rem;">
                <a href="{{ route('logout') }}" class="btn btn-danger btn-sm" style="width:100%;justify-content:center;" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Salir</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
            </div>
        </div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <h2>@yield('title', 'Dashboard')</h2>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Recepción') - GimnasioV1</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #0f172a; color: #e2e8f0; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; height: 100vh; z-index: 50; }
        .sidebar .brand { padding: 1.5rem; border-bottom: 1px solid #1e293b; }
        .sidebar .brand h1 { font-size: 1.25rem; font-weight: 700; color: #f8fafc; }
        .sidebar .brand span { color: #10b981; }
        .sidebar .nav { padding: 1rem 0; flex: 1; overflow-y: auto; }
        .sidebar .nav a { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.5rem; color: #94a3b8; text-decoration: none; transition: all 0.2s; font-size: 0.9rem; font-weight: 500; }
        .sidebar .nav a:hover, .sidebar .nav a.active { background: #1e293b; color: #f8fafc; }
        .sidebar .nav a.active { border-right: 3px solid #10b981; }
        .sidebar .nav a svg { width: 20px; height: 20px; flex-shrink: 0; }
        .sidebar .user-info { padding: 1rem 1.5rem; border-top: 1px solid #1e293b; font-size: 0.85rem; }
        .sidebar .user-info .name { color: #f8fafc; font-weight: 600; }
        .sidebar .user-info .role { color: #94a3b8; font-size: 0.8rem; }
        .user-photo { width: 48px; height: 48px; border-radius: 0.5rem; overflow: hidden; flex-shrink: 0; background: #1e293b; }
        .user-photo img { width: 100%; height: 100%; object-fit: cover; object-position: top; }
        .main-content { margin-left: 260px; flex: 1; padding: 2rem; min-height: 100vh; width: calc(100% - 260px); }
        .topbar .user-badge { display: flex; align-items: center; gap: 0.75rem; background: white; padding: 0.5rem 1rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .topbar h2 { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
        .card { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); padding: 1.5rem; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-primary { background: #10b981; color: white; }
        .btn-primary:hover { background: #059669; }
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
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .search-box { display: flex; gap: 0.5rem; }
        .search-box input { flex: 1; padding: 0.7rem 1rem; border: 2px solid #e2e8f0; border-radius: 0.5rem; font-size: 1rem; outline: none; }
        .search-box input:focus { border-color: #10b981; }
        form { margin: 0; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.4rem; font-size: 0.85rem; font-weight: 600; color: #374151; }
        .form-control { display: block; width: 100%; padding: 0.6rem 0.75rem; font-size: 0.9rem; border: 1.5px solid #e2e8f0; border-radius: 0.5rem; outline: none; transition: border-color 0.2s; background: white; color: #1e293b; font-family: inherit; }
        .form-control:focus { border-color: #10b981; }
        select.form-control { appearance: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 0.75rem 1rem; font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid #e2e8f0; }
        td { padding: 0.75rem 1rem; font-size: 0.9rem; color: #334155; border-bottom: 1px solid #f1f5f9; }
        .table-responsive { overflow-x: auto; }
        .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .toast-container{position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:99999;display:flex;flex-direction:column;align-items:center;pointer-events:none;}
        .toast{padding:14px 28px;border-radius:12px;font-size:0.9rem;font-weight:500;color:white;box-shadow:0 8px 32px rgba(0,0,0,0.25);margin-bottom:8px;animation:toastIn .3s ease,toastOut .3s ease 1.7s forwards;pointer-events:auto;max-width:520px;text-align:center;line-height:1.4;}
        .toast-success{background:#10b981;}
        .toast-error{background:#ef4444;}
        @keyframes toastIn{from{opacity:0;transform:translateY(-24px)}to{opacity:1;transform:translateY(0)}}
        @keyframes toastOut{from{opacity:1;transform:translateY(0)}to{opacity:0;transform:translateY(-24px)}}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><h1><svg fill="none" stroke="#10b981" viewBox="0 0 24 24" width="22" height="22" stroke-width="2" style="vertical-align:middle;margin-right:6px;"><rect x="3" y="8" width="18" height="8" rx="2"/><rect x="5" y="5" width="2" height="14" rx="1"/><rect x="17" y="5" width="2" height="14" rx="1"/></svg><span>Gimnasio</span>V1</h1></div>
        <nav class="nav">
            <a href="{{ route('recepcionista.dashboard') }}" class="{{ request()->routeIs('recepcionista.dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Panel de Ingreso
            </a>
            <a href="{{ route('recepcionista.caja') }}" class="{{ request()->routeIs('recepcionista.caja') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Caja
            </a>
            <a href="{{ route('recepcionista.socios') }}" class="{{ request()->routeIs('recepcionista.socios') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Consultar Socios
            </a>
        </nav>
        <div class="user-info">
            <a href="{{ route('perfil') }}" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="12" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/></svg>
                <div><div class="name">{{ session('usuario')->nombre1 ?? 'Usuario' }}</div>
                <div class="role">Recepcionista</div></div>
            </a>
            <div style="margin-top:0.75rem;">
                <a href="{{ route('logout') }}" class="btn btn-danger btn-sm" style="width:100%;justify-content:center;" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Salir</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">@csrf</form>
            </div>
        </div>
    </aside>
    <div class="main-content">
        <div class="topbar">
            <h2>@yield('title', 'Recepción')</h2>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @yield('content')
    </div>
<script>
function mostrarToast(m,t){t=t||'success';var c=document.querySelector('.toast-container');if(!c){c=document.createElement('div');c.className='toast-container';document.body.appendChild(c)}var o=document.createElement('div');o.className='toast toast-'+t;o.textContent=m;c.appendChild(o);setTimeout(function(){if(o.parentNode)o.parentNode.removeChild(o)},2000)}
</script>
</body>
</html>

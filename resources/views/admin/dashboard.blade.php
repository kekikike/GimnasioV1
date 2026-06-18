@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="grid-3" style="margin-bottom: 2rem;">
        <div class="card stat-card">
            <div class="icon" style="background:#dbeafe;">
                <svg fill="none" stroke="#2563eb" width="24" height="24" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="number">{{ $totalEquipos }}</div>
            <div class="label">Equipos Registrados</div>
        </div>
        <div class="card stat-card">
            <div class="icon" style="background:#d1fae5;">
                <svg fill="none" stroke="#059669" width="24" height="24" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="number">{{ $totalSocios }}</div>
            <div class="label">Socios Activos</div>
        </div>
        <div class="card stat-card">
            <div class="icon" style="background:#fef3c7;">
                <svg fill="none" stroke="#d97706" width="24" height="24" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div class="number">{{ $totalEmpleados }}</div>
            <div class="label">Empleados</div>
        </div>
    </div>

    <div class="card" style="padding: 1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="font-size:1.1rem; font-weight:600; color:#0f172a;">Equipos Recientes</h3>
            <a href="{{ route('equipamiento.index') }}" class="btn btn-primary btn-sm">Ver Todos</a>
        </div>

        @if(empty($equiposRecientes))
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>No hay equipos registrados a&uacute;n.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Sucursal</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equiposRecientes as $eq)
                    @php $m = $marcas[$eq->idMarca] ?? null; $s = $sucursales[$eq->idSucursal] ?? null; @endphp
                    <tr>
                        <td style="font-weight:600;">{{ $eq->nombreEquipo }}</td>
                        <td>{{ $m->nombreMarca ?? '-' }}</td>
                        <td>{{ $eq->modelo ?? '-' }}</td>
                        <td>{{ $s->nombre ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $eq->estadoEquipo == 'Operativo' ? 'badge-success' : ($eq->estadoEquipo == 'En Mantenimiento' ? 'badge-warning' : 'badge-danger') }}">
                                {{ $eq->estadoEquipo }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection

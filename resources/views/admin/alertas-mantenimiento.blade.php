@extends('layouts.admin')

@section('title', 'Alertas de Mantenimiento')

@section('content')
<div class="grid-3" style="margin-bottom: 2rem;">
    <div class="card stat-card">
        <div class="icon" style="background:#fef3c7;">
            <svg fill="none" stroke="#d97706" width="24" height="24" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        </div>
        <div class="number">{{ $resumen['pendientes'] }}</div>
        <div class="label">Pendientes</div>
    </div>
    <div class="card stat-card">
        <div class="icon" style="background:#d1fae5;">
            <svg fill="none" stroke="#059669" width="24" height="24" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="number">{{ $resumen['realizados'] }}</div>
        <div class="label">Realizados</div>
    </div>
    <div class="card stat-card">
        <div class="icon" style="background:#fee2e2;">
            <svg fill="none" stroke="#dc2626" width="24" height="24" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="number">{{ $resumen['vencidas'] }}</div>
        <div class="label">Vencidas</div>
    </div>
</div>

<div class="card" style="overflow:hidden;">
    @if(empty($alertas))
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>No hay alertas de mantenimiento.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th>Estado Equipo</th>
                        <th>Fecha Programada</th>
                        <th>D&iacute;as Restantes</th>
                        <th>Estado Mantenimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alertas as $a)
                    <tr>
                        <td style="font-weight:600;">{{ $a->nombreEquipo }}</td>
                        <td>
                            <span class="badge {{ $a->estadoEquipo == 'Operativo' ? 'badge-success' : ($a->estadoEquipo == 'En Mantenimiento' ? 'badge-warning' : 'badge-danger') }}">
                                {{ $a->estadoEquipo }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($a->fechaProgramada)->format('d/m/Y') }}</td>
                        <td>
                            @if($a->diasRestantes > 0)
                                <span style="color:#059669; font-weight:600;">Faltan {{ $a->diasRestantes }} d&iacute;a(s)</span>
                            @elseif($a->diasRestantes == 0)
                                <span style="color:#d97706; font-weight:600;">Hoy</span>
                            @else
                                <span style="color:#dc2626; font-weight:600;">Vencido hace {{ abs($a->diasRestantes) }} d&iacute;a(s)</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $a->estadoMantenimiento == 'Realizado' ? 'badge-success' : 'badge-warning' }}">
                                {{ $a->estadoMantenimiento }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@extends('layouts.socio')
@section('title', 'Asistencias')
@section('content')

@if(count($accesos) > 0)
@php
    $totalVisitas = count(array_filter($accesos, fn($a) => !$a->bloqueo));
    $totalDenegados = count(array_filter($accesos, fn($a) => $a->bloqueo));
    $ultimoAcceso = $accesos[0] ?? null;
    \Carbon\Carbon::setLocale('es');
@endphp
<div class="grid-3" style="margin-bottom:1.5rem;">
    <div class="card stat-card">
        <div class="number">{{ $totalVisitas }}</div>
        <div class="label">Ingresos Registrados</div>
    </div>
    <div class="card stat-card">
        <div class="number" style="color:#ef4444;">{{ $totalDenegados }}</div>
        <div class="label">Accesos Denegados</div>
    </div>
    <div class="card stat-card">
        <div class="number">{{ $ultimoAcceso ? \Carbon\Carbon::parse($ultimoAcceso->fechaAcceso)->diffForHumans() : '—' }}</div>
        <div class="label">Último Acceso</div>
    </div>
</div>
@endif

<div class="card">
    <div class="section-title">Historial de Accesos</div>
    @if(count($accesos) > 0)
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Sucursal</th>
                <th>Estado</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accesos as $a)
            <tr>
                <td>{{ \Carbon\Carbon::parse($a->fechaAcceso)->format('d/m/Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($a->horaAcceso)->format('H:i') }}</td>
                <td>{{ $a->sucursal ?? '—' }}</td>
                <td>
                    @if($a->bloqueo)
                    <span class="badge badge-danger">Denegado</span>
                    @else
                    <span class="badge badge-success">Ingresó</span>
                    @endif
                </td>
                <td style="color:#64748b;font-size:0.85rem;">{{ $a->motivoDenegacion ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="empty-msg">No hay asistencias registradas.</p>
    @endif
</div>
@endsection

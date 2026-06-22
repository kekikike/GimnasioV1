@extends('layouts.socio')
@section('title', 'Historial de Membresias')
@section('content')
<div class="card" style="padding:1.5rem;">
    @if(count($membresias) > 0)
    <table>
        <thead>
            <tr>
                <th>Plan</th>
                <th>Costo</th>
                <th>Duracion</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($membresias as $m)
            <tr>
                <td><strong>{{ $m->nombrePlan }}</strong></td>
                <td>${{ number_format($m->costoPlan, 2) }}</td>
                <td>{{ $m->duracionDias }} dias</td>
                <td>{{ $m->fechaInicioMembresia }}</td>
                <td>{{ $m->fechaFinMembresia }}</td>
                <td>
                    <span class="badge badge-{{ $m->estadoMembresia === 'Activa' ? 'success' : ($m->estadoMembresia === 'Vencida' ? 'warning' : 'danger') }}">
                        {{ $m->estadoMembresia }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">No tienes membresias registradas</div>
    @endif
</div>
@endsection
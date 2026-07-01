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
                    @php $hoy = date('Y-m-d'); @endphp
                    @if($m->estadoMembresia === 'Activa' && $m->fechaFinMembresia >= $hoy)
                        <span class="badge badge-success">Activa</span>
                    @elseif($m->fechaFinMembresia < $hoy)
                        <span class="badge badge-danger">Vencida</span>
                    @else
                        <span class="badge badge-warning">{{ $m->estadoMembresia }}</span>
                    @endif
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
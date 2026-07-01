@extends('layouts.socio')
@section('title', 'Inicio')
@section('content')

@if(!$socio)
<div class="card">
    <p class="empty-msg">No tienes un perfil de socio registrado.</p>
</div>
@else

<div class="grid-3" style="margin-bottom:1.5rem;">
    <div class="card stat-card">
        <div class="number">{{ $socio->strikes ?? 0 }}</div>
        <div class="label">Strikes</div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="section-title">Datos Personales</div>
        @if($socio->fotografiaUrl)
        <div style="text-align:center; margin-bottom:1rem;">
            <div class="foto-socio">
                <img src="{{ asset('storage/' . $socio->fotografiaUrl) }}" alt="Foto">
            </div>
        </div>
        @endif
        <div class="info-row">
            <span class="label">Nombre</span>
            <span class="value">{{ trim($socio->nombre1 . ' ' . ($socio->nombre2 ?? '') . ' ' . $socio->apellido1 . ' ' . ($socio->apellido2 ?? '')) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Correo</span>
            <span class="value">{{ $socio->correo }}</span>
        </div>
        <div class="info-row">
            <span class="label">Teléfono</span>
            <span class="value">{{ $socio->telefono }}</span>
        </div>
        <div class="info-row">
            <span class="label">Estado</span>
            <span class="value"><span class="badge badge-success">{{ $socio->estadoSocio }}</span></span>
        </div>
        <div class="info-row">
            <span class="label">Dirección</span>
            <span class="value">{{ $socio->direccion ?? '—' }}</span>
        </div>
        @if($socio->observacionesMedicas)
        <div class="info-row">
            <span class="label">Observaciones Médicas</span>
            <span class="value" style="color:#b45309; font-size:0.85rem;">{{ $socio->observacionesMedicas }}</span>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="section-title">Membresía</div>
        @if($membresia)
        <div class="info-row">
            <span class="label">Plan</span>
            <span class="value">{{ $membresia->nombrePlan }}</span>
        </div>
        <div class="info-row">
            <span class="label">Costo</span>
            <span class="value">${{ number_format($membresia->costoPlan, 2) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Duración</span>
            <span class="value">{{ $membresia->duracionDias }} días</span>
        </div>
        <div class="info-row">
            <span class="label">Inicio</span>
            <span class="value">{{ $membresia->fechaInicioMembresia }}</span>
        </div>
        <div class="info-row">
            <span class="label">Vencimiento</span>
            <span class="value">{{ $membresia->fechaFinMembresia }}</span>
        </div>
        <div class="info-row">
            <span class="label">Estado</span>
            <span class="value">
                @php $hoy = now()->format('Y-m-d'); @endphp
                @if($membresia->estadoMembresia === 'Activa' && $membresia->fechaFinMembresia >= $hoy)
                <span class="badge badge-success">Activa</span>
                @elseif($membresia->fechaFinMembresia < $hoy)
                <span class="badge badge-danger">Vencida</span>
                @else
                <span class="badge badge-warning">{{ $membresia->estadoMembresia }}</span>
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="label">Sucursal</span>
            <span class="value">{{ $membresia->sucursal ?? '—' }}</span>
        </div>
        @else
        <p class="empty-msg">Sin membresía activa.</p>
        @endif
    </div>
</div>

<div class="card" style="margin-top:1.5rem;">
    <div class="section-title">Asistencias Recientes</div>
    @if(count($accesos) > 0)
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Sucursal</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accesos as $a)
            <tr>
                <td>{{ $a->fechaAcceso }}</td>
                <td>{{ $a->horaAcceso }}</td>
                <td>{{ $a->sucursal }}</td>
                <td>
                    @if($a->bloqueo)
                    <span class="badge badge-danger">Denegado</span>
                    @else
                    <span class="badge badge-success">Ingresó</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="empty-msg">Sin asistencias registradas.</p>
    @endif
</div>

<div class="grid-2" style="margin-top:1.5rem;">
    <div class="card">
        <div class="section-title">Mis Reservas</div>
        @if(count($reservas) > 0)
        <table>
            <thead>
                <tr>
                    <th>Actividad</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservas as $r)
                <tr>
                    <td>{{ $r->nombreActividad }}</td>
                    <td>{{ $r->fecha }}</td>
                    <td>{{ substr($r->horaInicio, 0, 5) }} - {{ substr($r->horaFin, 0, 5) }}</td>
                    <td>
                        @if($r->estadoReserva === 'Reservado' || $r->estadoReserva === 'Confirmada')
                        <span class="badge badge-success">{{ $r->estadoReserva }}</span>
                        @elseif($r->estadoReserva === 'Cancelado' || $r->estadoReserva === 'Cancelada')
                        <span class="badge badge-danger">{{ $r->estadoReserva }}</span>
                        @else
                        <span class="badge badge-info">{{ $r->estadoReserva }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="empty-msg">Sin reservas.</p>
        @endif
    </div>

    <div class="card">
        <div class="section-title">Próximas Clases</div>
        @if(count($clases) > 0)
        <table>
            <thead>
                <tr>
                    <th>Actividad</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Cupo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($clases as $c)
                <tr>
                    <td>{{ $c->nombreActividad }}</td>
                    <td>{{ $c->fecha }}</td>
                    <td>{{ substr($c->horaInicio, 0, 5) }} - {{ substr($c->horaFin, 0, 5) }}</td>
                    <td>{{ $c->cupoMaximo }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="empty-msg">No hay clases programadas.</p>
        @endif
    </div>
</div>

@endif
@endsection

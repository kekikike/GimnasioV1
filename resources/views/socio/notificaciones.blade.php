@extends('layouts.socio')
@section('title', 'Notificaciones')
@section('content')

<div class="card">
    <div class="section-title">Notificaciones</div>

    @if(count($notificaciones) === 0)
    <p class="empty-msg">No tienes notificaciones.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Mensaje</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notificaciones as $n)
            <tr>
                <td style="white-space:nowrap;">{{ $n->fechaEnvio }}</td>
                <td>
                    <span style="font-weight:bold;color:{{ $n->tipoNotificacion === 'Alerta' ? '#dc2626' : ($n->tipoNotificacion === 'Recordatorio' ? '#d97706' : '#059669') }}">
                        {{ $n->tipoNotificacion }}
                    </span>
                </td>
                <td style="max-width:320px;">{{ $n->mensaje }}</td>
                <td>
                    <span class="badge {{ $n->estado === 'Enviado' ? 'badge-success' : 'badge-warning' }}">
                        {{ $n->estado }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection

@extends('layouts.entrenador')
@section('title', 'Reportar Fallas')
@section('content')
<div class="card" style="max-width:640px; padding:1.5rem; margin-bottom:1.5rem;">
    <h3 style="margin-bottom:0.25rem; color:#0f172a;">Reportar Falla de Equipo</h3>
    <p style="color:#64748b; font-size:0.9rem; margin-bottom:1.5rem;">Seleccione un equipo operativo de su sucursal para reportar una falla.</p>

    @if(empty($equipos))
        <div class="empty-state" style="text-align:center; padding:2rem 1rem; color:#94a3b8;">
            <p>No hay equipos operativos disponibles en tu sucursal.</p>
        </div>
    @else
        <form action="{{ route('entrenador.fallas.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="idEquipo">Equipo</label>
                <select name="idEquipo" id="idEquipo" class="form-control" required>
                    <option value="">-- Seleccione un equipo --</option>
                    @foreach($equipos as $eq)
                        <option value="{{ $eq->idEquipo }}">{{ $eq->nombreEquipo }} {{ !empty($eq->sucursal) ? '- ' . $eq->sucursal : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="gravedad">Gravedad</label>
                <select name="gravedad" id="gravedad" class="form-control" required>
                    <option value="">-- Seleccione --</option>
                    <option value="Baja">Baja</option>
                    <option value="Media">Media</option>
                    <option value="Alta">Alta</option>
                    <option value="Critica">Cr&iacute;tica</option>
                </select>
            </div>

            <div class="form-group">
                <label for="descripcionFalla">Descripci&oacute;n de la Falla</label>
                <textarea name="descripcionFalla" id="descripcionFalla" class="form-control" rows="4" maxlength="255" required placeholder="Describa el problema detectado..."></textarea>
            </div>

            <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center;">
                <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                Reportar Falla
            </button>
        </form>
    @endif
</div>

@if(!empty($historial))
<div class="card" style="overflow:hidden; padding:1.5rem;">
    <h3 style="margin-bottom:1rem; color:#0f172a; font-size:1rem;">Mis Reportes Anteriores</h3>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Equipo</th>
                    <th>Gravedad</th>
                    <th>Estado Falla</th>
                    <th>Estado Equipo</th>
                    <th>Descripci&oacute;n</th>
                </tr>
            </thead>
            <tbody>
                @foreach($historial as $h)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($h->fechaReporte)->format('d/m/Y H:i') }}</td>
                    <td style="font-weight:600;">{{ $h->nombreEquipo }}</td>
                    <td>
                        <span class="badge {{ $h->gravedad == 'Critica' ? 'badge-danger' : ($h->gravedad == 'Alta' ? 'badge-warning' : 'badge-info') }}">
                            {{ $h->gravedad }}
                        </span>
                    </td>
                    <td>{{ $h->estadoReporte }}</td>
                    <td>
                        <span style="font-size:0.8rem; color:{{ $h->estadoEquipo == 'Operativo' ? '#059669' : ($h->estadoEquipo == 'En Mantenimiento' ? '#d97706' : '#dc2626') }}">
                            {{ $h->estadoEquipo }}
                        </span>
                    </td>
                    <td style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $h->descripcionFalla }}">{{ $h->descripcionFalla }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

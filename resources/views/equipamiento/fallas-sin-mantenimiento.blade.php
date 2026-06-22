@extends('layouts.admin')
@section('title', 'Fallas sin Mantenimiento')
@section('content')
<div class="page-actions">
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <span style="font-size:0.9rem; color:#64748b;">{{ count($equipos) }} equipo(s) con fallas sin mantenimiento</span>
    </div>
</div>

<div class="card" style="padding:1rem; margin-bottom:1.5rem;">
    <form method="GET" action="{{ route('equipamiento.fallas-sin-mantenimiento') }}" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="margin-bottom:0; min-width:160px;">
            <label>Fecha Desde</label>
            <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
        </div>
        <div class="form-group" style="margin-bottom:0; min-width:160px;">
            <label>Fecha Hasta</label>
            <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        <a href="{{ route('equipamiento.fallas-sin-mantenimiento') }}" class="btn btn-outline btn-sm">Limpiar</a>
    </form>
</div>

<div class="card" style="overflow:hidden;">
    @if(empty($equipos))
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>No hay equipos con fallas pendientes de mantenimiento.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th>Sucursal</th>
                        <th>Estado Eq.</th>
                        <th>Fecha Falla</th>
                        <th>Gravedad</th>
                        <th>Descripci&oacute;n</th>
                        <th style="text-align:center;">Acci&oacute;n</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equipos as $eq)
                    <tr>
                        <td style="font-weight:600;">{{ $eq->nombreEquipo }}</td>
                        <td>{{ $eq->nombreSucursal ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $eq->estadoEquipo == 'Operativo' ? 'badge-success' : ($eq->estadoEquipo == 'En Mantenimiento' ? 'badge-warning' : 'badge-danger') }}">
                                {{ $eq->estadoEquipo }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($eq->fechaReporte)->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge {{ $eq->gravedad == 'Critica' ? 'badge-danger' : ($eq->gravedad == 'Alta' ? 'badge-warning' : 'badge-info') }}">
                                {{ $eq->gravedad }}
                            </span>
                        </td>
                        <td style="max-width:250px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $eq->descripcionFalla }}">{{ $eq->descripcionFalla }}</td>
                        <td style="text-align:center;">
                            @if($eq->estadoEquipo != 'De Baja')
                                <button onclick="openMantoModal({{ $eq->idEquipo }})" class="btn btn-warning btn-sm">
                                    <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Iniciar Mantenimiento
                                </button>
                            @else
                                <span style="color:#94a3b8; font-size:0.85rem;">No disponible</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Modal Iniciar Mantenimiento --}}
<div id="mantoModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeMantoModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Iniciar Mantenimiento</h3>
            <button onclick="closeMantoModal()" class="modal-close">&times;</button>
        </div>
        <form id="mantoForm" method="POST" action="">
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label>Fecha Programada</label>
                    <input type="date" name="fechaProgramada" id="manto_fecha" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                </div>
                <div class="form-group">
                    <label>Tecnico Asignado</label>
                    <input type="text" name="tecnicoAsignado" id="manto_tecnico" class="form-control" placeholder="Nombre del tecnico">
                </div>
                <div class="form-group">
                    <label>Costo Estimado</label>
                    <input type="number" step="0.01" min="0" name="costoMantenimiento" id="manto_costo" class="form-control" placeholder="0.00">
                </div>
            </div>
            <div class="form-group">
                <label>Descripcion del Mantenimiento</label>
                <textarea name="descripcionMantenimiento" id="manto_descripcion" class="form-control" rows="3" placeholder="Describa las tareas a realizar..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <svg fill="none" stroke="currentColor" width="16" height="16" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Iniciar Mantenimiento
                </button>
                <button type="button" onclick="closeMantoModal()" class="btn btn-outline">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openMantoModal(id) {
    document.getElementById('mantoForm').action = '{{ url("equipamiento") }}/' + id + '/iniciar-mantenimiento';
    document.getElementById('manto_fecha').value = new Date().toISOString().split('T')[0];
    document.getElementById('manto_tecnico').value = '';
    document.getElementById('manto_costo').value = '';
    document.getElementById('manto_descripcion').value = '';
    document.getElementById('mantoModal').style.display = 'flex';
}

function closeMantoModal() {
    document.getElementById('mantoModal').style.display = 'none';
}
</script>
@endsection

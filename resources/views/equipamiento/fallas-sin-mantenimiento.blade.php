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
        <form id="mantoForm" method="POST" action="" novalidate>
            @csrf
            <div class="grid-2">
                <div class="form-group">
                    <label>Fecha Programada</label>
                    <input type="date" name="fechaProgramada" id="manto_fecha" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                </div>
                <div class="form-group">
                    <label>Tecnico Asignado <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="tecnicoAsignado" id="manto_tecnico" class="form-control" required placeholder="Nombre del tecnico">
                    <small id="error_tecnico" style="color:#ef4444; font-size:0.8em; display:none;">El técnico asignado es obligatorio.</small>
                </div>
                <div class="form-group">
                    <label>Costo Estimado (Bs.) <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="costoMantenimiento" id="manto_costo" class="form-control" required placeholder="0.00" oninput="filtrarMonto(this)">
                    <small id="error_costo" style="color:#ef4444; font-size:0.8em; display:none;">El costo estimado debe ser un número mayor a 0.</small>
                </div>
            </div>
            <div class="form-group">
                <label>Descripcion del Mantenimiento <span style="color:#ef4444;">*</span></label>
                <textarea name="descripcionMantenimiento" id="manto_descripcion" class="form-control" rows="3" required placeholder="Describa las tareas a realizar..."></textarea>
                <small id="error_descripcion" style="color:#ef4444; font-size:0.8em; display:none;">La descripción del mantenimiento es obligatoria.</small>
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
function filtrarMonto(input) {
    var val = input.value.replace(/[^0-9.]/g, '');
    var pts = val.match(/\./g);
    if (pts && pts.length > 1) val = val.substring(0, val.lastIndexOf('.'));
    if (val.startsWith('.')) val = '0' + val;
    input.value = val;
}

function preValidarManto() {
    var valido = true;
    var limpiarError = function(id) { var el = document.getElementById(id); if (el) el.style.display = 'none'; };

    limpiarError('error_tecnico');
    limpiarError('error_costo');
    limpiarError('error_descripcion');

    var tecnico = document.getElementById('manto_tecnico').value.trim();
    if (!tecnico) {
        document.getElementById('error_tecnico').style.display = 'block';
        valido = false;
    }

    var costo = document.getElementById('manto_costo').value.replace(/[^0-9.]/g, '');
    if (!costo || parseFloat(costo) <= 0) {
        document.getElementById('error_costo').style.display = 'block';
        valido = false;
    }

    var desc = document.getElementById('manto_descripcion').value.trim();
    if (!desc) {
        document.getElementById('error_descripcion').style.display = 'block';
        valido = false;
    }

    return valido;
}

(function() {
    var form = document.getElementById('mantoForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!preValidarManto()) {
                e.preventDefault();
            }
        });
    }

    @if(session('manto_equipo_id'))
        openMantoModal({{ session('manto_equipo_id') }});
        document.getElementById('manto_tecnico').value = {{ json_encode(old('tecnicoAsignado', '')) }};
        document.getElementById('manto_costo').value = {{ json_encode(old('costoMantenimiento', '')) }};
        document.getElementById('manto_descripcion').value = {{ json_encode(old('descripcionMantenimiento', '')) }};
    @endif
})();

function openMantoModal(id) {
    document.getElementById('mantoForm').action = '{{ url("equipamiento") }}/' + id + '/iniciar-mantenimiento';
    document.getElementById('manto_fecha').value = new Date().toISOString().split('T')[0];
    document.getElementById('manto_tecnico').value = '';
    document.getElementById('manto_costo').value = '';
    document.getElementById('manto_descripcion').value = '';
    document.getElementById('mantoModal').style.display = 'flex';
    ['error_tecnico','error_costo','error_descripcion'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
}

function closeMantoModal() {
    document.getElementById('mantoModal').style.display = 'none';
}
</script>
@endsection

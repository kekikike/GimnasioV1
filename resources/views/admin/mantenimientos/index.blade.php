@extends('layouts.admin')

@section('title', 'Mantenimientos')

@section('content')
<div class="page-actions">
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <span style="font-size:0.9rem; color:#64748b;">{{ count($mantenimientos) }} registro(s)</span>
    </div>
</div>

<div class="card" style="padding:1rem; margin-bottom:1.5rem;">
    <form method="GET" action="{{ route('admin.mantenimientos.index') }}" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="margin-bottom:0; min-width:140px;">
            <label>Estado</label>
            <select name="estado" class="form-control">
                <option value="">Todos</option>
                <option value="Pendiente" {{ request('estado') == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="Realizado" {{ request('estado') == 'Realizado' ? 'selected' : '' }}>Realizado</option>
                <option value="Cancelado" {{ request('estado') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0; min-width:140px;">
            <label>Fecha Desde</label>
            <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
        </div>
        <div class="form-group" style="margin-bottom:0; min-width:140px;">
            <label>Fecha Hasta</label>
            <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        <a href="{{ route('admin.mantenimientos.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
    </form>
</div>

<div class="card" style="overflow:hidden;">
    @if(empty($mantenimientos))
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>No hay mantenimientos registrados.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Equipo</th>
                        <th>Modelo</th>
                        <th>Fecha Programada</th>
                        <th>Fecha Realizada</th>
                        <th>Costo</th>
                        <th>Tecnico</th>
                        <th>Estado</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mantenimientos as $m)
                    <tr>
                        <td style="color:#64748b; font-weight:500;">#{{ $m->idMantenimiento }}</td>
                        <td style="font-weight:600;">{{ $m->nombreEquipo }}</td>
                        <td>{{ $m->modelo ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($m->fechaProgramada)->format('d/m/Y') }}</td>
                        <td>{{ $m->fechaRealizada ? \Carbon\Carbon::parse($m->fechaRealizada)->format('d/m/Y') : '-' }}</td>
                        <td>${{ number_format($m->costoMantenimiento ?? 0, 2) }}</td>
                        <td>{{ $m->tecnicoAsignado ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $m->estadoMantenimiento == 'Realizado' ? 'badge-success' : ($m->estadoMantenimiento == 'Cancelado' ? 'badge-danger' : 'badge-warning') }}">
                                {{ $m->estadoMantenimiento }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <div class="action-group" style="justify-content:center;">
                                <button onclick="openEditMantoModal({{ $m->idMantenimiento }})" class="btn btn-warning btn-sm">
                                    <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Editar
                                </button>
                                <form action="{{ route('admin.mantenimientos.destroy', $m->idMantenimiento) }}" method="POST" onsubmit="return confirm('¿Eliminar este mantenimiento?')" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Modal Editar Mantenimiento --}}
<div id="editMantoModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeEditMantoModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Mantenimiento</h3>
            <button onclick="closeEditMantoModal()" class="modal-close">&times;</button>
        </div>
        <form id="editMantoForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="grid-2">
                <div class="form-group">
                    <label>Fecha Programada</label>
                    <input type="date" name="fechaProgramada" id="em_fechaProg" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required onchange="actualizarRangoFechaRealizada()">
                </div>
                <div class="form-group">
                    <label>Fecha Realizada</label>
                    <input type="date" name="fechaRealizada" id="em_fechaReal" class="form-control">
                </div>
                <div class="form-group">
                    <label>Tecnico Asignado</label>
                    <input type="text" name="tecnicoAsignado" id="em_tecnico" class="form-control">
                </div>
                <div class="form-group">
                    <label>Costo</label>
                    <input type="number" step="0.01" min="0" name="costoMantenimiento" id="em_costo" class="form-control">
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select name="estadoMantenimiento" id="em_estado" class="form-control" required>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Realizado">Realizado</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Descripcion</label>
                <textarea name="descripcionMantenimiento" id="em_descripcion" class="form-control" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <button type="button" onclick="closeEditMantoModal()" class="btn btn-outline">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function actualizarRangoFechaRealizada() {
    var prog = document.getElementById('em_fechaProg').value;
    var real = document.getElementById('em_fechaReal');
    if (prog) {
        var d = new Date(prog);
        d.setDate(d.getDate() + 1);
        var min = d.toISOString().slice(0, 10);
        d.setDate(d.getDate() + 6);
        var max = d.toISOString().slice(0, 10);
        real.min = min;
        real.max = max;
    } else {
        real.removeAttribute('min');
        real.removeAttribute('max');
    }
}

function openEditMantoModal(id) {
    document.getElementById('editMantoForm').action = '{{ url("admin/mantenimientos") }}/' + id;
    fetch('{{ url("admin/mantenimientos") }}/' + id + '/json')
        .then(function(r) { return r.json(); })
        .then(function(m) {
            document.getElementById('em_fechaProg').value = m.fechaProgramada || '';
            document.getElementById('em_fechaReal').value = m.fechaRealizada || '';
            document.getElementById('em_tecnico').value = m.tecnicoAsignado || '';
            document.getElementById('em_costo').value = m.costoMantenimiento || '';
            document.getElementById('em_estado').value = m.estadoMantenimiento || 'Pendiente';
            document.getElementById('em_descripcion').value = m.descripcionMantenimiento || '';
            actualizarRangoFechaRealizada();
            document.getElementById('editMantoModal').style.display = 'flex';
        });
}

function closeEditMantoModal() {
    document.getElementById('editMantoModal').style.display = 'none';
}
</script>
@endsection

@extends('layouts.admin')

@section('title', 'Equipamiento')

@section('content')
<div class="page-actions">
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <span style="font-size:0.9rem; color:#64748b;">{{ count($equipos) }} equipo(s)</span>
    </div>
    <div style="display:flex; gap:0.5rem;">
        <input type="text" id="buscadorModelo" class="form-control" placeholder="Buscar por modelo..." style="width:220px; padding:0.5rem 0.75rem;" oninput="filtrarEquipos()">
        <a href="{{ route('equipamiento.create') }}" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nuevo Equipo
        </a>
    </div>
</div>

<div class="card" style="padding:1rem; margin-bottom:1.5rem;">
    <form method="GET" action="{{ route('equipamiento.index') }}" style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="margin-bottom:0; min-width:160px;">
            <label>Estado</label>
            <select name="estado" class="form-control">
                <option value="">Todos</option>
                <option value="Operativo" {{ request('estado') == 'Operativo' ? 'selected' : '' }}>Operativo</option>
                <option value="En Mantenimiento" {{ request('estado') == 'En Mantenimiento' ? 'selected' : '' }}>En Mantenimiento</option>
                <option value="Fuera de Servicio" {{ request('estado') == 'Fuera de Servicio' ? 'selected' : '' }}>Fuera de Servicio</option>
                <option value="De Baja" {{ request('estado') == 'De Baja' ? 'selected' : '' }}>De Baja</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
        <a href="{{ route('equipamiento.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
    </form>
</div>

<div class="card" style="overflow:hidden;">
    @if(empty($equipos))
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>No hay equipos registrados.</p>
            <a href="{{ route('equipamiento.create') }}" class="btn btn-primary" style="margin-top:1rem; display:inline-flex;">Registrar primer equipo</a>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Equipo</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Sucursal</th>
                        <th>Adquisición</th>
                        <th>Estado</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equipos as $eq)
                    @php $marca = $marcas[$eq->idMarca] ?? null; $suc = $sucursales[$eq->idSucursal] ?? null; @endphp
                    <tr data-id="{{ $eq->idEquipo }}">
                        <td style="color:#64748b; font-weight:500;">#{{ $eq->idEquipo }}</td>
                        <td style="font-weight:600;">{{ $eq->nombreEquipo }}</td>
                        <td>{{ $marca->nombreMarca ?? '-' }}</td>
                        <td class="td-modelo">{{ $eq->modelo ?? '-' }}</td>
                        <td>{{ $suc->nombre ?? '-' }}</td>
                        <td>{{ $eq->fechaAdquisicion ? \Carbon\Carbon::parse($eq->fechaAdquisicion)->format('d/m/Y') : '-' }}</td>
                        <td>
                            <span class="badge {{ $eq->estadoEquipo == 'Operativo' ? 'badge-success' : ($eq->estadoEquipo == 'En Mantenimiento' ? 'badge-warning' : 'badge-danger') }}">
                                {{ $eq->estadoEquipo }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <div class="action-group" style="justify-content:center;">
                                <button onclick="openEditModal({{ $eq->idEquipo }})" class="btn btn-warning btn-sm">
                                    <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Editar
                                </button>
                                @if($eq->estadoEquipo == 'Operativo')
                                    <button onclick="openMantoModal({{ $eq->idEquipo }})" class="btn btn-warning btn-sm">
                                        <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Mantenimiento
                                    </button>
                                @elseif($eq->estadoEquipo == 'En Mantenimiento')
                                    @if(!empty($tieneRealizado[$eq->idEquipo]))
                                        <a href="{{ route('equipamiento.toggleEstado', $eq->idEquipo) }}" class="btn btn-success btn-sm">
                                            <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Operativo
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-outline" disabled title="Requiere mantenimiento Realizado">
                                            Bloqueado
                                        </button>
                                    @endif
                                @endif
                                <form action="{{ route('equipamiento.destroy', $eq->idEquipo) }}" method="POST" onsubmit="return confirm('¿Desactivar este equipo?')" style="display:inline;">
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

{{-- Modal Editar Equipo --}}
<div id="editModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)closeEditModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Editar Equipo</h3>
            <button onclick="closeEditModal()" class="modal-close">&times;</button>
        </div>
        <form id="editForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="grid-2">
                <div class="form-group">
                    <label>Nombre del Equipo</label>
                    <input type="text" name="nombreEquipo" id="edit_nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Modelo</label>
                    <input type="text" name="modelo" id="edit_modelo" class="form-control">
                </div>
                <div class="form-group">
                    <label>Marca</label>
                    <select name="idMarca" id="edit_idMarca" class="form-control" required>
                        @foreach(\App\Models\Marca::getAll() as $m)
                            <option value="{{ $m->idMarca }}">{{ $m->nombreMarca }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Sucursal</label>
                    <select name="idSucursal" id="edit_idSucursal" class="form-control" required>
                        @foreach(\App\Models\Sucursal::getAll() as $s)
                            <option value="{{ $s->idSucursal }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Fecha Adquisicion</label>
                    <input type="date" name="fechaAdquisicion" id="edit_fecha" class="form-control">
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select name="estadoEquipo" id="edit_estado" class="form-control" required>
                        <option value="Operativo">Operativo</option>
                        <option value="En Mantenimiento">En Mantenimiento</option>
                        <option value="Fuera de Servicio">Fuera de Servicio</option>
                        <option value="De Baja">De Baja</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                <button type="button" onclick="closeEditModal()" class="btn btn-outline">Cancelar</button>
            </div>
        </form>
    </div>
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
var equiposData = @json($equipos);

function filtrarEquipos() {
    var input = document.getElementById('buscadorModelo').value.toLowerCase();
    var rows = document.querySelectorAll('table tbody tr');
    rows.forEach(function(row) {
        var modelo = (row.querySelector('.td-modelo')?.textContent || '').toLowerCase();
        row.style.display = modelo.indexOf(input) === -1 ? 'none' : '';
    });
}

function openEditModal(id) {
    var eq = equiposData.find(function(e) { return e.idEquipo == id; });
    if (!eq) return;
    document.getElementById('editForm').action = '{{ url("equipamiento") }}/' + id;
    document.getElementById('edit_nombre').value = eq.nombreEquipo || '';
    document.getElementById('edit_modelo').value = eq.modelo || '';
    document.getElementById('edit_idMarca').value = eq.idMarca || '';
    document.getElementById('edit_idSucursal').value = eq.idSucursal || '';
    document.getElementById('edit_fecha').value = eq.fechaAdquisicion || '';
    document.getElementById('edit_estado').value = eq.estadoEquipo || 'Operativo';
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

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

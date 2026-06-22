@extends('layouts.admin')
@section('title', 'Reportes')
@section('content')

<style>
.tab-bar { display:flex; gap:0; margin-bottom:1.5rem; border-bottom:2px solid #e2e8f0; }
.tab-btn { padding:0.6rem 1.25rem; font-size:0.9rem; font-weight:600; color:#64748b; background:none; border:none; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all 0.2s; }
.tab-btn:hover { color:#0f172a; }
.tab-btn.active { color:#3b82f6; border-bottom-color:#3b82f6; }
.tab-pane { display:none; }
.tab-pane.active { display:block; }
.filter-row { display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end; margin-bottom:1.5rem; }
.filter-row .form-group { margin-bottom:0; min-width:160px; }
.filter-row .btn { flex-shrink:0; }
</style>

<div class="tab-bar">
    <button class="tab-btn" onclick="switchTab('financiero')">Ingresos Financieros</button>
    <button class="tab-btn" onclick="switchTab('equipos')">Estado de Equipos</button>
</div>
<div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem;">
    <a href="{{ route('reportes.socios') }}" class="btn btn-primary btn-sm" target="_blank">Socios</a>
    <a href="{{ route('reportes.asistencia') }}" class="btn btn-primary btn-sm" target="_blank">Asistencia</a>
    <a href="{{ route('reportes.clases') }}" class="btn btn-primary btn-sm" target="_blank">Clases</a>
    <a href="{{ route('reportes.equipamiento') }}" class="btn btn-primary btn-sm" target="_blank">Equipamiento</a>
    <a href="{{ route('reportes.financiero') }}" class="btn btn-primary btn-sm" target="_blank">Financiero</a>
</div>
</div>

<div id="tab-financiero" class="tab-pane active">
    <div class="card" style="padding:1.5rem;">
        <h3 style="margin-bottom:1rem; color:#0f172a;">Filtros de Ingresos</h3>
        <div class="filter-row">
            <div class="form-group">
                <label>Fecha Desde</label>
                <input type="date" id="fecha_desde" class="form-control">
            </div>
            <div class="form-group">
                <label>Fecha Hasta</label>
                <input type="date" id="fecha_hasta" class="form-control">
            </div>
            <div class="form-group">
                <label>Sucursal</label>
                <select id="idSucursal" class="form-control">
                    <option value="">Todas</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->idSucursal }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Metodo Pago</label>
                <select id="idMetodoPago" class="form-control">
                    <option value="">Todos</option>
                    @foreach($metodosPago as $mp)
                        <option value="{{ $mp->idMetodoPago }}">{{ $mp->nombreMetodoPago }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Cajero</label>
                <select id="carnetEmpleado" class="form-control">
                    <option value="">Todos</option>
                    @foreach($empleados as $e)
                        <option value="{{ $e->idUsuario }}">{{ $e->nombre1 }} {{ $e->apellido1 }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary" onclick="filtrarIngresos()">Filtrar</button>
        </div>
    </div>

    <div class="card" style="overflow:hidden; margin-top:1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; padding:1rem 1.5rem; border-bottom:1px solid #f1f5f9;">
            <h4 style="font-weight:600; color:#0f172a;">Resultados</h4>
            <span id="totalGeneral" style="font-size:1.25rem; font-weight:700; color:#059669;">$0.00</span>
        </div>
        <div style="overflow-x:auto;">
            <table id="tablaIngresos">
                <thead>
                    <tr>
                        <th># Recibo</th>
                        <th>Fecha</th>
                        <th>Socio</th>
                        <th>Sucursal</th>
                        <th>Cajero</th>
                        <th>Metodo Pago</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody id="cuerpoIngresos">
                    <tr><td colspan="7" style="text-align:center;color:#94a3b8;">Aplique filtros para ver resultados.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="tab-equipos" class="tab-pane">
    <div class="card" style="overflow:hidden;">
        <div style="padding:1rem 1.5rem; border-bottom:1px solid #f1f5f9; display:flex; gap:2rem;">
            <span><strong>Operativos:</strong> <span id="countOperativos" style="color:#059669;">0</span></span>
            <span><strong>En Mantenimiento:</strong> <span id="countMantenimiento" style="color:#d97706;">0</span></span>
            <span><strong>Fuera de Servicio:</strong> <span id="countFueraServicio" style="color:#dc2626;">0</span></span>
        </div>
        <button class="btn btn-primary btn-sm" style="margin:1rem 1.5rem;" onclick="cargarReporteEquipos()">
            <svg fill="none" stroke="currentColor" width="16" height="16" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Cargar Reporte
        </button>
    </div>

    <div class="card" style="overflow:hidden; margin-top:1.5rem;">
        <h4 style="padding:1rem 1.5rem; font-weight:600; color:#0f172a; border-bottom:1px solid #f1f5f9;">Historial de Fallas</h4>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr><th>Equipo</th><th>Fecha</th><th>Gravedad</th><th>Descripcion</th><th>Estado Equipo</th></tr>
                </thead>
                <tbody id="cuerpoFallas">
                    <tr><td colspan="5" style="text-align:center;color:#94a3b8;">Cargue el reporte para ver historial.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="overflow:hidden; margin-top:1.5rem;">
        <h4 style="padding:1rem 1.5rem; font-weight:600; color:#0f172a; border-bottom:1px solid #f1f5f9;">Historial de Mantenimientos</h4>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr><th>Equipo</th><th>Fecha Programada</th><th>Estado Mantenimiento</th><th>Estado Equipo</th></tr>
                </thead>
                <tbody id="cuerpoMantenimientos">
                    <tr><td colspan="4" style="text-align:center;color:#94a3b8;">Cargue el reporte para ver historial.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}

function filtrarIngresos() {
    const params = new URLSearchParams();
    ['fecha_desde','fecha_hasta','idSucursal','idMetodoPago','carnetEmpleado'].forEach(id => {
        const val = document.getElementById(id).value;
        if (val) params.append(id, val);
    });

    fetch('{{ route("admin.reportes.financiero") }}?' + params.toString())
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('cuerpoIngresos');
            if (data.ingresos.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#94a3b8;">Sin resultados.</td></tr>';
                document.getElementById('totalGeneral').textContent = '$0.00';
                return;
            }
            tbody.innerHTML = data.ingresos.map(r => `
                <tr>
                    <td>#${r.nroRecibo}</td>
                    <td>${r.fechaPago ? new Date(r.fechaPago).toLocaleDateString('es-ES') : '-'}</td>
                    <td>${r.carnetSocio ?? '-'}</td>
                    <td>${r.sucursal ?? '-'}</td>
                    <td>${r.nombre1 ?? ''} ${r.apellido1 ?? ''}</td>
                    <td>${r.nombreMetodoPago ?? '-'}</td>
                    <td style="font-weight:600;">$${parseFloat(r.montoTotal).toFixed(2)}</td>
                </tr>
            `).join('');
            document.getElementById('totalGeneral').textContent = '$' + parseFloat(data.totalGeneral).toFixed(2);
        });
}

function cargarReporteEquipos() {
    fetch('{{ route("admin.reportes.equipos") }}')
        .then(r => r.json())
        .then(d => {
            document.getElementById('countOperativos').textContent = d.operativos.length;
            document.getElementById('countMantenimiento').textContent = d.enMantenimiento.length;
            document.getElementById('countFueraServicio').textContent = d.fueraServicio.length;

            const fallasTbody = document.getElementById('cuerpoFallas');
            if (d.historialFallas.length === 0) {
                fallasTbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#94a3b8;">Sin registros.</td></tr>';
            } else {
                fallasTbody.innerHTML = d.historialFallas.map(f => `
                    <tr>
                        <td style="font-weight:600;">${f.nombreEquipo}</td>
                        <td>${f.fechaReporte ? new Date(f.fechaReporte).toLocaleDateString('es-ES') : '-'}</td>
                        <td><span class="badge ${f.gravedad == 'Critica' ? 'badge-danger' : f.gravedad == 'Alta' ? 'badge-warning' : 'badge-info'}">${f.gravedad}</span></td>
                        <td style="max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${f.descripcionFalla ?? '-'}</td>
                        <td>${f.estadoEquipo}</td>
                    </tr>
                `).join('');
            }

            const mantTbody = document.getElementById('cuerpoMantenimientos');
            if (d.historialMantenimientos.length === 0) {
                mantTbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#94a3b8;">Sin registros.</td></tr>';
            } else {
                mantTbody.innerHTML = d.historialMantenimientos.map(m => `
                    <tr>
                        <td style="font-weight:600;">${m.nombreEquipo}</td>
                        <td>${m.fechaProgramada ? new Date(m.fechaProgramada).toLocaleDateString('es-ES') : '-'}</td>
                        <td><span class="badge ${m.estadoMantenimiento == 'Completado' ? 'badge-success' : m.estadoMantenimiento == 'En Curso' ? 'badge-info' : 'badge-warning'}">${m.estadoMantenimiento}</span></td>
                        <td>${m.estadoEquipo}</td>
                    </tr>
                `).join('');
            }
        });
}
</script>
@endsection

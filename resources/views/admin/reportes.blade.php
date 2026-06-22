@extends('layouts.admin')
@section('title', 'Reportes y Estadísticas')
@section('content')

<style>
.tab-bar { display:flex; gap:0; margin-bottom:1.5rem; border-bottom:2px solid #e2e8f0; }
.tab-btn { padding:0.6rem 1.25rem; font-size:0.9rem; font-weight:600; color:#64748b; background:none; border:none; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all 0.2s; }
.tab-btn:hover { color:#0f172a; }
.tab-btn.active { color:#3b82f6; border-bottom-color:#3b82f6; }
.tab-pane { display:none; }
.tab-pane.active { display:block; }
.filter-row { display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end; margin-bottom:1.5rem; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;}
.filter-row .form-group { margin-bottom:0; min-width:160px; }
.filter-row .btn { flex-shrink:0; height: 38px; }
</style>

<div class="tab-bar">
    <button class="tab-btn active" onclick="switchTab('financiero')">💰 Ingresos Financieros</button>
    <button class="tab-btn" onclick="switchTab('equipos')">⚙️ Estado de Equipos</button>
    <button class="tab-btn" onclick="switchTab('desempeno')">👥 Desempeño y Asistencias (RF-8)</button>
</div>

<div id="tab-financiero" class="tab-pane active">
    <div class="card" style="padding: 20px;">
        <h4 style="margin-bottom: 15px; color: #1e293b;">Reporte de Ingresos</h4>
        <p style="color:#64748b;">Módulo financiero en construcción...</p>
    </div>
</div>

<div id="tab-equipos" class="tab-pane">
    <div class="card" style="padding: 20px;">
        <h4 style="margin-bottom: 15px; color: #1e293b;">Historial de Mantenimientos</h4>
        <p style="color:#64748b;">Módulo de equipos en construcción...</p>
    </div>
</div>

<div id="tab-desempeno" class="tab-pane">
    <div class="card" style="padding: 20px;">
        <h4 style="margin-bottom: 15px; color: #1e293b;">Control de Asistencias y Desempeño</h4>
        
        <form id="formFiltroDesempeno" class="filter-row">
            <div class="form-group">
                <label style="font-size: 0.8rem; font-weight: bold;">Fecha Inicio</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label style="font-size: 0.8rem; font-weight: bold;">Fecha Fin</label>
                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label style="font-size: 0.8rem; font-weight: bold;">Carnet Empleado (Opcional)</label>
                <input type="text" id="empleado_id" name="empleado_id" class="form-control" placeholder="Ej. 1234567">
            </div>
            <button type="button" class="btn btn-primary" onclick="cargarReporteDesempeno()" style="margin-bottom: 2px;">🔍 Generar Reporte</button>
        </form>

        <table style="width: 100%; border-collapse: collapse; text-align: left; margin-top: 15px;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Empleado</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Carnet (CI)</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Fecha y Hora de Entrada</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Fecha y Hora de Salida</th>
                </tr>
            </thead>
            <tbody id="cuerpoDesempeno">
                <tr><td colspan="4" style="text-align:center; padding: 20px; color:#94a3b8;">Presione "Generar Reporte" para cargar los datos.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).classList.add('active');
    event.currentTarget.classList.add('active');
}

async function cargarReporteDesempeno() {
    const btn = event.currentTarget;
    btn.innerHTML = '⏳ Cargando...';
    btn.disabled = true;

    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    const empleadoId = document.getElementById('empleado_id').value;

    try {
        // Llama a la ruta que configuró tu equipo en el web.php
        const res = await fetch(`/reportes/personal?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&empleado_id=${empleadoId}`, {
            headers: { 'Accept': 'application/json' }
        });
        
        const data = await res.json();
        const tbody = document.getElementById('cuerpoDesempeno');
        
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 20px; color:#ef4444; font-weight: bold;">No se encontraron asistencias en este rango.</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(asis => `
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 12px; font-weight: bold;">${asis.nombre1} ${asis.apellido1}</td>
                <td style="padding: 12px; font-family: monospace;">${asis.carnetEmpleado}</td>
                <td style="padding: 12px; color: #059669;">✅ ${new Date(asis.fechaHoraEntrada).toLocaleString('es-ES')}</td>
                <td style="padding: 12px; color: ${asis.fechaHoraSalida ? '#dc2626' : '#d97706'};">
                    ${asis.fechaHoraSalida ? '🔴 ' + new Date(asis.fechaHoraSalida).toLocaleString('es-ES') : '⏳ Turno Activo'}
                </td>
            </tr>
        `).join('');

    } catch (e) {
        document.getElementById('cuerpoDesempeno').innerHTML = '<tr><td colspan="4" style="text-align:center; padding: 20px; color:#ef4444;">Error al cargar el reporte. Verifique las rutas del backend.</td></tr>';
    } finally {
        btn.innerHTML = '🔍 Generar Reporte';
        btn.disabled = false;
    }
}
</script>
@endsection
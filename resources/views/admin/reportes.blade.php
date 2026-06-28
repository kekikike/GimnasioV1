@extends('layouts.admin')
@section('title', 'Reportes y Estadisticas')
@section('content')
<style>
.tab-bar { display:flex; gap:0; margin-bottom:1.5rem; border-bottom:2px solid #e2e8f0; flex-wrap:wrap; }
.tab-btn { padding:0.5rem 0.9rem; font-size:0.8rem; font-weight:600; color:#64748b; background:none; border:none; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all 0.2s; white-space:nowrap; }
.tab-btn:hover { color:#0f172a; }
.tab-btn.active { color:#3b82f6; border-bottom-color:#3b82f6; }
.tab-pane { display:none; }
.tab-pane.active { display:block; }
.filter-row { display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end; margin-bottom:1.5rem; background:#f8fafc; padding:15px; border-radius:8px; border:1px solid #e2e8f0;}
.filter-row .form-group { margin-bottom:0; min-width:160px; }
.filter-row .btn { flex-shrink:0; height:38px; }
.stats-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; margin-bottom:1.5rem; }
.stat-card { background:#fff; border-radius:0.5rem; box-shadow:0 1px 3px rgba(0,0,0,0.1); padding:1.25rem; text-align:center; }
.stat-card .number { font-size:2rem; font-weight:700; color:#0f172a; }
.stat-card .label { font-size:0.8rem; color:#64748b; font-weight:500; margin-top:0.25rem; }
.stat-card .number.red { color:#ef4444; }
.stat-card .number.green { color:#22c55e; }
.stat-card .number.blue { color:#3b82f6; }
.stat-card .number.amber { color:#f59e0b; }
table { width:100%; border-collapse:collapse; }
thead th { text-align:left; padding:0.75rem; font-size:0.8rem; font-weight:600; color:#64748b; border-bottom:2px solid #e2e8f0; text-transform:uppercase; letter-spacing:0.5px; }
tbody td { padding:0.75rem; font-size:0.85rem; color:#1e293b; border-bottom:1px solid #f1f5f9; }
tbody tr:hover { background:#f8fafc; }
.badge { display:inline-block; padding:0.25rem 0.75rem; border-radius:999px; font-size:0.75rem; font-weight:600; }
.badge-green { background:#dcfce7; color:#166534; }
.badge-amber { background:#fef3c7; color:#92400e; }
.badge-red { background:#fee2e2; color:#991b1b; }
.badge-gray { background:#f1f5f9; color:#475569; }
.badge-blue { background:#dbeafe; color:#1e40af; }
.badge-success { background:#dcfce7; color:#166534; }
.badge-danger { background:#fee2e2; color:#991b1b; }
.badge-warning { background:#fef3c7; color:#92400e; }
.empty-state { text-align:center; padding:2rem; color:#94a3b8; }
.action-print { display:flex; gap:0.75rem; margin-top:1rem; padding-top:1rem; border-top:1px solid #e2e8f0; }
.detalle-socio { margin-top:1.5rem; border:1px solid #e2e8f0; border-radius:8px; padding:1.25rem; background:#fff; display:none; }
.detalle-socio.visible { display:block; }
.detalle-socio h3 { font-size:1rem; font-weight:600; margin:0 0 1rem 0; color:#0f172a; }
.detalle-socio h4 { font-size:0.9rem; font-weight:600; margin:1.25rem 0 0.5rem 0; color:#1e293b; }
.detalle-socio .socio-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
.detalle-socio .socio-header .nombre { font-size:1.1rem; font-weight:700; }
.detalle-socio .socio-header .carnet { color:#64748b; font-size:0.85rem; }
.detalle-socio table { margin-bottom:0.5rem; }
.detalle-socio td, .detalle-socio th { font-size:0.82rem; }
.row-clickable { cursor:pointer; position:relative; }
.row-clickable:hover { background:#eef2ff !important; box-shadow:inset 0 -2px 0 #6366f1, inset 0 2px 0 #6366f1, inset 2px 0 0 #6366f1, inset -2px 0 0 #6366f1; }
.row-clickable td:first-child { position:relative; }
.row-clickable:hover td:first-child::before { content:'\2192'; position:absolute; left:2px; top:50%; transform:translateY(-50%); color:#6366f1; font-weight:700; font-size:1rem; }
.ocupacion-bar { height:1.3rem; background:#e2e8f0; border:1px solid #94a3b8; border-radius:999px; overflow:hidden; min-width:100px; position:relative; }
.ocupacion-fill { height:100%; border-radius:999px; display:flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:700; transition:width 0.3s; }
.ocupacion-text { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:700; color:#0f172a; text-shadow:0 0 3px #fff, 0 0 3px #fff; z-index:1; pointer-events:none; }
@media print{.no-print{display:none!important}}
</style>

<div class="tab-bar">
    <button class="tab-btn active" onclick="cargarRF('socios',this)">Socios</button>
    <button class="tab-btn" onclick="cargarRF('financiero',this)">Financiero</button>
    <button class="tab-btn" onclick="cargarRF('asistencia',this)">Asistencia</button>
    <button class="tab-btn" onclick="cargarRF('clases',this)">Clases</button>
    <button class="tab-btn" onclick="cargarRF('equipamiento',this)">Equipamiento</button>
    <button class="tab-btn" onclick="switchTab('admin-financiero',this)">Ingresos Financieros</button>
    <button class="tab-btn" onclick="switchTab('admin-equipos',this)">Estado de Equipos</button>
    <button class="tab-btn" onclick="switchTab('admin-desempeno',this)">Desempeno y Asistencias</button>
    <button class="tab-btn" onclick="switchTab('admin-membresias',this)">Membresias</button>
    <button class="tab-btn" onclick="switchTab('admin-renovaciones',this)">Renovaciones</button>
</div>

<div id="tab-socios" class="tab-pane active">
    <div class="filter-row no-print">
        <div class="form-group">
            <label>Estado</label>
            <select name="estado" class="form-control">
                <option value="todos">Todos</option>
                <option value="activos">Activos</option>
                <option value="inactivos">Inactivos</option>
            </select>
        </div>
        <div class="form-group">
            <label>Nombre / Carnet</label>
            <input type="text" name="nombre" class="form-control" placeholder="Buscar socio..." oninput="programarBusquedaSocios()">
        </div>
        <button class="btn btn-primary" onclick="recargarRF('socios',this)">Generar Reporte</button>
    </div>
    <div id="contenido-socios" class="empty-state">Cargando...</div>
    <div id="detalle-socio-container">
        <div id="detalle-socio" class="detalle-socio"></div>
    </div>
    <div class="action-print no-print">
        <button onclick="imprimirPDFGeneral()" class="btn btn-primary">Imprimir PDF General</button>
        <button onclick="descargarPDF('contenido-socios','Reporte General de Socios',new Date().toISOString().slice(0,10)+'_Reporte_Socios')" class="btn btn-outline">Descargar PDF</button>
    </div>
</div>

<div id="tab-financiero" class="tab-pane">
    <div class="filter-row no-print">
        <div class="form-group">
            <label>Fecha Inicio</label>
            <input type="date" name="fecha_inicio" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label>Fecha Fin</label>
            <input type="date" name="fecha_fin" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label>Estado Caja</label>
            <select name="estado_caja" class="form-control">
                <option value="">Todos</option>
                <option value="Abierta">Abierta</option>
                <option value="Cerrada">Cerrada</option>
                <option value="Auditada">Auditada</option>
            </select>
        </div>
        <div class="form-group">
            <label>Sucursal</label>
            <select name="id_sucursal" class="form-control">
                <option value="">Todas</option>
                @foreach($sucursales as $s)
                    <option value="{{ $s->idSucursal }}">{{ $s->nombre }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary" onclick="recargarRF('financiero',this)">Generar Reporte</button>
    </div>
    <div id="contenido-financiero" class="empty-state">Cargando...</div>
    <div id="detalle-financiero-container">
        <div id="detalle-financiero" class="detalle-socio"></div>
    </div>
    <div class="action-print no-print">
        <button onclick="imprimirPDFFinancieroGeneral()" class="btn btn-primary">Imprimir PDF General</button>
        <button onclick="descargarPDF('contenido-financiero','Reporte Financiero',new Date().toISOString().slice(0,10)+'_Reporte_Financiero')" class="btn btn-outline">Descargar PDF</button>
    </div>
</div>

<div id="tab-asistencia" class="tab-pane">
    <div class="filter-row no-print">
        <div class="form-group">
            <label>Fecha Inicio</label>
            <input type="date" name="fecha_inicio" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label>Fecha Fin</label>
            <input type="date" name="fecha_fin" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label>Nombre / Carnet</label>
            <input type="text" name="nombre" class="form-control" placeholder="Buscar empleado...">
        </div>
        <button class="btn btn-primary" onclick="recargarRF('asistencia',this)">Generar Reporte</button>
    </div>
    <div id="contenido-asistencia" class="empty-state">Cargando...</div>
    <div class="action-print no-print">
        <button onclick="imprimirContenido('contenido-asistencia')" class="btn btn-primary">Exportar PDF</button>
        <button onclick="descargarPDF('contenido-asistencia','Reporte de Asistencia',new Date().toISOString().slice(0,10)+'_Reporte_Asistencia')" class="btn btn-outline">Descargar PDF</button>
    </div>
</div>

<div id="tab-clases" class="tab-pane">
    <div class="filter-row no-print">
        <div class="form-group">
            <label>Fecha Inicio</label>
            <input type="date" name="fecha_inicio" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label>Fecha Fin</label>
            <input type="date" name="fecha_fin" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label>Instructor</label>
            <input type="text" name="instructor" class="form-control" placeholder="Nombre del instructor...">
        </div>
        <button class="btn btn-primary" onclick="recargarRF('clases',this)">Generar Reporte</button>
    </div>
    <div id="contenido-clases" class="empty-state">Cargando...</div>
    <div id="detalle-clases-container">
        <div id="detalle-clases" class="detalle-socio"></div>
    </div>
    <div class="action-print no-print">
        <button onclick="imprimirPDFClasesGeneral()" class="btn btn-primary">Exportar PDF</button>
        <button onclick="descargarPDF('contenido-clases','Reporte de Clases',new Date().toISOString().slice(0,10)+'_Reporte_Clases')" class="btn btn-outline">Descargar PDF</button>
    </div>
</div>

<div id="tab-equipamiento" class="tab-pane">
    <div class="filter-row no-print">
        <div class="form-group">
            <label>Estado</label>
            <select name="estado" class="form-control">
                <option value="">Todos</option>
                <option value="Operativo">Operativo</option>
                <option value="En Mantenimiento">En Mantenimiento</option>
                <option value="Fuera de Servicio">Fuera de Servicio</option>
                <option value="De Baja">De Baja</option>
            </select>
        </div>
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" placeholder="Buscar equipo...">
        </div>
        <button class="btn btn-primary" onclick="recargarRF('equipamiento',this)">Generar Reporte</button>
    </div>
    <div id="contenido-equipamiento" class="empty-state">Cargando...</div>
    <div class="action-print no-print">
        <button onclick="imprimirContenido('contenido-equipamiento')" class="btn btn-primary">Exportar PDF</button>
        <button onclick="descargarPDF('contenido-equipamiento','Reporte de Equipamiento',new Date().toISOString().slice(0,10)+'_Reporte_Equipamiento')" class="btn btn-outline">Descargar PDF</button>
    </div>
</div>

<div id="tab-admin-financiero" class="tab-pane">
    <div class="card" style="padding:20px;">
        <div class="filter-row">
            <div class="form-group">
                <label>Fecha Inicio</label>
                <input type="date" id="fin_fecha_inicio" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>Fecha Fin</label>
                <input type="date" id="fin_fecha_fin" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>Sucursal</label>
                <select id="fin_sucursal" class="form-control">
                    <option value="">Todas</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->idSucursal }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Metodo de Pago</label>
                <select id="fin_metodo" class="form-control">
                    <option value="">Todos</option>
                    @foreach($metodosPago as $mp)
                        <option value="{{ $mp->idMetodoPago }}">{{ $mp->nombreMetodoPago }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Operador</label>
                <select id="fin_operador" class="form-control">
                    <option value="">Todos</option>
                    @foreach($empleados as $e)
                        <option value="{{ $e->carnetEmpleado }}">{{ $e->nombre1 }} {{ $e->apellido1 }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary" onclick="cargarAdminFinanciero(this)">Generar Reporte</button>
        </div>
        <div id="admin-financiero-content"><div class="empty-state">Presione Generar Reporte</div></div>
        <div class="action-print no-print">
            <button onclick="imprimirContenido('admin-financiero-content')" class="btn btn-primary">Exportar PDF</button>
            <button onclick="descargarPDF('admin-financiero-content','Reporte Financiero Admin',new Date().toISOString().slice(0,10)+'_Reporte_Financiero')" class="btn btn-outline">Descargar PDF</button>
        </div>
    </div>
</div>

<div id="tab-admin-equipos" class="tab-pane">
    <div class="card" style="padding:20px;">
        <div class="filter-row">
            <div class="form-group">
                <label>Filtrar por Estado</label>
                <select id="eq_estado" class="form-control" onchange="programarBusquedaEquipos()">
                    <option value="">Todos los estados</option>
                    <option value="Operativo">Operativo</option>
                    <option value="En Mantenimiento">En Mantenimiento</option>
                    <option value="Fuera de Servicio">Fuera de Servicio</option>
                    <option value="De Baja">De Baja</option>
                </select>
            </div>
            <div class="form-group">
                <label>Buscar por nombre</label>
                <input type="text" id="eq_busqueda" class="form-control" placeholder="Escriba para buscar..." oninput="programarBusquedaEquipos()">
            </div>
            <button class="btn btn-primary" onclick="cargarAdminEquipos(this)">Buscar</button>
        </div>
        <div id="admin-equipos-content"><div class="empty-state">Seleccione un filtro y presione Generar Reporte</div></div>
        <div class="action-print no-print">
            <button onclick="imprimirContenido('admin-equipos-content')" class="btn btn-primary">Exportar PDF</button>
            <button onclick="descargarPDF('admin-equipos-content','Reporte de Equipos',new Date().toISOString().slice(0,10)+'_Reporte_Equipos')" class="btn btn-outline">Descargar PDF</button>
        </div>
    </div>
</div>

<div id="tab-admin-desempeno" class="tab-pane">
    <div class="card" style="padding:20px;">
        <div class="filter-row">
            <div class="form-group">
                <label>Fecha Inicio</label>
                <input type="date" id="des_fecha_inicio" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>Fecha Fin</label>
                <input type="date" id="des_fecha_fin" class="form-control" value="{{ \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>Carnet Empleado</label>
                <input type="text" id="des_empleado_id" class="form-control" placeholder="Opcional">
            </div>
            <button class="btn btn-primary" onclick="cargarAdminDesempeno(this)">Generar Reporte</button>
        </div>
        <div id="admin-desempeno-content"><div class="empty-state">Presione Generar Reporte</div></div>
        <div class="action-print no-print">
            <button onclick="imprimirContenido('admin-desempeno-content')" class="btn btn-primary">Exportar PDF</button>
            <button onclick="descargarPDF('admin-desempeno-content','Reporte de Desempeño',new Date().toISOString().slice(0,10)+'_Reporte_Desempeno')" class="btn btn-outline">Descargar PDF</button>
        </div>
    </div>
</div>

<div id="tab-admin-membresias" class="tab-pane">
    <div class="card" style="padding:20px;">
        <div class="filter-row">
            <div class="form-group">
                <label>Fecha Inicio</label>
                <input type="date" id="mem_fecha_inicio" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>Fecha Fin</label>
                <input type="date" id="mem_fecha_fin" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            </div>
            <button class="btn btn-primary" onclick="cargarAdminMembresias(this)">Generar Reporte</button>
        </div>
        <div id="admin-membresias-content"><div class="empty-state">Presione Generar Reporte</div></div>
        <div class="action-print no-print">
            <button onclick="imprimirContenido('admin-membresias-content')" class="btn btn-primary">Exportar PDF</button>
            <button onclick="descargarPDF('admin-membresias-content','Reporte de Membresías',new Date().toISOString().slice(0,10)+'_Reporte_Membresias')" class="btn btn-outline">Descargar PDF</button>
        </div>
    </div>
</div>

<div id="tab-admin-renovaciones" class="tab-pane">
    <div class="card" style="padding:20px;">
        <div class="filter-row">
            <div class="form-group">
                <label>Fecha Inicio</label>
                <input type="date" id="ren_fecha_inicio" class="form-control" value="{{ \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label>Fecha Fin</label>
                <input type="date" id="ren_fecha_fin" class="form-control" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
            </div>
            <button class="btn btn-primary" onclick="cargarAdminRenovaciones(this)">Generar Reporte</button>
        </div>
        <div id="admin-renovaciones-content"><div class="empty-state">Presione Generar Reporte</div></div>
        <div class="action-print no-print">
            <button onclick="imprimirContenido('admin-renovaciones-content')" class="btn btn-primary">Exportar PDF</button>
            <button onclick="descargarPDF('admin-renovaciones-content','Reporte de Renovaciones',new Date().toISOString().slice(0,10)+'_Reporte_Renovaciones')" class="btn btn-outline">Descargar PDF</button>
        </div>
    </div>
</div>

<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-pane').forEach(function(el){el.classList.remove('active');});
    document.querySelectorAll('.tab-btn').forEach(function(el){el.classList.remove('active');});
    document.getElementById('tab-'+tabId).classList.add('active');
    if(btn) btn.classList.add('active');
}

function cargarRF(reporte, btn) {
    switchTab(reporte, btn);
    var cont=document.getElementById('contenido-'+reporte);
    if(reporte==='socios'){
        document.getElementById('detalle-socio').classList.remove('visible');
        document.getElementById('detalle-socio').innerHTML='';
    }
    if(reporte==='financiero'){
        document.getElementById('detalle-financiero').classList.remove('visible');
        document.getElementById('detalle-financiero').innerHTML='';
    }
    if(reporte==='clases'){
        document.getElementById('detalle-clases').classList.remove('visible');
        document.getElementById('detalle-clases').innerHTML='';
    }
    if(cont.getAttribute('data-cargado')) return;
    fetchReporte(reporte, cont);
}

function recargarRF(reporte, btn) {
    if(btn){btn.innerHTML='Cargando...';btn.disabled=true;}
    var cont=document.getElementById('contenido-'+reporte);
    cont.removeAttribute('data-cargado');
    if(reporte==='socios'){
        document.getElementById('detalle-socio').classList.remove('visible');
        document.getElementById('detalle-socio').innerHTML='';
    }
    if(reporte==='financiero'){
        document.getElementById('detalle-financiero').classList.remove('visible');
        document.getElementById('detalle-financiero').innerHTML='';
    }
    if(reporte==='clases'){
        document.getElementById('detalle-clases').classList.remove('visible');
        document.getElementById('detalle-clases').innerHTML='';
    }
    fetchReporte(reporte, cont, function(){
        if(btn){btn.innerHTML='Generar Reporte';btn.disabled=false;}
    });
}

function fetchReporte(reporte, cont, cb) {
    cont.innerHTML='<div class="empty-state">Cargando...</div>';
    var params=new URLSearchParams();
    params.set('json','1');
    var fiInput=document.querySelector('#tab-'+reporte+' input[name=fecha_inicio]');
    if(fiInput) params.set('fecha_inicio', fiInput.value);
    var ffInput=document.querySelector('#tab-'+reporte+' input[name=fecha_fin]');
    if(ffInput) params.set('fecha_fin', ffInput.value);
    var estadoSelect=document.querySelector('#tab-'+reporte+' select[name=estado]');
    if(estadoSelect) params.set('estado', estadoSelect.value);
    var ecSelect=document.querySelector('#tab-'+reporte+' select[name=estado_caja]');
    if(ecSelect) params.set('estado_caja', ecSelect.value);
    var nombreInput=document.querySelector('#tab-'+reporte+' input[name=nombre]');
    if(nombreInput) params.set('nombre', nombreInput.value);
    var sucSelect=document.querySelector('#tab-'+reporte+' select[name=id_sucursal]');
    if(sucSelect) params.set('id_sucursal', sucSelect.value);
    var instructorInput=document.querySelector('#tab-'+reporte+' input[name=instructor]');
    if(instructorInput) params.set('instructor', instructorInput.value);
    fetch('/reportes/'+reporte+'?'+params.toString(),{headers:{'Accept':'application/json'}})
        .then(function(r){return r.json();}).then(function(d){
            cont.setAttribute('data-cargado','1');
            cont.innerHTML=renderReporte(reporte,d);
            if(cb) cb();
        }).catch(function(){cont.innerHTML='<div class="empty-state" style="color:#ef4444;">Error al cargar</div>';if(cb) cb();});
}

window.renderReporte=function(reporte, d) {
    var renderers={
        socios: function() {
            var h='<div class="stats-grid"><div class="stat-card"><div class="number">'+d.totalSocios+'</div><div class="label">Total Socios</div></div>';
            h+='<div class="stat-card"><div class="number green">'+d.conMembresia+'</div><div class="label">Con Membresia Activa</div></div>';
            h+='<div class="stat-card"><div class="number red">'+d.sinMembresia+'</div><div class="label">Sin Membresia Activa</div></div></div>';
            h+='<table><thead><tr><th>Carnet</th><th>Nombre</th><th>Correo</th><th>Telefono</th><th>Estado</th><th>Vencimiento</th><th>Strikes</th></tr></thead><tbody>';
            (d.socios||[]).forEach(function(s){
                var nom=(s.usuario?s.usuario.nombre1:'')+' '+(s.usuario?s.usuario.apellido1:'');
                var act=s.membresia&&s.membresia.estadoMembresia=='Activa';
                h+='<tr class="row-clickable" data-carnet="'+s.carnetSocio+'" onclick="cargarDetalleSocio(\''+s.carnetSocio+'\')"><td><strong>'+s.carnetSocio+'</strong></td><td>'+nom+'</td><td>'+(s.usuario?s.usuario.correo:'N/A')+'</td><td>'+(s.usuario?s.usuario.telefono:'N/A')+'</td>';
                h+='<td><span class="badge '+(act?'badge-success':'badge-warning')+'">'+(act?'Activo':'Vencido/Inactivo')+'</span></td>';
                h+='<td>'+(s.membresia?s.membresia.fechaFinMembresia:'N/A')+'</td>';
                h+='<td>'+(s.strikes>0?'<span class="badge badge-danger">'+s.strikes+'</span>':'<span style="color:#94a3b8;">0</span>')+'</td></tr>';
            });
            if(!d.socios||!d.socios.length) h+='<tr><td colspan="7" class="empty-state">No hay socios</td></tr>';
            h+='</tbody></table>'; h+=footerReporteHTML(); return h;
        },
        financiero: function() {
            var h='<div class="stats-grid"><div class="stat-card"><div class="number green">$'+Number(d.totalIngresos||0).toFixed(2)+'</div><div class="label">Total Ingresos</div></div>';
            h+='<div class="stat-card"><div class="number blue">'+d.totalTransacciones+'</div><div class="label">Transacciones</div></div>';
            h+='<div class="stat-card"><div class="number amber">$'+Number(d.totalTransacciones>0?d.totalIngresos/d.totalTransacciones:0).toFixed(2)+'</div><div class="label">Promedio</div></div></div>';
            if(d.ingresosPorEstado&&Object.keys(d.ingresosPorEstado).length){
                h+='<div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem;">';
                for(var k in d.ingresosPorEstado) h+='<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:0.5rem;padding:0.75rem 1rem;text-align:center;flex:1;min-width:120px;"><strong style="display:block;font-size:0.75rem;color:#64748b;">'+k+'</strong><span style="font-size:1.1rem;font-weight:700;color:#0f172a;">$'+Number(d.ingresosPorEstado[k]).toFixed(2)+'</span></div>';
                h+='</div>';
            }
            h+='<table><thead><tr><th>ID Caja</th><th>Sucursal</th><th>Fecha</th><th>Apertura</th><th>Cierre</th><th>Estado</th></tr></thead><tbody>';
            (d.pagos||[]).forEach(function(p){
                var bc=p.estadoCaja==='Abierta'?'badge-green':p.estadoCaja==='Cerrada'?'badge-amber':'badge-blue';
                h+='<tr class="row-clickable" data-idcaja="'+p.idCaja+'" onclick="cargarDetalleFinanciero('+p.idCaja+')"><td><strong>'+p.idCaja+'</strong></td><td>'+(p.sucursalNombre||'N/A')+'</td><td>'+p.fechaApertura+'</td><td>$'+Number(p.montoApertura).toFixed(2)+'</td><td>$'+Number(p.montoCierre||0).toFixed(2)+'</td><td><span class="badge '+bc+'">'+p.estadoCaja+'</span></td></tr>';
            });
            if(!d.pagos||!d.pagos.length) h+='<tr><td colspan="6" class="empty-state">No hay pagos</td></tr>';
            h+='</tbody></table>'; h+=footerReporteHTML(); return h;
        },
        asistencia: function() {
            var h='<div class="stats-grid"><div class="stat-card"><div class="number">'+(d.totalAsistencias||0)+'</div><div class="label">Total Asistencias</div></div>';
            var prom=(d.asistenciasPorDia&&Object.keys(d.asistenciasPorDia).length>0)?(d.totalAsistencias/Object.keys(d.asistenciasPorDia).length).toFixed(2):0;
            h+='<div class="stat-card"><div class="number blue">'+prom+'</div><div class="label">Promedio Diario</div></div></div>';
            h+='<table><thead><tr><th>CI</th><th>Empleado</th><th>Fecha</th><th>Dia</th><th>Entrada</th><th>Salida</th><th>Esperado</th><th>Estado</th></tr></thead><tbody>';
            (d.asistencias||[]).forEach(function(a){
                var nom=a.nombreEmpleado||'';
                var fe=new Date(a.fechaHoraEntrada).toLocaleDateString('es-ES');
                var en=a.fechaHoraEntrada?new Date(a.fechaHoraEntrada).toLocaleTimeString('es-ES',{hour:'2-digit',minute:'2-digit'}):'N/A';
                var sa=a.fechaHoraSalida?new Date(a.fechaHoraSalida).toLocaleTimeString('es-ES',{hour:'2-digit',minute:'2-digit'}):'N/A';
                var bc=a.estadoAsistencia==='Puntual'?'badge-green':a.estadoAsistencia==='Tardanza'?'badge-amber':'badge-red';
                var esp=a.esperadoEntrada!=='—' ? a.esperadoEntrada+' - '+a.esperadoSalida : '—';
                h+='<tr><td><strong>'+a.carnetEmpleado+'</strong></td><td>'+nom+'</td><td>'+fe+'</td><td>'+(a.diaSemana||'')+'</td><td>'+en+'</td><td>'+sa+'</td><td style="font-size:0.75rem;">'+esp+'</td><td><span class="badge '+bc+'">'+(a.estadoAsistencia||'Falta')+'</span></td></tr>';
            });
            if(!d.asistencias||!d.asistencias.length) h+='<tr><td colspan="8" class="empty-state">No hay asistencias</td></tr>';
            h+='</tbody></table>'; h+=footerReporteHTML(); return h;
        },
        clases: function() {
            var h='<table><thead><tr><th>Clase</th><th>Instructor</th><th>Fecha</th><th>Hora</th><th>Capacidad</th><th>Reservados</th><th>Asistieron</th><th>Ocupacion</th></tr></thead><tbody>';
            (d.estadisticas||[]).forEach(function(c){
                var pct=c.ocupacion||0;
                var barBg=pct>80?'#dc2626':pct>50?'#d97706':'#16a34a';
                h+='<tr class="row-clickable" data-idclase="'+c.idClaseGrupal+'" onclick="cargarDetalleClase('+c.idClaseGrupal+')"><td><strong>'+c.nombre+'</strong></td><td>'+c.instructor+'</td><td>'+c.fecha+'</td>';
                h+='<td>'+(c.horaInicio?c.horaInicio.substring(0,5):'')+' - '+(c.horaFin?c.horaFin.substring(0,5):'')+'</td>';
                h+='<td>'+c.capacidad+'</td><td>'+c.reservados+'</td><td>'+c.asistieron+'</td>';
                h+='<td><div class="ocupacion-bar"><div class="ocupacion-fill" style="width:'+pct+'%;background:'+barBg+';"></div><span class="ocupacion-text">'+pct+'%</span></div></td></tr>';
            });
            if(!d.estadisticas||!d.estadisticas.length) h+='<tr><td colspan="8" class="empty-state">No hay clases</td></tr>';
            h+='</tbody></table>'; h+=footerReporteHTML(); return h;
        },
        equipamiento: function() {
            var e=d.estadisticas||{};
            var h='<div class="stats-grid"><div class="stat-card"><div class="number">'+(e.total||0)+'</div><div class="label">Total Equipos</div></div>';
            h+='<div class="stat-card"><div class="number green">'+(e.operativos||0)+'</div><div class="label">Operativos</div></div>';
            h+='<div class="stat-card"><div class="number amber">'+(e.mantenimiento||0)+'</div><div class="label">En Mantenimiento</div></div>';
            h+='<div class="stat-card"><div class="number red">'+(e.fuera_servicio||0)+'</div><div class="label">Fuera de Servicio</div></div>';
            h+='<div class="stat-card"><div class="number red">'+(e.fallas_recientes||0)+'</div><div class="label">Fallas (30d)</div></div></div>';
            h+='<table><thead><tr><th>ID</th><th>Nombre</th><th>Modelo</th><th>Estado</th><th>Adquisicion</th></tr></thead><tbody>';
            (d.equipos||[]).forEach(function(eq){
                var bc=eq.estadoEquipo==='Operativo'?'badge-green':eq.estadoEquipo==='En Mantenimiento'?'badge-amber':eq.estadoEquipo==='Fuera de Servicio'?'badge-red':'badge-gray';
                h+='<tr><td>'+eq.idEquipo+'</td><td><strong>'+eq.nombreEquipo+'</strong></td><td>'+(eq.modelo||'N/A')+'</td><td><span class="badge '+bc+'">'+eq.estadoEquipo+'</span></td><td>'+(eq.fechaAdquisicion||'N/A')+'</td></tr>';
            });
            if(!d.equipos||!d.equipos.length) h+='<tr><td colspan="5" class="empty-state">No hay equipos</td></tr>';
            h+='</tbody></table>'; h+=footerReporteHTML(); return h;
        }
    };
    return (renderers[reporte]||function(){return '<div class="empty-state">Reporte no disponible</div>';})();
};

function stripNoPrint(html){
    var d=document.createElement('div');
    d.innerHTML=html;
    d.querySelectorAll('.no-print').forEach(function(el){el.remove();});
    return d.innerHTML;
}
function imprimirHTML(html, titulo){
    var iframe=document.createElement('iframe');
    iframe.style.position='fixed';iframe.style.width='0';iframe.style.height='0';iframe.style.border='none';
    document.body.appendChild(iframe);
    var doc=iframe.contentWindow.document;
    doc.open();
    doc.write('<!DOCTYPE html><html><head><title>'+titulo+'</title><link rel="preconnect" href="https://fonts.bunny.net"><link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet"><style>body{font-family:Inter,sans-serif;padding:20px;padding-bottom:40px;}table{width:100%;border-collapse:collapse;}th{text-align:left;padding:0.75rem;font-size:0.8rem;font-weight:600;color:#64748b;border-bottom:2px solid #e2e8f0;text-transform:uppercase;}td{padding:0.75rem;font-size:0.85rem;color:#1e293b;border-bottom:1px solid #f1f5f9;}.badge{display:inline-block;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.75rem;font-weight:600;}.badge-green,.badge-success{background:#dcfce7;color:#166534;}.badge-amber,.badge-warning{background:#fef3c7;color:#92400e;}.badge-red,.badge-danger{background:#fee2e2;color:#991b1b;}.badge-blue{background:#dbeafe;color:#1e40af;}.badge-gray{background:#f1f5f9;color:#475569;}.stat-card{text-align:center;padding:1rem;display:inline-block;margin:0.5rem;}.stat-card .number{font-size:2rem;font-weight:700;color:#0f172a;}.stat-card .label{font-size:0.8rem;color:#64748b;}.socio-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;}.socio-header .nombre{font-size:1.1rem;font-weight:700;}.socio-header .carnet{color:#64748b;font-size:0.85rem;}h4{font-size:0.9rem;font-weight:600;margin:1.25rem 0 0.5rem 0;color:#1e293b;}h2{margin-bottom:1rem;}.row-clickable{cursor:default;}</style></head><body>');
    doc.write(html);
    doc.write(pieReporte());
    doc.write('</body></html>');
    doc.close();
    setTimeout(function(){iframe.contentWindow.print();document.body.removeChild(iframe);},500);
}
var usuarioReporte='{{ session("usuario") ? session("usuario")->nombre1." ".session("usuario")->nombre2." ".session("usuario")->apellido1." ".session("usuario")->apellido2 : "Usuario" }}';
function pieReporte(){
    var now=new Date();
    var f=now.getDate().toString().padStart(2,'0')+'/'+(now.getMonth()+1).toString().padStart(2,'0')+'/'+now.getFullYear()+' '+now.getHours().toString().padStart(2,'0')+':'+now.getMinutes().toString().padStart(2,'0')+':'+now.getSeconds().toString().padStart(2,'0');
    return '<div style="position:fixed;bottom:10px;right:20px;font-size:0.75rem;color:#94a3b8;text-align:right;">Generado por: '+usuarioReporte+'<br>'+f+'</div>';
}
function footerReporteHTML(){
    var now=new Date();
    var f=now.getDate().toString().padStart(2,'0')+'/'+(now.getMonth()+1).toString().padStart(2,'0')+'/'+now.getFullYear()+' '+now.getHours().toString().padStart(2,'0')+':'+now.getMinutes().toString().padStart(2,'0');
    return '<div style="margin-top:1.5rem;padding-top:0.75rem;border-top:1px solid #e2e8f0;font-size:0.75rem;color:#94a3b8;text-align:right;">Generado por: '+usuarioReporte+' | '+f+'</div>';
}
function descargarPDF(contenidoId, titulo, nombreArchivo) {
    var original=document.getElementById(contenidoId);
    if(!original) return;
    var html=stripNoPrint(original.innerHTML);
    var csrf=document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'';
    fetch('/reportes/generar-pdf',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'},
        body:JSON.stringify({html:html,titulo:titulo,nombreArchivo:nombreArchivo,usuario:usuarioReporte})
    }).then(function(r){
        if(!r.ok) throw new Error('Error '+r.status);
        return r.blob();
    }).then(function(blob){
        var url=URL.createObjectURL(blob);
        var a=document.createElement('a');a.href=url;a.download=nombreArchivo+'.pdf';
        document.body.appendChild(a);a.click();
        document.body.removeChild(a);URL.revokeObjectURL(url);
    }).catch(function(e){
        console.error('Error PDF:',e);
        alert('Error al descargar el PDF. Verifique la consola para detalles.');
    });
}

function imprimirContenido(id) {
    var c=stripNoPrint(document.getElementById(id).innerHTML);
    imprimirHTML(c,'Reporte');
}

function cargarAdminFinanciero(btn) {
    if(btn){btn.innerHTML='Cargando...';btn.disabled=true;}
    var fi=document.getElementById('fin_fecha_inicio').value;
    var ff=document.getElementById('fin_fecha_fin').value;
    var fs=document.getElementById('fin_sucursal').value;
    var fm=document.getElementById('fin_metodo').value;
    var fo=document.getElementById('fin_operador').value;
    var params='fecha_desde='+encodeURIComponent(fi)+'&fecha_hasta='+encodeURIComponent(ff);
    if(fs) params+='&idSucursal='+fs;
    if(fm) params+='&idMetodoPago='+fm;
    if(fo) params+='&carnetEmpleado='+fo;
    fetch('/admin/reportes/financiero?'+params,{headers:{'Accept':'application/json'}})
        .then(function(r){return r.json();}).then(function(d){
            var c=document.getElementById('admin-financiero-content');
            if(!d||!d.ingresos){c.innerHTML='<div class="empty-state">No hay datos</div>';return;}
            var h='<div class="stats-grid"><div class="stat-card"><div class="number green">$'+Number(d.totalGeneral||0).toFixed(2)+'</div><div class="label">Total Ingresos</div></div>';
            h+='<div class="stat-card"><div class="number blue">'+d.ingresos.length+'</div><div class="label">Transacciones</div></div>';
            h+='<div class="stat-card"><div class="number amber">$'+Number(d.ingresos.length>0?d.totalGeneral/d.ingresos.length:0).toFixed(2)+'</div><div class="label">Promedio</div></div></div>';
            if(d.ingresos.length){h+='<table><thead><tr><th>Recibo</th><th>Sucursal</th><th>Socio</th><th>Fecha</th><th>Metodos de Pago</th><th>Total</th><th>Estado</th></tr></thead><tbody>';
            d.ingresos.forEach(function(r){
                var bc=r.estadoRecibo==='Emitido'?'badge-green':'badge-red';
                var socio=r.nombre1+' '+r.apellido1+(r.carnetSocio?' ('+r.carnetSocio+')':'');
                h+='<tr><td><strong>#'+r.idRecibo+'</strong></td><td>'+(r.sucursal||'')+'</td><td>'+socio+'</td><td>'+(r.fechaPago||'')+'</td><td style="font-size:0.8rem;">'+(r.metodos_pago||'')+'</td><td><strong>$'+Number(r.montoTotal||0).toFixed(2)+'</strong></td><td><span class="badge '+bc+'">'+r.estadoRecibo+'</span></td></tr>';
            });
            h+='</tbody></table>';}else{h+='<div class="empty-state">No hay transacciones</div>';}
            h+=footerReporteHTML(); c.innerHTML=h;
        }).catch(function(){document.getElementById('admin-financiero-content').innerHTML='<div class="empty-state" style="color:#ef4444;">Error</div>';})
        .finally(function(){if(btn){btn.innerHTML='Generar Reporte';btn.disabled=false;}});
}

var timeoutBusquedaEquipos=null;
function programarBusquedaEquipos(){
    if(timeoutBusquedaEquipos) clearTimeout(timeoutBusquedaEquipos);
    timeoutBusquedaEquipos=setTimeout(function(){cargarAdminEquipos();},250);
}
function cargarAdminEquipos(btn) {
    if(timeoutBusquedaEquipos){clearTimeout(timeoutBusquedaEquipos);timeoutBusquedaEquipos=null;}
    if(btn){btn.innerHTML='Cargando...';btn.disabled=true;}
    var estado=document.getElementById('eq_estado').value;
    var nombre=document.getElementById('eq_busqueda').value;
    var url='/admin/reportes/equipos?estado='+encodeURIComponent(estado)+'&nombre='+encodeURIComponent(nombre);
    fetch(url,{headers:{'Accept':'application/json'}})
        .then(function(r){return r.json();}).then(function(d){
            var c=document.getElementById('admin-equipos-content');
            if(!d){c.innerHTML='<div class="empty-state">No hay datos</div>';return;}
            var h='<div class="stats-grid"><div class="stat-card"><div class="number">'+(d.equipos||[]).length+'</div><div class="label">Total Equipos</div></div>';
            h+='<div class="stat-card"><div class="number red">'+(d.historialFallas||[]).length+'</div><div class="label">Fallas Reportadas</div></div>';
            h+='<div class="stat-card"><div class="number blue">'+(d.historialMantenimientos||[]).length+'</div><div class="label">Mantenimientos</div></div></div>';
            h+='<h4 style="font-size:0.9rem;margin-bottom:0.5rem;">Equipos'+(d.estado?' ['+d.estado+']':' [Todos]')+(d.nombre?' / &quot;'+d.nombre+'&quot;':'')+'</h4>';
            if(d.equipos&&d.equipos.length){h+='<div style="overflow-x:auto;margin-bottom:1.5rem;"><table><thead><tr><th>ID</th><th>Nombre</th><th>Marca</th><th>Modelo</th><th>Sucursal</th><th>Estado</th><th>Adquisicion</th></tr></thead><tbody>';
            d.equipos.forEach(function(eq){
                var bc=eq.estadoEquipo==='Operativo'?'badge-green':eq.estadoEquipo==='En Mantenimiento'?'badge-amber':eq.estadoEquipo==='Fuera de Servicio'?'badge-red':'badge-gray';
                h+='<tr><td>'+eq.idEquipo+'</td><td><strong>'+eq.nombreEquipo+'</strong></td><td>'+(eq.nombreMarca||'')+'</td><td>'+(eq.modelo||'')+'</td><td>'+(eq.sucursal||'')+'</td><td><span class="badge '+bc+'">'+eq.estadoEquipo+'</span></td><td>'+(eq.fechaAdquisicion||'')+'</td></tr>';
            });
            h+='</tbody></table></div>';}else{h+='<div class="empty-state" style="margin-bottom:1.5rem;">No hay equipos en este estado</div>';}
            h+='<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;"><div><h4 style="font-size:0.9rem;margin-bottom:0.5rem;">Historial de Fallas</h4>';
            if(d.historialFallas&&d.historialFallas.length){h+='<div style="overflow-x:auto;"><table><thead><tr><th>Equipo</th><th>Fecha</th><th>Gravedad</th><th>Estado</th></tr></thead><tbody>';
            d.historialFallas.forEach(function(f){
                var bc=f.gravedad==='Critica'?'badge-red':f.gravedad==='Alta'?'badge-amber':f.gravedad==='Media'?'badge-blue':'badge-green';
                var ec=f.estadoReporte==='Resuelto'?'badge-green':'badge-amber';
                h+='<tr><td>'+(f.nombreEquipo||f.idEquipo||'')+'</td><td>'+(f.fechaReporte||'')+'</td><td><span class="badge '+bc+'">'+(f.gravedad||'')+'</span></td><td><span class="badge '+ec+'">'+(f.estadoReporte||'')+'</span></td></tr>';
            });
            h+='</tbody></table></div>';}else{h+='<div class="empty-state">Sin fallas registradas</div>';}
            h+='</div><div><h4 style="font-size:0.9rem;margin-bottom:0.5rem;">Historial de Mantenimientos</h4>';
            if(d.historialMantenimientos&&d.historialMantenimientos.length){h+='<div style="overflow-x:auto;"><table><thead><tr><th>Equipo</th><th>Programada</th><th>Realizada</th><th>Costo</th><th>Estado</th></tr></thead><tbody>';
            d.historialMantenimientos.forEach(function(m){
                var bc=m.estadoMantenimiento==='Realizado'?'badge-green':m.estadoMantenimiento==='Pendiente'?'badge-amber':'badge-red';
                h+='<tr><td>'+(m.nombreEquipo||m.idEquipo||'')+'</td><td>'+(m.fechaProgramada||'')+'</td><td>'+(m.fechaRealizada||'-')+'</td><td>$'+Number(m.costoMantenimiento||0).toFixed(2)+'</td><td><span class="badge '+bc+'">'+(m.estadoMantenimiento||'')+'</span></td></tr>';
            });
            h+='</tbody></table></div>';}else{h+='<div class="empty-state">Sin mantenimientos registrados</div>';}
            h+=footerReporteHTML();c.innerHTML=h;
        }).catch(function(){document.getElementById('admin-equipos-content').innerHTML='<div class="empty-state" style="color:#ef4444;">Error</div>';})
        .finally(function(){if(btn){btn.innerHTML='Buscar';btn.disabled=false;}});
}

function cargarAdminDesempeno(btn) {
    if(btn){btn.innerHTML='Cargando...';btn.disabled=true;}
    var fi=document.getElementById('des_fecha_inicio').value,ff=document.getElementById('des_fecha_fin').value,em=document.getElementById('des_empleado_id').value;
    fetch('/reportes/personal?fecha_inicio='+fi+'&fecha_fin='+ff+'&empleado_id='+em,{headers:{'Accept':'application/json'}})
        .then(function(r){return r.json();}).then(function(d){
            var c=document.getElementById('admin-desempeno-content');
            if(!d||!d.length){c.innerHTML='<div class="empty-state">No se encontraron asistencias</div>';return;}
            var h='<div style="margin-bottom:1rem;font-size:0.9rem;color:#64748b;">Total: <strong>'+d.length+'</strong> registros</div><table><thead><tr><th>Empleado</th><th>Carnet</th><th>Entrada</th><th>Salida</th></tr></thead><tbody>';
            d.forEach(function(a){var en=new Date(a.fechaHoraEntrada).toLocaleString('es-ES');var sa=a.fechaHoraSalida?new Date(a.fechaHoraSalida).toLocaleString('es-ES'):'Turno Activo';h+='<tr><td><strong>'+a.nombre1+' '+a.apellido1+'</strong></td><td>'+a.carnetEmpleado+'</td><td>'+en+'</td><td>'+sa+'</td></tr>';});
            h+='</tbody></table>';h+=footerReporteHTML();c.innerHTML=h;
        }).catch(function(){document.getElementById('admin-desempeno-content').innerHTML='<div class="empty-state" style="color:#ef4444;">Error</div>';})
        .finally(function(){if(btn){btn.innerHTML='Generar Reporte';btn.disabled=false;}});
}

function cargarAdminMembresias(btn) {
    if(btn){btn.innerHTML='Cargando...';btn.disabled=true;}
    var fi=document.getElementById('mem_fecha_inicio').value,ff=document.getElementById('mem_fecha_fin').value;
    fetch('/admin/reportes/membresias?fecha_inicio='+fi+'&fecha_fin='+ff,{headers:{'Accept':'application/json'}})
        .then(function(r){return r.json();}).then(function(d){
            var c=document.getElementById('admin-membresias-content');
            if(!d||!d.membresias||!d.membresias.length){c.innerHTML='<div class="empty-state">No hay datos</div>';return;}
            var h='<div class="stats-grid"><div class="stat-card"><div class="number">'+d.totalMembresias+'</div><div class="label">Total Membresias</div></div>';
            h+='<div class="stat-card"><div class="number green">$'+Number(d.totalGeneral||0).toFixed(2)+'</div><div class="label">Ingresos Totales</div></div></div>';
            h+='<table><thead><tr><th>Plan</th><th>Costo</th><th>Vendidas</th><th>Ingresos</th></tr></thead><tbody>';
            d.membresias.forEach(function(m){
                h+='<tr><td><strong>'+m.nombrePlan+'</strong></td><td>$'+Number(m.costoPlan).toFixed(2)+'</td><td>'+m.total_vendidas+'</td><td>$'+Number(m.ingresos_totales).toFixed(2)+'</td></tr>';
            });
            h+='</tbody></table>';h+=footerReporteHTML();c.innerHTML=h;
        }).catch(function(){document.getElementById('admin-membresias-content').innerHTML='<div class="empty-state" style="color:#ef4444;">Error</div>';})
        .finally(function(){if(btn){btn.innerHTML='Generar Reporte';btn.disabled=false;}});
}

function cargarAdminRenovaciones(btn) {
    if(btn){btn.innerHTML='Cargando...';btn.disabled=true;}
    var fi=document.getElementById('ren_fecha_inicio').value,ff=document.getElementById('ren_fecha_fin').value;
    fetch('/admin/reportes/renovaciones?fecha_inicio='+fi+'&fecha_fin='+ff,{headers:{'Accept':'application/json'}})
        .then(function(r){return r.json();}).then(function(d){
            var c=document.getElementById('admin-renovaciones-content');
            if(!d||!d.renovaciones||!d.renovaciones.length){c.innerHTML='<div class="empty-state">No se encontraron renovaciones</div>';return;}
            var h='<div class="stats-grid"><div class="stat-card"><div class="number">'+d.totalRenovaciones+'</div><div class="label">Total Renovaciones</div></div>';
            h+='<div class="stat-card"><div class="number blue">'+d.sociosUnicos+'</div><div class="label">Socios que renovaron</div></div></div>';
            h+='<table><thead><tr><th>Socio</th><th>Carnet</th><th>Plan</th><th>Inicio</th><th>Fin</th><th>Estado</th><th># Membresia</th></tr></thead><tbody>';
            d.renovaciones.forEach(function(r){
                var bc=r.estadoMembresia==='Activa'?'badge-success':r.estadoMembresia==='Vencida'?'badge-warning':'badge-gray';
                h+='<tr><td><strong>'+r.nombre_socio+'</strong></td><td>'+r.carnetSocio+'</td><td>'+r.nombrePlan+'</td><td>'+r.fechaInicioMembresia+'</td><td>'+r.fechaFinMembresia+'</td><td><span class="badge '+bc+'">'+r.estadoMembresia+'</span></td><td>#'+r.num_membresia+'</td></tr>';
            });
            h+='</tbody></table>';h+=footerReporteHTML();c.innerHTML=h;
        }).catch(function(){document.getElementById('admin-renovaciones-content').innerHTML='<div class="empty-state" style="color:#ef4444;">Error</div>';})
        .finally(function(){if(btn){btn.innerHTML='Generar Reporte';btn.disabled=false;}});
}

var timeoutBusquedaSocios=null;
function programarBusquedaSocios(){
    if(timeoutBusquedaSocios) clearTimeout(timeoutBusquedaSocios);
    timeoutBusquedaSocios=setTimeout(function(){recargarRF('socios');},300);
}

function cargarDetalleSocio(carnet){
    var detail=document.getElementById('detalle-socio');
    detail.innerHTML='<div class="empty-state">Cargando detalle...</div>';
    detail.classList.remove('visible');
    fetch('/reportes/socios/'+carnet,{headers:{'Accept':'application/json'}})
        .then(function(r){return r.json();}).then(function(d){
            var h='<div class="socio-header"><div><div class="nombre">'+(d.socio.usuario?d.socio.usuario.nombre1+' '+d.socio.usuario.apellido1:'')+'</div><div class="carnet">Carnet: '+d.socio.carnetSocio+' | '+(d.socio.usuario?d.socio.usuario.correo:'')+' | Tel: '+(d.socio.usuario?d.socio.usuario.telefono:'')+'</div></div><div><span class="badge '+(d.socio.membresia&&d.socio.membresia.estadoMembresia=='Activa'?'badge-success':'badge-warning')+'">'+(d.socio.membresia&&d.socio.membresia.estadoMembresia=='Activa'?'Activo':'Vencido/Inactivo')+'</span></div></div>';

            h+='<h4>Historial de Membresias</h4>';
            h+='<table><thead><tr><th>#</th><th>Plan</th><th>Inicio</th><th>Fin</th><th>Estado</th></tr></thead><tbody>';
            (d.membresias||[]).forEach(function(m){
                var bc=m.estadoMembresia==='Activa'?'badge-success':m.estadoMembresia==='Vencida'?'badge-warning':'badge-gray';
                h+='<tr><td>'+m.idMembresia+'</td><td>'+(m.plan?m.plan.nombrePlan:'N/A')+'</td><td>'+m.fechaInicioMembresia+'</td><td>'+m.fechaFinMembresia+'</td><td><span class="badge '+bc+'">'+m.estadoMembresia+'</span></td></tr>';
            });
            if(!d.membresias||!d.membresias.length) h+='<tr><td colspan="5" class="empty-state">Sin membresias registradas</td></tr>';
            h+='</tbody></table>';

            h+='<h4>Clases Pasadas ('+(d.clasesPasadas||[]).length+')</h4>';
            h+='<table><thead><tr><th>Clase</th><th>Fecha</th><th>Hora</th><th>Estado</th></tr></thead><tbody>';
            (d.clasesPasadas||[]).forEach(function(r){
                var horario=r.clase?r.clase.horaInicio+' - '+r.clase.horaFin:'';
                h+='<tr><td>'+(r.clase&&r.clase.actividad?r.clase.actividad.nombreActividad:'N/A')+'</td><td>'+r.fechaReserva+'</td><td>'+horario+'</td><td><span class="badge badge-success">Asistido</span></td></tr>';
            });
            if(!d.clasesPasadas||!d.clasesPasadas.length) h+='<tr><td colspan="4" class="empty-state">Sin clases pasadas</td></tr>';
            h+='</tbody></table>';

            h+='<h4>Clases Futuras ('+(d.clasesFuturas||[]).length+')</h4>';
            h+='<table><thead><tr><th>Clase</th><th>Fecha</th><th>Hora</th><th>Estado</th></tr></thead><tbody>';
            (d.clasesFuturas||[]).forEach(function(r){
                var horario=r.clase?r.clase.horaInicio+' - '+r.clase.horaFin:'';
                h+='<tr><td>'+(r.clase&&r.clase.actividad?r.clase.actividad.nombreActividad:'N/A')+'</td><td>'+r.fechaReserva+'</td><td>'+horario+'</td><td><span class="badge badge-blue">'+r.estadoReserva+'</span></td></tr>';
            });
            if(!d.clasesFuturas||!d.clasesFuturas.length) h+='<tr><td colspan="4" class="empty-state">Sin clases futuras</td></tr>';
            h+='</tbody></table>';

            var socioNombre=((d.socio.usuario?d.socio.usuario.nombre1+' '+d.socio.usuario.apellido1:'Socio')||'Socio').replace(/\s+/g,'_').replace(/[^a-zA-Z0-9_]/g,'');
            var fechaHoy=new Date().toISOString().slice(0,10);
            h+='<div class="no-print" style="margin-top:1rem;text-align:right;"><button onclick="imprimirPDFSocio('+carnet+')" class="btn btn-primary">Imprimir PDF Socio</button> <button onclick="descargarPDF(\'detalle-socio\',\'Ficha Socio - Carnet #'+carnet+'\',\''+carnet+'_'+socioNombre+'_'+fechaHoy+'\')" class="btn btn-outline">Descargar PDF</button></div>';
            h+=footerReporteHTML();
            detail.innerHTML=h;
            detail.classList.add('visible');
        }).catch(function(){detail.innerHTML='<div class="empty-state" style="color:#ef4444;">Error al cargar detalle</div>';detail.classList.add('visible');});
}

function imprimirPDFGeneral(){
    var content=stripNoPrint(document.getElementById('contenido-socios').innerHTML);
    content='<h2 style="margin-bottom:1rem;">Reporte General de Socios</h2>'+content;
    imprimirHTML(content,'Reporte General de Socios');
}

function imprimirPDFSocio(carnet){
    if(!carnet) return;
    var content=stripNoPrint(document.getElementById('detalle-socio').innerHTML);
    content='<h2 style="margin-bottom:1rem;">Detalle de Socio - Carnet #'+carnet+'</h2>'+content;
    imprimirHTML(content,'Detalle Socio - '+carnet);
}

function cargarDetalleFinanciero(idCaja){
    var detail=document.getElementById('detalle-financiero');
    detail.innerHTML='<div class="empty-state">Cargando detalle...</div>';
    detail.classList.remove('visible');
    fetch('/reportes/financiero/'+idCaja,{headers:{'Accept':'application/json'}})
        .then(function(r){return r.json();}).then(function(d){
            var empName=d.caja.nombreEmpleado||'Desconocido';
            var h='<div class="socio-header"><div><div class="nombre">Caja #'+d.caja.idCaja+' - '+d.caja.sucursalNombre+'</div><div class="carnet">'+d.caja.fechaApertura+' | Apertura: $'+Number(d.caja.montoApertura).toFixed(2)+' | Cierre real: $'+Number(d.caja.montoCierre||0).toFixed(2)+' | Cierre calculado: $'+Number(d.caja.montoCierreCalculado||0).toFixed(2)+' | Estado: '+d.caja.estadoCaja+'<br><strong>Encargado: '+empName+' (CI: '+d.caja.carnetEmpleado+')</strong></div></div></div>';

            h+='<h4>Membresias Compradas ('+(d.membresias||[]).length+')</h4>';
            h+='<table><thead><tr><th>Recibo</th><th>Socio</th><th>Plan</th><th>Costo</th><th>Inicio</th><th>Fin</th><th>Pago</th></tr></thead><tbody>';
            (d.membresias||[]).forEach(function(m){
                h+='<tr><td>#'+m.idRecibo+'</td><td>'+m.nombreSocio+' ('+m.carnetSocio+')</td><td>'+m.nombrePlan+'</td><td>$'+Number(m.costoPlan).toFixed(2)+'</td><td>'+m.fechaInicioMembresia+'</td><td>'+m.fechaFinMembresia+'</td><td>$'+Number(m.montoTotal).toFixed(2)+'</td></tr>';
            });
            if(!d.membresias||!d.membresias.length) h+='<tr><td colspan="7" class="empty-state">Sin membresias en esta caja</td></tr>';
            h+='</tbody></table>';
            h+='<div style="text-align:right;margin-bottom:1rem;font-size:0.9rem;"><strong>Total Membresias: $'+Number(d.totalMembresias||0).toFixed(2)+'</strong></div>';

            h+='<h4>Salidas de Caja ('+(d.salidas||[]).length+')</h4>';
            h+='<table><thead><tr><th>ID</th><th>Descripcion</th><th>Costo</th><th>Fecha</th></tr></thead><tbody>';
            (d.salidas||[]).forEach(function(s){
                h+='<tr><td>'+s.idSalida+'</td><td>'+s.descripcion+'</td><td>$'+Number(s.costo).toFixed(2)+'</td><td>'+s.fechaA+'</td></tr>';
            });
            if(!d.salidas||!d.salidas.length) h+='<tr><td colspan="4" class="empty-state">Sin salidas en esta caja</td></tr>';
            h+='</tbody></table>';
            h+='<div style="text-align:right;font-size:0.9rem;"><strong>Total Salidas: $'+Number(d.totalSalidas||0).toFixed(2)+'</strong></div>';

            var sucursalNombreSanitized=(d.caja.sucursalNombre||'Sucursal').replace(/\s+/g,'_').replace(/[^a-zA-Z0-9_]/g,'');
            var fechaHoy=new Date().toISOString().slice(0,10);
            h+='<div class="no-print" style="margin-top:1rem;text-align:right;"><button onclick="imprimirPDFFinancieroDetalle('+idCaja+')" class="btn btn-primary">Imprimir PDF Caja</button> <button onclick="descargarPDF(\'detalle-financiero\',\'Detalle de Caja #'+idCaja+'\',\''+idCaja+'_'+sucursalNombreSanitized+'_'+fechaHoy+'\')" class="btn btn-outline">Descargar PDF</button></div>';
            h+=footerReporteHTML();
            detail.innerHTML=h;
            detail.classList.add('visible');
        }).catch(function(){detail.innerHTML='<div class="empty-state" style="color:#ef4444;">Error al cargar detalle</div>';detail.classList.add('visible');});
}

function imprimirPDFFinancieroGeneral(){
    var content=stripNoPrint(document.getElementById('contenido-financiero').innerHTML);
    content='<h2 style="margin-bottom:1rem;">Reporte Financiero</h2>'+content;
    imprimirHTML(content,'Reporte Financiero');
}

function imprimirPDFFinancieroDetalle(idCaja){
    if(!idCaja) return;
    var content=stripNoPrint(document.getElementById('detalle-financiero').innerHTML);
    content='<h2 style="margin-bottom:1rem;">Detalle de Caja #'+idCaja+'</h2>'+content;
    imprimirHTML(content,'Detalle Caja - '+idCaja);
}

var _cargandoDetalleClase=0;
function cargarDetalleClase(idClase){
    if(_cargandoDetalleClase) return;
    _cargandoDetalleClase=1;
    var detail=document.getElementById('detalle-clases');
    detail.innerHTML='<div class="empty-state">Cargando detalle...</div>';
    detail.classList.remove('visible');
    fetch('/reportes/clases/'+idClase,{headers:{'Accept':'application/json'}})
        .then(function(r){
            if(!r.ok) throw new Error('HTTP '+r.status);
            return r.text();
        }).then(function(txt){
            var d=JSON.parse(txt);
            if(!d||!d.clase) throw new Error('Respuesta sin clase');
            var c=d.clase;
            var hi=c.horaInicio?c.horaInicio.substring(0,5):'--:--';
            var hf=c.horaFin?c.horaFin.substring(0,5):'--:--';
            var h='<div class="socio-header"><div><div class="nombre">'+c.nombreActividad+'</div><div class="carnet">'+c.fecha+' | '+hi+' - '+hf+' | Instructor: '+c.instructor+'</div></div><div><span class="badge badge-blue">Capacidad: '+c.capacidad+'</span> <span class="badge badge-success">Reservados: '+c.totalReservas+'</span> <span class="badge badge-amber">Asistieron: '+c.asistieron+'</span></div></div>';

            h+='<h4>Socios que Reservaron ('+(d.socios||[]).length+')</h4>';
            h+='<table><thead><tr><th>Socio</th><th>Carnet</th><th>Reserva</th><th>Estado</th></tr></thead><tbody>';
            (d.socios||[]).forEach(function(s){
                var bc=s.estadoReserva==='Asistido'?'badge-success':s.estadoReserva==='Reservado'?'badge-blue':s.estadoReserva==='Cancelado'?'badge-gray':'badge-red';
                h+='<tr><td><strong>'+s.nombreSocio+'</strong></td><td>'+s.carnetSocio+'</td><td>'+s.fechaReserva+'</td><td><span class="badge '+bc+'">'+s.estadoReserva+'</span></td></tr>';
            });
            if(!d.socios||!d.socios.length) h+='<tr><td colspan="4" class="empty-state">Sin reservas</td></tr>';
            h+='</tbody></table>';

            var claseNombre=String(c.nombreActividad||'Clase').replace(/\s+/g,'_').replace(/[^a-zA-Z0-9_]/g,'');
            var ciInstructor=String(c.carnetInstructor||'sin_ci').replace(/\s+/g,'_').replace(/[^a-zA-Z0-9_]/g,'');
            var fechaHoy=new Date().toISOString().slice(0,10);
            h+='<div class="no-print" style="margin-top:1rem;text-align:right;"><button onclick="descargarPDF(\'detalle-clases\',\'Detalle de Clase #'+idClase+'\',\''+claseNombre+'_'+ciInstructor+'_'+fechaHoy+'\')" class="btn btn-outline">Descargar PDF</button></div>';
            h+=footerReporteHTML();
            detail.innerHTML=h;
            detail.classList.add('visible');
            _cargandoDetalleClase=0;
        }).catch(function(e){
            console.error('cargarDetalleClase error:',e);
            detail.innerHTML='<div class="empty-state" style="color:#ef4444;">Error: '+(e.message||'desconocido')+'</div>';
            detail.classList.add('visible');
            _cargandoDetalleClase=0;
        });
}

function imprimirPDFClasesGeneral(){
    var content=stripNoPrint(document.getElementById('contenido-clases').innerHTML);
    content='<h2 style="margin-bottom:1rem;">Reporte de Clases Grupales</h2>'+content;
    imprimirHTML(content,'Reporte de Clases');
}

function imprimirPDFClasesDetalle(idClase){
    if(!idClase) return;
    var content=stripNoPrint(document.getElementById('detalle-clases').innerHTML);
    content='<h2 style="margin-bottom:1rem;">Detalle de Clase</h2>'+content;
    imprimirHTML(content,'Detalle Clase');
}

document.addEventListener('DOMContentLoaded',function(){
    document.getElementById('contenido-clases').addEventListener('click',function(e){
        var row=e.target.closest('.row-clickable');
        if(row){
            var idClase=row.getAttribute('data-idclase');
            if(idClase) cargarDetalleClase(idClase);
        }
    });
    setTimeout(function(){
        var btn=document.querySelector('.tab-btn.active');if(btn)btn.click();
        cargarAdminEquipos();
    },100);
});
</script>
@endsection
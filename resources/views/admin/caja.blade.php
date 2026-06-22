@extends('layouts.admin')
@section('title', 'Caja')
@section('content')
<div class="card" style="padding: 24px;">
    <h2 style="margin-bottom: 1rem; color: #0f172a;">Módulo de Caja - Apertura, Cobros, Cierre y Movimientos</h2>

    <div id="cajaStatus" style="margin-bottom: 1.5rem;">
        <strong>Estado actual:</strong>
        <span id="statusBadge" style="padding: 0.35rem 0.75rem; border-radius: 999px; background: #f8fafc; color: #334155;">Cargando...</span>
    </div>

    <div id="formApertura" style="margin-bottom: 1.5rem; display: none; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem;">
        <h3 style="margin-bottom: 1rem; color: #1e293b;">Abrir caja</h3>
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;">
            <div>
                <label>Sucursal</label>
                <select id="idSucursal" class="form-control"></select>
            </div>
            <div>
                <label>Monto apertura (Bs)</label>
                <input id="montoApertura" type="number" step="0.01" class="form-control" min="0">
            </div>
            <div style="align-self: end;">
                <button id="btnAbrir" class="btn btn-primary" style="width: 100%;">Abrir Caja</button>
            </div>
        </div>
    </div>

    <div id="cajaAbiertaPanel" style="margin-bottom: 1.5rem; display: none; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem;">
        <h3 style="margin-bottom: 1rem; color: #1e293b;">Caja Abierta</h3>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div><strong>ID Caja:</strong> <span id="cajaId">-</span></div>
            <div><strong>Sucursal:</strong> <span id="cajaSucursal">-</span></div>
            <div><strong>Apertura:</strong> <span id="cajaFecha">-</span> <span id="cajaHora">-</span></div>
            <div><strong>Monto Apertura:</strong> <span id="cajaMontoApertura">-</span></div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; align-items: end;">
            <div>
                <label>Monto cierre (Bs)</label>
                <input id="montoCierre" type="number" step="0.01" class="form-control" min="0">
            </div>
            <div>
                <label>Monto cierre calculado (Bs)</label>
                <input id="montoCierreCalculado" type="number" step="0.01" class="form-control" min="0">
            </div>
            <div>
                <button id="btnCerrar" class="btn btn-danger" style="width: 100%;">Cerrar Caja</button>
            </div>
        </div>
    </div>

    <div class="card" style="padding: 1rem; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; border-radius: 12px;">
        <h3 style="margin-bottom: 1rem; color: #1e293b;">Registrar recibo</h3>
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem;">
            <div>
                <label>Número de recibo</label>
                <input id="nroRecibo" type="text" class="form-control" placeholder="REC-000001">
            </div>
            <div>
                <label>Fecha de pago</label>
                <input id="fechaPago" type="date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            <div>
                <label>Monto total (Bs)</label>
                <input id="montoTotal" type="number" step="0.01" class="form-control" min="0">
            </div>
            <div>
                <label>Método de pago</label>
                <select id="idMetodoPago" class="form-control"></select>
            </div>
            <div>
                <label>Monto con método (Bs)</label>
                <input id="montoMetodo" type="number" step="0.01" class="form-control" min="0">
            </div>
            <div style="align-self: end;">
                <button id="btnRegistrarRecibo" class="btn btn-success" style="width: 100%;">Registrar Recibo</button>
            </div>
            <div style="grid-column: span 3;">
                <label>Membresía</label>
                <select id="idMembresia" class="form-control"></select>
            </div>
        </div>
    </div>

    <div class="card" style="padding: 1rem; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; border-radius: 12px;">
        <h3 style="margin-bottom: 1rem; color: #1e293b;">Movimientos de caja</h3>
        <div style="overflow-x:auto;">
            <table class="table" style="width:100%; border-collapse: collapse;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="padding: 0.75rem; text-align:left;"># Recibo</th>
                        <th style="padding: 0.75rem; text-align:left;">Fecha</th>
                        <th style="padding: 0.75rem; text-align:left;">Socio</th>
                        <th style="padding: 0.75rem; text-align:left;">Monto</th>
                        <th style="padding: 0.75rem; text-align:left;">Método</th>
                        <th style="padding: 0.75rem; text-align:left;">Cajero</th>
                        <th style="padding: 0.75rem; text-align:left;">Acción</th>
                    </tr>
                </thead>
                <tbody id="movimientosBody">
                    <tr><td colspan="7" style="padding: 1rem; color: #64748b; text-align:center;">No hay movimientos cargados.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="reciboPreview" class="card" style="padding: 1rem; display: none; border: 1px solid #e2e8f0; border-radius: 12px;">
        <h3 style="margin-bottom: 1rem; color: #1e293b;">Vista previa del recibo</h3>
        <div id="reciboHtml" style="background:#ffffff; padding:1rem; border:1px solid #e2e8f0; border-radius:8px;"></div>
        <button id="btnImprimir" class="btn btn-primary" style="margin-top:1rem;">Imprimir Recibo</button>
    </div>
</div>

<script>
    const cajaEstadoUrl = '{{ route('admin.caja.estado') }}';
    const cajaAbrirUrl = '{{ route('admin.caja.abrir') }}';
    const cajaMovimientosUrl = '{{ route('admin.caja.movimientos') }}';
    const cajaReciboUrl = '{{ route('admin.caja.recibo') }}';
    const cajaCerrarBase = '{{ url('/admin/caja') }}';
    const cajaMostrarReciboBase = '{{ url('/admin/caja/recibo') }}';

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token,
    };
    let cajaAbierta = null;
    let ultimoRecibo = null;

    const setStatus = (text, color, background) => {
        const badge = document.getElementById('statusBadge');
        badge.textContent = text;
        badge.style.color = color;
        badge.style.background = background;
    };

    const cargarSelects = () => {
        const sucursalSelect = document.getElementById('idSucursal');
        const metodoSelect = document.getElementById('idMetodoPago');
        const membresiaSelect = document.getElementById('idMembresia');
        sucursalSelect.innerHTML = '<option value="">Seleccione sucursal</option>';
        metodoSelect.innerHTML = '<option value="">Seleccione método</option>';
        membresiaSelect.innerHTML = '<option value="">Seleccione membresía</option>';

        const sucursales = @json($sucursales);
        const metodosPago = @json($metodosPago);
        const membresias = @json($membresias).filter(m => m.estadoMembresia === 'Activa');

        sucursales.forEach(s => {
            const option = document.createElement('option');
            option.value = s.idSucursal;
            option.textContent = s.nombre;
            sucursalSelect.appendChild(option);
        });

        metodosPago.forEach(m => {
            const option = document.createElement('option');
            option.value = m.idMetodoPago;
            option.textContent = m.nombreMetodoPago;
            metodoSelect.appendChild(option);
        });

        membresias.forEach(m => {
            const option = document.createElement('option');
            option.value = m.idMembresia;
            option.textContent = `#${m.carnetSocio} - ${m.estadoMembresia}`;
            membresiaSelect.appendChild(option);
        });
    };

    const mostrarApertura = (caja) => {
        document.getElementById('cajaAbiertaPanel').style.display = 'block';
        document.getElementById('formApertura').style.display = 'none';
        document.getElementById('cajaId').textContent = caja.idCaja;
        document.getElementById('cajaSucursal').textContent = caja.idSucursal || '-';
        document.getElementById('cajaFecha').textContent = caja.fechaApertura;
        document.getElementById('cajaHora').textContent = caja.horaApertura;
        document.getElementById('cajaMontoApertura').textContent = parseFloat(caja.montoApertura).toFixed(2);
        cajaAbierta = caja;
    };

    const mostrarCierre = () => {
        document.getElementById('cajaAbiertaPanel').style.display = 'none';
        document.getElementById('formApertura').style.display = 'block';
        cajaAbierta = null;
    };

    const actualizarEstado = async () => {
        try {
            const res = await fetch(cajaEstadoUrl);
            const data = await res.json();
            if (data.open) {
                setStatus('Caja abierta', '#0f5132', '#d1e7dd');
                mostrarApertura(data.caja);
            } else {
                setStatus('Caja cerrada / no abierta', '#713f12', '#fff3cd');
                mostrarCierre();
            }
            cargarMovimientos();
        } catch (e) {
            setStatus('Error al cargar estado', '#842029', '#f8d7da');
        }
    };

    const cargarMovimientos = async () => {
        try {
            const res = await fetch(cajaMovimientosUrl);
            const data = await res.json();
            const body = document.getElementById('movimientosBody');
            if (!data.movimientos || data.movimientos.length === 0) {
                body.innerHTML = '<tr><td colspan="7" style="padding: 1rem; color: #64748b; text-align:center;">No hay movimientos de caja disponibles.</td></tr>';
                return;
            }
            body.innerHTML = data.movimientos.map(m => `
                <tr>
                    <td style="padding: 0.75rem;">${m.nroRecibo}</td>
                    <td style="padding: 0.75rem;">${new Date(m.fechaPago).toLocaleDateString('es-ES')}</td>
                    <td style="padding: 0.75rem;">${m.carnetSocio || '-'}</td>
                    <td style="padding: 0.75rem;">Bs. ${parseFloat(m.montoTotal).toFixed(2)}</td>
                    <td style="padding: 0.75rem;">${m.nombreMetodoPago}</td>
                    <td style="padding: 0.75rem;">${m.nombre1 ? `${m.nombre1} ${m.apellido1}` : '-'}</td>
                    <td style="padding: 0.75rem;"><button class="btn btn-outline" onclick="verRecibo(${m.idRecibo})">Ver</button></td>
                </tr>
            `).join('');
        } catch (e) {
            document.getElementById('movimientosBody').innerHTML = '<tr><td colspan="7" style="padding: 1rem; color: #ef4444; text-align:center;">Error al cargar movimientos.</td></tr>';
        }
    };

    const crearRecibo = async () => {
        if (!cajaAbierta) {
            alert('Debe abrir caja antes de registrar recibos.');
            return;
        }
        const data = {
            idCaja: cajaAbierta.idCaja,
            idMembresia: document.getElementById('idMembresia').value,
            idMetodoPago: document.getElementById('idMetodoPago').value,
            nroRecibo: document.getElementById('nroRecibo').value,
            montoTotal: document.getElementById('montoTotal').value,
            montoMetodo: document.getElementById('montoMetodo').value,
            fechaPago: document.getElementById('fechaPago').value,
        };

        const res = await fetch(cajaReciboUrl, {
            method: 'POST',
            headers: defaultHeaders,
            body: JSON.stringify(data),
        });

        const result = await res.json();
        if (!result.success) {
            alert(result.message || 'Error al registrar recibo.');
            return;
        }
        alert(result.message);
        cargarMovimientos();
        if (result.idRecibo) {
            verRecibo(result.idRecibo);
        }
    };

    const abrirCaja = async () => {
        const data = {
            idSucursal: document.getElementById('idSucursal').value,
            montoApertura: document.getElementById('montoApertura').value,
        };
        const res = await fetch(cajaAbrirUrl, {
            method: 'POST',
            headers: defaultHeaders,
            body: JSON.stringify(data),
        });
        const result = await res.json();
        if (!result.success) {
            alert(result.message || 'Error al abrir la caja.');
            return;
        }
        alert(result.message);
        actualizarEstado();
    };

    const cerrarCaja = async () => {
        if (!cajaAbierta) return;
        const data = {
            montoCierre: document.getElementById('montoCierre').value,
            montoCierreCalculado: document.getElementById('montoCierreCalculado').value,
        };
        const url = `${cajaCerrarBase}/${cajaAbierta.idCaja}/cerrar`;
        const res = await fetch(url, {
            method: 'POST',
            headers: defaultHeaders,
            body: JSON.stringify(data),
        });
        const result = await res.json();
        if (!result.success) {
            alert(result.message || 'Error al cerrar la caja.');
            return;
        }
        alert(result.message);
        actualizarEstado();
    };

    const verRecibo = async (id) => {
        const url = `${cajaMostrarReciboBase}/${id}`;
        const res = await fetch(url);
        const data = await res.json();
        if (!data.success) {
            alert(data.message || 'Recibo no encontrado.');
            return;
        }
        ultimoRecibo = data.recibo;
        document.getElementById('reciboPreview').style.display = 'block';
        document.getElementById('reciboHtml').innerHTML = `
            <div style="font-family:Arial, sans-serif;">
                <h4>Recibo #${data.recibo.nroRecibo}</h4>
                <p><strong>Fecha pago:</strong> ${new Date(data.recibo.fechaPago).toLocaleDateString('es-ES')}</p>
                <p><strong>Sucursal:</strong> ${data.recibo.sucursal}</p>
                <p><strong>Cajero:</strong> ${data.recibo.nombre1 ? `${data.recibo.nombre1} ${data.recibo.apellido1}` : '-'}</p>
                <p><strong>Socio:</strong> ${data.recibo.carnetSocio || '-'}</p>
                <p><strong>Método de pago:</strong> ${data.recibo.nombreMetodoPago || '-'}</p>
                <p><strong>Monto total:</strong> Bs. ${parseFloat(data.recibo.montoTotal).toFixed(2)}</p>
                <p><strong>Monto método:</strong> Bs. ${parseFloat(data.recibo.montoMetodo).toFixed(2)}</p>
                <p><strong>Estado:</strong> ${data.recibo.estadoRecibo}</p>
            </div>
        `;
    };

    const imprimirRecibo = () => {
        if (!ultimoRecibo) return;
        const contenido = `
            <html>
            <head><title>Recibo #${ultimoRecibo.nroRecibo}</title></head>
            <body>${document.getElementById('reciboHtml').innerHTML}</body>
            </html>
        `;
        const ventana = window.open('', '_blank');
        ventana.document.write(contenido);
        ventana.document.close();
        ventana.print();
    };

    document.getElementById('btnAbrir').addEventListener('click', abrirCaja);
    document.getElementById('btnCerrar').addEventListener('click', cerrarCaja);
    document.getElementById('btnRegistrarRecibo').addEventListener('click', crearRecibo);
    document.getElementById('btnImprimir').addEventListener('click', imprimirRecibo);

    cargarSelects();
    actualizarEstado();
</script>
@endsection

@extends('layouts.admin')
@section('title', 'Caja')
@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<style>
.caja-info-grid { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:1rem; margin-bottom:1.5rem; }
.caja-info-card { background:var(--bg,#f8fafc); padding:0.75rem 1rem; border-radius:0.5rem; }
.caja-info-card .lbl { font-size:0.75rem; color:#64748b; font-weight:600; text-transform:uppercase; }
.caja-info-card .val { font-size:1.1rem; font-weight:700; color:#0f172a; }
.metodo-row { display:grid; grid-template-columns:1fr 1fr auto; gap:0.75rem; align-items:end; margin-bottom:0.5rem; }
</style>

@verbatim
<div id="appCaja">
    <div class="card" style="padding:24px;">
        <h2 style="margin-bottom:1rem; color:#0f172a;">Modulo de Caja</h2>

        <div style="margin-bottom:1.5rem;">
            <strong>Estado:</strong>
            <span id="statusBadge" :style="estiloStatus">{{ textoStatus }}</span>
        </div>

        <!-- Form Apertura -->
        <div v-if="!cajaAbierta" style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem; margin-bottom:1.5rem;">
            <h3 style="margin-bottom:1rem; color:#1e293b;">Abrir Caja</h3>
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; align-items:end;">
                <div>
                    <label>Sucursal (automatica)</label>
                    <input class="form-control" :value="sucursalNombre" readonly style="background:#f1f5f9;">
                </div>
                <div>
                    <label>Monto apertura (Bs)</label>
                    <input v-model="montoApertura" type="number" step="0.01" class="form-control" min="0" placeholder="0.00">
                </div>
                <div>
                    <button @click="abrirCaja" class="btn btn-primary" style="width:100%;" :disabled="!montoApertura">Abrir Caja</button>
                </div>
            </div>
        </div>

        <!-- Panel Caja Abierta -->
        <div v-if="cajaAbierta" style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem; margin-bottom:1.5rem;">
            <h3 style="margin-bottom:1rem; color:#1e293b;">Caja Abierta</h3>
            <div class="caja-info-grid">
                <div class="caja-info-card"><div class="lbl">ID Caja</div><div class="val">{{ cajaAbierta.idCaja }}</div></div>
                <div class="caja-info-card"><div class="lbl">Sucursal</div><div class="val">{{ sucursalNombre }}</div></div>
                <div class="caja-info-card"><div class="lbl">Apertura</div><div class="val">{{ cajaAbierta.fechaApertura }} {{ cajaAbierta.horaApertura }}</div></div>
                <div class="caja-info-card"><div class="lbl">Monto Apertura</div><div class="val">Bs. {{ formatNum(cajaAbierta.montoApertura) }}</div></div>
            </div>

            <div v-if="cajaAbierta.estadoCaja === 'Abierta'" style="display:flex; gap:1rem; align-items:end;">
                <div style="flex:1;">
                    <label>Monto cierre real (Bs)</label>
                    <input v-model="montoCierre" type="number" step="0.01" class="form-control" min="0">
                </div>
                <div style="flex:1;">
                    <label>Calculado automaticamente</label>
                    <input class="form-control" :value="formatNum(montoCierreCalculado)" readonly style="background:#f1f5f9;">
                </div>
                <div>
                    <button @click="cerrarCaja" class="btn btn-danger" style="width:100%;" :disabled="!montoCierre">Cerrar Caja</button>
                </div>
            </div>
            <div v-if="cajaAbierta.estadoCaja === 'Cerrada'" style="padding:0.5rem 0; color:#64748b;">
                Caja cerrada. Monto cierre: Bs. {{ formatNum(cajaAbierta.montoCierre) }} | Calculado: Bs. {{ formatNum(cajaAbierta.montoCierreCalculado) }} | Diferencia: Bs. {{ formatNum(cajaAbierta.diferenciaArqueo) }}
            </div>
        </div>

        <!-- Registrar Recibo -->
        <div v-if="cajaAbierta && cajaAbierta.estadoCaja === 'Abierta'" style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem; margin-bottom:1.5rem;">
            <h3 style="margin-bottom:1rem; color:#1e293b;">Registrar Recibo</h3>

            <div style="display:grid; grid-template-columns:2fr 3fr; gap:1rem; margin-bottom:1rem; align-items:end;">
                <div>
                    <label>Buscar Socio por CI</label>
                    <div style="display:flex; gap:0.5rem;">
                        <input v-model="socioCarnet" @keyup.enter="buscarSocio" type="text" class="form-control" placeholder="Ingrese CI...">
                        <button @click="buscarSocio" class="btn btn-sm btn-primary">Buscar</button>
                    </div>
                </div>
                <div v-if="socioInfo">
                    <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:0.5rem; padding:0.5rem 1rem; display:flex; align-items:center; gap:1rem;">
                        <strong>{{ socioInfo.nombre1 }} {{ socioInfo.nombre2 ? socioInfo.nombre2+' ' : '' }}{{ socioInfo.apellido1 }} {{ socioInfo.apellido2 ? socioInfo.apellido2 : '' }}</strong>
                        <span class="badge" :class="socioInfo.estadoSocio === 'Activo' ? 'badge-success' : 'badge-warning'">{{ socioInfo.estadoSocio }}</span>
                        <small style="color:#64748b;">CI: {{ socioInfo.carnetSocio }}</small>
                    </div>
                </div>
            </div>

            <div v-if="socioInfo" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1rem; align-items:end;">
                <div>
                    <label>Plan</label>
                    <select v-model="idPlan" @change="onPlanChange" class="form-control">
                        <option value="">Seleccione plan...</option>
                        <option v-for="p in planes" :key="p.idPlan" :value="p.idPlan">{{ p.nombrePlan }} - Bs. {{ formatNum(p.costoPlan) }} ({{ p.duracionDias }} dias)</option>
                    </select>
                </div>
                <div>
                    <label>Monto total (Bs)</label>
                    <input v-model="montoTotal" type="number" step="0.01" class="form-control" readonly style="background:#f1f5f9;">
                </div>
            </div>

            <!-- Metodos de Pago -->
            <div v-if="socioInfo && idPlan">
                <label>Metodos de Pago <small style="color:#64748b;">(suma debe ser Bs. {{ formatNum(parseFloat(montoTotal||0)) }})</small></label>
                <div v-for="(m, i) in metodosPagoArr" :key="i" class="metodo-row">
                    <select v-model="m.idMetodoPago" class="form-control">
                        <option value="">Seleccione metodo...</option>
                        <option v-for="mp in metodosPago" :key="mp.idMetodoPago" :value="mp.idMetodoPago">{{ mp.nombreMetodoPago }}</option>
                    </select>
                    <input v-model="m.monto" type="number" step="0.01" class="form-control" min="0" placeholder="Monto">
                    <button @click="quitarMetodo(i)" class="btn btn-sm btn-danger" style="white-space:nowrap;">X</button>
                </div>
                <div style="display:flex; gap:0.75rem; margin-top:0.5rem; align-items:center;">
                    <button @click="agregarMetodo" class="btn btn-sm btn-outline">+ Agregar metodo</button>
                    <span v-if="diferenciaMetodos > 0.01" style="color:#dc2626; font-size:0.85rem; font-weight:600;">
                        Diferencia: Bs. {{ formatNum(diferenciaMetodos) }}
                    </span>
                    <span v-else style="color:#059669; font-size:0.85rem; font-weight:600;">
                        Montos correctos
                    </span>
                </div>
                <button @click="registrarRecibo" class="btn btn-success" style="margin-top:1rem;" :disabled="!puedeRegistrar">
                    Registrar Recibo
                </button>
            </div>
        </div>

        <!-- Registrar Salida -->
        <div v-if="cajaAbierta && cajaAbierta.estadoCaja === 'Abierta'" style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem; margin-bottom:1.5rem;">
            <h3 style="margin-bottom:1rem; color:#1e293b;">Registrar Salida (Egreso)</h3>
            <div style="display:grid; grid-template-columns:2fr 1fr auto; gap:1rem; align-items:end;">
                <div>
                    <label>Descripcion</label>
                    <input v-model="descripcionSalida" type="text" class="form-control" placeholder="Ej: Compra de insumos...">
                </div>
                <div>
                    <label>Costo (Bs)</label>
                    <input v-model="costosalida" type="number" step="0.01" class="form-control" min="0.01" placeholder="0.00">
                </div>
                <div>
                    <button @click="registrarSalida" class="btn btn-warning" style="width:100%;" :disabled="!descripcionSalida || !costosalida || costosalida <= 0">Registrar Salida</button>
                </div>
            </div>
            <div v-if="salidas.length > 0" style="margin-top:1rem;">
                <table class="table" style="width:100%;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th style="padding:0.5rem; text-align:left;">Descripcion</th>
                            <th style="padding:0.5rem; text-align:left;">Costo</th>
                            <th style="padding:0.5rem; text-align:left;">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in salidas" :key="s.idSalida">
                            <td style="padding:0.5rem;">{{ s.descripcion }}</td>
                            <td style="padding:0.5rem;">Bs. {{ formatNum(s.costo) }}</td>
                            <td style="padding:0.5rem;">{{ formatFecha(s.fechaA) }}</td>
                        </tr>
                    </tbody>
                </table>
                <div style="text-align:right; font-weight:700; padding:0.5rem; border-top:2px solid #e2e8f0;">
                    Total salidas: Bs. {{ formatNum(totalSalidasHoy) }}
                </div>
            </div>
        </div>

        <!-- Movimientos -->
        <div style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem;">
            <h3 style="margin-bottom:1rem; color:#1e293b;">Movimientos de Caja</h3>
            <div style="overflow-x:auto;">
                <table class="table" style="width:100%; border-collapse:collapse;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th style="padding:0.75rem; text-align:left;"># Recibo</th>
                            <th style="padding:0.75rem; text-align:left;">Fecha</th>
                            <th style="padding:0.75rem; text-align:left;">Socio</th>
                            <th style="padding:0.75rem; text-align:left;">Monto</th>
                            <th style="padding:0.75rem; text-align:left;">Metodo</th>
                            <th style="padding:0.75rem; text-align:left;">Cajero</th>
                            <th style="padding:0.75rem; text-align:left;">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="movimientos.length === 0">
                            <td colspan="7" style="padding:1rem; color:#64748b; text-align:center;">No hay movimientos.</td>
                        </tr>
                        <tr v-for="m in movimientos" :key="m.idRecibo">
                            <td style="padding:0.75rem;">{{ m.idRecibo }}</td>
                            <td style="padding:0.75rem;">{{ formatFecha(m.fechaPago) }}</td>
                            <td style="padding:0.75rem;">{{ m.carnetSocio }}</td>
                            <td style="padding:0.75rem;">Bs. {{ formatNum(m.montoTotal) }}</td>
                            <td style="padding:0.75rem;">{{ m.metodos_pago }}</td>
                            <td style="padding:0.75rem;">{{ m.nombre1 ? m.nombre1+' '+m.apellido1 : '-' }}</td>
                            <td style="padding:0.75rem;"><button @click="verRecibo(m.idRecibo)" class="btn btn-sm btn-outline">Ver</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Salidas de Caja -->
        <div v-if="cajaAbierta" style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem; margin-top:1.5rem;">
            <h3 style="margin-bottom:1rem; color:#1e293b;">Salidas de Caja</h3>
            <div style="overflow-x:auto;">
                <table class="table" style="width:100%; border-collapse:collapse;">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th style="padding:0.75rem; text-align:left;">Descripcion</th>
                            <th style="padding:0.75rem; text-align:left;">Costo</th>
                            <th style="padding:0.75rem; text-align:left;">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="salidas.length === 0">
                            <td colspan="3" style="padding:1rem; color:#64748b; text-align:center;">No hay salidas registradas.</td>
                        </tr>
                        <tr v-for="s in salidas" :key="s.idSalida">
                            <td style="padding:0.75rem;">{{ s.descripcion }}</td>
                            <td style="padding:0.75rem;">Bs. {{ formatNum(s.costo) }}</td>
                            <td style="padding:0.75rem;">{{ formatFecha(s.fechaA) }}</td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="salidas.length > 0" style="text-align:right; font-weight:700; padding:0.75rem; border-top:2px solid #e2e8f0;">
                    Total salidas: Bs. {{ formatNum(totalSalidasHoy) }}
                </div>
            </div>
        </div>

        <!-- Recibo Preview -->
        <div v-if="reciboPreview" style="border:1px solid #e2e8f0; border-radius:12px; padding:1rem; margin-top:1.5rem;">
            <h3 style="margin-bottom:1rem; color:#1e293b;">Vista previa del Recibo #{{ reciboPreview.idRecibo }}</h3>
            <div style="background:#fff; padding:1rem; border:1px solid #e2e8f0; border-radius:8px;">
                <p><strong>Fecha:</strong> {{ formatFecha(reciboPreview.fechaPago) }}</p>
                <p><strong>Sucursal:</strong> {{ reciboPreview.sucursal }}</p>
                <p><strong>Socio:</strong> {{ reciboPreview.carnetSocio }}</p>
                <p><strong>Monto total:</strong> Bs. {{ formatNum(reciboPreview.montoTotal) }}</p>
                <p><strong>Metodos de pago:</strong></p>
                <ul v-if="reciboMetodos.length">
                    <li v-for="rm in reciboMetodos">{{ rm.nombreMetodoPago }}: Bs. {{ formatNum(rm.monto) }}</li>
                </ul>
                <p><strong>Estado:</strong> {{ reciboPreview.estadoRecibo }}</p>
            </div>
            <button @click="imprimirRecibo" class="btn btn-primary" style="margin-top:1rem;">Imprimir Recibo</button>
        </div>
    </div>
</div>
@endverbatim

<script>
const { createApp, ref, computed, onMounted, watch } = Vue;

createApp({
    setup() {
        const cajaAbierta = ref(null);
        const sucursalNombre = ref('{{ $sucursalNombre }}');
        const montoApertura = ref('');
        const montoCierre = ref('');
        const metodosPago = ref([]);
        const planes = ref([]);
        const movimientos = ref([]);
        const socioCarnet = ref('');
        const socioInfo = ref(null);
        const idPlan = ref('');
        const montoTotal = ref('');
        const metodosPagoArr = ref([]);
        const reciboPreview = ref(null);
        const reciboMetodos = ref([]);
        const totalSalidasHoy = ref(0);
        const salidas = ref([]);
        const descripcionSalida = ref('');
        const costosalida = ref('');

        const textoStatus = computed(() => {
            if (!cajaAbierta.value) return 'Caja cerrada / no abierta';
            if (cajaAbierta.value.estadoCaja === 'Abierta') return 'Caja abierta';
            return 'Caja cerrada hoy';
        });
        const estiloStatus = computed(() => {
            if (!cajaAbierta.value || cajaAbierta.value.estadoCaja === 'Cerrada') return 'padding:0.35rem 0.75rem; border-radius:999px; background:#fff3cd; color:#713f12; font-weight:600;';
            return 'padding:0.35rem 0.75rem; border-radius:999px; background:#d1e7dd; color:#0f5132; font-weight:600;';
        });

        const totalRecibos = computed(() => {
            let t = 0;
            movimientos.value.forEach(m => { t += parseFloat(m.montoTotal || 0); });
            return t;
        });

        const montoCierreCalculado = computed(() => {
            if (!cajaAbierta.value) return 0;
            return parseFloat(cajaAbierta.value.montoApertura || 0) + totalRecibos.value - totalSalidasHoy.value;
        });

        const diferenciaMetodos = computed(() => {
            const totalMetodos = metodosPagoArr.value.reduce((s, m) => s + parseFloat(m.monto || 0), 0);
            return Math.abs(parseFloat(montoTotal.value || 0) - totalMetodos);
        });

        const puedeRegistrar = computed(() => {
            return socioInfo.value && idPlan.value && montoTotal.value > 0
                && metodosPagoArr.value.length > 0
                && diferenciaMetodos.value <= 0.01
                && metodosPagoArr.value.every(m => m.idMetodoPago && m.monto > 0);
        });

        const cargarEstado = async () => {
            try {
                const res = await fetch('{{ route("admin.caja.estado") }}');
                const data = await res.json();
                if (data.caja) {
                    cajaAbierta.value = data.caja;
                } else {
                    cajaAbierta.value = null;
                }
            } catch (e) { console.error(e); }
        };

        const formatNum = (n) => parseFloat(n || 0).toFixed(2);
        const formatFecha = (d) => { if (!d) return '-'; const dt = new Date(d); return dt.toLocaleDateString('es-ES'); };

        const abrirCaja = async () => {
            const res = await fetch('{{ route("admin.caja.abrir") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ montoApertura: montoApertura.value })
            });
            const data = await res.json();
            alert(data.message);
            if (data.success) { montoApertura.value = ''; cargarEstado(); cargarMovimientos(); }
        };

        const cerrarCaja = async () => {
            if (!cajaAbierta.value) return;
            const res = await fetch('{{ url("/admin/caja") }}/' + cajaAbierta.value.idCaja + '/cerrar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ montoCierre: montoCierre.value })
            });
            const data = await res.json();
            alert(data.message);
            if (data.success) { montoCierre.value = ''; cajaAbierta.value = data; cargarEstado(); cargarMovimientos(); }
        };

        const cargarMovimientos = async () => {
            try {
                const res = await fetch('{{ route("admin.caja.movimientos") }}');
                const data = await res.json();
                movimientos.value = data.movimientos || [];
                totalSalidasHoy.value = parseFloat(data.totalSalidasHoy || 0);
                salidas.value = data.salidas || [];
                if (data.caja) cajaAbierta.value = data.caja;
            } catch (e) { movimientos.value = []; }
        };

        const cargarSalidas = async () => {
            try {
                const res = await fetch('{{ route("admin.caja.salidas.listar") }}');
                const data = await res.json();
                salidas.value = data.salidas || [];
                totalSalidasHoy.value = parseFloat(data.totalSalidas || 0);
            } catch (e) { salidas.value = []; }
        };

        const registrarSalida = async () => {
            if (!descripcionSalida.value || !costosalida.value || costosalida.value <= 0) return;
            const res = await fetch('{{ route("admin.caja.salidas.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ descripcion: descripcionSalida.value, costo: costosalida.value })
            });
            const data = await res.json();
            alert(data.message);
            if (data.success) {
                descripcionSalida.value = '';
                costosalida.value = '';
                cargarSalidas();
                cargarMovimientos();
            }
        };

        const buscarSocio = async () => {
            if (!socioCarnet.value) return;
            socioInfo.value = null;
            idPlan.value = '';
            montoTotal.value = '';
            metodosPagoArr.value = [];
            try {
                const res = await fetch('{{ url("/admin/caja/buscar-socio") }}/' + socioCarnet.value);
                if (!res.ok) { alert('Socio no encontrado.'); return; }
                const data = await res.json();
                if (data.success) {
                    socioInfo.value = data.socio;
                    if (data.tieneMembresiaActiva) {
                        alert('El socio ya tiene una membresia activa. No puede comprar otra hasta que venza.');
                        socioInfo.value = null;
                    }
                }
            } catch (e) { alert('Error al buscar socio.'); }
        };

        const cargarPlanes = async () => {
            try {
                const res = await fetch('{{ route("admin.caja.planes") }}');
                planes.value = await res.json();
            } catch (e) { planes.value = []; }
        };

        const onPlanChange = () => {
            const plan = planes.value.find(p => p.idPlan == idPlan.value);
            if (plan) montoTotal.value = plan.costoPlan.toString();
            metodosPagoArr.value = [{ idMetodoPago: '', monto: '' }];
        };

        const agregarMetodo = () => {
            metodosPagoArr.value.push({ idMetodoPago: '', monto: '' });
        };

        const quitarMetodo = (i) => {
            if (metodosPagoArr.value.length > 1) metodosPagoArr.value.splice(i, 1);
        };

        const registrarRecibo = async () => {
            if (!puedeRegistrar.value) return;
            const payload = {
                carnetSocio: socioInfo.value.carnetSocio,
                idPlan: idPlan.value,
                montoTotal: montoTotal.value,
                metodos: metodosPagoArr.value.map(m => ({ idMetodoPago: m.idMetodoPago, monto: m.monto }))
            };
            try {
                const res = await fetch('{{ route("admin.caja.recibo") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    if (data.errors) { const msgs = Object.values(data.errors).flat().join('\n'); alert(msgs); }
                    else alert(data.message || 'Error al registrar recibo.');
                    return;
                }
                alert(data.message);
                socioInfo.value = null;
                socioCarnet.value = '';
                idPlan.value = '';
                montoTotal.value = '';
                metodosPagoArr.value = [];
                cargarMovimientos();
                if (data.idRecibo) verRecibo(data.idRecibo);
            } catch (e) { alert('Error: ' + e.message); }
        };

        const verRecibo = async (id) => {
            try {
                const res = await fetch('{{ url("/admin/caja/recibo") }}/' + id);
                const data = await res.json();
                if (data.success) {
                    reciboPreview.value = data.recibo;
                    reciboMetodos.value = data.metodos || [];
                }
            } catch (e) {}
        };

        const imprimirRecibo = () => {
            if (!reciboPreview.value) return;
            const contenido = `
                <html><head><title>Recibo #${reciboPreview.value.idRecibo}</title></head>
                <body>
                    <h2>Recibo #${reciboPreview.value.idRecibo}</h2>
                    <p><strong>Fecha:</strong> ${formatFecha(reciboPreview.value.fechaPago)}</p>
                    <p><strong>Sucursal:</strong> ${reciboPreview.value.sucursal}</p>
                    <p><strong>Socio CI:</strong> ${reciboPreview.value.carnetSocio}</p>
                    <p><strong>Monto total:</strong> Bs. ${formatNum(reciboPreview.value.montoTotal)}</p>
                    <p><strong>Metodos:</strong></p>
                    <ul>${reciboMetodos.value.map(rm => `<li>${rm.nombreMetodoPago}: Bs. ${formatNum(rm.monto)}</li>`).join('')}</ul>
                    <p><strong>Estado:</strong> ${reciboPreview.value.estadoRecibo}</p>
                </body></html>`;
            const ventana = window.open('', '_blank');
            ventana.document.write(contenido);
            ventana.document.close();
            ventana.print();
        };

        onMounted(() => {
            const metas = @json($metodosPago);
            metodosPago.value = metas;
            cargarEstado();
            cargarMovimientos();
            cargarPlanes();
            cargarSalidas();
        });

        return {
            cajaAbierta, sucursalNombre, montoApertura, montoCierre, metodosPago, planes, movimientos,
            socioCarnet, socioInfo, idPlan, montoTotal, metodosPagoArr,
            reciboPreview, reciboMetodos,
            textoStatus, estiloStatus, totalRecibos, totalSalidasHoy, montoCierreCalculado, diferenciaMetodos, puedeRegistrar,
            salidas, descripcionSalida, costosalida,
            formatNum, formatFecha,
            abrirCaja, cerrarCaja, cargarMovimientos, cargarSalidas, registrarSalida, buscarSocio, onPlanChange,
            agregarMetodo, quitarMetodo, registrarRecibo, verRecibo, imprimirRecibo
        };
    }
}).mount('#appCaja');
</script>
@endsection

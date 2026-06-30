@extends('layouts.admin')
@section('title', 'Esquemas de Sueldo')
@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<style>
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; z-index:1000; }
.modal-content { background:#fff; border-radius:0.75rem; padding:1.5rem; width:100%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
.modal-content h3 { margin-bottom:1rem; font-size:1.1rem; color:#0f172a; }
.modal-content .form-group { margin-bottom:1rem; }
.modal-content .form-group label { display:block; font-size:0.85rem; font-weight:600; color:#374151; margin-bottom:0.3rem; }
.modal-content .modal-actions { display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.5rem; }
</style>

<div id="appEsquemas">
    <div class="card" style="padding:1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <h2 style="color:#0f172a;">Esquemas de Sueldo</h2>
            <button @click="abrirModal()" class="btn btn-primary">Nuevo Esquema</button>
        </div>

        <div v-if="mensaje" style="padding:0.75rem; border-radius:0.5rem; margin-bottom:1rem; font-weight:500;" :style="{background: mensajeTipo === 'success' ? '#dcfce7' : '#fee2e2', color: mensajeTipo === 'success' ? '#166534' : '#991b1b'}">
            @{{ mensaje }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Modalidad</th>
                    <th>Monto Base</th>
                    <th>Tarifa Hora/Clase</th>
                    <th style="text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="e in esquemas" :key="e.idEsquemaSueldo">
                    <td><strong>@{{ e.nombre1 }} @{{ e.apellido1 }}</strong> <span style="color:#94a3b8;font-size:0.8rem;">(@{{ e.carnetEmpleado }})</span></td>
                    <td><span class="badge badge-blue">@{{ e.modalidadPago }}</span></td>
                    <td>Bs. @{{ Number(e.montoBase).toFixed(2) }}</td>
                    <td>@{{ e.tarifaHoraOClase > 0 ? 'Bs. ' + e.tarifaHoraOClase + '/h' : '--' }}</td>
                    <td style="text-align:center;">
                        <button @click="abrirModal(e)" class="btn btn-sm" style="background:#3b82f6;color:#fff;margin-right:5px;">Editar</button>
                        <button @click="eliminar(e.idEsquemaSueldo)" class="btn btn-sm" style="background:#ef4444;color:#fff;">Eliminar</button>
                    </td>
                </tr>
                <tr v-if="esquemas.length === 0">
                    <td colspan="5" class="empty-state">No hay esquemas registrados.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="modalAbierto" class="modal-overlay" @click.self="cerrarModal">
        <div class="modal-content">
            <h3>@{{ editando ? 'Editar Esquema' : 'Nuevo Esquema' }}</h3>
            <div class="form-group">
                <label>Empleado</label>
                <template v-if="editando">
                    <input type="text" class="form-control" :value="editandoNombre" disabled style="background:#f1f5f9; color:#475569;">
                </template>
                <template v-else-if="empleados.length === 0">
                    <div style="padding:10px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; color:#94a3b8; font-style:italic;">No hay empleados disponibles para asignar esquema.</div>
                </template>
                <template v-else>
                    <select v-model="form.carnetEmpleado" class="form-control" @change="onCambioEmpleado">
                        <option value="">Seleccione un empleado...</option>
                        <option v-for="emp in empleados" :key="emp.carnetEmpleado" :value="emp.carnetEmpleado">
                            @{{ emp.nombre1 }} @{{ emp.apellido1 }} (CI: @{{ emp.carnetEmpleado }})
                        </option>
                    </select>
                    <small v-if="errores.carnetEmpleado" style="color:#ef4444;">@{{ errores.carnetEmpleado }}</small>
                </template>
            </div>
            <div class="form-group">
                <label>Modalidad de Pago</label>
                <select v-model="form.modalidadPago" class="form-control">
                    <option value="">Seleccione...</option>
                    <option v-for="m in modalidades" :key="m" :value="m">@{{ m }}</option>
                </select>
                <small v-if="errores.modalidadPago" style="color:#ef4444;">@{{ errores.modalidadPago }}</small>
            </div>
            <div class="form-group">
                <label>Monto Base (Bs.)</label>
                <input type="text" v-model="form.montoBase" @input="validarMontoBase" class="form-control" placeholder="0.00">
                <small v-if="errores.montoBase" style="color:#ef4444;">@{{ errores.montoBase }}</small>
            </div>
            <div class="form-group">
                <label>Tarifa por Hora / Clase</label>
                <template v-if="esEntrenador">
                    <input type="text" v-model="form.tarifaHoraOClase" @input="validarTarifa" class="form-control" placeholder="0">
                    <small v-if="errores.tarifaHoraOClase" style="color:#ef4444;">@{{ errores.tarifaHoraOClase }}</small>
                </template>
                <template v-else>
                    <input type="text" class="form-control" value="Solo disponible para Entrenador" disabled style="background:#f1f5f9; color:#94a3b8; font-style:italic;">
                    <small style="color:#f59e0b;font-size:0.75rem;display:block;margin-top:2px;">Seleccione un empleado con rol Entrenador para habilitar este campo.</small>
                </template>
            </div>
            <div class="modal-actions">
                <button @click="cerrarModal" class="btn" style="background:#64748b;color:#fff;">Cancelar</button>
                <button @click="guardar" class="btn btn-primary" :disabled="guardando">
                    @{{ guardando ? 'Guardando...' : (editando ? 'Actualizar' : 'Guardar') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const {createApp, ref, computed, onMounted} = Vue;
createApp({
    setup() {
        const esquemas = ref([]);
        const empleados = ref([]);
        const modalidades = @json($modalidades);
        const modalAbierto = ref(false);
        const editando = ref(false);
        const editandoNombre = ref('');
        const guardando = ref(false);
        const mensaje = ref('');
        const mensajeTipo = ref('success');
        const errores = ref({});
        const form = ref({carnetEmpleado:'', modalidadPago:'', montoBase:0, tarifaHoraOClase:0});

        const esEntrenador = computed(() => {
            if (!form.value.carnetEmpleado) return false;
            const emp = empleados.value.find(e => e.carnetEmpleado == form.value.carnetEmpleado);
            return emp && emp.idRol == 3;
        });

        const onCambioEmpleado = () => {
            if (!esEntrenador.value) {
                form.value.tarifaHoraOClase = 0;
            }
        };

        const validarMontoBase = () => {
            let val = String(form.value.montoBase || '').replace(/[^0-9.]/g, '');
            const puntos = val.match(/\./g);
            if (puntos && puntos.length > 1) val = val.substring(0, val.lastIndexOf('.'));
            if (val.startsWith('.')) val = '0' + val;
            form.value.montoBase = val;
        };

        const validarTarifa = () => {
            let val = String(form.value.tarifaHoraOClase || '').replace(/[^0-9.]/g, '');
            const puntos = val.match(/\./g);
            if (puntos && puntos.length > 1) val = val.substring(0, val.lastIndexOf('.'));
            if (val.startsWith('.')) val = '0' + val;
            form.value.tarifaHoraOClase = val;
        };

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const headers = {'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf};

        const cargar = async () => {
            const r = await fetch('/admin/esquema-sueldos/data');
            const d = await r.json();
            esquemas.value = d.esquemas;
            empleados.value = d.empleados;
        };

        const abrirModal = (e) => {
            errores.value = {};
            if (e) {
                editando.value = true;
                editandoNombre.value = (e.nombre1 || '') + ' ' + (e.nombre2 ? e.nombre2 + ' ' : '') + (e.apellido1 || '') + (e.apellido2 ? ' ' + e.apellido2 : '') + ' (CI: ' + e.carnetEmpleado + ')';
                form.value = {
                    carnetEmpleado: e.carnetEmpleado,
                    modalidadPago: e.modalidadPago,
                    montoBase: e.montoBase,
                    tarifaHoraOClase: e.tarifaHoraOClase,
                };
                form.value._id = e.idEsquemaSueldo;
            } else {
                editando.value = false;
                editandoNombre.value = '';
                form.value = {carnetEmpleado:'', modalidadPago:'', montoBase:0, tarifaHoraOClase:0};
            }
            modalAbierto.value = true;
        };

        const cerrarModal = () => { modalAbierto.value = false; errores.value = {}; };

        const mostrarMsg = (tipo, texto) => {
            mostrarToast(texto, tipo);
        };

        const preValidarEsquema = () => {
            const errs = {};
            const f = form.value;
            if (!f.carnetEmpleado) errs.carnetEmpleado = 'Debe seleccionar un empleado.';
            if (!f.modalidadPago) errs.modalidadPago = 'Debe seleccionar una modalidad de pago.';
            const monto = parseFloat(String(f.montoBase).replace(/[^0-9.]/g, ''));
            if (!monto || monto < 100) errs.montoBase = 'El monto base mínimo es de 100 Bs.';
            return errs;
        };

        const guardar = async () => {
            guardando.value = true;
            errores.value = {};

            const errs = preValidarEsquema();
            if (Object.keys(errs).length > 0) {
                errores.value = errs;
                guardando.value = false;
                return;
            }

            try {
                const url = editando.value ? `/admin/esquema-sueldos/${form.value._id}` : '/admin/esquema-sueldos';
                const method = editando.value ? 'PUT' : 'POST';
                const r = await fetch(url, {method, headers, body: JSON.stringify(form.value)});
                const d = await r.json();
                if (r.ok && d.success) {
                    mostrarMsg('success', d.message);
                    cerrarModal();
                    await cargar();
                } else if (r.status === 422) {
                    for (const campo in d.errors) errores.value[campo] = d.errors[campo][0];
                } else {
                    mostrarMsg('error', d.message || 'Error');
                }
            } catch(e) {
                mostrarMsg('error', 'Error de conexion');
            } finally {
                guardando.value = false;
            }
        };

        const eliminar = async (id) => {
            confirmarAccion('Eliminar este esquema de sueldo?', async function() {
                const r = await fetch(`/admin/esquema-sueldos/${id}`, {method:'DELETE', headers});
                const d = await r.json();
                mostrarMsg(r.ok ? 'success' : 'error', d.message);
                if (r.ok) await cargar();
            });
        };

        onMounted(cargar);

        return { esquemas, empleados, modalidades, modalAbierto, editando, editandoNombre, guardando, mensaje, mensajeTipo, errores, form, esEntrenador, onCambioEmpleado, validarMontoBase, validarTarifa, abrirModal, cerrarModal, guardar, eliminar };
    }
}).mount('#appEsquemas');
</script>
@endsection

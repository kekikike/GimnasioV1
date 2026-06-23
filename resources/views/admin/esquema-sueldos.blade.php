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
                    <td>$@{{ Number(e.montoBase).toFixed(2) }}</td>
                    <td>@{{ e.tarifaHoraOClase > 0 ? '$' + e.tarifaHoraOClase + '/h' : '--' }}</td>
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
                <select v-model="form.carnetEmpleado" class="form-control" :disabled="editando" @change="onCambioEmpleado">
                    <option value="">Seleccione un empleado...</option>
                    <option v-for="emp in empleados" :key="emp.carnetEmpleado" :value="emp.carnetEmpleado">
                        @{{ emp.nombre1 }} @{{ emp.apellido1 }} (CI: @{{ emp.carnetEmpleado }})
                    </option>
                </select>
                <small v-if="errores.carnetEmpleado" style="color:#ef4444;">@{{ errores.carnetEmpleado }}</small>
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
                <label>Monto Base ($)</label>
                <input type="number" step="0.01" min="0" v-model.number="form.montoBase" class="form-control" placeholder="0.00">
                <small v-if="errores.montoBase" style="color:#ef4444;">@{{ errores.montoBase }}</small>
            </div>
            <div class="form-group">
                <label>Tarifa por Hora / Clase</label>
                <input type="number" min="0" v-model.number="form.tarifaHoraOClase" class="form-control" placeholder="0" :disabled="!esEntrenador">
                <small style="color:#64748b;font-size:0.75rem;" v-if="!esEntrenador && !editando">Solo entrenadores pueden tener tarifa.</small>
                <small v-if="errores.tarifaHoraOClase" style="color:#ef4444;">@{{ errores.tarifaHoraOClase }}</small>
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
                form.value = {
                    carnetEmpleado: e.carnetEmpleado,
                    modalidadPago: e.modalidadPago,
                    montoBase: e.montoBase,
                    tarifaHoraOClase: e.tarifaHoraOClase,
                };
                form.value._id = e.idEsquemaSueldo;
            } else {
                editando.value = false;
                form.value = {carnetEmpleado:'', modalidadPago:'', montoBase:0, tarifaHoraOClase:0};
            }
            modalAbierto.value = true;
        };

        const cerrarModal = () => { modalAbierto.value = false; errores.value = {}; };

        const mostrarMsg = (tipo, texto) => {
            mensajeTipo.value = tipo;
            mensaje.value = texto;
            setTimeout(() => mensaje.value = '', 5000);
        };

        const guardar = async () => {
            guardando.value = true;
            errores.value = {};
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
            if (!confirm('Eliminar este esquema de sueldo?')) return;
            const r = await fetch(`/admin/esquema-sueldos/${id}`, {method:'DELETE', headers});
            const d = await r.json();
            mostrarMsg(r.ok ? 'success' : 'error', d.message);
            if (r.ok) await cargar();
        };

        onMounted(cargar);

        return { esquemas, empleados, modalidades, modalAbierto, editando, guardando, mensaje, mensajeTipo, errores, form, esEntrenador, onCambioEmpleado, abrirModal, cerrarModal, guardar, eliminar };
    }
}).mount('#appEsquemas');
</script>
@endsection

@extends('layouts.admin')
@section('title', 'Clases Grupales')
@section('content')

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
    window.listaActividades = @json($actividades);
    window.listaEmpleados = @json($empleados);
    window.listaSucursales = @json($sucursales);
</script>

<div id="appClases">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
        <h3 style="color: #1e293b; margin: 0;">📅 @{{ modoEdicion ? 'Editar Clase' : 'Registrar Nueva Clase Grupal' }}</h3>
    </div>

    <div v-if="mensaje" class="alert" :class="mensajeTipo === 'error' ? 'alert-danger' : 'alert-success'" style="display: flex; justify-content: space-between; align-items: center;">
        <span>@{{ mensaje }}</span>
        <button @click="mensaje = ''" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: inherit;">&times;</button>
    </div>

    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <form @submit.prevent="guardarClase" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: end;">
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Actividad *</label>
                <select v-model="formulario.idActividad" class="form-control" required>
                    <option value="" disabled>Seleccione actividad...</option>
                    <option v-for="a in actividades" :key="a.idActividad" :value="a.idActividad">@{{ a.nombreActividad }}</option>
                </select>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Instructor *</label>
                <select v-model="formulario.carnetEmpleado" class="form-control" required>
                    <option value="" disabled>Seleccione instructor...</option>
                    <option v-for="e in empleados" :key="e.carnetEmpleado" :value="e.carnetEmpleado">@{{ e.nombre1 }} @{{ e.apellido1 }}</option>
                </select>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Sucursal *</label>
                <select v-model="formulario.idSucursal" class="form-control" required>
                    <option value="" disabled>Seleccione sucursal...</option>
                    <option v-for="s in sucursales" :key="s.idSucursal" :value="s.idSucursal">@{{ s.nombre }}</option>
                </select>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Fecha *</label>
                <input type="date" v-model="formulario.fecha" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Hora Inicio *</label>
                <input type="time" v-model="formulario.horaInicio" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Hora Fin *</label>
                <input type="time" v-model="formulario.horaFin" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Cupo Máximo *</label>
                <input type="number" v-model="formulario.cupoMaximo" class="form-control" required min="1">
            </div>
            <div v-if="modoEdicion">
                <label style="font-weight: bold; font-size: 0.9em;">Estado</label>
                <select v-model="formulario.estadoClase" class="form-control">
                    <option value="Programada">Programada</option>
                    <option value="Cursandose">Cursándose</option>
                    <option value="Cancelada">Cancelada</option>
                </select>
            </div>
            <div style="display: flex; gap: 10px; align-self: end;">
                <button type="submit" class="btn btn-primary">@{{ modoEdicion ? '💾 Guardar Cambios' : '➕ Registrar Clase' }}</button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary">❌ Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">📋 Clases Programadas</h3>

        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
            <input type="date" v-model="filtroFecha" @change="cargarClases" class="form-control" style="max-width: 200px;">
            <select v-model="filtroEstado" @change="cargarClases" class="form-control" style="max-width: 180px;">
                <option value="">Todos los estados</option>
                <option value="Programada">Programada</option>
                <option value="Cursandose">Cursándose</option>
                <option value="Cancelada">Cancelada</option>
            </select>
        </div>

        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Actividad</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Instructor</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Sucursal</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Fecha/Hora</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Cupo</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Estado</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="clase in clasesFiltradas" :key="clase.idClaseGrupal" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px; font-weight: 600;">@{{ clase.nombreActividad }}</td>
                    <td style="padding: 12px;">@{{ clase.nombre1 }} @{{ clase.apellido1 }}</td>
                    <td style="padding: 12px;">@{{ clase.nombreSucursal }}</td>
                    <td style="padding: 12px;">
                        @{{ clase.fecha }}<br>
                        <small style="color: #64748b;">@{{ clase.horaInicio?.substring(0,5) }} - @{{ clase.horaFin?.substring(0,5) }}</small>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <strong>@{{ clase.cuposOcupados }}/@{{ clase.cupoMaximo }}</strong>
                        <br><small style="color: #64748b;">@{{ clase.totalReservas }} reserva(s)</small>
                    </td>
                    <td style="padding: 12px;">
                        <span :style="{
                            background: clase.estadoClase === 'Programada' ? '#dbeafe' : clase.estadoClase === 'Cursandose' ? '#fef3c7' : '#fee2e2',
                            color: clase.estadoClase === 'Programada' ? '#1e40af' : clase.estadoClase === 'Cursandose' ? '#92400e' : '#991b1b',
                            padding: '2px 8px', borderRadius: '999px', fontSize: '0.75rem', fontWeight: 600
                        }">@{{ clase.estadoClase }}</span>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarClase(clase)" class="btn btn-sm btn-info" style="margin-right: 4px;">✏️</button>
                        <button @click="verReservas(clase)" class="btn btn-sm btn-primary" style="margin-right: 4px;">👥</button>
                        <button @click="confirmarEliminar(clase)" class="btn btn-sm btn-danger">🗑️</button>
                    </td>
                </tr>
                <tr v-if="clasesFiltradas.length === 0">
                    <td colspan="7" style="text-align: center; padding: 20px; color: #64748b;">No hay clases registradas.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="modalReservas" class="modal-overlay" @click.self="modalReservas = false">
        <div class="modal-content" style="max-width: 600px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0;">👥 Reservas — @{{ claseSeleccionada?.nombreActividad }}</h3>
                <button @click="modalReservas = false" class="btn btn-danger btn-sm">✕</button>
            </div>
            <p style="color: #64748b; font-size: 0.9rem;">
                @{{ claseSeleccionada?.fecha }} | @{{ claseSeleccionada?.horaInicio?.substring(0,5) }} - @{{ claseSeleccionada?.horaFin?.substring(0,5) }}
            </p>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th style="padding: 8px; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">Socio</th>
                        <th style="padding: 8px; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">Reserva</th>
                        <th style="padding: 8px; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">Estado</th>
                        <th style="padding: 8px; background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in reservasClase" :key="r.idReserva">
                        <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                            <strong>@{{ r.nombre1 }} @{{ r.apellido1 }}</strong>
                            <br><small style="color: #64748b;">@{{ r.correo }}</small>
                        </td>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f5f9; font-size: 0.8rem;">
                            @{{ r.fechaReserva }}
                        </td>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                            <span :class="'badge ' + (r.estadoReserva === 'Reservado' ? 'badge-info' : r.estadoReserva === 'Asistido' ? 'badge-success' : r.estadoReserva === 'Penalizado' ? 'badge-danger' : 'badge-warning')">
                                @{{ r.estadoReserva }}
                            </span>
                        </td>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f5f9; text-align: center;">
                            <button v-if="r.estadoReserva === 'Reservado'" @click="marcarAsistencia(r.idReserva, 'Asistido')" class="btn btn-sm btn-success" style="font-size: 0.75rem;">✅ Asistió</button>
                            <button v-if="r.estadoReserva === 'Reservado'" @click="marcarAsistencia(r.idReserva, 'Penalizado')" class="btn btn-sm btn-danger" style="font-size: 0.75rem; margin-left: 4px;">🚫 No Asistió</button>
                        </td>
                    </tr>
                    <tr v-if="reservasClase.length === 0">
                        <td colspan="4" style="text-align: center; padding: 20px; color: #94a3b8;">Sin reservas.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .alert { padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
    .modal-content { background: white; border-radius: 0.75rem; padding: 1.5rem; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
    .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-info { background: #dbeafe; color: #1e40af; }
    .btn-sm { padding: 0.4rem 0.75rem; font-size: 0.8rem; }
    .btn-success { background: #10b981; color: white; border: none; cursor: pointer; border-radius: 0.4rem; font-weight: 600; }
    .btn-success:hover { background: #059669; }
    .btn-secondary { background: #e2e8f0; color: #475569; border: none; cursor: pointer; border-radius: 0.5rem; padding: 0.6rem 1.25rem; font-weight: 600; }
    .btn-secondary:hover { background: #cbd5e1; }
    .form-control { width: 100%; padding: 0.6rem 0.75rem; border: 2px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.9rem; outline: none; transition: border-color 0.2s; background: white; }
    .form-control:focus { border-color: #3b82f6; }
</style>

<script>
const { createApp, ref, computed, onMounted } = Vue;

createApp({
    setup() {
        const clases = ref([]);
        const actividades = ref(window.listaActividades || []);
        const empleados = ref(window.listaEmpleados || []);
        const sucursales = ref(window.listaSucursales || []);
        const modoEdicion = ref(false);
        const mensaje = ref('');
        const mensajeTipo = ref('success');
        const filtroFecha = ref('');
        const filtroEstado = ref('');
        const modalReservas = ref(false);
        const reservasClase = ref([]);
        const claseSeleccionada = ref(null);
        const editandoId = ref(null);

        const formBase = {
            idActividad: '', carnetEmpleado: '', idSucursal: '',
            fecha: '', horaInicio: '', horaFin: '', cupoMaximo: '', estadoClase: 'Programada'
        };
        const formulario = ref({ ...formBase });

        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        };

        const clasesFiltradas = computed(() => {
            return clases.value.filter(c => {
                if (filtroFecha.value && c.fecha !== filtroFecha.value) return false;
                if (filtroEstado.value && c.estadoClase !== filtroEstado.value) return false;
                return true;
            });
        });

        const cargarClases = async () => {
            try {
                const res = await fetch('{{ route("admin.clases.listar") }}');
                clases.value = await res.json();
            } catch (e) {
                console.error('Error cargando clases:', e);
            }
        };

        const guardarClase = async () => {
            const url = modoEdicion.value
                ? `{{ route("admin.clases.update", ["id" => ":id"]) }}`.replace(':id', editandoId.value)
                : '{{ route("admin.clases.store") }}';
            const metodo = modoEdicion.value ? 'PUT' : 'POST';
            const body = { ...formulario.value };
            if (!modoEdicion.value) delete body.estadoClase;

            try {
                const res = await fetch(url, { method: metodo, headers, body: JSON.stringify(body) });
                const data = await res.json();
                mensaje.value = data.message;
                mensajeTipo.value = data.success ? 'success' : 'error';
                if (data.success) {
                    cancelarEdicion();
                    await cargarClases();
                }
            } catch (e) {
                mensaje.value = 'Error de conexión.';
                mensajeTipo.value = 'error';
            }
        };

        const editarClase = (clase) => {
            modoEdicion.value = true;
            editandoId.value = clase.idClaseGrupal;
            formulario.value = {
                idActividad: clase.idActividad,
                carnetEmpleado: clase.carnetEmpleado,
                idSucursal: clase.idSucursal,
                fecha: clase.fecha,
                horaInicio: clase.horaInicio,
                horaFin: clase.horaFin,
                cupoMaximo: clase.cupoMaximo,
                estadoClase: clase.estadoClase || 'Programada',
            };
        };

        const cancelarEdicion = () => {
            modoEdicion.value = false;
            editandoId.value = null;
            formulario.value = { ...formBase };
        };

        const confirmarEliminar = async (clase) => {
            if (!confirm(`¿Cancelar la clase "${clase.nombreActividad}" del ${clase.fecha}?`)) return;
            try {
                const res = await fetch(`{{ route("admin.clases.destroy", ["id" => ":id"]) }}`.replace(':id', clase.idClaseGrupal), { method: 'DELETE', headers });
                const data = await res.json();
                mensaje.value = data.message;
                mensajeTipo.value = data.success ? 'success' : 'error';
                if (data.success) await cargarClases();
            } catch (e) {
                mensaje.value = 'Error de conexión.';
                mensajeTipo.value = 'error';
            }
        };

        const verReservas = async (clase) => {
            claseSeleccionada.value = clase;
            modalReservas.value = true;
            try {
                const res = await fetch(`{{ route("admin.clases.reservas", ["id" => ":id"]) }}`.replace(':id', clase.idClaseGrupal));
                reservasClase.value = await res.json();
            } catch (e) {
                console.error('Error cargando reservas:', e);
                reservasClase.value = [];
            }
        };

        const marcarAsistencia = async (idReserva, estado) => {
            try {
                const res = await fetch('{{ route("admin.clases.asistencia") }}', {
                    method: 'POST', headers,
                    body: JSON.stringify({ idReserva, estado })
                });
                const data = await res.json();
                mensaje.value = data.message;
                mensajeTipo.value = data.success ? 'success' : 'error';
                if (data.success && claseSeleccionada.value) {
                    await verReservas(claseSeleccionada.value);
                    await cargarClases();
                }
            } catch (e) {
                mensaje.value = 'Error de conexión.';
                mensajeTipo.value = 'error';
            }
        };

        onMounted(cargarClases);

        return {
            clases, actividades, empleados, sucursales, formulario, modoEdicion,
            mensaje, mensajeTipo, filtroFecha, filtroEstado, clasesFiltradas,
            modalReservas, reservasClase, claseSeleccionada,
            cargarClases, guardarClase, editarClase, cancelarEdicion,
            confirmarEliminar, verReservas, marcarAsistencia,
        };
    }
}).mount('#appClases');
</script>
@endsection

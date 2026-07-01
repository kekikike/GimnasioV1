@extends('layouts.admin')
@section('title', 'Clases Grupales')
@section('content')

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
    window.listaActividades = @json($actividades);
    window.listaEmpleados = @json($empleados);
    window.adminSucursalId = @json($adminSucursalId);
    window.adminSucursalNombre = @json($adminSucursalNombre);
</script>

<div id="appClases">
    <div v-if="mensaje" class="alert" :class="mensajeTipo === 'error' ? 'alert-danger' : 'alert-success'" style="display: flex; justify-content: space-between; align-items: center;">
        <span>@{{ mensaje }}</span>
        <button @click="mensaje = ''" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: inherit;">&times;</button>
    </div>

    <div class="page-actions">
        <div style="display:flex; gap:0.75rem; align-items:center;">
            <span style="font-size:0.9rem; color:#64748b;">Clases programadas</span>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <a href="{{ route('admin.clases.create') }}" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Registrar Clase
            </a>
        </div>
    </div>

    <div class="card" style="padding: 1rem; margin-bottom: 1.5rem;">
        <div style="display: flex; gap: 10px;">
            <input type="date" v-model="filtroFecha" @change="cargarClases" class="form-control" style="max-width: 200px;">
            <select v-model="filtroEstado" @change="cargarClases" class="form-control" style="max-width: 180px;">
                <option value="">Todos los estados</option>
                <option value="Programada">Programada</option>
                <option value="Cursandose">Cursándose</option>
                <option value="Finalizada">Finalizada</option>
                <option value="Cancelada">Cancelada</option>
            </select>
        </div>
    </div>

    <div class="card" style="padding: 20px;">
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
                                background: clase.estadoClase === 'Programada' ? '#dbeafe' : clase.estadoClase === 'Cursandose' ? '#fef3c7' : clase.estadoClase === 'Finalizada' ? '#d1fae5' : '#fee2e2',
                                color: clase.estadoClase === 'Programada' ? '#1e40af' : clase.estadoClase === 'Cursandose' ? '#92400e' : clase.estadoClase === 'Finalizada' ? '#065f46' : '#991b1b',
                                padding: '2px 8px', borderRadius: '999px', fontSize: '0.75rem', fontWeight: 600
                            }">@{{ clase.estadoClase }}</span>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="openEditModal(clase)" class="btn btn-sm btn-info" style="margin-right: 4px;">✏️</button>
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

    {{-- Modal Editar Clase --}}
    <div v-if="editModal" class="modal-overlay" @click.self="closeEditModal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3>✏️ Editar Clase</h3>
                <button @click="closeEditModal" class="modal-close">&times;</button>
            </div>
            <form @submit.prevent="guardarEdicion" novalidate style="padding: 1.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Actividad *</label>
                        <select v-model="editFormulario.idActividad" class="form-control" required>
                            <option value="" disabled>Seleccione actividad...</option>
                            <option v-for="a in actividades" :key="a.idActividad" :value="a.idActividad">@{{ a.nombreActividad }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Instructor *</label>
                        <select v-model="editFormulario.carnetEmpleado" class="form-control" required>
                            <option value="" disabled>Seleccione instructor...</option>
                            <option v-for="e in empleados" :key="e.carnetEmpleado" :value="e.carnetEmpleado">@{{ e.nombre1 }} @{{ e.apellido1 }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sucursal</label>
                        <div style="padding: 0.6rem 0.75rem; background: #f1f5f9; border-radius: 0.5rem; font-size: 0.9rem; color: #0f172a; border: 2px solid #e2e8f0;">
                            <strong>@{{ adminSucursalNombre }}</strong>
                            <small style="color: #64748b; display: block;">Sucursal asignada según tu perfil.</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Fecha *</label>
                        <input type="date" v-model="editFormulario.fecha" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Hora Inicio *</label>
                        <input type="time" v-model="editFormulario.horaInicio" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Hora Fin *</label>
                        <input type="time" v-model="editFormulario.horaFin" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Cupo Máximo *</label>
                        <input type="number" v-model="editFormulario.cupoMaximo" class="form-control" required min="1" max="99999">
                    </div>
                    <div class="form-group">
                        <label>Estado</label>
                        <select v-model="editFormulario.estadoClase" class="form-control">
                            <option value="Programada">Programada</option>
                            <option value="Cursandose">Cursándose</option>
                            <option value="Finalizada">Finalizada</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" :disabled="guardando">💾 Guardar Cambios</button>
                    <button type="button" @click="closeEditModal" class="btn btn-outline">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Reservas --}}
    <div v-if="modalReservas" class="modal-overlay" @click.self="modalReservas = false">
        <div class="modal-content" style="max-width: 600px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0;">👥 Reservas — @{{ claseSeleccionada?.nombreActividad }}</h3>
                <button @click="modalReservas = false" class="btn btn-danger btn-sm">✕</button>
            </div>
            <p style="color: #64748b; font-size: 0.9rem;">
                @{{ claseSeleccionada?.fecha }} | @{{ claseSeleccionada?.horaInicio?.substring(0,5) }} - @{{ claseSeleccionada?.horaFin?.substring(0,5) }}
                &nbsp;|&nbsp; Cupo: <strong>@{{ reservasData.cuposOcupados }}/@{{ reservasData.cupoMaximo }}</strong>
            </p>

            <h4 style="margin: 1rem 0 0.5rem; font-size: 0.95rem;">Participantes</h4>
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
                    <tr v-for="r in reservasData.participantes" :key="r.idReserva">
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
                    <tr v-if="reservasData.participantes.length === 0">
                        <td colspan="4" style="text-align: center; padding: 20px; color: #94a3b8;">Sin participantes activos.</td>
                    </tr>
                </tbody>
            </table>

            <h4 v-if="reservasData.cancelados.length > 0" style="margin: 1rem 0 0.5rem; font-size: 0.95rem; color: #64748b;">Cancelados</h4>
            <table v-if="reservasData.cancelados.length > 0" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th style="padding: 8px; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">Socio</th>
                        <th style="padding: 8px; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">Reserva</th>
                        <th style="padding: 8px; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">Estado</th>
                        <th style="padding: 8px; background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: center;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in reservasData.cancelados" :key="r.idReserva">
                        <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                            <strong style="color: #94a3b8;">@{{ r.nombre1 }} @{{ r.apellido1 }}</strong>
                            <br><small style="color: #94a3b8;">@{{ r.correo }}</small>
                        </td>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f5f9; font-size: 0.8rem; color: #94a3b8;">
                            @{{ r.fechaReserva }}
                        </td>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f5f9;">
                            <span class="badge badge-warning">Cancelado</span>
                        </td>
                        <td style="padding: 8px; border-bottom: 1px solid #f1f5f9; text-align: center;"></td>
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
    .btn-info { background: #3b82f6; color: white; border: none; cursor: pointer; border-radius: 0.4rem; font-weight: 600; }
    .btn-info:hover { background: #2563eb; }
    .btn-success { background: #10b981; color: white; border: none; cursor: pointer; border-radius: 0.4rem; font-weight: 600; }
    .btn-success:hover { background: #059669; }
    .btn-danger { background: #ef4444; color: white; border: none; cursor: pointer; border-radius: 0.4rem; font-weight: 600; }
    .btn-danger:hover { background: #dc2626; }
    .btn-primary { background: #3b82f6; color: white; border: none; cursor: pointer; border-radius: 0.4rem; font-weight: 600; }
    .btn-primary:hover { background: #2563eb; }
    .btn-outline { background: transparent; color: #475569; border: 2px solid #e2e8f0; border-radius: 0.5rem; padding: 0.5rem 1.25rem; cursor: pointer; font-weight: 600; }
    .btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }
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
        const adminSucursalId = ref(window.adminSucursalId || null);
        const adminSucursalNombre = ref(window.adminSucursalNombre || 'No definida');
        const mensaje = ref('{{ session('success') ? session('success') : (session('error') ? session('error') : '') }}');
        const mensajeTipo = ref('{{ session('success') ? 'success' : (session('error') ? 'error' : 'success') }}');
        const filtroFecha = ref('');
        const filtroEstado = ref('');
        const modalReservas = ref(false);
        const reservasData = ref({ participantes: [], cancelados: [], penalizados: [], cupoMaximo: 0, cuposOcupados: 0 });
        const claseSeleccionada = ref(null);
        const editModal = ref(false);
        const editandoId = ref(null);
        const editFormulario = ref({
            idActividad: '', carnetEmpleado: '',
            fecha: '', horaInicio: '',
            horaFin: '', cupoMaximo: '', estadoClase: 'Programada'
        });

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

        const openEditModal = (clase) => {
            editandoId.value = clase.idClaseGrupal;
            editFormulario.value = {
                idActividad: clase.idActividad,
                carnetEmpleado: clase.carnetEmpleado,
                fecha: clase.fecha,
                horaInicio: clase.horaInicio,
                horaFin: clase.horaFin,
                cupoMaximo: clase.cupoMaximo,
                estadoClase: clase.estadoClase || 'Programada',
            };
            editModal.value = true;
        };

        const closeEditModal = () => {
            editModal.value = false;
            editandoId.value = null;
        };

        const guardando = ref(false);

        const guardarEdicion = async () => {
            if (guardando.value) return;
            guardando.value = true;
            try {
                const res = await fetch(
                    `{{ route("admin.clases.update", ["id" => ":id"]) }}`.replace(':id', editandoId.value),
                    { method: 'PUT', headers, body: JSON.stringify(editFormulario.value) }
                );
                const data = await res.json();
                mostrarToast(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    closeEditModal();
                    await cargarClases();
                }
            } catch (e) {
                mostrarToast('Error de conexión.', 'error');
            } finally {
                guardando.value = false;
            }
        };

        const ejecutarCancelacion = async (clase) => {
            try {
                const res = await fetch(
                    `{{ route("admin.clases.destroy", ["id" => ":id"]) }}`.replace(':id', clase.idClaseGrupal),
                    { method: 'DELETE', headers }
                );
                const data = await res.json();
                mostrarToast(data.message, data.success ? 'success' : 'error');
                if (data.success) await cargarClases();
            } catch (e) {
                mostrarToast('Error de conexión.', 'error');
            }
        };

        const confirmarEliminar = async (clase) => {
            try {
                const res = await fetch(`{{ route("admin.clases.reservas", ["id" => ":id"]) }}`.replace(':id', clase.idClaseGrupal), { headers });
                const data = await res.json();
                const activas = data.participantes || [];
                const numActivas = activas.length;

                if (numActivas > 0) {
                    confirmarAccion(
                        `La clase "${clase.nombreActividad}" del ${clase.fecha} tiene ${numActivas} reserva(s) activa(s). ¿Desea cancelar la clase? Todas las reservas serán canceladas.`,
                        function () {
                            confirmarAccion(
                                `Confirmación final. ¿Está totalmente seguro de cancelar la clase y sus ${numActivas} reservas?`,
                                async function () {
                                    await ejecutarCancelacion(clase);
                                }
                            );
                        }
                    );
                } else {
                    confirmarAccion(
                        `¿Cancelar la clase "${clase.nombreActividad}" del ${clase.fecha}?`,
                        async function () {
                            await ejecutarCancelacion(clase);
                        }
                    );
                }
            } catch (e) {
                mostrarToast('Error al verificar reservas.', 'error');
            }
        };

        const verReservas = async (clase) => {
            claseSeleccionada.value = clase;
            modalReservas.value = true;
            reservasData.value = { participantes: [], cancelados: [], penalizados: [], cupoMaximo: 0, cuposOcupados: 0 };
            try {
                const res = await fetch(`{{ route("admin.clases.reservas", ["id" => ":id"]) }}`.replace(':id', clase.idClaseGrupal));
                const json = await res.json();
                reservasData.value = {
                    participantes: json.participantes || [],
                    cancelados: json.cancelados || [],
                    penalizados: json.penalizados || [],
                    cupoMaximo: json.cupoMaximo || 0,
                    cuposOcupados: json.cuposOcupados || 0,
                };
            } catch (e) {
                console.error('Error cargando reservas:', e);
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

        onMounted(() => {
            cargarClases();
            if (mensaje.value) {
                setTimeout(() => { mensaje.value = ''; }, 5000);
            }
        });

        return {
            clases, actividades, empleados, adminSucursalId, adminSucursalNombre,
            mensaje, mensajeTipo, filtroFecha, filtroEstado, clasesFiltradas, guardando,
            modalReservas, reservasData, claseSeleccionada,
            editModal, editFormulario,
            cargarClases, openEditModal, closeEditModal, guardarEdicion,
            confirmarEliminar, verReservas, marcarAsistencia,
        };
    }
}).mount('#appClases');
</script>
@endsection

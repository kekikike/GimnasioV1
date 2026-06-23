@extends('layouts.entrenador')
@section('title', 'Asistencias Clase')
@section('content')

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<style>
    .text-muted { color: #64748b; }
    .text-success { color: #10b981; }
    .text-danger { color: #ef4444; }
    .text-warning { color: #f59e0b; }
    .btn-sm { padding: 0.4rem 0.75rem; font-size: 0.8rem; }
    .btn-success { background: #10b981; color: white; border: none; cursor: pointer; border-radius: 0.4rem; font-weight: 600; }
    .btn-success:hover:not(:disabled) { background: #059669; }
    .btn-success:disabled { background: #a7f3d0; color: #6ee7b7; cursor: not-allowed; }
    .btn-danger { background: #ef4444; color: white; border: none; cursor: pointer; border-radius: 0.4rem; font-weight: 600; }
    .btn-danger:hover:not(:disabled) { background: #dc2626; }
    .btn-danger:disabled { background: #fecaca; color: #fca5a5; cursor: not-allowed; }
    .badge { display: inline-flex; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-info { background: #dbeafe; color: #1e40af; }
    .badge-secondary { background: #f1f5f9; color: #64748b; }
    .badge-proxima { background: #e0e7ff; color: #4338ca; }
    .badge-expirada { background: #f1f5f9; color: #94a3b8; }
    .badge-en-curso { background: #d1fae5; color: #065f46; }
    .alert { padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .btn-outline { background: transparent; color: #475569; border: 2px solid #e2e8f0; border-radius: 0.5rem; padding: 0.5rem 1.25rem; cursor: pointer; font-weight: 600; }
    .btn-outline:hover { background: #f8fafc; border-color: #cbd5e1; }
    .fw-bold { font-weight: 700; }
    .small { font-size: 0.8rem; }
</style>

<div id="appAsistencias">
    <div v-if="mensaje" class="alert" :class="mensajeTipo === 'error' ? 'alert-danger' : 'alert-success'" style="display: flex; justify-content: space-between; align-items: center;">
        <span>@{{ mensaje }}</span>
        <button @click="mensaje = ''" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: inherit;">&times;</button>
    </div>

    <div style="display: flex; gap: 1.5rem; align-items: stretch;">
        <div style="flex: 1; min-width: 0;">
            <div class="card" style="margin-bottom: 1rem; padding: 1rem 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 1.1rem;">Clases de Hoy</h3>
                    <button @click="cargarClasesHoy" class="btn btn-outline btn-sm" :disabled="cargando">Actualizar</button>
                </div>
            </div>

            <div v-if="cargando" style="text-align: center; padding: 3rem; color: #94a3b8;">Cargando clases...</div>

            <div v-else-if="clases.length === 0" class="card" style="text-align: center; padding: 3rem;">
                <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📅</div>
                <h3 style="color: #0f172a; margin-bottom: 0.25rem;">Sin clases hoy</h3>
                <p style="color: #64748b; font-size: 0.9rem;">No tienes clases programadas para el día de hoy.</p>
            </div>

            <div v-else>
                <div
                    v-for="c in clases"
                    :key="c.idClaseGrupal"
                    class="card"
                    style="margin-bottom: 0.6rem; padding: 0.9rem 1.1rem; cursor: pointer; transition: all 0.15s;"
                    :style="{ borderLeft: claseSeleccionada?.idClaseGrupal === c.idClaseGrupal ? '4px solid #8b5cf6' : '4px solid transparent', opacity: c.estadoAsistencia === 'expirada' ? 0.6 : 1 }"
                    @click="seleccionarClase(c)"
                >
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 40px; height: 40px; border-radius: 0.5rem; background: #f5f3ff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">🏋️</div>
                            <div>
                                <div style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">@{{ c.nombreActividad }}</div>
                                <div style="font-size: 0.78rem; color: #64748b;">
                                    <strong>@{{ c.horaInicio?.substring(0, 5) }}</strong> - @{{ c.horaFin?.substring(0, 5) }} · @{{ c.nombreSucursal }}
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="font-size: 0.78rem; color: #64748b; white-space: nowrap;">
                                <span class="fw-bold">@{{ c.reservados }}</span>/@{{ c.cupoMaximo }} cupos
                            </span>
                            <span v-if="c.estadoAsistencia === 'proxima'" class="badge badge-proxima">Próxima</span>
                            <span v-else-if="c.estadoAsistencia === 'en_curso'" class="badge badge-en-curso">En curso</span>
                            <span v-else class="badge badge-expirada">Expirada</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="claseSeleccionada" style="width: 440px; flex-shrink: 0;">
            <div class="card" style="height: 100%; display: flex; flex-direction: column;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9;">
                    <div>
                        <h3 style="margin: 0; font-size: 1rem;">@{{ claseSeleccionada.nombreActividad }}</h3>
                        <div class="small text-muted" style="margin-top: 0.25rem;">
                            @{{ claseSeleccionada.horaInicio?.substring(0, 5) }} - @{{ claseSeleccionada.horaFin?.substring(0, 5) }}
                            · @{{ claseSeleccionada.nombreSucursal }}
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span v-if="claseSeleccionada.estadoAsistencia === 'proxima'" class="badge badge-proxima">Próxima</span>
                        <span v-else-if="claseSeleccionada.estadoAsistencia === 'en_curso'" class="badge badge-en-curso">En curso</span>
                        <span v-else class="badge badge-expirada">Expirada</span>
                        <button @click="cerrarPanel" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.5rem;">✕</button>
                    </div>
                </div>

                <div v-if="claseSeleccionada.estadoAsistencia === 'proxima'" class="alert" style="background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; padding: 0.5rem 0.75rem; font-size: 0.8rem; margin-bottom: 0.75rem;">
                    La clase comenzará a las <strong>@{{ claseSeleccionada.horaInicio?.substring(0, 5) }}</strong>.
                    Los botones se habilitarán automáticamente en su horario.
                </div>

                <div v-if="claseSeleccionada.estadoAsistencia === 'expirada'" class="alert alert-danger" style="padding: 0.5rem 0.75rem; font-size: 0.8rem; margin-bottom: 0.75rem;">
                    La clase finalizó a las <strong>@{{ claseSeleccionada.horaFin?.substring(0, 5) }}</strong>.
                    No se puede tomar asistencia.
                </div>

                <div v-if="cargandoAlumnos" style="text-align: center; padding: 2rem; color: #94a3b8;">Cargando alumnos...</div>

                <div v-else-if="alumnos.length === 0" style="text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.9rem;">
                    No hay alumnos inscritos en esta clase.
                </div>

                <div v-else style="flex: 1; overflow-y: auto;">
                    <div v-for="a in alumnos" :key="a.idReserva" style="display: flex; gap: 0.65rem; padding: 0.6rem 0; border-bottom: 1px solid #f8fafc; align-items: center;">
                        <div style="width: 34px; height: 34px; border-radius: 0.4rem; overflow: hidden; background: #e2e8f0; flex-shrink: 0;">
                            <img :src="'/storage/' + (a.fotografiaUrl || 'fotos_socios/default.jpeg')" alt="" style="width: 100%; height: 100%; object-fit: cover; object-position: top;" @@error="$el.style.display='none'">
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; color: #0f172a; font-size: 0.82rem;">@{{ nombreCompleto(a) }}</div>
                            <div style="font-size: 0.7rem; color: #94a3b8;">@{{ a.correo }}</div>
                            <div v-if="a.observacionesMedicas" style="font-size: 0.7rem; color: #dc2626; margin-top: 1px;">⚠️ @{{ a.observacionesMedicas }}</div>
                        </div>
                        <div v-if="a.estadoReserva === 'Asistido'" style="white-space: nowrap;">
                            <span class="badge badge-success">Presente</span>
                        </div>
                        <div v-else-if="a.estadoReserva === 'Penalizado'" style="white-space: nowrap;">
                            <span class="badge badge-danger">Falta</span>
                        </div>
                        <div v-else style="display: flex; gap: 4px; flex-shrink: 0;">
                            <button
                                @click="marcar(a.idReserva, 'Asistido')"
                                class="btn btn-success btn-sm"
                                :disabled="claseSeleccionada.estadoAsistencia !== 'en_curso' || a.estadoReserva !== 'Reservado'"
                                style="font-size: 0.7rem; padding: 0.3rem 0.6rem;"
                            >Presente</button>
                            <button
                                @click="marcar(a.idReserva, 'Penalizado')"
                                class="btn btn-danger btn-sm"
                                :disabled="claseSeleccionada.estadoAsistencia !== 'en_curso' || a.estadoReserva !== 'Reservado'"
                                style="font-size: 0.7rem; padding: 0.3rem 0.6rem;"
                            >Falta</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
Vue.createApp({
    data() {
        return {
            clases: [],
            claseSeleccionada: null,
            alumnos: [],
            cargando: false,
            cargandoAlumnos: false,
            mensaje: '',
            mensajeTipo: 'success',
        };
    },
    methods: {
        nombreCompleto(a) {
            return [a.nombre1, a.nombre2, a.apellido1, a.apellido2].filter(Boolean).join(' ');
        },
        async cargarClasesHoy() {
            this.cargando = true;
            this.mensaje = '';
            try {
                const res = await fetch('{{ route("entrenador.asistencias.hoy") }}');
                this.clases = await res.json();
                if (this.claseSeleccionada) {
                    const stillExists = this.clases.find(c => c.idClaseGrupal === this.claseSeleccionada.idClaseGrupal);
                    if (stillExists) {
                        this.claseSeleccionada = stillExists;
                        await this.cargarAlumnos(stillExists.idClaseGrupal);
                    } else {
                        this.cerrarPanel();
                    }
                }
            } catch (e) {
                console.error('Error cargando clases:', e);
                this.clases = [];
            } finally {
                this.cargando = false;
            }
        },
        async seleccionarClase(clase) {
            this.claseSeleccionada = clase;
            await this.cargarAlumnos(clase.idClaseGrupal);
        },
        cerrarPanel() {
            this.claseSeleccionada = null;
            this.alumnos = [];
        },
        async cargarAlumnos(idClase) {
            this.cargandoAlumnos = true;
            this.mensaje = '';
            try {
                const url = '{{ route("entrenador.asistencias.alumnos", ["id" => ":id"]) }}';
                const res = await fetch(url.replace(':id', idClase));
                const data = await res.json();
                this.alumnos = data.alumnos ?? [];
            } catch (e) {
                console.error('Error cargando alumnos:', e);
                this.alumnos = [];
            } finally {
                this.cargandoAlumnos = false;
            }
        },
        async marcar(idReserva, estado) {
            this.mensaje = '';
            this.mensajeTipo = 'success';
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            };
            try {
                const res = await fetch('{{ route("entrenador.asistencias.marcar") }}', {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({ idReserva, estado })
                });
                const data = await res.json();
                this.mensaje = data.message;
                this.mensajeTipo = data.success ? 'success' : 'error';
                if (data.success && this.claseSeleccionada) {
                    await this.cargarAlumnos(this.claseSeleccionada.idClaseGrupal);
                    await this.cargarClasesHoy();
                }
            } catch (e) {
                this.mensaje = 'Error de conexión. Verifique su red e intente nuevamente.';
                this.mensajeTipo = 'error';
                console.error('Error marcando asistencia:', e);
            }
        }
    },
    async mounted() {
        await this.cargarClasesHoy();
        if (this.clases.length > 0) {
            const enCurso = this.clases.find(c => c.estadoAsistencia === 'en_curso');
            await this.seleccionarClase(enCurso || this.clases[0]);
        }
    }
}).mount('#appAsistencias');
</script>
@endsection

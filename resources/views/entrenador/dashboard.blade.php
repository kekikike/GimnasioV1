@extends('layouts.entrenador')
@section('title', 'Mi Agenda')
@section('content')

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appEntrenador">
    <div style="display: flex; gap: 1.5rem; height: calc(100vh - 140px); overflow: hidden;">

        <div style="flex: 1; overflow-y: auto; padding-right: 0.5rem;">
            <div class="card" style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 1.1rem;">Mis Clases</h3>
                    <div style="display: flex; gap: 0.5rem;">
                        <button @click="filtroEstado = 'todas'" class="btn" :class="filtroEstado === 'todas' ? 'btn-primary' : ''" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">Todas</button>
                        <button @click="filtroEstado = 'Programada'" class="btn" :class="filtroEstado === 'Programada' ? 'btn-primary' : ''" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">Programadas</button>
                        <button @click="filtroEstado = 'Cursandose'" class="btn" :class="filtroEstado === 'Cursandose' ? 'btn-primary' : ''" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">En Curso</button>
                        <button @click="filtroEstado = 'Finalizada'" class="btn" :class="filtroEstado === 'Finalizada' ? 'btn-primary' : ''" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;">Finalizadas</button>
                    </div>
                </div>
            </div>

            <div v-if="cargandoClases" style="text-align: center; padding: 3rem; color: #94a3b8;">
                Cargando clases...
            </div>

            <div v-else-if="clasesFiltradas.length === 0" class="card" style="text-align: center; padding: 3rem;">
                <div style="font-size: 2.5rem; margin-bottom: 1rem;">📋</div>
                <h3 style="color: #0f172a; margin-bottom: 0.5rem;">Sin clases</h3>
                <p style="color: #64748b;">No tienes clases en este estado.</p>
            </div>

            <div v-else>
                <div
                    v-for="c in clasesFiltradas"
                    :key="c.idClaseGrupal"
                    class="card"
                    style="margin-bottom: 0.75rem; padding: 1rem 1.25rem; cursor: pointer; transition: all 0.15s;"
                    :style="{ borderLeft: seleccion?.idClaseGrupal === c.idClaseGrupal ? '4px solid #8b5cf6' : '4px solid transparent' }"
                    @click="seleccionarClase(c)"
                >
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 44px; height: 44px; border-radius: 0.5rem; background: #f5f3ff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">🏋️</div>
                            <div>
                                <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;"><span v-text="c.nombreActividad"></span></div>
                                <div style="font-size: 0.8rem; color: #64748b;">
                                    <span v-text="c.fecha"></span> · <span v-text="c.horaInicio?.substring(0, 5)"></span> - <span v-text="c.horaFin?.substring(0, 5)"></span> · <span v-text="c.nombreSucursal"></span>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="font-size: 0.8rem; color: #64748b;">
                                <strong v-text="c.reservados"></strong>/<span v-text="c.cupoMaximo"></span> cupos
                                <span v-if="c.asistieron > 0" style="color: #10b981;"> · <span v-text="c.asistieron"></span> asistieron</span>
                            </span>
                            <span :style="{
                                background: c.estadoClase === 'Programada' ? '#f5f3ff' : c.estadoClase === 'Cursandose' ? '#fef3c7' : '#d1fae5',
                                color: c.estadoClase === 'Programada' ? '#6d28d9' : c.estadoClase === 'Cursandose' ? '#92400e' : '#065f46',
                                padding: '2px 10px', borderRadius: '999px', fontSize: '0.75rem', fontWeight: 600
                            }" v-text="c.estadoClase"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="seleccion" style="width: 420px; flex-shrink: 0; overflow: hidden;">
            <div class="card" style="height: 100%; display: flex; flex-direction: column;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h3 style="margin: 0; font-size: 1rem;">Participantes</h3>
                    <button @click="seleccion = null" class="btn btn-danger" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">✕</button>
                </div>
                <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9;">
                    <strong v-text="seleccion.nombreActividad"></strong> — <span v-text="seleccion.fecha"></span>
                    <br><span v-text="seleccion.horaInicio?.substring(0, 5)"></span> - <span v-text="seleccion.horaFin?.substring(0, 5)"></span>
                    <br>Cupo: <span v-text="seleccion.reservados"></span>/<span v-text="seleccion.cupoMaximo"></span> reservados · <span v-text="seleccion.asistieron"></span> asistieron
                </div>

                <div v-if="cargandoParticipantes" style="text-align: center; padding: 2rem; color: #94a3b8;">
                    Cargando...
                </div>

                <div v-else-if="participantes.length === 0" style="text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.9rem;">
                    No hay participantes registrados.
                </div>

                <div v-else style="flex: 1; overflow-y: auto;">
                    <div v-for="p in participantes" :key="p.idReserva" style="display: flex; gap: 0.75rem; padding: 0.65rem 0; border-bottom: 1px solid #f8fafc; align-items: center;">
                        <div style="width: 36px; height: 36px; border-radius: 0.4rem; overflow: hidden; background: #e2e8f0; flex-shrink: 0;">
                            <img :src="'/storage/' + (p.fotografiaUrl || 'fotos_socios/default.jpeg')" alt="" style="width: 100%; height: 100%; object-fit: cover; object-position: top;" @@error="$el.style.display='none'">
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; color: #0f172a; font-size: 0.85rem;"><span v-text="p.nombre1"></span> <span v-text="p.apellido1"></span></div>
                            <div style="font-size: 0.75rem; color: #94a3b8;"><span v-text="p.correo"></span><span v-if="p.telefono"> · <span v-text="p.telefono"></span></span></div>
                            <div v-if="p.observacionesMedicas" style="font-size: 0.75rem; color: #dc2626; margin-top: 2px;">⚠️ <span v-text="p.observacionesMedicas"></span></div>
                        </div>
                        <span :style="{
                            background: p.estadoReserva === 'Reservado' ? '#f5f3ff' : p.estadoReserva === 'Asistido' ? '#d1fae5' : p.estadoReserva === 'Penalizado' ? '#fee2e2' : '#f1f5f9',
                            color: p.estadoReserva === 'Reservado' ? '#6d28d9' : p.estadoReserva === 'Asistido' ? '#065f46' : p.estadoReserva === 'Penalizado' ? '#991b1b' : '#64748b',
                            padding: '2px 8px', borderRadius: '999px', fontSize: '0.7rem', fontWeight: 600, whiteSpace: 'nowrap'
                        }" v-text="p.estadoReserva"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
    .btn-primary { background: #8b5cf6; color: white; }
    .btn-primary:hover { background: #7c3aed; }
    .btn-danger { background: #ef4444; color: white; }
    .btn-danger:hover { background: #dc2626; }
    .btn-primary.btn-sm { background: #8b5cf6; color: white; }
</style>

<script>
Vue.createApp({
    data() {
        return {
            clases: [],
            cargandoClases: true,
            seleccion: null,
            participantes: [],
            cargandoParticipantes: false,
            filtroEstado: 'todas'
        };
    },
    computed: {
        clasesFiltradas() {
            if (this.filtroEstado === 'todas') return this.clases;
            return this.clases.filter(c => c.estadoClase === this.filtroEstado);
        }
    },
    methods: {
        async seleccionarClase(clase) {
            this.seleccion = clase;
            this.cargandoParticipantes = true;
            try {
                const url = '{{ route("entrenador.clases.participantes", ["id" => ":id"]) }}';
                const res = await fetch(url.replace(':id', clase.idClaseGrupal));
                this.participantes = await res.json();
            } catch (e) {
                console.error('Error cargando participantes:', e);
                this.participantes = [];
            } finally {
                this.cargandoParticipantes = false;
            }
        }
    },
    async mounted() {
        try {
            const res = await fetch('{{ route("entrenador.clases") }}');
            this.clases = await res.json();
            if (this.clases.length > 0) {
                await this.seleccionarClase(this.clases[0]);
            }
        } catch (e) {
            console.error('Error cargando clases:', e);
        } finally {
            this.cargandoClases = false;
        }
    }
}).mount('#appEntrenador');
</script>
@endsection

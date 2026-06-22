@extends('layouts.socio')
@section('title', 'Mis Reservas')
@section('content')

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appReservas">
    <div v-if="mensaje" class="alert" :class="mensajeTipo === 'error' ? 'alert-danger' : 'alert-success'" style="display: flex; justify-content: space-between; align-items: center;">
        <span>@{{ mensaje }}</span>
        <button @click="mensaje = ''" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: inherit;">&times;</button>
    </div>

    <div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem;">
        <button @click="pestana = 'disponibles'" class="btn" :class="pestana === 'disponibles' ? 'btn-primary' : 'btn-secondary'">📅 Clases Disponibles</button>
        <button @click="pestana = 'reservas'" class="btn" :class="pestana === 'reservas' ? 'btn-primary' : 'btn-secondary'">📋 Mis Reservas</button>
    </div>

    <div v-if="pestana === 'disponibles'">
        <div class="card">
            <div class="section-title">Clases Grupales Disponibles</div>
            <div v-if="cargando" style="text-align: center; padding: 2rem; color: #94a3b8;">Cargando clases...</div>
            <div v-else-if="clasesDisponibles.length === 0" style="text-align: center; padding: 2rem; color: #94a3b8;">
                No hay clases disponibles próximamente.
            </div>
            <div v-else style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div v-for="c in clasesDisponibles" :key="c.idClaseGrupal" class="card" style="padding: 1rem; border: 1px solid #e2e8f0;">
                    <div style="font-weight: 700; color: #0f172a; font-size: 1rem;">@{{ c.nombreActividad }}</div>
                    <div style="color: #64748b; font-size: 0.85rem; margin: 0.25rem 0;">
                        📍 @{{ c.sucursal }} · 👤 @{{ c.instructor }}
                    </div>
                    <div style="color: #64748b; font-size: 0.85rem;">
                        📅 @{{ c.fecha }} · 🕐 @{{ c.horaInicio?.substring(0,5) }} - @{{ c.horaFin?.substring(0,5) }}
                    </div>
                    <div style="margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <span :style="{
                            fontWeight: 700,
                            color: c.cuposDisponibles > 0 ? '#10b981' : '#ef4444'
                        }">
                            @{{ c.cuposDisponibles > 0 ? c.cuposDisponibles + ' cupo(s) disponible(s)' : 'Completo' }}
                        </span>
                        <button
                            v-if="clasesReservadas.has(c.idClaseGrupal)"
                            class="btn btn-sm"
                            disabled
                            style="font-size: 0.8rem; background: #d1fae5; color: #065f46; cursor: default;"
                        >
                            ✅ Ya Registrado
                        </button>
                        <button
                            v-else
                            @click="reservarClase(c.idClaseGrupal)"
                            class="btn btn-primary btn-sm"
                            :disabled="c.cuposDisponibles <= 0 || reservando"
                            style="font-size: 0.8rem;"
                        >
                            @{{ reservando ? '⏳' : '✅' }} Reservar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div v-if="pestana === 'reservas'">
        <div class="card">
            <div class="section-title">Mis Reservas</div>
            <div v-if="cargandoReservas" style="text-align: center; padding: 2rem; color: #94a3b8;">Cargando reservas...</div>
            <div v-else-if="misReservas.length === 0" style="text-align: center; padding: 2rem; color: #94a3b8;">
                No tienes reservas registradas.
            </div>
            <table v-else>
                <thead>
                    <tr>
                        <th>Actividad</th>
                        <th>Instructor</th>
                        <th>Sucursal</th>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="r in misReservas" :key="r.idReserva">
                        <td style="font-weight: 600;">@{{ r.nombreActividad }}</td>
                        <td style="color: #64748b;">@{{ r.instructor }}</td>
                        <td>@{{ r.sucursal }}</td>
                        <td>@{{ r.fecha }}</td>
                        <td>@{{ r.horaInicio?.substring(0,5) }} - @{{ r.horaFin?.substring(0,5) }}</td>
                        <td>
                            <span :class="'badge ' + (
                                r.estadoReserva === 'Reservado' ? 'badge-info' :
                                r.estadoReserva === 'Asistido' ? 'badge-success' :
                                r.estadoReserva === 'Cancelado' ? 'badge-warning' : 'badge-danger'
                            )">@{{ r.estadoReserva }}</span>
                        </td>
                        <td>
                            <button
                                v-if="r.estadoReserva === 'Reservado'"
                                @click="cancelarReserva(r)"
                                class="btn btn-danger btn-sm"
                                style="font-size: 0.75rem;"
                            >❌ Cancelar</button>
                        </td>
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
    .badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-info { background: #dbeafe; color: #1e40af; }
    .btn-sm { padding: 0.4rem 0.75rem; font-size: 0.8rem; }
    .btn-secondary { background: #e2e8f0; color: #475569; border: none; cursor: pointer; border-radius: 0.5rem; padding: 0.6rem 1.25rem; font-weight: 600; }
    .btn-secondary:hover { background: #cbd5e1; }
    .section-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #f1f5f9; }
    table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    th { text-align: left; padding: 0.6rem 0.75rem; background: #f8fafc; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
    td { padding: 0.6rem 0.75rem; border-bottom: 1px solid #f1f5f9; color: #0f172a; }
    .card { background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); padding: 1.5rem; }
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1.25rem; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
    .btn-primary { background: #f43f5e; color: white; }
    .btn-primary:hover { background: #e11d48; }
    .btn-danger { background: #ef4444; color: white; }
    .btn-danger:hover { background: #dc2626; }
</style>

<script>
const { createApp, ref, onMounted } = Vue;

createApp({
    setup() {
        const pestana = ref('disponibles');
        const clasesDisponibles = ref([]);
        const misReservas = ref([]);
        const clasesReservadas = ref(new Set());
        const cargando = ref(false);
        const cargandoReservas = ref(false);
        const reservando = ref(false);
        const mensaje = ref('');
        const mensajeTipo = ref('success');

        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        };

        const cargarDisponibles = async () => {
            cargando.value = true;
            try {
                const res = await fetch('{{ route("socio.reservas.disponibles") }}');
                clasesDisponibles.value = await res.json();
            } catch (e) {
                console.error(e);
            } finally {
                cargando.value = false;
            }
        };

        const cargarMisReservas = async () => {
            cargandoReservas.value = true;
            try {
                const res = await fetch('{{ route("socio.reservas.mis") }}');
                misReservas.value = await res.json();
                clasesReservadas.value = new Set(
                    misReservas.value
                        .filter(r => r.estadoReserva === 'Reservado')
                        .map(r => r.idClaseGrupal)
                );
            } catch (e) {
                console.error(e);
            } finally {
                cargandoReservas.value = false;
            }
        };

        const reservarClase = async (idClaseGrupal) => {
            reservando.value = true;
            try {
                const res = await fetch('{{ route("socio.reservas.reservar") }}', {
                    method: 'POST', headers,
                    body: JSON.stringify({ idClaseGrupal })
                });
                const data = await res.json();
                mensaje.value = data.message;
                mensajeTipo.value = data.success ? 'success' : 'error';
                if (data.success) {
                    await cargarDisponibles();
                    await cargarMisReservas();
                    pestana.value = 'reservas';
                }
            } catch (e) {
                mensaje.value = 'Error de conexión.';
                mensajeTipo.value = 'error';
            } finally {
                reservando.value = false;
            }
        };

        const cancelarReserva = async (reserva) => {
            if (!confirm(`¿Cancelar tu reserva para "${reserva.nombreActividad}" del ${reserva.fecha} a las ${reserva.horaInicio?.substring(0,5)}?`)) return;
            try {
                const res = await fetch('{{ route("socio.reservas.cancelar") }}', {
                    method: 'POST', headers,
                    body: JSON.stringify({ idReserva: reserva.idReserva })
                });
                const data = await res.json();
                mensaje.value = data.message;
                mensajeTipo.value = data.success ? 'success' : 'error';
                if (data.success) {
                    await cargarMisReservas();
                }
            } catch (e) {
                mensaje.value = 'Error de conexión.';
                mensajeTipo.value = 'error';
            }
        };

        onMounted(() => {
            cargarDisponibles();
            cargarMisReservas();
        });

        return {
            pestana, clasesDisponibles, misReservas, clasesReservadas,
            cargando, cargandoReservas, reservando,
            mensaje, mensajeTipo,
            cargarDisponibles, cargarMisReservas, reservarClase, cancelarReserva,
        };
    }
}).mount('#appReservas');
</script>
@endsection

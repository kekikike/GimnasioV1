@extends('layouts.recepcionista')
@section('title', 'Panel de Ingreso')
@section('content')

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appDash">
    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0; font-size: 1.3rem;">Todos los Participantes</h2>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <input
                    type="text"
                    v-model="filtro"
                    @input="filtrarLocal"
                    placeholder="Filtrar en lista..."
                    style="padding: 0.55rem 0.85rem; border: 2px solid #e2e8f0; border-radius: 0.4rem; font-size: 0.85rem; outline: none; width: 220px;"
                >
                <span style="color: #64748b; font-size: 0.85rem;">Total: @{{ sociosFiltrados.length }} socio(s)</span>
            </div>
        </div>
    </div>

    <div v-if="cargando" style="text-align: center; padding: 3rem; color: #94a3b8;">
        Cargando socios...
    </div>

    <div v-else class="card" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="padding: 0.75rem 1rem; text-align: left; background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.8rem; border-bottom: 2px solid #e2e8f0;">Socio</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.8rem; border-bottom: 2px solid #e2e8f0;">Carnet</th>
                    <th style="padding: 0.75rem 1rem; text-align: left; background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.8rem; border-bottom: 2px solid #e2e8f0;">Plan</th>
                    <th style="padding: 0.75rem 1rem; text-align: center; background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.8rem; border-bottom: 2px solid #e2e8f0;">Estado</th>
                    <th style="padding: 0.75rem 1rem; text-align: center; background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.8rem; border-bottom: 2px solid #e2e8f0;">Membresía</th>
                    <th style="padding: 0.75rem 1rem; text-align: center; background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.8rem; border-bottom: 2px solid #e2e8f0;">Vence</th>
                    <th style="padding: 0.75rem 1rem; text-align: center; background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.8rem; border-bottom: 2px solid #e2e8f0;">Strikes</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="s in sociosFiltrados" :key="s.carnetSocio" style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" @mouseenter="s._hover = true" @mouseleave="s._hover = false">
                    <td style="padding: 0.65rem 1rem;">
                        <a :href="'{{ url("recepcionista/socios") }}?s=' + s.carnetSocio" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: inherit;">
                            <div style="width: 36px; height: 36px; border-radius: 0.4rem; overflow: hidden; background: #e2e8f0; flex-shrink: 0;">
                                <img :src="'/storage/' + (s.fotografiaUrl || 'fotos_socios/default.jpeg')" alt="" style="width: 100%; height: 100%; object-fit: cover; object-position: top;" @@error="$el.style.display='none'">
                            </div>
                            <div>
                                <div style="font-weight: 600; color: #0f172a; font-size: 0.85rem;">@{{ s.nombre1 }} @{{ s.apellido1 }}</div>
                                <div style="font-size: 0.75rem; color: #94a3b8;">@{{ s.correo }}</div>
                            </div>
                        </a>
                    </td>
                    <td style="padding: 0.65rem 1rem; font-family: monospace; font-size: 0.85rem; color: #64748b;">@{{ s.carnetSocio }}</td>
                    <td style="padding: 0.65rem 1rem; font-size: 0.85rem; color: #0f172a;">@{{ s.membresiaPlan || '—' }}</td>
                    <td style="padding: 0.65rem 1rem; text-align: center;">
                        <span :style="{
                            background: s.estadoSocio === 'Activo' ? '#d1fae5' : '#fef3c7',
                            color: s.estadoSocio === 'Activo' ? '#065f46' : '#92400e',
                            padding: '2px 10px', borderRadius: '999px', fontSize: '0.75rem', fontWeight: 600
                        }">@{{ s.estadoSocio }}</span>
                    </td>
                    <td style="padding: 0.65rem 1rem; text-align: center;">
                        <span :style="{
                            background: estadoMembresiaReal(s).fondo,
                            color: estadoMembresiaReal(s).color,
                            padding: '2px 8px', borderRadius: '999px', fontSize: '0.75rem', fontWeight: 600
                        }">@{{ estadoMembresiaReal(s).texto }}</span>
                    </td>
                    <td style="padding: 0.65rem 1rem; text-align: center; font-size: 0.85rem;">
                        <span v-if="s.membresiaFin" :style="{ color: diasRestantesFn(s.membresiaFin) < 0 ? '#ef4444' : diasRestantesFn(s.membresiaFin) <= 7 ? '#f59e0b' : '#64748b', fontWeight: diasRestantesFn(s.membresiaFin) <= 7 ? 700 : 400 }">
                            @{{ s.membresiaFin }}
                        </span>
                        <span v-else style="color: #94a3b8;">—</span>
                    </td>
                    <td style="padding: 0.65rem 1rem; text-align: center;">
                        <span v-if="s.strikes > 0" :style="{ color: s.strikes >= 3 ? '#ef4444' : '#f59e0b', fontWeight: 700, fontSize: '0.9rem' }">
                            @{{ '🔴'.repeat(s.strikes) }}@{{ '⚪'.repeat(3 - s.strikes) }}
                        </span>
                        <span v-else style="color: #94a3b8; font-size: 0.75rem;">—</span>
                    </td>
                </tr>
                <tr v-if="sociosFiltrados.length === 0">
                    <td colspan="7" style="padding: 2rem; text-align: center; color: #94a3b8;">
                        No se encontraron socios.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
    table { width: 100%; border-collapse: collapse; }
    th { padding: 0.75rem 1rem; text-align: left; background: #f8fafc; color: #64748b; font-weight: 600; font-size: 0.8rem; border-bottom: 2px solid #e2e8f0; }
    td { padding: 0.65rem 1rem; border-bottom: 1px solid #f1f5f9; }
    tr:hover { background: #f8fafc; }
</style>

<script>
const { createApp, ref, computed, onMounted } = Vue;

createApp({
    setup() {
        const socios = ref([]);
        const cargando = ref(true);
        const filtro = ref('');

        const sociosFiltrados = computed(() => {
            if (!filtro.value.trim()) return socios.value;
            const f = filtro.value.toLowerCase();
            return socios.value.filter(s =>
                (s.nombre1 + ' ' + s.nombre2 + ' ' + s.apellido1 + ' ' + s.apellido2).toLowerCase().includes(f) ||
                String(s.carnetSocio).toLowerCase().includes(f) ||
                String(s.correo).toLowerCase().includes(f)
            );
        });

        const diasRestantesFn = (fechaStr) => {
            const hoy = new Date(); hoy.setHours(0,0,0,0);
            const fin = new Date(fechaStr); fin.setHours(0,0,0,0);
            return Math.ceil((fin - hoy) / (1000 * 60 * 60 * 24));
        };

        const estadoMembresiaReal = (socio) => {
            if (!socio.membresiaFin) return { texto: 'Sin membresía', fondo: '#f1f5f9', color: '#64748b' };
            const dias = diasRestantesFn(socio.membresiaFin);
            if (dias < 0) return { texto: 'Vencida', fondo: '#fee2e2', color: '#991b1b' };
            return { texto: socio.membresiaEstado || 'Activa', fondo: '#d1fae5', color: '#065f46' };
        };

        onMounted(async () => {
            try {
                const res = await fetch('{{ route("recepcionista.ingreso.todos") }}');
                socios.value = await res.json();
            } catch (e) {
                console.error('Error al cargar socios:', e);
            } finally {
                cargando.value = false;
            }
        });

        return { socios, cargando, filtro, sociosFiltrados, diasRestantesFn, estadoMembresiaReal };
    }
}).mount('#appDash');
</script>
@endsection

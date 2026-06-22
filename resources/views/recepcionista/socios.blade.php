@extends('layouts.recepcionista')
@section('title', 'Consultar Socios')
@section('content')

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appIngreso">
    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div style="flex: 1; position: relative;">
                <input
                    type="text"
                    v-model="termino"
                    @input="buscar"
                    @keydown.escape="resultados = []"
                    placeholder="Buscar socio por nombre, carnet, correo o teléfono..."
                    style="width: 100%; padding: 0.85rem 1rem; border: 2px solid #e2e8f0; border-radius: 0.5rem; font-size: 1rem; outline: none; transition: border-color 0.2s;"
                    :style="{ borderColor: buscando ? '#10b981' : '#e2e8f0' }"
                >
                <div v-if="resultados.length > 0" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 100; max-height: 320px; overflow-y: auto;">
                    <div
                        v-for="s in resultados"
                        :key="s.carnetSocio"
                        @click="seleccionarSocio(s)"
                        class="resultado-item"
                        :class="{ 'seleccionado': socioSeleccionado?.carnetSocio === s.carnetSocio }"
                    >
                        <div style="width: 40px; height: 40px; border-radius: 0.4rem; overflow: hidden; background: #e2e8f0; flex-shrink: 0;">
                            <img :src="'/storage/' + (s.fotografiaUrl || 'fotos_socios/default.jpeg')" alt="" style="width: 100%; height: 100%; object-fit: cover;" @@error="$el.style.display='none'">
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; color: #0f172a; font-size: 0.9rem;">@{{ s.nombre1 }} @{{ s.apellido1 }}</div>
                            <div style="font-size: 0.8rem; color: #64748b;">Carnet: @{{ s.carnetSocio }} · @{{ s.correo }}</div>
                        </div>
                        <div>
                            <span :style="{
                                background: s.estadoSocio === 'Activo' ? '#d1fae5' : '#fef3c7',
                                color: s.estadoSocio === 'Activo' ? '#065f46' : '#92400e',
                                padding: '2px 8px', borderRadius: '999px', fontSize: '0.75rem', fontWeight: 600
                            }">@{{ s.estadoSocio }}</span>
                        </div>
                    </div>
                </div>
                <div v-if="sinResultados" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; margin-top: 4px; padding: 1rem; text-align: center; color: #94a3b8; font-size: 0.9rem;">
                    No se encontraron socios.
                </div>
            </div>
            <div v-if="socioSeleccionado" style="display: flex; gap: 0.5rem;">
                <button @click="registrarAcceso" class="btn" :class="accesoRegistrado ? 'btn-success' : 'btn-primary'" style="font-size: 1rem; padding: 0.85rem 1.5rem;" :disabled="procesando">
                    <span v-if="procesando">⏳</span>
                    <span v-else-if="accesoRegistrado">✅</span>
                    <span v-else>🔑</span>
                    @{{ procesando ? 'Procesando...' : accesoRegistrado ? 'Ingreso Registrado' : 'Registrar Ingreso' }}
                </button>
                <button @click="limpiarSeleccion" class="btn btn-danger" style="padding: 0.85rem 1rem;">✕</button>
            </div>
        </div>
    </div>

    <div v-if="mensaje" class="alert" :class="mensajeTipo === 'error' ? 'alert-danger' : 'alert-success'" style="display: flex; justify-content: space-between; align-items: center;">
        <span>@{{ mensaje }}</span>
        <button @click="mensaje = ''" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: inherit;">&times;</button>
    </div>

    <div v-if="socioSeleccionado" class="card" style="padding: 0;">
        <div style="display: grid; grid-template-columns: 280px 1fr; gap: 0;">
            <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 2rem; border-radius: 0.75rem 0 0 0.75rem; color: white; text-align: center;">
                <div style="width: 140px; height: 140px; border-radius: 0.75rem; overflow: hidden; margin: 0 auto 1rem; border: 3px solid #10b981; background: #f8fafc;">
                    <img :src="'/storage/' + (socioSeleccionado.fotografiaUrl || 'fotos_socios/default.jpeg')" alt="" style="width: 100%; height: 100%; object-fit: cover; object-position: top;" @@error="$el.style.display='none'">
                </div>
                <h3 style="font-size: 1.2rem; margin-bottom: 0.25rem;">@{{ socioSeleccionado.nombre1 }} @{{ socioSeleccionado.apellido1 }}</h3>
                <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 0.5rem;">@{{ socioSeleccionado.correo }}</p>
                <div style="font-family: monospace; font-size: 1.1rem; color: #10b981; font-weight: 700; margin-bottom: 1rem;">
                    Carnet: @{{ socioSeleccionado.carnetSocio }}
                </div>
                <div>
                    <span :style="{
                        background: socioSeleccionado.estadoSocio === 'Activo' ? '#065f46' : '#78350f',
                        color: 'white',
                        padding: '4px 12px', borderRadius: '999px', fontSize: '0.85rem', fontWeight: 600
                    }">@{{ socioSeleccionado.estadoSocio }}</span>
                </div>
                <div v-if="socioSeleccionado.telefono" style="margin-top: 1rem; color: #94a3b8; font-size: 0.85rem;">
                    📞 @{{ socioSeleccionado.telefono }}
                </div>
            </div>

            <div style="padding: 2rem;">
                <div v-if="cargandoDetalle" style="text-align: center; padding: 2rem; color: #94a3b8;">
                    Cargando detalles...
                </div>
                <template v-else>
                    <div v-if="detalle?.membresia" style="margin-bottom: 1.5rem;">
                        <div class="section-title">Membresía</div>
                        <div class="info-grid">
                            <div class="info-row">
                                <span class="label">Plan</span>
                                <span class="value">@{{ detalle.membresia.nombrePlan }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Costo</span>
                                <span class="value">Bs. @{{ detalle.membresia.costoPlan }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Inicio</span>
                                <span class="value">@{{ detalle.membresia.fechaInicioMembresia }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Vencimiento</span>
                                <span class="value">@{{ detalle.membresia.fechaFinMembresia }}</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Estado</span>
                                <span class="value">
                                    <span :class="'badge ' + estadoMembresiaClass">@{{ estadoMembresiaTexto }}</span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="label">Días restantes</span>
                                <span class="value" :style="{ color: diasRestantes < 0 ? '#ef4444' : diasRestantes < 7 ? '#f59e0b' : '#10b981', fontWeight: 700 }">
                                    @{{ diasRestantes < 0 ? 'Vencida' : diasRestantes + ' día(s)' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else style="margin-bottom: 1.5rem;">
                        <div class="section-title" style="color: #ef4444;">Membresía</div>
                        <p style="color: #ef4444; font-weight: 600;">Sin membresía activa registrada.</p>
                    </div>

                    <div v-if="tieneAlertas" style="margin-bottom: 1.5rem;">
                        <div class="section-title" style="color: #dc2626;">
                            ⚠️ Alertas y Observaciones
                        </div>
                        <div v-if="socioSeleccionado.observacionesMedicas" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem; padding: 0.75rem 1rem; margin-bottom: 0.5rem;">
                            <div style="font-weight: 600; color: #991b1b; font-size: 0.85rem; margin-bottom: 0.25rem;">Observaciones Médicas</div>
                            <div style="color: #7f1d1d; font-size: 0.9rem;">@{{ socioSeleccionado.observacionesMedicas }}</div>
                        </div>
                        <div v-if="socioSeleccionado.strikes > 0" style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 0.5rem; padding: 0.75rem 1rem;">
                            <div style="font-weight: 600; color: #9a3412; font-size: 0.85rem; margin-bottom: 0.25rem;">Strikes</div>
                            <div style="display: flex; gap: 4px;">
                                <span v-for="i in 3" :key="i" style="font-size: 1.2rem;">@{{ i <= socioSeleccionado.strikes ? '🔴' : '⚪' }}</span>
                                <span style="color: #9a3412; font-size: 0.85rem; margin-left: 0.5rem;">@{{ socioSeleccionado.strikes }}/3 — @{{ socioSeleccionado.strikes >= 3 ? 'SUSPENDIDO' : (3 - socioSeleccionado.strikes) + ' strike(s) para suspensión' }}</span>
                            </div>
                        </div>
                        <div v-if="detalle?.penalizacionesActivas > 0" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem; padding: 0.75rem 1rem; margin-top: 0.5rem;">
                            <div style="font-weight: 600; color: #991b1b; font-size: 0.85rem;">🚫 Penalización activa — Acceso suspendido</div>
                        </div>
                    </div>

                    <div v-if="detalle?.ultimosAccesos?.length > 0">
                        <div class="section-title">Últimos Accesos</div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                    <th>Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="a in detalle.ultimosAccesos" :key="a.idControlAcceso">
                                    <td>@{{ a.fechaAcceso }}</td>
                                    <td>@{{ a.horaAcceso?.substring(0, 5) }}</td>
                                    <td>
                                        <span :class="'badge ' + (a.bloqueo ? 'badge-danger' : 'badge-success')">
                                            @{{ a.bloqueo ? 'Denegado' : 'Ingresó' }}
                                        </span>
                                    </td>
                                    <td style="color: #64748b; font-size: 0.85rem;">@{{ a.motivoDenegacion || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="detalle?.reservasPendientes > 0" style="margin-top: 1rem;">
                        <div class="section-title">Reservas Pendientes</div>
                        <p style="color: #0f172a; font-size: 0.9rem;">Tiene @{{ detalle.reservasPendientes }} reserva(s) activa(s) para hoy.</p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <div v-else>
        <div class="card" style="text-align: center; padding: 3rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
            <h3 style="color: #0f172a; margin-bottom: 0.5rem;">Buscar Socios</h3>
            <p style="color: #64748b;">Busque un socio por nombre, carnet, correo o teléfono para consultar su perfil y registrar su ingreso.</p>
            <div style="display: flex; justify-content: center; gap: 2rem; margin-top: 1.5rem; color: #94a3b8; font-size: 0.85rem;">
                <div>👤 Por nombre completo</div>
                <div>🆔 Por número de carnet</div>
                <div>📧 Por correo electrónico</div>
            </div>
        </div>
    </div>
</div>

<style>
    .section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.75rem;
        padding-bottom: 0.4rem;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 1rem;
    }
    .info-row {
        display: flex;
        padding: 0.4rem 0;
        border-bottom: 1px solid #f8fafc;
    }
    .info-row .label {
        width: 120px;
        font-weight: 600;
        color: #64748b;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
    .info-row .value {
        flex: 1;
        color: #0f172a;
        font-size: 0.85rem;
    }
    .badge {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-success { background: #d1fae5; color: #065f46; }
    .badge-warning { background: #fef3c7; color: #92400e; }
    .badge-danger { background: #fee2e2; color: #991b1b; }
    .badge-info { background: #dbeafe; color: #1e40af; }
    .btn-success { background: #10b981; color: white; }
    .btn-success:hover { background: #059669; }
    table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    th { text-align: left; padding: 0.6rem 0.75rem; background: #f8fafc; color: #64748b; font-weight: 600; border-bottom: 2px solid #e2e8f0; }
    td { padding: 0.6rem 0.75rem; border-bottom: 1px solid #f1f5f9; color: #0f172a; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .resultado-item:hover { background: #f8fafc !important; }
    .resultado-item.seleccionado { background: #f0fdf4; }
</style>

<script>
const { createApp, ref, computed, onMounted } = Vue;

createApp({
    setup() {
        const termino = ref('');
        const resultados = ref([]);
        const sinResultados = ref(false);
        const hoverSocio = ref(null);
        const socioSeleccionado = ref(null);
        const detalle = ref(null);
        const cargandoDetalle = ref(false);
        const procesando = ref(false);
        const accesoRegistrado = ref(false);
        const mensaje = ref('');
        const mensajeTipo = ref('success');
        let timeoutId = null;

        const buscando = computed(() => termino.value.length > 0);

        const estadoMembresiaClass = computed(() => {
            if (!detalle.value?.membresia) return 'badge-danger';
            const m = detalle.value.membresia;
            const hoy = new Date();
            const fin = new Date(m.fechaFinMembresia);
            const inicio = new Date(m.fechaInicioMembresia);
            if (m.estadoMembresia === 'Activa' && fin >= hoy && inicio <= hoy) return 'badge-success';
            if (fin < hoy) return 'badge-danger';
            return 'badge-warning';
        });

        const estadoMembresiaTexto = computed(() => {
            if (!detalle.value?.membresia) return 'Sin membresía';
            const m = detalle.value.membresia;
            const hoy = new Date();
            const fin = new Date(m.fechaFinMembresia);
            const inicio = new Date(m.fechaInicioMembresia);
            if (m.estadoMembresia === 'Activa' && fin >= hoy && inicio <= hoy) return 'Vigente';
            if (fin < hoy) return 'Vencida';
            return m.estadoMembresia;
        });

        const diasRestantes = computed(() => {
            if (!detalle.value?.membresia) return -1;
            const hoy = new Date();
            hoy.setHours(0,0,0,0);
            const fin = new Date(detalle.value.membresia.fechaFinMembresia);
            fin.setHours(0,0,0,0);
            return Math.ceil((fin - hoy) / (1000 * 60 * 60 * 24));
        });

        const tieneAlertas = computed(() => {
            if (!socioSeleccionado.value) return false;
            return socioSeleccionado.value.observacionesMedicas
                || socioSeleccionado.value.strikes > 0
                || detalle.value?.penalizacionesActivas > 0;
        });

        const buscar = () => {
            sinResultados.value = false;
            if (timeoutId) clearTimeout(timeoutId);
            if (termino.value.length < 1) {
                resultados.value = [];
                sinResultados.value = false;
                return;
            }
            timeoutId = setTimeout(async () => {
                try {
                    const res = await fetch('{{ route("recepcionista.ingreso.buscar") }}?q=' + encodeURIComponent(termino.value));
                    const data = await res.json();
                    resultados.value = data;
                    sinResultados.value = data.length === 0;
                } catch (e) {
                    console.error('Error en búsqueda:', e);
                }
            }, 300);
        };

        const seleccionarSocio = async (socio) => {
            socioSeleccionado.value = socio;
            resultados.value = [];
            sinResultados.value = false;
            accesoRegistrado.value = false;
            mensaje.value = '';
            await cargarDetalle(socio.carnetSocio);
        };

        const cargarDetalle = async (carnet) => {
            cargandoDetalle.value = true;
            try {
                const res = await fetch('{{ route("recepcionista.ingreso.detalle", ["carnet" => ":carnet"]) }}'.replace(':carnet', carnet));
                const data = await res.json();
                detalle.value = data;
            } catch (e) {
                console.error('Error cargando detalle:', e);
            } finally {
                cargandoDetalle.value = false;
            }
        };

        const registrarAcceso = async () => {
            if (!socioSeleccionado.value) return;
            procesando.value = true;
            mensaje.value = '';
            try {
                const res = await fetch('{{ route("recepcionista.ingreso.registrar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ carnetSocio: socioSeleccionado.value.carnetSocio })
                });
                const data = await res.json();
                mensaje.value = data.message;
                mensajeTipo.value = data.bloqueo ? 'error' : 'success';
                if (data.success) {
                    accesoRegistrado.value = true;
                    await cargarDetalle(socioSeleccionado.value.carnetSocio);
                }
            } catch (e) {
                mensaje.value = 'Error al registrar acceso.';
                mensajeTipo.value = 'error';
            } finally {
                procesando.value = false;
            }
        };

        const autoCargarPorParametro = async () => {
            const params = new URLSearchParams(window.location.search);
            const carnet = params.get('s');
            if (carnet) {
                termino.value = carnet;
                try {
                    const res = await fetch('{{ route("recepcionista.ingreso.buscar") }}?q=' + encodeURIComponent(carnet));
                    const data = await res.json();
                    if (data.length > 0) {
                        await seleccionarSocio(data[0]);
                    }
                } catch (e) {
                    console.error('Error auto-cargando socio:', e);
                }
            }
        };

        onMounted(autoCargarPorParametro);

        const limpiarSeleccion = () => {
            socioSeleccionado.value = null;
            detalle.value = null;
            accesoRegistrado.value = false;
            mensaje.value = '';
            termino.value = '';
            resultados.value = [];
        };

        return {
            termino, resultados, sinResultados, socioSeleccionado, detalle,
            cargandoDetalle, procesando, accesoRegistrado, mensaje, mensajeTipo,
            buscando, estadoMembresiaClass, estadoMembresiaTexto, diasRestantes, tieneAlertas,
            buscar, seleccionarSocio, cargarDetalle, registrarAcceso, limpiarSeleccion, hoverSocio
        };
    }
}).mount('#appIngreso');
</script>
@endsection

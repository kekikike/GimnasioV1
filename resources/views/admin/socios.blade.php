@extends('layouts.admin')
@section('title', 'Gestion de Socios')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
    window.listaSucursales = @json($sucursales);
</script>

<div id="appSocios">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
        <h3 style="color: #1e293b; margin: 0; font-size: 1.15rem;">Registrar Nuevo Socio y Membresia</h3>
        <a href="{{ route('admin.planes.index') }}" class="btn btn-warning" style="font-size:0.85rem; display:inline-flex; align-items:center; gap:6px;">
            <svg fill="none" stroke="currentColor" width="16" height="16" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Configurar Planes
        </a>
    </div>

    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <form @submit.prevent="guardarSocio" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: end;">
            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; color: #3b82f6; font-weight: 600; font-size: 0.9rem;">Datos Personales y de Contacto</div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Nombre(s) <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.nombre1" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Apellidos <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.apellido1" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Correo Electronico <span style="color:#ef4444;">*</span></label>
                <input type="email" v-model="formulario.correo" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Telefono (WhatsApp) <span style="color:#ef4444;">*</span></label>
                <input type="number" v-model="formulario.telefono" class="form-control" required>
            </div>
            <div style="grid-column: span 2;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Direccion Exacta</label>
                <input type="text" v-model="formulario.direccion" class="form-control" required>
            </div>

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #3b82f6; font-weight: 600; font-size: 0.9rem;">Informacion de Emergencia y Acceso</div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Nombre Cont. Emergencia</label>
                <input type="text" v-model="formulario.nombreContactoEmergencia" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Telf. Cont. Emergencia</label>
                <input type="number" v-model="formulario.telefonoContactoEmergencia" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Contrasena (Portal Socio) <span style="color:#ef4444;">*</span></label>
                <input type="password" v-model="formulario.contrasena" class="form-control" required>
            </div>

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #10b981; font-weight: 600; font-size: 0.9rem;">Configuracion de Membresia Inicial</div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Sucursal de Registro</label>
                <select v-model="formulario.idSucursal" class="form-control" required>
                    <option value="" disabled>Seleccione sede...</option>
                    <option v-for="suc in sucursales" :key="suc.idSucursal" :value="suc.idSucursal">@{{ suc.nombre }}</option>
                </select>
            </div>
            <div style="grid-column: span 2;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Plan Inicial</label>
                <select v-model="formulario.idPlan" class="form-control" required>
                    <option value="" disabled>Seleccione un plan...</option>
                    <option v-for="plan in planes" :key="plan.idPlan" :value="plan.idPlan">
                        @{{ plan.nombrePlan }} - Bs. @{{ plan.costoPlan }} (@{{ plan.duracionDias }} Dias)
                    </option>
                </select>
            </div>

            <div style="grid-column: span 3; display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    Registrar Socio y Generar Codigo
                </button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">Listado de Socios</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Socio y Estado</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Contacto</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="socio in socios" :key="socio.carnetSocio" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px;">
                        <strong>@{{ socio.nombre1 }} @{{ socio.apellido1 }}</strong> <br>
                        <span :style="{ backgroundColor: socio.estadoSocio === 'Activo' ? '#dcfce3' : '#fef08a', color: socio.estadoSocio === 'Activo' ? '#166534' : '#854d0e', padding: '2px 6px', borderRadius: '4px', fontSize: '0.8em', fontWeight: 'bold' }">
                            @{{ socio.estadoSocio }}
                        </span>
                    </td>
                    <td style="padding: 12px;">@{{ socio.correo }}<br><small style="color:#64748b;">Cel: @{{ socio.telefono }}</small></td>
                    <td style="padding: 12px; text-align: center;">
                        <div class="action-group" style="justify-content:center;">
                            <button @click="congelarSocio(socio)" :class="socio.estadoSocio === 'Activo' ? 'btn btn-warning btn-sm' : 'btn btn-success btn-sm'" style="margin-right: 5px;">
                                <template v-if="socio.estadoSocio === 'Activo'">Congelar</template>
                                <template v-else>Activar</template>
                            </button>
                            <button @click="eliminarSocio(socio.carnetSocio)" class="btn btn-danger btn-sm">
                                <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Dar de Baja
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="socios.length === 0">
                    <td colspan="4" style="text-align: center; padding: 20px; color: #64748b;">No hay socios registrados.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    const { createApp, ref, onMounted } = Vue;

    createApp({
        setup() {
            const socios = ref([]);
            const planes = ref([]);
            const sucursales = ref(window.listaSucursales || []);
            const guardando = ref(false);

            const formulario = ref({
                nombre1: '', apellido1: '', correo: '', telefono: '', direccion: '',
                nombreContactoEmergencia: '', telefonoContactoEmergencia: '', contrasena: '', idPlan: '', idSucursal: ''
            });

            const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' };

            const cargarSocios = async () => {
                const res = await fetch('{{ route("admin.socios.listar") }}');
                socios.value = await res.json();
            };

            const cargarPlanes = async () => {
                const res = await fetch('{{ route("admin.planes.listar") }}');
                planes.value = await res.json();
            };

            const guardarSocio = async () => {
                guardando.value = true;
                try {
                    const res = await fetch(`/admin/socios`, { method: 'POST', headers: headers, body: JSON.stringify(formulario.value) });
                    const data = await res.json();
                    if(data.success) {
                        formulario.value.nombre1 = ''; formulario.value.apellido1 = ''; formulario.value.correo = '';
                        formulario.value.telefono = ''; formulario.value.direccion = ''; formulario.value.contrasena = '';
                        formulario.value.nombreContactoEmergencia = ''; formulario.value.telefonoContactoEmergencia = '';
                        cargarSocios();
                    }
                } catch(e) {
                    console.error("Error guardando socio:", e);
                } finally {
                    guardando.value = false;
                }
            };

            const congelarSocio = async (socio) => {
                const accion = socio.estadoSocio === 'Activo' ? 'congelar' : 'activar';
                if(confirm(`Esta accion cambiara el estado del socio a "${socio.estadoSocio === 'Activo' ? 'Congelado' : 'Activo'}". Continuar?`)) {
                    const res = await fetch(`/admin/socios/${socio.carnetSocio}/congelar`, { method: 'PATCH', headers: headers });
                    const data = await res.json();
                    if(data.success) cargarSocios();
                }
            };

            const eliminarSocio = async (id) => {
                if(confirm("Esta accion dara de baja permanentemente al socio. Continuar?")) {
                    const res = await fetch(`/admin/socios/${id}`, { method: 'DELETE', headers: headers });
                    const data = await res.json();
                    if(data.success) cargarSocios();
                }
            };

            onMounted(() => { cargarSocios(); cargarPlanes(); });

            return { socios, planes, sucursales, formulario, guardando, guardarSocio, congelarSocio, eliminarSocio };
        }
    }).mount('#appSocios');
</script>
@endsection

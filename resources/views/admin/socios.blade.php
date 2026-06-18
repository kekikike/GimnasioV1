@extends('layouts.admin')
@section('title', 'Gestión de Socios')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
    window.listaSucursales = @json($sucursales);
</script>

<div id="appSocios">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
        <h3 style="color: #1e293b; margin: 0;">🏋️ Registrar Nuevo Socio y Membresía</h3>
        <a href="{{ route('admin.planes.index') }}" class="btn btn-warning" style="background-color: #f59e0b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.9em; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s;">
            ⚙️ Configurar Planes (RF-12)
        </a>
    </div>
    
    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <form @submit.prevent="guardarSocio" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: end;">
            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; color: #3b82f6; font-weight: bold;">Datos Personales y de Contacto</div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Nombre(s) *</label>
                <input type="text" v-model="formulario.nombre1" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Apellidos *</label>
                <input type="text" v-model="formulario.apellido1" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Correo Electrónico *</label>
                <input type="email" v-model="formulario.correo" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Teléfono (WhatsApp) *</label>
                <input type="number" v-model="formulario.telefono" class="form-control" required>
            </div>
            <div style="grid-column: span 2;">
                <label style="font-weight: bold; font-size: 0.9em;">Dirección Exacta</label>
                <input type="text" v-model="formulario.direccion" class="form-control" required>
            </div>

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #3b82f6; font-weight: bold;">Información de Emergencia y Acceso</div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Nombre Cont. Emergencia</label>
                <input type="text" v-model="formulario.nombreContactoEmergencia" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Telf. Cont. Emergencia</label>
                <input type="number" v-model="formulario.telefonoContactoEmergencia" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Contraseña (Portal Socio)</label>
                <input type="password" v-model="formulario.contrasena" class="form-control" required>
            </div>

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #10b981; font-weight: bold;">Configuración de Membresía Inicial</div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Sucursal de Registro</label>
                <select v-model="formulario.idSucursal" class="form-control" required>
                    <option value="" disabled>Seleccione sede...</option>
                    <option v-for="suc in sucursales" :key="suc.idSucursal" :value="suc.idSucursal">@{{ suc.nombre }}</option>
                </select>
            </div>
            <div style="grid-column: span 2;">
                <label style="font-weight: bold; font-size: 0.9em;">Plan Inicial</label>
                <select v-model="formulario.idPlan" class="form-control" required>
                    <option value="" disabled>Seleccione un plan...</option>
                    <option v-for="plan in planes" :key="plan.idPlan" :value="plan.idPlan">
                        @{{ plan.nombrePlan }} - Bs. @{{ plan.costoPlan }} (@{{ plan.duracionDias }} Días)
                    </option>
                </select>
            </div>

            <div style="grid-column: span 3; display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" class="btn btn-primary">🔐 Registrar Socio y Generar Código</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">📋 Listado de Socios Activos y Congelados</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Acceso</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Socio y Estado</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Contacto</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones (RF-14)</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="socio in socios" :key="socio.carnetSocio" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px; font-family: monospace; font-size: 1.2em; color: #059669; font-weight: bold; letter-spacing: 1px;">
                        @{{ socio.codigoAcceso }}
                    </td>
                    <td style="padding: 12px;">
                        <strong>@{{ socio.nombre1 }} @{{ socio.apellido1 }}</strong> <br>
                        <span :style="{ backgroundColor: socio.estadoSocio === 'Activo' ? '#dcfce3' : '#fef08a', color: socio.estadoSocio === 'Activo' ? '#166534' : '#854d0e', padding: '2px 6px', borderRadius: '4px', fontSize: '0.8em', fontWeight: 'bold' }">
                            @{{ socio.estadoSocio }}
                        </span>
                    </td>
                    <td style="padding: 12px;">@{{ socio.correo }}<br><small>Cel: @{{ socio.telefono }}</small></td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="congelarSocio(socio)" :class="socio.estadoSocio === 'Activo' ? 'btn btn-sm btn-warning' : 'btn btn-sm btn-success'" style="margin-right: 5px;">
                            @{{ socio.estadoSocio === 'Activo' ? '❄️ Congelar' : '▶️ Activar' }}
                        </button>
                        <button @click="eliminarSocio(socio.carnetSocio)" class="btn btn-sm btn-danger">🗑️ Baja</button>
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
                const res = await fetch(`/admin/socios`, { method: 'POST', headers: headers, body: JSON.stringify(formulario.value) });
                const data = await res.json();
                alert(data.message); 
                if(data.success) {
                    formulario.value.nombre1 = ''; formulario.value.apellido1 = ''; formulario.value.correo = '';
                    formulario.value.telefono = ''; formulario.value.direccion = ''; formulario.value.contrasena = '';
                    formulario.value.nombreContactoEmergencia = ''; formulario.value.telefonoContactoEmergencia = '';
                    cargarSocios();
                }
            };

            const congelarSocio = async (socio) => {
                const accion = socio.estadoSocio === 'Activo' ? 'congelar' : 'activar';
                if(confirm(`¿Estás seguro de ${accion} a este socio y su membresía? (RF-14)`)) {
                    const res = await fetch(`/admin/socios/${socio.carnetSocio}/congelar`, { method: 'PATCH', headers: headers });
                    const data = await res.json();
                    alert(data.message);
                    if(data.success) cargarSocios();
                }
            };

            const eliminarSocio = async (id) => {
                if(confirm("¿Confirmar baja permanente de este socio?")) {
                    const res = await fetch(`/admin/socios/${id}`, { method: 'DELETE', headers: headers });
                    const data = await res.json();
                    if(data.success) cargarSocios();
                }
            };

            onMounted(() => { cargarSocios(); cargarPlanes(); });

            return { socios, planes, sucursales, formulario, guardarSocio, congelarSocio, eliminarSocio };
        }
    }).mount('#appSocios');
</script>
@endsection
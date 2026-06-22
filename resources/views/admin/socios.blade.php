@extends('layouts.admin')
@section('title', 'Gestión de Socios')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<script>
    window.listaSucursales = @json($sucursales);
</script>

<div id="appSocios">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
        <h3 style="color: #1e293b; margin: 0;">
            <template v-if="modoEdicion">✏️ Editar Perfil de Socio</template>
            <template v-else>🏋️ Registrar Nuevo Socio y Membresía</template>
        </h3>
        <a href="{{ route('admin.planes.index') }}" class="btn btn-warning" style="background-color: #f59e0b; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 0.9em; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s;">
            ⚙️ Configurar Planes (RF-12)
        </a>
    </div>
    
    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <form @submit.prevent="guardarSocio" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: start;">
            
            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; color: #3b82f6; font-weight: bold;">Datos Personales y de Contacto</div>
            
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Nro. Carnet (CI) <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.carnetSocio" @input="validarNumeros('carnetSocio')" class="form-control" required :disabled="modoEdicion">
                <small v-if="errores.carnetSocio" style="color:#ef4444; font-size: 0.8em;">@{{ errores.carnetSocio }}</small>
            </div>
            <div v-if="!modoEdicion">
                <label style="font-weight: bold; font-size: 0.85rem;">Confirmar CI <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.carnetSocio_confirmation" @input="validarNumeros('carnetSocio_confirmation')" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Foto Perfil (JPG, PNG)</label>
                <input type="file" @change="manejarFoto" class="form-control" accept="image/jpeg, image/png">
                <small v-if="errores.foto" style="color:#ef4444; font-size: 0.8em;">@{{ errores.foto }}</small>
            </div>

            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Nombres <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.nombre1" @input="validarLetras('nombre1')" class="form-control" required>
                <small v-if="errores.nombre1" style="color:#ef4444; font-size: 0.8em;">@{{ errores.nombre1 }}</small>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Apellidos <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.apellido1" @input="validarLetras('apellido1')" class="form-control" required>
                <small v-if="errores.apellido1" style="color:#ef4444; font-size: 0.8em;">@{{ errores.apellido1 }}</small>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Correo Electrónico <span style="color:#ef4444;">*</span></label>
                <input type="email" v-model="formulario.correo" class="form-control" required>
                <small v-if="errores.correo" style="color:#ef4444; font-size: 0.8em;">@{{ errores.correo }}</small>
            </div>

            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Teléfono Móvil <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.telefono" @input="validarNumeros('telefono')" class="form-control" required maxlength="15">
                <small v-if="errores.telefono" style="color:#ef4444; font-size: 0.8em;">@{{ errores.telefono }}</small>
            </div>
            <div style="grid-column: span 2;">
                <label style="font-weight: bold; font-size: 0.85rem;">Dirección Exacta</label>
                <input type="text" v-model="formulario.direccion" class="form-control">
                <small v-if="errores.direccion" style="color:#ef4444; font-size: 0.8em;">@{{ errores.direccion }}</small>
            </div>

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #3b82f6; font-weight: bold;">Información de Emergencia y Acceso</div>
            
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Nombre Cont. Emergencia</label>
                <input type="text" v-model="formulario.contacto_emergencia_nombre" @input="validarLetras('contacto_emergencia_nombre')" class="form-control">
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Telf. Cont. Emergencia</label>
                <input type="text" v-model="formulario.contacto_emergencia_telefono" @input="validarNumeros('contacto_emergencia_telefono')" class="form-control" maxlength="15">
            </div>
            <div></div> <div>
                <label style="font-weight: bold; font-size: 0.85rem;">@{{ modoEdicion ? 'Nueva Contraseña' : 'Contraseña Portal *' }}</label>
                <input type="password" v-model="formulario.contrasena" class="form-control" :required="!modoEdicion">
                <small v-if="errores.contrasena" style="color:#ef4444; font-size: 0.8em;">@{{ errores.contrasena }}</small>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Confirmar Contraseña <span v-if="!modoEdicion" style="color:#ef4444;">*</span></label>
                <input type="password" v-model="formulario.contrasena_confirmation" class="form-control" :required="!modoEdicion || formulario.contrasena !== ''">
            </div>

            <div v-if="!modoEdicion" style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #10b981; font-weight: bold;">Configuración de Membresía Inicial</div>
            
            <div v-if="!modoEdicion">
                <label style="font-weight: bold; font-size: 0.85rem;">Sucursal de Registro</label>
                <select v-model="formulario.idSucursal" class="form-control" required>
                    <option value="" disabled>Seleccione sede...</option>
                    <option v-for="suc in sucursales" :key="suc.idSucursal" :value="suc.idSucursal">@{{ suc.nombre }}</option>
                </select>
            </div>
            <div v-if="!modoEdicion" style="grid-column: span 2;">
                <label style="font-weight: bold; font-size: 0.85rem;">Plan Inicial</label>
                <select v-model="formulario.idPlan" class="form-control" required>
                    <option value="" disabled>Seleccione un plan...</option>
                    <option v-for="plan in planes" :key="plan.idPlan" :value="plan.idPlan">
                        @{{ plan.nombrePlan }} - Bs. @{{ plan.costoPlan }} (@{{ plan.duracionDias }} Días)
                    </option>
                </select>
            </div>

            <div style="grid-column: span 3; display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <template v-if="guardando">⏳ Procesando...</template>
                    <template v-else>@{{ modoEdicion ? '💾 Guardar Cambios' : '🔐 Registrar Socio' }}</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary">❌ Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">📋 Listado de Socios Activos y Congelados</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Acceso y CI</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Socio y Estado</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Contacto</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="socio in socios" :key="socio.carnetSocio" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px; font-family: monospace; font-size: 1.1em; color: #059669; font-weight: bold;">
                        @{{ socio.codigoAcceso }}<br>
                        <small style="color: #64748b; font-size: 0.7em;">CI: @{{ socio.carnetSocio }}</small>
                    </td>
                    <td style="padding: 12px; display: flex; align-items: center; gap: 10px;">
                        <img v-if="socio.foto_url" :src="'/storage/' + socio.foto_url" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0;">
                        <div v-else style="width: 40px; height: 40px; border-radius: 50%; background-color: #cbd5e1; display:flex; align-items:center; justify-content:center; font-size: 1.2rem;">👤</div>
                        <div>
                            <strong>@{{ socio.nombre1 }} @{{ socio.apellido1 }}</strong> <br>
                            <span :style="{ backgroundColor: socio.estadoSocio === 'Activo' ? '#dcfce3' : '#fef08a', color: socio.estadoSocio === 'Activo' ? '#166534' : '#854d0e', padding: '2px 6px', borderRadius: '4px', fontSize: '0.8em', fontWeight: 'bold' }">
                                @{{ socio.estadoSocio }}
                            </span>
                        </div>
                    </td>
                    <td style="padding: 12px; font-size: 0.9em;">
                        ✉️ @{{ socio.correo }}<br>
                        📞 @{{ socio.telefono }}
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarSocio(socio)" class="btn btn-sm btn-info" style="margin-right: 5px;">✏️ Editar</button>
                        <button @click="congelarSocio(socio)" :class="socio.estadoSocio === 'Activo' ? 'btn btn-sm btn-warning' : 'btn btn-sm btn-success'" style="margin-right: 5px;">
                            @{{ socio.estadoSocio === 'Activo' ? '❄️ Congelar' : '▶️ Activar' }}
                        </button>
                    </td>
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
            const modoEdicion = ref(false);
            const guardando = ref(false);
            
            const formBase = {
                carnetSocio: '', carnetSocio_confirmation: '', idUsuario: '', nombre1: '', apellido1: '', 
                correo: '', telefono: '', direccion: '', contacto_emergencia_nombre: '', contacto_emergencia_telefono: '',
                contrasena: '', contrasena_confirmation: '', idPlan: '', idSucursal: ''
            };
            const formulario = ref({ ...formBase });
            const archivoFoto = ref(null);
            const errores = ref({});

            const cargarSocios = async () => {
                const res = await fetch('{{ route("admin.socios.listar") }}');
                socios.value = await res.json();
            };

            const cargarPlanes = async () => {
                const res = await fetch('{{ route("admin.planes.listar") }}');
                planes.value = await res.json();
            };

            const manejarFoto = (event) => {
                archivoFoto.value = event.target.files[0];
            };

            const validarLetras = (campo) => { formulario.value[campo] = formulario.value[campo].replace(/[^a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]/g, ''); };
            const validarNumeros = (campo) => { formulario.value[campo] = formulario.value[campo].replace(/[^0-9]/g, ''); };

            const guardarSocio = async () => {
                guardando.value = true;
                errores.value = {};

                const formData = new FormData();
                for (let key in formulario.value) {
                    if (formulario.value[key] !== null && formulario.value[key] !== '') {
                        formData.append(key, formulario.value[key]);
                    }
                }
                if (archivoFoto.value) {
                    formData.append('foto', archivoFoto.value);
                }

                // Truco de Laravel para enviar archivos por PUT
                if (modoEdicion.value) formData.append('_method', 'PUT');

                try {
                    const url = modoEdicion.value ? `/admin/socios/${formulario.value.carnetSocio}` : `/admin/socios`;
                    
                    const res = await fetch(url, {
                        method: 'POST', // Siempre enviamos POST, Laravel leerá el _method
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                        body: formData
                    });
                    
                    const data = await res.json();

                    if (res.ok && data.success) {
                        alert(data.message);
                        cancelarEdicion();
                        cargarSocios();
                    } else if (res.status === 422) {
                        for (const campo in data.errors) errores.value[campo] = data.errors[campo][0];
                    } else {
                        alert(data.message || 'Error inesperado.');
                    }
                } catch (e) { console.error(e); } finally { guardando.value = false; }
            };

            const editarSocio = (socio) => {
                modoEdicion.value = true;
                errores.value = {};
                archivoFoto.value = null;
                formulario.value = {
                    carnetSocio: socio.carnetSocio,
                    idUsuario: socio.idUsuario,
                    nombre1: socio.nombre1,
                    apellido1: socio.apellido1,
                    correo: socio.correo,
                    telefono: socio.telefono,
                    direccion: socio.direccion || '',
                    contacto_emergencia_nombre: socio.contacto_emergencia_nombre || '',
                    contacto_emergencia_telefono: socio.contacto_emergencia_telefono || '',
                    contrasena: '',
                    contrasena_confirmation: '',
                    idPlan: '',
                    idSucursal: ''
                };
            };

            const cancelarEdicion = () => {
                modoEdicion.value = false;
                errores.value = {};
                archivoFoto.value = null;
                document.querySelector('input[type="file"]').value = ''; // Limpiar el input file visualmente
                formulario.value = { ...formBase };
            };

            const congelarSocio = async (socio) => {
                const accion = socio.estadoSocio === 'Activo' ? 'congelar' : 'activar';
                if(confirm(`¿Estás seguro de ${accion} a este socio?`)) {
                    const res = await fetch(`/admin/socios/${socio.carnetSocio}/congelar`, { 
                        method: 'PATCH', 
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') } 
                    });
                    const data = await res.json();
                    if(data.success) cargarSocios();
                }
            };

            onMounted(() => { cargarSocios(); cargarPlanes(); });

            return { socios, planes, sucursales, formulario, errores, modoEdicion, guardando, manejarFoto, validarLetras, validarNumeros, guardarSocio, editarSocio, cancelarEdicion, congelarSocio };
        }
    }).mount('#appSocios');
</script>
@endsection
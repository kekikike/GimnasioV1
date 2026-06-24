@extends('layouts.admin')
@section('title', 'Gestion de Socios')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<script>
    window.listaSucursales = @json($sucursales);
</script>

<style>
.cropper-container { max-height: 400px; }
#cropperModal { position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:2000; }
#cropperModal .modal-content { background:#fff; border-radius:0.75rem; padding:1.5rem; width:90%; max-width:600px; }
#cropperModal img { max-width:100%; max-height:350px; }
</style>

<div id="appSocios">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
        <h3 style="color: #1e293b; margin: 0;">
            <template v-if="modoEdicion">Editar Perfil de Socio</template>
            <template v-else>Registrar Nuevo Socio</template>
        </h3>
    </div>

    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <form @submit.prevent="guardarSocio" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: start;">

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; color: #3b82f6; font-weight: bold;">Datos Personales y de Contacto</div>

            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Nro. Carnet (CI) <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.carnetSocio" @input="validarCI('carnetSocio')" class="form-control" required :disabled="modoEdicion" maxlength="10">
                <small v-if="errores.carnetSocio" style="color:#ef4444; font-size: 0.8em;">@{{ errores.carnetSocio }}</small>
            </div>
            <div v-if="!modoEdicion">
                <label style="font-weight: bold; font-size: 0.85rem;">Confirmar CI <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.carnetSocio_confirmation" @input="validarCI('carnetSocio_confirmation')" class="form-control" required maxlength="10">
            </div>
            <div v-else></div> <div style="grid-row: span 2;">
                <label style="font-weight: bold; font-size: 0.85rem;">Foto Perfil (JPG, PNG)</label>
                <input type="file" @change="manejarFoto" class="form-control" accept="image/jpeg, image/png" ref="fileInput">
                <small v-if="errores.foto" style="color:#ef4444; font-size: 0.8em;">@{{ errores.foto }}</small>
                <div v-if="fotoPreview && !mostrarCropper" style="margin-top:6px; position:relative; display:inline-block;">
                    <img :src="fotoPreview" style="width:120px; height:90px; object-fit:cover; border-radius:6px; border:1px solid #e2e8f0;">
                    <button type="button" @click="abrirCropper" style="position:absolute; top:4px; right:4px; background:#3b82f6; color:#fff; border:none; border-radius:4px; padding:2px 8px; font-size:0.75rem; cursor:pointer;">Recortar</button>
                </div>
            </div>

            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">@{{ modoEdicion ? 'Nueva Contraseña' : 'Contraseña Portal *' }}</label>
                <input type="password" v-model="formulario.contrasena" class="form-control" :required="!modoEdicion">
                <small v-if="errores.contrasena" style="color:#ef4444; font-size: 0.8em;">@{{ errores.contrasena }}</small>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Confirmar Contraseña <span v-if="!modoEdicion" style="color:#ef4444;">*</span></label>
                <input type="password" v-model="formulario.contrasena_confirmation" class="form-control" :required="!modoEdicion || formulario.contrasena !== ''">
            </div>

            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Nombre 1 <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.nombre1" @input="validarLetras('nombre1')" class="form-control" required>
                <small v-if="errores.nombre1" style="color:#ef4444; font-size: 0.8em;">@{{ errores.nombre1 }}</small>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Nombre 2</label>
                <input type="text" v-model="formulario.nombre2" @input="validarLetras('nombre2')" class="form-control">
                <small v-if="errores.nombre2" style="color:#ef4444; font-size: 0.8em;">@{{ errores.nombre2 }}</small>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Apellido Paterno <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.apellidoPaterno" @input="validarLetras('apellidoPaterno')" class="form-control" required>
                <small v-if="errores.apellidoPaterno" style="color:#ef4444; font-size: 0.8em;">@{{ errores.apellidoPaterno }}</small>
            </div>

            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Apellido Materno</label>
                <input type="text" v-model="formulario.apellidoMaterno" @input="validarLetras('apellidoMaterno')" class="form-control">
                <small v-if="errores.apellidoMaterno" style="color:#ef4444; font-size: 0.8em;">@{{ errores.apellidoMaterno }}</small>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Correo Electrónico <span style="color:#ef4444;">*</span></label>
                <input type="email" v-model="formulario.correo" class="form-control" required>
                <small v-if="errores.correo" style="color:#ef4444; font-size: 0.8em;">@{{ errores.correo }}</small>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Teléfono Móvil <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.telefono" @input="validarTelefono('telefono')" class="form-control" required maxlength="8" placeholder="Ej: 71234567">
                <small v-if="errores.telefono" style="color:#ef4444; font-size: 0.8em;">@{{ errores.telefono }}</small>
                <small style="color:#64748b; font-size:0.75rem;">Debe comenzar con 6 o 7 (7-8 dígitos)</small>
            </div>
            <div style="grid-column: span 3;">
                <label style="font-weight: bold; font-size: 0.85rem;">Dirección Exacta</label>
                <input type="text" v-model="formulario.direccion" class="form-control">
                <small v-if="errores.direccion" style="color:#ef4444; font-size: 0.8em;">@{{ errores.direccion }}</small>
            </div>

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #3b82f6; font-weight: bold;">Información de Emergencia</div>

            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Nombre Cont. Emergencia</label>
                <input type="text" v-model="formulario.contacto_emergencia_nombre" @input="validarLetras('contacto_emergencia_nombre')" class="form-control">
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Telf. Cont. Emergencia</label>
                <input type="text" v-model="formulario.contacto_emergencia_telefono" @input="validarTelefono('contacto_emergencia_telefono')" class="form-control" maxlength="8" placeholder="Ej: 71234567">
                <small v-if="errores.contacto_emergencia_telefono" style="color:#ef4444; font-size: 0.8em;">@{{ errores.contacto_emergencia_telefono }}</small>
            </div>
            <div></div> <div style="grid-column: span 3; display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <template v-if="guardando">Procesando...</template>
                    <template v-else>@{{ modoEdicion ? 'Guardar Cambios' : 'Registrar Socio' }}</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>

    <div v-if="mostrarCropper" id="cropperModal">
        <div class="modal-content">
            <h3 style="margin-bottom:1rem;">Recortar Foto</h3>
            <p style="font-size:0.85rem; color:#64748b; margin-bottom:0.75rem;">Ajusta el recuadro para obtener una foto 4:3.</p>
            <img ref="cropperImage" :src="cropperSrc" style="max-width:100%;">
            <div style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.5rem;">
                <button type="button" @click="cerrarCropper" class="btn" style="background:#64748b;color:#fff;">Cancelar</button>
                <button type="button" @click="confirmarCropper" class="btn btn-primary">Aplicar</button>
            </div>
        </div>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">Listado de Socios Activos y Congelados</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Acceso y CI</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Socio y Estado</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Membresia</th>
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
                        <div v-else style="width: 40px; height: 40px; border-radius: 50%; background-color: #cbd5e1; display:flex; align-items:center; justify-content:center; font-size:1.2rem; color:#64748b;">
                            <span>@</span>
                        </div>
                        <div>
                            <strong>@{{ socio.nombre1 }} @{{ socio.nombre2 ? socio.nombre2 : '' }} @{{ socio.apellido1 }} @{{ socio.apellido2 ? socio.apellido2 : '' }}</strong> <br>
                            <span :style="{ backgroundColor: socio.estadoSocio === 'Activo' ? '#dcfce3' : '#fef08a', color: socio.estadoSocio === 'Activo' ? '#166534' : '#854d0e', padding: '2px 6px', borderRadius: '4px', fontSize: '0.8em', fontWeight: 'bold' }">
                                @{{ socio.estadoSocio }}
                            </span>
                        </div>
                    </td>
                    <td style="padding: 12px; font-size: 0.85em;">
                        <span :style="{ backgroundColor: socio.estadoMembresia === 'Activa' ? '#dcfce3' : '#fee2e2', color: socio.estadoMembresia === 'Activa' ? '#166534' : '#991b1b', padding: '2px 6px', borderRadius: '4px', fontWeight: 'bold' }">
                            @{{ socio.estadoMembresia || '--' }}
                        </span>
                        <small v-if="socio.fechaCongelamiento" style="display:block; color:#64748b; margin-top:4px;">
                            Congelada: @{{ socio.fechaCongelamiento }}
                        </small>
                    </td>
                    <td style="padding: 12px; font-size: 0.9em;">
                        @{{ socio.correo }}<br>
                        @{{ socio.telefono }}
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarSocio(socio)" class="btn btn-sm btn-info" style="margin-right: 5px;">Editar</button>
                        <button @click="congelarMembresia(socio)" :disabled="socio.estadoMembresia !== 'Activa' && socio.estadoMembresia !== 'Congelada'" class="btn btn-sm" :style="{ background: socio.estadoMembresia === 'Congelada' ? '#22c55e' : '#f59e0b', color: '#fff', marginRight: '5px', opacity: socio.estadoMembresia !== 'Activa' && socio.estadoMembresia !== 'Congelada' ? '0.5' : '1' }">
                            @{{ socio.estadoMembresia === 'Congelada' ? 'Activar Memb.' : 'Congelar Memb.' }}
                        </button>
                        <button @click="verNotificaciones(socio)" class="btn btn-sm" style="background:#6366f1; color:#fff;">Notificaciones</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="mostrarFreezeModal" id="cropperModal">
        <div class="modal-content" style="max-width:420px;">
            <h3 style="margin-bottom:0.5rem;">Congelar Membresía</h3>
            <p style="font-size:0.85rem; color:#64748b; margin-bottom:1rem;">
                Socio: <strong>@{{ freezeNombre }}</strong>
            </p>
            <label style="font-weight:bold; font-size:0.85rem;">Fecha de Retorno *</label>
            <input type="date" v-model="freezeFecha" class="form-control" style="margin-top:4px;" :min="manana">
            <small style="color:#64748b; font-size:0.75rem;">Selecciona la fecha en que el socio volverá al gimnasio.</small>
            <div style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.5rem;">
                <button type="button" @click="cerrarFreezeModal" class="btn" style="background:#64748b;color:#fff;">Cancelar</button>
                <button type="button" @click="confirmarFreeze" class="btn btn-primary" :disabled="!freezeFecha">Congelar</button>
            </div>
        </div>
    </div>

    <div v-if="mostrarNotifModal" id="cropperModal">
        <div class="modal-content" style="max-width:700px;">
            <h3 style="margin-bottom:0.5rem;">Notificaciones de @{{ notifSocioNombre }}</h3>
            <p style="font-size:0.85rem; color:#64748b; margin-bottom:1rem;">
                CI: @{{ notifCarnet }} — @{{ notificaciones.length }} registro(s)
            </p>
            <div v-if="notificaciones.length === 0" style="text-align:center; padding:2rem; color:#94a3b8;">
                No hay notificaciones para este socio.
            </div>
            <table v-else style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th style="padding:8px; border-bottom:2px solid #cbd5e1;">Fecha</th>
                        <th style="padding:8px; border-bottom:2px solid #cbd5e1;">Tipo</th>
                        <th style="padding:8px; border-bottom:2px solid #cbd5e1;">Mensaje</th>
                        <th style="padding:8px; border-bottom:2px solid #cbd5e1;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="n in notificaciones" :key="n.idNotificacion" style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:8px; white-space:nowrap;">@{{ n.fechaEnvio }}</td>
                        <td style="padding:8px;">
                            <span :style="{ fontWeight:'bold', color: n.tipoNotificacion === 'Alerta' ? '#dc2626' : n.tipoNotificacion === 'Recordatorio' ? '#d97706' : '#059669' }">
                                @{{ n.tipoNotificacion }}
                            </span>
                        </td>
                        <td style="padding:8px; max-width:280px;">@{{ n.mensaje }}</td>
                        <td style="padding:8px;">
                            <span :style="{ padding:'2px 6px', borderRadius:'4px', fontSize:'0.8em', fontWeight:'bold', backgroundColor: n.estado === 'Enviado' ? '#dcfce3' : '#fef08a', color: n.estado === 'Enviado' ? '#166534' : '#854d0e' }">
                                @{{ n.estado }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="display:flex; justify-content:flex-end; margin-top:1.5rem;">
                <button type="button" @click="cerrarNotifModal" class="btn" style="background:#64748b;color:#fff;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    const { createApp, ref, onMounted, nextTick } = Vue;

    createApp({
        setup() {
            const socios = ref([]);
            const sucursales = ref(window.listaSucursales || []);
            const modoEdicion = ref(false);
            const guardando = ref(false);
            const mostrarCropper = ref(false);
            const cropperSrc = ref('');
            let cropperInstance = null;
            const cropperImage = ref(null);
            const fileInput = ref(null);
            const fotoPreview = ref('');
            let fotoBlob = null;
            const msjError = ref('');

            const formBase = {
                carnetSocio: '', carnetSocio_confirmation: '', idUsuario: '',
                nombre1: '', nombre2: '', apellidoPaterno: '', apellidoMaterno: '',
                correo: '', telefono: '', direccion: '',
                contacto_emergencia_nombre: '', contacto_emergencia_telefono: '',
                contrasena: '', contrasena_confirmation: '', idSucursal: ''
            };
            const formulario = ref({ ...formBase });
            const errores = ref({});

            const cargarSocios = async () => {
                const res = await fetch('{{ route("admin.socios.listar") }}');
                socios.value = await res.json();
            };

            const validarLetras = (campo) => { formulario.value[campo] = formulario.value[campo].replace(/[^a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]/g, ''); };

            const validarCI = (campo) => {
                formulario.value[campo] = formulario.value[campo].replace(/[^0-9]/g, '').slice(0, 10);
            };

            const validarTelefono = (campo) => {
                formulario.value[campo] = formulario.value[campo].replace(/[^0-9]/g, '').slice(0, 8);
            };

            const manejarFoto = (event) => {
                const file = event.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    cropperSrc.value = e.target.result;
                    mostrarCropper.value = true;
                    nextTick(() => {
                        if (cropperImage.value) {
                            if (cropperInstance) cropperInstance.destroy();
                            cropperInstance = new Cropper(cropperImage.value, {
                                aspectRatio: 4 / 3,
                                viewMode: 1,
                                autoCropArea: 1,
                            });
                        }
                    });
                };
                reader.readAsDataURL(file);
            };

            const abrirCropper = () => {
                mostrarCropper.value = true;
                nextTick(() => {
                    if (cropperImage.value) {
                        if (cropperInstance) cropperInstance.destroy();
                        cropperInstance = new Cropper(cropperImage.value, {
                            aspectRatio: 4 / 3,
                            viewMode: 1,
                            autoCropArea: 1,
                        });
                    }
                });
            };

            const cerrarCropper = () => {
                if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
                mostrarCropper.value = false;
                if (fileInput.value) fileInput.value.value = '';
                if (!fotoBlob) fotoPreview.value = '';
            };

            const confirmarCropper = () => {
                if (cropperInstance) {
                    const canvas = cropperInstance.getCroppedCanvas({ width: 400, height: 300 });
                    canvas.toBlob((blob) => {
                        fotoBlob = blob;
                        fotoPreview.value = URL.createObjectURL(blob);
                        cerrarCropper();
                    }, 'image/jpeg', 0.9);
                }
            };

            const guardarSocio = async () => {
                guardando.value = true;
                errores.value = {};

                const formData = new FormData();
                for (let key in formulario.value) {
                    if (formulario.value[key] !== null && formulario.value[key] !== '') {
                        formData.append(key, formulario.value[key]);
                    }
                }
                if (fotoBlob) {
                    formData.append('foto', fotoBlob, 'foto.jpg');
                }

                if (modoEdicion.value) formData.append('_method', 'PUT');

                try {
                    const url = modoEdicion.value ? `/admin/socios/${formulario.value.carnetSocio}` : `/admin/socios`;

                    const res = await fetch(url, {
                        method: 'POST',
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
                fotoBlob = null;
                fotoPreview.value = '';
                formulario.value = {
                    carnetSocio: socio.carnetSocio,
                    idUsuario: socio.idUsuario,
                    nombre1: socio.nombre1 || '', nombre2: socio.nombre2 || '',
                    apellidoPaterno: socio.apellido1 || '',
                    apellidoMaterno: socio.apellido2 || '',
                    correo: socio.correo,
                    telefono: socio.telefono,
                    direccion: socio.direccion || '',
                    contacto_emergencia_nombre: socio.contacto_emergencia_nombre || '',
                    contacto_emergencia_telefono: socio.contacto_emergencia_telefono || '',
                    contrasena: '',
                    contrasena_confirmation: '',
                    idSucursal: ''
                };
                if (socio.foto_url) fotoPreview.value = '/storage/' + socio.foto_url;
            };

            const cancelarEdicion = () => {
                modoEdicion.value = false;
                errores.value = {};
                fotoBlob = null;
                fotoPreview.value = '';
                if (fileInput.value) fileInput.value.value = '';
                formulario.value = { ...formBase };
            };

            const mostrarFreezeModal = ref(false);
            const freezeCarnet = ref('');
            const freezeNombre = ref('');
            const freezeFecha = ref('');
            const manana = ref(new Date(Date.now() + 86400000).toISOString().split('T')[0]);

            const congelarMembresia = async (socio) => {
                if (socio.estadoMembresia === 'Congelada') {
                    if (!confirm(`¿Activar la membresía de ${socio.nombre1} ${socio.apellido1}?`)) return;
                    const res = await fetch(`/admin/socios/${socio.carnetSocio}/activar-membresia`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    });
                    const data = await res.json();
                    alert(data.message);
                    if (data.success) cargarSocios();
                } else {
                    freezeCarnet.value = socio.carnetSocio;
                    freezeNombre.value = `${socio.nombre1} ${socio.apellido1}`;
                    freezeFecha.value = '';
                    mostrarFreezeModal.value = true;
                }
            };

            const cerrarFreezeModal = () => {
                mostrarFreezeModal.value = false;
                freezeFecha.value = '';
            };

            const confirmarFreeze = async () => {
                if (!freezeFecha.value) return;
                const res = await fetch(`/admin/socios/${freezeCarnet.value}/congelar-membresia`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ fechaCongelamiento: freezeFecha.value })
                });
                const data = await res.json();
                alert(data.message);
                cerrarFreezeModal();
                if (data.success) cargarSocios();
            };

            const mostrarNotifModal = ref(false);
            const notificaciones = ref([]);
            const notifCarnet = ref('');
            const notifSocioNombre = ref('');

            const verNotificaciones = async (socio) => {
                notifCarnet.value = socio.carnetSocio;
                notifSocioNombre.value = `${socio.nombre1} ${socio.apellido1}`;
                const url = `/admin/socios/${socio.carnetSocio}/notificaciones`;
                const res = await fetch(url, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                });
                notificaciones.value = await res.json();
                mostrarNotifModal.value = true;
            };

            const cerrarNotifModal = () => {
                mostrarNotifModal.value = false;
                notificaciones.value = [];
            };

            onMounted(() => { cargarSocios(); });

            return { socios, sucursales, formulario, errores, modoEdicion, guardando, mostrarCropper, cropperSrc, cropperImage, fileInput, fotoPreview, msjError, manejarFoto, validarLetras, validarCI, validarTelefono, guardarSocio, editarSocio, cancelarEdicion, congelarMembresia, abrirCropper, cerrarCropper, confirmarCropper, mostrarNotifModal, notificaciones, notifCarnet, notifSocioNombre, verNotificaciones, cerrarNotifModal, mostrarFreezeModal, freezeCarnet, freezeNombre, freezeFecha, manana, cerrarFreezeModal, confirmarFreeze };
        }
    }).mount('#appSocios');
</script>
@endsection
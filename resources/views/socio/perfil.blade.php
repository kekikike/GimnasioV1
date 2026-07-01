@extends('layouts.socio')
@section('title', 'Mi Perfil')
@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<style>
    .perfil-tabs { display:flex; gap:0; margin-bottom:1.5rem; border-bottom:2px solid #e2e8f0; }
    .perfil-tab { padding:0.7rem 1.4rem; font-size:0.9rem; font-weight:600; color:#64748b; background:none; border:none; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all 0.2s; }
    .perfil-tab:hover { color:#0f172a; background:#f8fafc; border-radius:0.5rem 0.5rem 0 0; }
    .perfil-tab.active { color:#3b82f6; border-bottom-color:#3b82f6; }
    .field-group { margin-bottom:1.2rem; }
    .field-group label { display:block; font-weight:600; font-size:0.85rem; color:#1e293b; margin-bottom:0.4rem; }
    .field-group .form-control { padding:0.7rem 0.85rem; font-size:0.9rem; border:1.5px solid #e2e8f0; border-radius:0.5rem; width:100%; box-sizing:border-box; transition:border-color 0.2s,box-shadow 0.2s; background:#fff; }
    .field-group .form-control:focus { border-color:#3b82f6; outline:none; box-shadow:0 0 0 3px rgba(59,130,246,0.15); }
    .field-group .form-control:disabled { background:#f8fafc; color:#94a3b8; border-color:#e2e8f0; cursor:not-allowed; }
    .btn-editar { background:#fff; color:#334155; border:1.5px solid #e2e8f0; padding:0.55rem 1.3rem; border-radius:0.5rem; font-weight:600; font-size:0.85rem; cursor:pointer; transition:all 0.2s; }
    .btn-editar:hover { background:#f1f5f9; border-color:#cbd5e1; }
    .btn-editar.active { background:#3b82f6; color:#fff; border-color:#3b82f6; }
    .btn-editar.active:hover { background:#2563eb; }
    .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    @media (max-width:640px) { .grid-2 { grid-template-columns:1fr; } }
    .password-mask{-webkit-text-security:disc}.password-mask.no-mask{-webkit-text-security:none}
</style>

<script>
    window.socioData = @json($socio ?? null);
</script>

<div id="appPerfil">
    <div class="card" style="padding:30px; max-width:900px; margin:0 auto; background:white; border-radius:12px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1),0 2px 4px -1px rgba(0,0,0,0.06);">

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; border-bottom:2px solid #f1f5f9; padding-bottom:1rem;">
            <h3 style="margin:0; font-size:1.3rem; font-weight:700; color:#0f172a;">Mi Perfil</h3>
            <button class="btn-editar" :class="{active: editando}" @click="toggleEditar">
                <template v-if="editando">Cancelar</template>
                <template v-else>Editar</template>
            </button>
        </div>

        <div class="perfil-tabs">
            <button class="perfil-tab" :class="{active: tabActiva==='datos'}" @click="tabActiva='datos'">Datos Personales</button>
            <button class="perfil-tab" :class="{active: tabActiva==='usuario'}" @click="tabActiva='usuario'">Usuario</button>
        </div>

        <form @submit.prevent="guardarPerfil">
            <div v-show="tabActiva==='datos'">
                <div class="grid-2">
                    <div class="field-group">
                        <label>Nro. Carnet (CI)</label>
                        <input type="text" :value="formulario.carnet" class="form-control" disabled>
                    </div>
                    <div class="field-group">
                        <label>Primer Nombre <span style="color:#ef4444;">*</span></label>
                        <input type="text" v-model="formulario.nombre1" @input="validarLetras('nombre1')" class="form-control" :disabled="!editando" required>
                    </div>
                    <div class="field-group">
                        <label>Segundo Nombre</label>
                        <input type="text" v-model="formulario.nombre2" @input="validarLetras('nombre2')" class="form-control" :disabled="!editando">
                    </div>
                    <div class="field-group">
                        <label>Apellido Paterno <span style="color:#ef4444;">*</span></label>
                        <input type="text" v-model="formulario.apellido1" @input="validarLetras('apellido1')" class="form-control" :disabled="!editando" required>
                    </div>
                    <div class="field-group">
                        <label>Apellido Materno</label>
                        <input type="text" v-model="formulario.apellido2" @input="validarLetras('apellido2')" class="form-control" :disabled="!editando">
                    </div>
                    <div class="field-group">
                        <label>Correo Electronico <span style="color:#ef4444;">*</span></label>
                        <input type="text" v-model="formulario.correo" @input="limpiarError('correo')" class="form-control" :class="{'is-invalid': errores.correo}" :disabled="!editando" required>
                        <small v-if="errores.correo" style="color:#ef4444; font-size:0.8em; display:block; margin-top:4px;">@{{ errores.correo }}</small>
                    </div>
                    <div class="field-group">
                        <label>Telefono Movil <span style="color:#ef4444;">*</span></label>
                        <input type="text" v-model="formulario.telefono" @input="filtrarTelefono" class="form-control" :class="{'is-invalid': errores.telefono}" :disabled="!editando" required maxlength="8" placeholder="Ej: 71234567">
                        <small style="color:#94a3b8; font-size:0.75em; display:block; margin-top:2px;">Debe comenzar con 6 o 7 y tener 7-8 d&iacute;gitos.</small>
                        <small v-if="errores.telefono" style="color:#ef4444; font-size:0.8em; display:block; margin-top:4px;">@{{ errores.telefono }}</small>
                    </div>
                    <div class="field-group">
                        <label>Direccion Exacta</label>
                        <input type="text" v-model="formulario.direccion" class="form-control" :disabled="!editando">
                    </div>
                    <div class="field-group">
                        <label>Nombre Contacto Emergencia</label>
                        <input type="text" v-model="formulario.contacto_emergencia_nombre" @input="validarLetras('contacto_emergencia_nombre')" class="form-control" :disabled="!editando">
                    </div>
                    <div class="field-group">
                        <label>Telefono Contacto Emergencia</label>
                        <input type="text" v-model="formulario.contacto_emergencia_telefono" @input="filtrarContactoTelefono" class="form-control" :disabled="!editando" maxlength="8">
                    </div>
                </div>
                <div class="field-group" style="margin-top:0.5rem;">
                    <label>Observaciones Medicas</label>
                    <textarea class="form-control" rows="2" disabled style="resize:none;background:#f8fafc;color:#b45309;">{{ $socio->observacionesMedicas ?? '—' }}</textarea>
                    <small style="color:#94a3b8; font-size:0.75em;">Solo el administrador puede modificar esta informacion.</small>
                </div>
            </div>

            <div v-show="tabActiva==='usuario'">
                <div class="grid-2">
                    <div class="field-group" style="grid-column:span 2;">
                        <label>Nombre de Usuario</label>
                        <input type="text" :value="formulario.correo" class="form-control" disabled>
                        <small style="color:#64748b; margin-top:0.3rem; display:block;">Tu correo electronico es tu nombre de usuario.</small>
                    </div>
                    <div class="field-group">
                        <label>Nueva Contrasena <small style="color:#64748b;font-weight:normal;">(Dejar en blanco para no cambiar)</small></label>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <input type="text" v-model="formulario.contrasena" class="form-control password-mask" :class="{'no-mask': mostrarPassword}" :disabled="!editando" placeholder="Minimo 8 caracteres" style="flex:1;" autocomplete="off" :readonly="!editando || passReadonly" @focus="if(!editando) return; passReadonly = false">
                            <button type="button" @click="mostrarPassword = !mostrarPassword" style="background:none; border:1px solid #ccc; border-radius:4px; padding:6px 10px; cursor:pointer; line-height:1;" :title="mostrarPassword ? 'Ocultar' : 'Mostrar'" :disabled="!editando">
                                <svg v-if="mostrarPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="field-group">
                        <label>Confirmar Contrasena</label>
                        <input type="text" v-model="formulario.contrasena_confirmation" class="form-control password-mask" :disabled="!editando" :required="formulario.contrasena!==''" placeholder="Repite la contrasena" autocomplete="off" :readonly="!editando || passConfirmReadonly" @focus="if(!editando) return; passConfirmReadonly = false">
                    </div>
                </div>
            </div>

            <div style="margin-top:1.5rem; text-align:right; padding-top:1rem; border-top:1px solid #f1f5f9;" v-show="editando">
                <button type="submit" class="btn btn-primary" :disabled="guardando" style="padding:0.7rem 2rem; font-weight:700; font-size:0.9rem;">
                    <template v-if="guardando">Guardando...</template>
                    <template v-else>Guardar Cambios</template>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const { createApp, ref, reactive } = Vue;

createApp({
    setup() {
        const editando = ref(false);
        const guardando = ref(false);
        const tabActiva = ref('datos');
        const mostrarPassword = ref(false);
        const passReadonly = ref(true);
        const passConfirmReadonly = ref(true);
        const socio = window.socioData || {};

        const formulario = ref({
            carnet: socio.carnetSocio || '—',
            nombre1: socio.nombre1 || '',
            nombre2: socio.nombre2 || '',
            apellido1: socio.apellido1 || '',
            apellido2: socio.apellido2 || '',
            correo: socio.correo || '',
            telefono: String(socio.telefono || ''),
            direccion: socio.direccion || '',
            contacto_emergencia_nombre: socio.nombreContactoEmergencia || '',
            contacto_emergencia_telefono: String(socio.telefonoContactoEmergencia || ''),
            contrasena: '',
            contrasena_confirmation: ''
        });

        const errores = reactive({
            nombre1: '',
            apellido1: '',
            correo: '',
            telefono: '',
            contrasena: '',
            general: ''
        });

        const limpiarError = (campo) => { errores[campo] = ''; };

        const limpiarErrores = () => {
            for (var k in errores) errores[k] = '';
        };

        const toggleEditar = () => {
            editando.value = !editando.value;
            if (editando.value) {
                passReadonly.value = true;
                passConfirmReadonly.value = true;
            } else {
                formulario.value.contrasena = '';
                formulario.value.contrasena_confirmation = '';
                limpiarErrores();
            }
        };

        const validarLetras = (campo) => {
            formulario.value[campo] = formulario.value[campo].replace(/[^a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]/g, '');
            limpiarError(campo);
        };

        const filtrarTelefono = () => {
            formulario.value.telefono = formulario.value.telefono.replace(/[^0-9]/g, '').slice(0, 8);
            limpiarError('telefono');
        };

        const filtrarContactoTelefono = () => {
            formulario.value.contacto_emergencia_telefono = formulario.value.contacto_emergencia_telefono.replace(/[^0-9]/g, '').slice(0, 8);
        };

        const preValidar = () => {
            limpiarErrores();
            var valido = true;

            if (!formulario.value.nombre1.trim()) {
                errores.nombre1 = 'El primer nombre es obligatorio.';
                valido = false;
            }
            if (!formulario.value.apellido1.trim()) {
                errores.apellido1 = 'El apellido paterno es obligatorio.';
                valido = false;
            }

            var correo = formulario.value.correo.trim();
            if (!correo) {
                errores.correo = 'El correo electrónico es obligatorio.';
                valido = false;
            } else if (correo.indexOf('@') === -1 || correo.indexOf('.') === -1) {
                errores.correo = 'Ingrese un correo válido (debe contener @ y .).';
                valido = false;
            }

            var tel = formulario.value.telefono.replace(/[^0-9]/g, '');
            if (!tel) {
                errores.telefono = 'El teléfono es obligatorio.';
                valido = false;
            } else if (tel.length < 7 || tel.length > 8) {
                errores.telefono = 'El teléfono debe tener entre 7 y 8 dígitos.';
                valido = false;
            } else if (tel.charAt(0) !== '6' && tel.charAt(0) !== '7') {
                errores.telefono = 'El teléfono debe comenzar con 6 o 7.';
                valido = false;
            }

            return valido;
        };

        const guardarPerfil = async () => {
            guardando.value = true;
            try {
                if (!preValidar()) {
                    guardando.value = false;
                    return;
                }

                const res = await fetch('/socio/perfil', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(formulario.value)
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    errores.general = '';
                    formulario.value.contrasena = '';
                    formulario.value.contrasena_confirmation = '';
                    editando.value = false;
                    mostrarToast(data.message, 'success');
                    window.location.reload();
                } else if (res.status === 422) {
                    limpiarErrores();
                    for (const campo in data.errors) {
                        if (errores.hasOwnProperty(campo)) errores[campo] = data.errors[campo][0];
                    }
                } else {
                    errores.general = data.message || 'Error inesperado.';
                }
            } catch (e) {
                console.error(e);
                errores.general = 'Error de conexión con el servidor.';
            } finally {
                guardando.value = false;
            }
        };

        return {
            formulario, errores, editando, guardando, tabActiva, mostrarPassword,
            passReadonly, passConfirmReadonly,
            toggleEditar, validarLetras, filtrarTelefono, filtrarContactoTelefono,
            limpiarError, guardarPerfil
        };
    }
}).mount('#appPerfil');
</script>
@endsection

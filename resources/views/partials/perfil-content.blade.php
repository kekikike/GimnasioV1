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
</style>

<script>
    window.perfilData = @json($data ?? null);
    window.perfilUsuario = @json($usuario ?? null);
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
                        <label>Carnet / CI</label>
                        <input type="text" :value="formulario.carnet" class="form-control" disabled>
                    </div>
                    <div class="field-group">
                        <label>Sucursal</label>
                        <input type="text" :value="formulario.sucursal" class="form-control" disabled>
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
                        <input type="email" v-model="formulario.correo" class="form-control" :disabled="!editando" required>
                    </div>
                    <div class="field-group">
                        <label>Telefono <span style="color:#ef4444;">*</span></label>
                        <input type="text" v-model="formulario.telefono" @input="validarTelefono" class="form-control" :disabled="!editando" required maxlength="15">
                    </div>
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
                            <input :type="mostrarPassword ? 'text' : 'password'" v-model="formulario.contrasena" class="form-control" :disabled="!editando" placeholder="Minimo 8 caracteres" style="flex:1;">
                            <button type="button" @click="mostrarPassword = !mostrarPassword" style="background:none; border:1px solid #ccc; border-radius:4px; padding:6px 10px; cursor:pointer; line-height:1;" :title="mostrarPassword ? 'Ocultar' : 'Mostrar'" :disabled="!editando">
                                <svg v-if="mostrarPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="field-group">
                        <label>Confirmar Contrasena</label>
                        <input type="password" v-model="formulario.contrasena_confirmation" class="form-control" :disabled="!editando" :required="formulario.contrasena!==''" placeholder="Repite la contrasena">
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
const { createApp, ref } = Vue;

createApp({
    setup() {
        const editando = ref(false);
        const guardando = ref(false);
        const tabActiva = ref('datos');
        const mostrarPassword = ref(false);
        const pdata = window.perfilData || {};
        const usr = window.perfilUsuario || {};

        const formulario = ref({
            carnet: pdata.carnetEmpleado || '—',
            sucursal: pdata.nombreSucursal || '—',
            nombre1: pdata.nombre1 || usr.nombre1 || '',
            nombre2: pdata.nombre2 || usr.nombre2 || '',
            apellido1: pdata.apellido1 || usr.apellido1 || '',
            apellido2: pdata.apellido2 || usr.apellido2 || '',
            correo: pdata.correo || usr.correo || '',
            telefono: pdata.telefono || usr.telefono || '',
            contrasena: '',
            contrasena_confirmation: ''
        });

        const toggleEditar = () => {
            editando.value = !editando.value;
            if (!editando.value) {
                formulario.value.contrasena = '';
                formulario.value.contrasena_confirmation = '';
            }
        };

        const validarLetras = (campo) => {
            formulario.value[campo] = formulario.value[campo].replace(/[^a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]/g, '');
        };

        const validarTelefono = () => {
            formulario.value.telefono = formulario.value.telefono.replace(/[^0-9]/g, '');
        };

        const guardarPerfil = async () => {
            guardando.value = true;
            try {
                const res = await fetch('/perfil', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        nombre1: formulario.value.nombre1,
                        nombre2: formulario.value.nombre2,
                        apellido1: formulario.value.apellido1,
                        apellido2: formulario.value.apellido2,
                        correo: formulario.value.correo,
                        telefono: formulario.value.telefono,
                        contrasena: formulario.value.contrasena,
                        contrasena_confirmation: formulario.value.contrasena_confirmation
                    })
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    alert(data.message);
                    formulario.value.contrasena = '';
                    formulario.value.contrasena_confirmation = '';
                    editando.value = false;
                    window.location.reload();
                } else if (res.status === 422) {
                    let msgs = [];
                    for (const campo in data.errors) {
                        msgs.push('• ' + data.errors[campo][0]);
                    }
                    alert('Error:\n\n' + msgs.join('\n'));
                } else {
                    alert(data.message || 'Error inesperado');
                }
            } catch (e) {
                console.error(e);
                alert('Error de conexion.');
            } finally {
                guardando.value = false;
            }
        };

        return { formulario, editando, guardando, tabActiva, mostrarPassword, toggleEditar, validarLetras, validarTelefono, guardarPerfil };
    }
}).mount('#appPerfil');
</script>

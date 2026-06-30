@extends('layouts.admin')
@section('title', 'Gestion de Personal')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<style>
    .text-danger { color: #ef4444; }
    .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .alert { padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem; }
    .form-control.is-invalid { border-color: #ef4444; }
</style>

<div id="appPersonal">
    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">
            <template v-if="modoEdicion">Editar Personal</template>
            <template v-else>Registrar Nuevo Empleado</template>
        </h3>

        <div v-if="Object.keys(errores).length > 0" class="alert alert-danger">
            <strong style="display:block;margin-bottom:4px;">Se encontraron errores:</strong>
            <ul style="margin:0;padding-left:1.2rem;">
                <li v-for="(msg, campo) in errores" :key="campo">@{{ msg }}</li>
            </ul>
        </div>

        <form @submit.prevent="guardarEmpleado" novalidate style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: start;">

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; color: #3b82f6; font-weight: 600;">Datos de Usuario (Acceso al Sistema)</div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Primer Nombre <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.nombre1" @input="validarLetras('nombre1')" class="form-control" :class="{ 'is-invalid': errores.nombre1 }" required>
                <small v-if="errores.nombre1" class="text-danger">@{{ errores.nombre1 }}</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Segundo Nombre</label>
                <input type="text" v-model="formulario.nombre2" @input="validarLetras('nombre2')" class="form-control" :class="{ 'is-invalid': errores.nombre2 }">
                <small v-if="errores.nombre2" class="text-danger">@{{ errores.nombre2 }}</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Apellido Paterno <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.apellido1" @input="validarLetras('apellido1')" class="form-control" :class="{ 'is-invalid': errores.apellido1 }" required>
                <small v-if="errores.apellido1" class="text-danger">@{{ errores.apellido1 }}</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Apellido Materno</label>
                <input type="text" v-model="formulario.apellido2" @input="validarLetras('apellido2')" class="form-control" :class="{ 'is-invalid': errores.apellido2 }">
                <small v-if="errores.apellido2" class="text-danger">@{{ errores.apellido2 }}</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Rol Asignado <span style="color:#ef4444;">*</span></label>
                <select v-model="formulario.idRol" class="form-control" :class="{ 'is-invalid': errores.idRol }" required>
                    <option value="" disabled>Seleccione un rol...</option>
                    <option v-for="rol in rolesFiltrados" :key="rol.idRol" :value="rol.idRol">@{{ rol.nombreRol }}</option>
                </select>
                <small v-if="errores.idRol" class="text-danger">@{{ errores.idRol }}</small>
            </div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Correo Electrónico <span style="color:#ef4444;">*</span></label>
                <input type="email" v-model="formulario.correo" class="form-control" :class="{ 'is-invalid': errores.correo }" required>
                <small v-if="errores.correo" class="text-danger">@{{ errores.correo }}</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Teléfono <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.telefono" @input="validarTelefono" class="form-control" :class="{ 'is-invalid': errores.telefono }" required maxlength="8" placeholder="Ej: 71234567">
                <small v-if="errores.telefono" class="text-danger">@{{ errores.telefono }}</small>
                <small v-else style="color:#64748b;font-size:0.75em;">Debe iniciar con 6 o 7 (7-8 dígitos)</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">
                    @{{ modoEdicion ? 'Nueva Contraseña (Opcional)' : 'Contraseña *' }}
                </label>
                <div style="display:flex; align-items:center; gap:4px;">
                    <input :type="mostrarPassword ? 'text' : 'password'" v-model="formulario.contrasena" class="form-control" :class="{ 'is-invalid': errores.contrasena }" :required="!modoEdicion" :placeholder="modoEdicion ? '********' : ''" style="flex:1;">
                    <button type="button" @click="mostrarPassword = !mostrarPassword" style="background:none; border:1px solid #ccc; border-radius:4px; padding:6px 10px; cursor:pointer; line-height:1;" :title="mostrarPassword ? 'Ocultar' : 'Mostrar'">
                        <svg v-if="mostrarPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                <small v-if="errores.contrasena" class="text-danger">@{{ errores.contrasena }}</small>
            </div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Confirmar Contraseña <span v-if="!modoEdicion" style="color:#ef4444;">*</span></label>
                <input type="password" v-model="formulario.contrasena_confirmation" class="form-control" :required="!modoEdicion || formulario.contrasena !== ''">
            </div>

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #3b82f6; font-weight: 600;">Datos Laborales (Recursos Humanos)</div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Nro. Carnet (CI) <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.carnetEmpleado" @input="validarCarnet" class="form-control" :class="{ 'is-invalid': errores.carnetEmpleado }" :disabled="modoEdicion" required maxlength="10" placeholder="Máx. 10 dígitos">
                <small v-if="errores.carnetEmpleado" class="text-danger">@{{ errores.carnetEmpleado }}</small>
            </div>
            <div v-if="!modoEdicion">
                <label style="font-weight: 600; font-size: 0.85rem;">Confirmar Nro. Carnet <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.carnetEmpleado_confirmation" @input="validarCarnetConfirm" class="form-control" maxlength="10" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Sucursal Base <span style="color:#ef4444;">*</span></label>
                <select v-model="formulario.idSucursal" class="form-control" :class="{ 'is-invalid': errores.idSucursal }" required>
                    <option value="" disabled>Seleccione sede...</option>
                    <option v-for="suc in sucursales" :key="suc.idSucursal" :value="suc.idSucursal">@{{ suc.nombre }}</option>
                </select>
                <small v-if="errores.idSucursal" class="text-danger">@{{ errores.idSucursal }}</small>
                <small v-else-if="adminSucursalId" style="color:#64748b;font-size:0.75em;">Sucursal pre-seleccionada según tu perfil.</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Fecha Contrato Inicio <span style="color:#ef4444;">*</span></label>
                <input type="date" v-model="formulario.fechaContratoInicio" class="form-control" :class="{ 'is-invalid': errores.fechaContratoInicio }" required>
                <small v-if="errores.fechaContratoInicio" class="text-danger">@{{ errores.fechaContratoInicio }}</small>
            </div>

            <div style="grid-column: span 3; display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <template v-if="guardando">Guardando...</template>
                    <template v-else>@{{ modoEdicion ? 'Guardar Cambios' : 'Guardar Empleado' }}</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">Listado de Personal Activo</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Identidad y Rol</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Contacto</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Asignación</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="emp in empleados" :key="emp.carnetEmpleado" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px;">
                        <strong>@{{ nombreCompleto(emp) }}</strong><br>
                        <span style="color:#64748b; font-size:0.85em;">CI: @{{ emp.carnetEmpleado }}</span><br>
                        <span class="badge badge-info" style="background-color: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-size: 0.8em;">@{{ emp.nombreRol }}</span>
                    </td>
                    <td style="padding: 12px;">
                        @{{ emp.correo }}<br>
                        <span style="color:#64748b; font-size:0.85em;">@{{ emp.telefono }}</span>
                    </td>
                    <td style="padding: 12px;">
                        @{{ emp.nombreSucursal }}
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarEmpleado(emp)" class="btn btn-sm btn-info" style="margin-right: 3px;">Editar</button>
                        <button @click="confirmarContrato(emp)" class="btn btn-sm btn-warning" style="margin-right: 3px;">Acabar Contrato</button>
                        <button @click="eliminarEmpleado(emp.carnetEmpleado)" class="btn btn-sm btn-danger">Baja</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="card" style="padding: 20px; margin-top: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">Personal Inactivo / Contratos Finalizados</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Identidad y Rol</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Contacto</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Asignacion</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Accion</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="emp in inactivos" :key="emp.carnetEmpleado" style="border-bottom: 1px solid #e2e8f0; opacity: 0.7;">
                    <td style="padding: 12px;">
                        <strong>@{{ nombreCompleto(emp) }}</strong><br>
                        <span style="color:#64748b; font-size:0.85em;">CI: @{{ emp.carnetEmpleado }}</span><br>
                        <span class="badge badge-secondary" style="background-color: #e2e8f0; color: #475569; padding: 2px 6px; border-radius: 4px; font-size: 0.8em;">@{{ emp.nombreRol }}</span><br>
                        <span v-if="emp.fechaContratoFin" style="color:#dc2626; font-size:0.8em;">Fin contrato: @{{ emp.fechaContratoFin }}</span>
                    </td>
                    <td style="padding: 12px;">
                        @{{ emp.correo }}<br>
                        <span style="color:#64748b; font-size:0.85em;">@{{ emp.telefono }}</span>
                    </td>
                    <td style="padding: 12px;">
                        @{{ emp.nombreSucursal }}
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="reactivarEmpleado(emp.carnetEmpleado)" class="btn btn-sm btn-success">Reactivar</button>
                    </td>
                </tr>
                <tr v-if="inactivos.length === 0">
                    <td colspan="4" style="padding: 20px; text-align: center; color: #64748b;">No hay personal inactivo registrado.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    const { createApp, ref, computed, onMounted } = Vue;

    createApp({
        setup() {
            const empleados = ref([]);
            const inactivos = ref([]);
            const roles = ref([]);
            const rolesFiltrados = ref([]);
            const sucursales = ref([]);
            const adminSucursalId = {{ $adminSucursalId ?? 'null' }};
            const modoEdicion = ref(false);
            const idActual = ref(null);
            const guardando = ref(false);
            const mostrarPassword = ref(false);

            // 1. Función inteligente para calcular la fecha de hoy sin problemas de zona horaria
            const obtenerFechaHoy = () => {
                const hoy = new Date();
                const yyyy = hoy.getFullYear();
                const mm = String(hoy.getMonth() + 1).padStart(2, '0');
                const dd = String(hoy.getDate()).padStart(2, '0');
                return `${yyyy}-${mm}-${dd}`;
            };

            // 2. Agregamos el llamado automático a la propiedad fechaContratoInicio
            const formularioBase = {
                carnetEmpleado: '', 
                carnetEmpleado_confirmation: '',
                idRol: '', 
                idSucursal: '',
                nombre1: '', 
                apellidoPaterno: '', 
                correo: '', 
                telefono: '',
                contrasena: '', 
                contrasena_confirmation: '',
                fechaContratoInicio: obtenerFechaHoy() // <--- ¡AQUÍ ESTÁ LA MAGIA!
            };
            
            const formulario = ref({ ...formularioBase });
            
            const errores = ref({});

            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            };

            const cargarEmpleados = async () => {
                const res = await fetch('{{ route("admin.personal.listar") }}');
                empleados.value = await res.json();
                const resInactivos = await fetch('{{ route("admin.personal.listar-inactivos") }}');
                inactivos.value = await resInactivos.json();
            };

            const nombreCompleto = (emp) => {
                const partes = [emp.nombre1, emp.nombre2, emp.apellido1, emp.apellido2].filter(Boolean);
                return partes.join(' ');
            };

            const validarLetras = (campo) => {
                formulario.value[campo] = formulario.value[campo].replace(/[^a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]/g, '');
            };

            const validarTelefono = () => {
                let val = String(formulario.value.telefono || '').replace(/[^0-9]/g, '');
                if (val.length > 0 && val[0] !== '6' && val[0] !== '7') {
                    val = val.substring(1);
                }
                if (val.length > 8) val = val.substring(0, 8);
                formulario.value.telefono = val;
            };

            const validarCarnet = () => {
                let val = formulario.value.carnetEmpleado.replace(/[^0-9]/g, '');
                if (val.length > 10) val = val.substring(0, 10);
                formulario.value.carnetEmpleado = val;
            };

            const validarCarnetConfirm = () => {
                let val = formulario.value.carnetEmpleado_confirmation.replace(/[^0-9]/g, '');
                if (val.length > 10) val = val.substring(0, 10);
                formulario.value.carnetEmpleado_confirmation = val;
            };

            const preValidar = () => {
                const errs = {};
                const f = formulario.value;

                if (!f.nombre1?.trim()) errs.nombre1 = 'El primer nombre es obligatorio.';
                else if (f.nombre1.length > 50) errs.nombre1 = 'El nombre no debe exceder 50 caracteres.';

                if (!f.apellido1?.trim()) errs.apellido1 = 'El apellido paterno es obligatorio.';
                else if (f.apellido1.length > 50) errs.apellido1 = 'El apellido no debe exceder 50 caracteres.';

                if (!f.idRol) errs.idRol = 'Debe seleccionar un rol.';

                if (!f.correo?.trim()) errs.correo = 'El correo electrónico es obligatorio.';
                else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f.correo)) errs.correo = 'Ingrese un correo electrónico válido.';

                const tel = String(f.telefono || '').replace(/\D/g, '');
                if (!tel) errs.telefono = 'El teléfono es obligatorio.';
                else if (tel.length < 7 || tel.length > 8) errs.telefono = 'El teléfono debe tener entre 7 y 8 dígitos.';
                else if (tel[0] !== '6' && tel[0] !== '7') errs.telefono = 'El teléfono debe iniciar con 6 o 7.';

                if (!modoEdicion.value) {
                    if (!f.carnetEmpleado?.trim()) errs.carnetEmpleado = 'El número de carnet es obligatorio.';
                    else if (!/^\d+$/.test(f.carnetEmpleado)) errs.carnetEmpleado = 'El carnet solo debe contener números.';
                    else if (f.carnetEmpleado.length > 10) errs.carnetEmpleado = 'El carnet no debe exceder 10 dígitos.';

                    if (f.carnetEmpleado !== f.carnetEmpleado_confirmation) {
                        errs.carnetEmpleado_confirmation = 'Los números de carnet no coinciden.';
                    }
                }

                if (!modoEdicion.value && !f.contrasena) errs.contrasena = 'La contraseña es obligatoria.';
                else if (f.contrasena && f.contrasena.length < 8) errs.contrasena = 'La contraseña debe tener al menos 8 caracteres.';

                if (f.contrasena || f.contrasena_confirmation) {
                    if (f.contrasena !== f.contrasena_confirmation) {
                        errs.contrasena_confirmation = 'Las contraseñas no coinciden.';
                    }
                }

                if (!f.idSucursal) errs.idSucursal = 'Debe seleccionar una sucursal.';

                if (!f.fechaContratoInicio) errs.fechaContratoInicio = 'La fecha de inicio es obligatoria.';
                else {
                    const hoy = new Date();
                    hoy.setHours(23, 59, 59, 0);
                    const fechaInicio = new Date(f.fechaContratoInicio + 'T23:59:59');
                    if (fechaInicio > hoy) errs.fechaContratoInicio = 'La fecha de inicio no puede ser en el futuro.';
                }

                return errs;
            };

            const guardarEmpleado = async () => {
                guardando.value = true;
                errores.value = {};

                const errs = preValidar();
                if (Object.keys(errs).length > 0) {
                    errores.value = errs;
                    guardando.value = false;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }

                try {
                    const url = modoEdicion.value ? `/admin/personal/${idActual.value}` : `/admin/personal`;
                    const metodo = modoEdicion.value ? 'PUT' : 'POST';

                    const res = await fetch(url, { method: metodo, headers: headers, body: JSON.stringify(formulario.value) });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        mostrarToast(data.message, 'success');
                        cancelarEdicion();
                        cargarEmpleados();
                    } else if (res.status === 422) {
                        for (const campo in data.errors) {
                            errores.value[campo] = data.errors[campo][0];
                        }
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        mostrarToast(data.message || 'Error inesperado. Intente nuevamente.', 'error');
                    }
                } catch(e) {
                    console.error("Error guardando:", e);
                    mostrarToast('Error de conexión. Verifique su red e intente nuevamente.', 'error');
                } finally {
                    guardando.value = false;
                }
            };

            const editarEmpleado = (emp) => {
                modoEdicion.value = true;
                idActual.value = emp.carnetEmpleado;
                errores.value = {};
                formulario.value = {
                    carnetEmpleado: emp.carnetEmpleado,
                    idUsuario: emp.idUsuario,
                    nombre1: emp.nombre1 || '',
                    nombre2: emp.nombre2 || '',
                    apellido1: emp.apellido1 || '',
                    apellido2: emp.apellido2 || '',
                    idRol: emp.idRol,
                    correo: emp.correo,
                    telefono: emp.telefono,
                    contrasena: '',
                    contrasena_confirmation: '',
                    idSucursal: emp.idSucursal,
                    fechaContratoInicio: emp.fechaContratoInicio
                };
            };

            const cancelarEdicion = () => {
                modoEdicion.value = false;
                idActual.value = null;
                errores.value = {};
                formulario.value = { ...formularioBase };
                if (adminSucursalId) {
                    formulario.value.idSucursal = adminSucursalId;
                }
            };

            const eliminarEmpleado = async (id) => {
                confirmarAccion("Esta acción dará de baja al empleado y quedará registrada en la auditoría. ¿Continuar?", async function() {
                    const res = await fetch(`/admin/personal/${id}`, { method: 'DELETE', headers: headers });
                    const data = await res.json();
                    if (data.success) { mostrarToast(data.message, 'success'); cargarEmpleados(); }
                    else { mostrarToast(data.message, 'error'); }
                });
            };

            const confirmarContrato = async (emp) => {
                confirmarAccion(`Finalizar contrato de ${nombreCompleto(emp)} (CI: ${emp.carnetEmpleado})? Se dará de baja al empleado.`, async function() {
                    const res = await fetch(`/admin/personal/${emp.carnetEmpleado}/acabar-contrato`, { method: 'PUT', headers: headers });
                    const data = await res.json();
                    if (data.success) { mostrarToast(data.message, 'success'); cargarEmpleados(); }
                    else { mostrarToast(data.message, 'error'); }
                });
            };

            const reactivarEmpleado = async (id) => {
                confirmarAccion("Reactivar este empleado? Se restablecerá su estado activo y se limpiará la fecha de fin de contrato.", async function() {
                    const res = await fetch(`/admin/personal/${id}/reactivar`, { method: 'PUT', headers: headers });
                    const data = await res.json();
                    if (data.success) { mostrarToast(data.message, 'success'); cargarEmpleados(); }
                });
            };

            onMounted(() => {
                cargarEmpleados();
                roles.value = @json($roles);
                rolesFiltrados.value = roles.value.filter(r => r.nombreRol.toLowerCase() !== 'socio');
                sucursales.value = @json($sucursales);
                if (adminSucursalId) {
                    formulario.value.idSucursal = adminSucursalId;
                }
            });

            return {
                empleados, inactivos, roles, rolesFiltrados, sucursales, adminSucursalId,
                formulario, errores, modoEdicion, guardando, mostrarPassword,
                nombreCompleto, validarLetras, validarTelefono, validarCarnet, validarCarnetConfirm,
                guardarEmpleado, editarEmpleado, eliminarEmpleado, cancelarEdicion,
                confirmarContrato, reactivarEmpleado
            };
        }
    }).mount('#appPersonal');
</script>
@endsection
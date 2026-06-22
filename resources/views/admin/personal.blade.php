@extends('layouts.admin')
@section('title', 'Gestion de Personal')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appPersonal">
    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">
            <template v-if="modoEdicion">✏️ Editar Personal</template>
            <template v-else>🏢 Registrar Nuevo Empleado</template>
        </h3>

        <form @submit.prevent="guardarEmpleado" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: start;">

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; color: #3b82f6; font-weight: 600;">Datos de Usuario (Acceso al Sistema)</div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Nombres <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.nombre1" @input="validarLetras('nombre1')" class="form-control" required>
                <small v-if="errores.nombre1" style="color:#ef4444; font-size: 0.8em;">@{{ errores.nombre1 }}</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Apellidos <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.apellido1" @input="validarLetras('apellido1')" class="form-control" required>
                <small v-if="errores.apellido1" style="color:#ef4444; font-size: 0.8em;">@{{ errores.apellido1 }}</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Rol Asignado <span style="color:#ef4444;">*</span></label>
                <select v-model="formulario.idRol" class="form-control" required>
                    <option value="" disabled>Seleccione un rol...</option>
                    <option v-for="rol in roles" :key="rol.idRol" :value="rol.idRol">@{{ rol.nombreRol }}</option>
                </select>
                <small v-if="errores.idRol" style="color:#ef4444; font-size: 0.8em;">@{{ errores.idRol }}</small>
            </div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Correo Electrónico <span style="color:#ef4444;">*</span></label>
                <input type="email" v-model="formulario.correo" class="form-control" required>
                <small v-if="errores.correo" style="color:#ef4444; font-size: 0.8em;">@{{ errores.correo }}</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Teléfono <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.telefono" @input="validarNumeros('telefono')" class="form-control" required maxlength="15">
                <small v-if="errores.telefono" style="color:#ef4444; font-size: 0.8em;">@{{ errores.telefono }}</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">
                    @{{ modoEdicion ? 'Nueva Contraseña (Opcional)' : 'Contraseña *' }}
                </label>
                <input type="password" v-model="formulario.contrasena" class="form-control" :required="!modoEdicion">
                <small v-if="errores.contrasena" style="color:#ef4444; font-size: 0.8em;">@{{ errores.contrasena }}</small>
            </div>

            <div style="grid-column: 3;">
                <label style="font-weight: 600; font-size: 0.85rem;">Confirmar Contraseña <span v-if="!modoEdicion" style="color:#ef4444;">*</span></label>
                <input type="password" v-model="formulario.contrasena_confirmation" class="form-control" :required="!modoEdicion || formulario.contrasena !== ''">
            </div>

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #3b82f6; font-weight: 600;">Datos Laborales (Recursos Humanos)</div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Nro. Carnet (CI) <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.carnetEmpleado" @input="validarNumeros('carnetEmpleado')" class="form-control" :disabled="modoEdicion" required>
                <small v-if="errores.carnetEmpleado" style="color:#ef4444; font-size: 0.8em;">@{{ errores.carnetEmpleado }}</small>
            </div>
            <div v-if="!modoEdicion">
                <label style="font-weight: 600; font-size: 0.85rem;">Confirmar Nro. Carnet <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.carnetEmpleado_confirmation" @input="validarNumeros('carnetEmpleado_confirmation')" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Sucursal Base <span style="color:#ef4444;">*</span></label>
                <select v-model="formulario.idSucursal" class="form-control" required>
                    <option value="" disabled>Seleccione sede...</option>
                    <option v-for="suc in sucursales" :key="suc.idSucursal" :value="suc.idSucursal">@{{ suc.nombre }}</option>
                </select>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Sueldo Base Mensual (Bs) <span style="color:#ef4444;">*</span></label>
                <input type="number" v-model="formulario.sueldo" class="form-control" required min="0" step="0.1">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Fecha Contrato Inicio <span style="color:#ef4444;">*</span></label>
                <input type="date" v-model="formulario.fechaContratoInicio" class="form-control" required>
                <small v-if="errores.fechaContratoInicio" style="color:#ef4444; font-size: 0.8em;">@{{ errores.fechaContratoInicio }}</small>
            </div>

            <div style="grid-column: span 3; display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <template v-if="guardando">⏳ Guardando...</template>
                    <template v-else>@{{ modoEdicion ? '💾 Guardar Cambios' : '➕ Guardar Empleado' }}</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary">❌ Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">📋 Listado de Personal Activo</h3>
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
                        <strong>@{{ emp.nombre1 }} @{{ emp.apellido1 }}</strong><br>
                        <span style="color:#64748b; font-size:0.85em;">CI: @{{ emp.carnetEmpleado }}</span><br>
                        <span class="badge badge-info" style="background-color: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-size: 0.8em;">@{{ emp.nombreRol }}</span>
                    </td>
                    <td style="padding: 12px;">
                        @{{ emp.correo }}<br>
                        <span style="color:#64748b; font-size:0.85em;">📞 @{{ emp.telefono }}</span>
                    </td>
                    <td style="padding: 12px;">
                        @{{ emp.nombreSucursal }}<br>
                        <span style="color:#64748b; font-size:0.85em;">Sueldo: Bs. @{{ emp.sueldo }}</span>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarEmpleado(emp)" class="btn btn-sm btn-info" style="margin-right: 5px;">✏️ Editar</button>
                        <button @click="eliminarEmpleado(emp.carnetEmpleado)" class="btn btn-sm btn-danger">🗑️ Baja</button>
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
            const empleados = ref([]);
            const roles = ref([]);
            const sucursales = ref([]);
            const modoEdicion = ref(false);
            const idActual = ref(null);
            const guardando = ref(false);

            const formularioBase = {
                carnetEmpleado: '', carnetEmpleado_confirmation: '', idUsuario: '', nombre1: '', apellido1: '', idRol: '', 
                correo: '', telefono: '', contrasena: '', contrasena_confirmation: '', idSucursal: '', sueldo: '', fechaContratoInicio: ''
            };
            const formulario = ref({ ...formularioBase });
            const errores = ref({}); // Capturador de errores de validación de Laravel

            const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' };

            const cargarDataBase = async () => {
                try {
                    const html = await (await fetch('{{ route("admin.personal.index") }}')).text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    // Extraemos los options directamente del HTML generado por Blade temporalmente si no hay endpoint JSON
                } catch(e){}
            };

            const cargarEmpleados = async () => {
                const res = await fetch('{{ route("admin.personal.listar") }}');
                empleados.value = await res.json();
            };

            const validarLetras = (campo) => {
                formulario.value[campo] = formulario.value[campo].replace(/[^a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]/g, '');
            };

            const validarNumeros = (campo) => {
                formulario.value[campo] = formulario.value[campo].replace(/[^0-9]/g, '');
            };

            const guardarEmpleado = async () => {
                guardando.value = true;
                errores.value = {}; 

                try {
                    const url = modoEdicion.value ? `/admin/personal/${idActual.value}` : `/admin/personal`;
                    const metodo = modoEdicion.value ? 'PUT' : 'POST';
                    
                    const res = await fetch(url, { method: metodo, headers: headers, body: JSON.stringify(formulario.value) });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        alert(data.message);
                        cancelarEdicion();
                        cargarEmpleados();
                    } else if (res.status === 422) {
                        // Atrapamos validaciones de Laravel y las mostramos en la interfaz
                        for (const campo in data.errors) {
                            errores.value[campo] = data.errors[campo][0];
                        }
                    } else {
                        alert(data.message || 'Error inesperado.');
                    }
                } catch(e) {
                    console.error("Error guardando:", e);
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
                    nombre1: emp.nombre1,
                    apellido1: emp.apellido1,
                    idRol: emp.idRol,
                    correo: emp.correo,
                    telefono: emp.telefono,
                    contrasena: '',
                    contrasena_confirmation: '',
                    idSucursal: emp.idSucursal,
                    sueldo: emp.sueldo,
                    fechaContratoInicio: emp.fechaContratoInicio
                };
            };

            const cancelarEdicion = () => {
                modoEdicion.value = false;
                idActual.value = null;
                errores.value = {};
                formulario.value = { ...formularioBase };
            };

            const eliminarEmpleado = async (id) => {
                if(confirm("¿Esta accion dará de baja al empleado y quedará registrada en la auditoría. Continuar?")) {
                    const res = await fetch(`/admin/personal/${id}`, { method: 'DELETE', headers: headers });
                    const data = await res.json();
                    if(data.success) cargarEmpleados();
                }
            };

            onMounted(() => {
                cargarEmpleados();
                // Rellenar selects manualmente ya que los pasas por compact()
                roles.value = @json($roles);
                sucursales.value = @json($sucursales);
            });

            return { empleados, roles, sucursales, formulario, errores, modoEdicion, guardando, validarLetras, validarNumeros, guardarEmpleado, editarEmpleado, eliminarEmpleado, cancelarEdicion };
        }
    }).mount('#appPersonal');
</script>
@endsection
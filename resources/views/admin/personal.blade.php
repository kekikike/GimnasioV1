@extends('layouts.admin')
@section('title', 'Gestion de Personal')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appPersonal">
    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">
            <template v-if="modoEdicion">Editar Personal</template>
            <template v-else>Registrar Nuevo Empleado</template>
        </h3>

        <form @submit.prevent="guardarEmpleado" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: end;">

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #3b82f6; font-weight: 600; font-size: 0.9rem;">Datos de Usuario (Acceso al Sistema)</div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Nombres <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.nombre1" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Apellidos <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.apellido1" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Rol Asignado <span style="color:#ef4444;">*</span></label>
                <select v-model="formulario.idRol" class="form-control" required>
                    <option value="" disabled>Seleccione un rol...</option>
                    <option v-for="rol in roles" :key="rol.idRol" :value="rol.idRol">@{{ rol.nombreRol }}</option>
                </select>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Correo Electronico <span style="color:#ef4444;">*</span></label>
                <input type="email" v-model="formulario.correo" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Telefono <span style="color:#ef4444;">*</span></label>
                <input type="number" v-model="formulario.telefono" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">
                    Contrasena
                    <span v-if="modoEdicion" style="color:#ef4444; font-size:0.8rem; font-weight:400;">(vacio = no cambiar)</span>
                    <span v-else style="color:#ef4444;">*</span>
                </label>
                <input type="password" v-model="formulario.contrasena" class="form-control" :required="!modoEdicion">
            </div>

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #3b82f6; font-weight: 600; font-size: 0.9rem;">Datos Laborales (Recursos Humanos)</div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Nro. Carnet (CI) <span style="color:#ef4444;">*</span></label>
                <input type="number" v-model="formulario.carnetEmpleado" class="form-control" :disabled="modoEdicion" required placeholder="Ej. 8456213">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Sucursal Base <span style="color:#ef4444;">*</span></label>
                <select v-model="formulario.idSucursal" class="form-control" required>
                    <option value="" disabled>Seleccione sede...</option>
                    <option v-for="suc in sucursales" :key="suc.idSucursal" :value="suc.idSucursal">@{{ suc.nombre }}</option>
                </select>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Sueldo Base Mensual (Bs) <span style="color:#ef4444;">*</span></label>
                <input type="number" step="0.01" v-model="formulario.sueldo" class="form-control" required>
            </div>
            <div style="grid-column: span 2;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Fecha Contrato Inicio <span style="color:#ef4444;">*</span></label>
                <input type="date" v-model="formulario.fechaContratoInicio" class="form-control" required>
            </div>

            <div style="grid-column: span 3; display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <template v-if="modoEdicion">Actualizar Datos</template>
                    <template v-else>Guardar Empleado</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-outline">Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">Nomina de Personal Activo</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Carnet (CI)</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Nombres y Rol</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Ubicacion y Sueldo</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="emp in empleados" :key="emp.carnetEmpleado" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px; font-weight:bold; color:#2563eb;"># @{{ emp.carnetEmpleado }}</td>
                    <td style="padding: 12px;">
                        <strong>@{{ emp.nombre1 }} @{{ emp.apellido1 }}</strong> <br>
                        <span class="badge" style="background:#e2e8f0; padding:2px 6px; font-size:0.8em; border-radius:4px; color:#334155;">@{{ emp.nombreRol }}</span>
                        <br><small style="color:#64748b;">@{{ emp.correo }}</small>
                    </td>
                    <td style="padding: 12px;">
                        <strong>@{{ emp.nombreSucursal }}</strong><br>
                        <span style="color:#059669; font-weight:600;">Bs. @{{ emp.sueldo }}</span>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <div class="action-group" style="justify-content:center;">
                            <button @click="editarEmpleado(emp)" class="btn btn-warning btn-sm" style="margin-right: 5px;">
                                <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Editar
                            </button>
                            <button @click="eliminarEmpleado(emp.carnetEmpleado)" class="btn btn-danger btn-sm">
                                <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Dar de Baja
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="empleados.length === 0">
                    <td colspan="4" style="text-align: center; padding: 20px; color: #64748b;">No hay empleados registrados.</td>
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
            const roles = ref(@json($roles));
            const sucursales = ref(@json($sucursales));
            const modoEdicion = ref(false);
            const idActual = ref(null);
            const guardando = ref(false);

            const formularioBase = {
                carnetEmpleado: '', idUsuario: '', nombre1: '', apellido1: '', correo: '', telefono: '',
                contrasena: '', idRol: '', idSucursal: '', sueldo: '',
                fechaContratoInicio: new Date().toISOString().split('T')[0]
            };
            const formulario = ref({ ...formularioBase });

            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            };

            const cargarEmpleados = async () => {
                const res = await fetch('{{ route("admin.personal.listar") }}');
                empleados.value = await res.json();
            };

            const guardarEmpleado = async () => {
                guardando.value = true;
                try {
                    const url = modoEdicion.value ? `/admin/personal/${idActual.value}` : `/admin/personal`;
                    const metodo = modoEdicion.value ? 'PUT' : 'POST';
                    const res = await fetch(url, { method: metodo, headers: headers, body: JSON.stringify(formulario.value) });
                    const data = await res.json();
                    if(data.success) {
                        cancelarEdicion();
                        cargarEmpleados();
                    }
                } catch(e) {
                    console.error("Error de conexion al guardar.");
                } finally {
                    guardando.value = false;
                }
            };

            const editarEmpleado = (emp) => {
                modoEdicion.value = true;
                idActual.value = emp.carnetEmpleado;
                formulario.value = {
                    carnetEmpleado: emp.carnetEmpleado,
                    idUsuario: emp.idUsuario,
                    nombre1: emp.nombre1,
                    apellido1: emp.apellido1,
                    idRol: emp.idRol,
                    correo: emp.correo,
                    telefono: emp.telefono,
                    contrasena: '',
                    idSucursal: emp.idSucursal,
                    sueldo: emp.sueldo,
                    fechaContratoInicio: emp.fechaContratoInicio
                };
            };

            const cancelarEdicion = () => {
                modoEdicion.value = false;
                idActual.value = null;
                formulario.value = { ...formularioBase };
            };

            const eliminarEmpleado = async (id) => {
                if(confirm("Esta accion dara de baja al empleado y quedara registrada en la auditoria. Continuar?")) {
                    const res = await fetch(`/admin/personal/${id}`, { method: 'DELETE', headers: headers });
                    const data = await res.json();
                    if(data.success) cargarEmpleados();
                }
            };

            onMounted(cargarEmpleados);

            return { empleados, roles, sucursales, formulario, modoEdicion, guardando, guardarEmpleado, editarEmpleado, eliminarEmpleado, cancelarEdicion };
        }
    }).mount('#appPersonal');
</script>
@endsection

@extends('layouts.admin')
@section('title', 'Gestión de Personal')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appPersonal">
    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">@{{ modoEdicion ? '✏️ Editar Personal' : '👤 Registrar Nuevo Empleado' }}</h3>
        
        <form @submit.prevent="guardarEmpleado" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: end;">
            
            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #3b82f6; font-weight: bold;">Datos de Usuario (Acceso al Sistema)</div>
            
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Nombres *</label>
                <input type="text" v-model="formulario.nombre1" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Apellidos *</label>
                <input type="text" v-model="formulario.apellido1" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Rol Asignado (RF-04) *</label>
                <select v-model="formulario.idRol" class="form-control" required>
                    <option value="" disabled>Seleccione un rol...</option>
                    <option v-for="rol in roles" :key="rol.idRol" :value="rol.idRol">@{{ rol.nombreRol }}</option>
                </select>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Correo Electrónico *</label>
                <input type="email" v-model="formulario.correo" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Teléfono *</label>
                <input type="number" v-model="formulario.telefono" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Contraseña <span v-if="modoEdicion" style="color:red; font-size:0.8em;">(Vacío = No cambiar)</span></label>
                <input type="password" v-model="formulario.contrasena" class="form-control" :required="!modoEdicion">
            </div>

            <div style="grid-column: span 3; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-top: 10px; color: #3b82f6; font-weight: bold;">Datos Laborales (Recursos Humanos)</div>

            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Nro. Carnet (CI) *</label>
                <input type="number" v-model="formulario.carnetEmpleado" class="form-control" :disabled="modoEdicion" required placeholder="Ej. 8456213">
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Sucursal Base *</label>
                <select v-model="formulario.idSucursal" class="form-control" required>
                    <option value="" disabled>Seleccione sede...</option>
                    <option v-for="suc in sucursales" :key="suc.idSucursal" :value="suc.idSucursal">@{{ suc.nombre }}</option>
                </select>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Sueldo Base Mensual (Bs) *</label>
                <input type="number" step="0.01" v-model="formulario.sueldo" class="form-control" required>
            </div>
            <div style="grid-column: span 2;">
                <label style="font-weight: bold; font-size: 0.9em;">Fecha Contrato Inicio *</label>
                <input type="date" v-model="formulario.fechaContratoInicio" class="form-control" required>
            </div>

            <div style="grid-column: span 3; display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit" class="btn btn-primary">
                    @{{ modoEdicion ? '💾 Actualizar Datos' : '💾 Guardar Empleado y Crear Usuario' }}
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary">❌ Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">📋 Nómina de Personal Activo</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Carnet (CI)</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Nombres y Rol</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Ubicación y Sueldo</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="emp in empleados" :key="emp.carnetEmpleado" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px; font-weight:bold; color:#2563eb;"># @{{ emp.carnetEmpleado }}</td>
                    <td style="padding: 12px;">
                        <strong>@{{ emp.nombre1 }} @{{ emp.apellido1 }}</strong> <br>
                        <span class="badge" style="background:#e2e8f0; padding:2px 6px; font-size:0.8em; border-radius:4px;">@{{ emp.nombreRol }}</span>
                        <br><small>@{{ emp.correo }}</small>
                    </td>
                    <td style="padding: 12px;">
                        <strong>@{{ emp.nombreSucursal }}</strong><br>
                        Bs. @{{ emp.sueldo }}
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarEmpleado(emp)" class="btn btn-sm btn-info" style="margin-right: 5px;">✏️ Editar</button>
                        <button @click="eliminarEmpleado(emp.carnetEmpleado)" class="btn btn-sm btn-danger">🗑️ Baja</button>
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
                const url = modoEdicion.value ? `/admin/personal/${idActual.value}` : `/admin/personal`;
                const metodo = modoEdicion.value ? 'PUT' : 'POST';

                try {
                    const res = await fetch(url, {
                        method: metodo,
                        headers: headers,
                        body: JSON.stringify(formulario.value)
                    });
                    const data = await res.json();
                    alert(data.message); 

                    if(data.success) {
                        cancelarEdicion();
                        cargarEmpleados();
                    }
                } catch(e) {
                    alert("Error de conexión al guardar.");
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
                if(confirm("¿Estás seguro de dar de baja a este empleado?")) {
                    const res = await fetch(`/admin/personal/${id}`, { method: 'DELETE', headers: headers });
                    const data = await res.json();
                    alert(data.message);
                    if(data.success) cargarEmpleados();
                }
            };

            onMounted(cargarEmpleados);

            return { empleados, roles, sucursales, formulario, modoEdicion, guardarEmpleado, editarEmpleado, eliminarEmpleado, cancelarEdicion };
        }
    }).mount('#appPersonal');
</script>
@endsection
@extends('layouts.admin')
@section('title', 'Gestión de Horarios')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
    // Laravel inyecta los empleados desde el controlador
    window.listaEmpleados = @json($empleados ?? []);
</script>

<div id="appHorarios">
    <div class="card" style="padding: 20px; margin-bottom: 20px; background-color: #f8fafc; border: 1px solid #e2e8f0;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">👤 Seleccionar Empleado</h3>
        <select v-model="empleadoSeleccionado" @change="cargarHorarios" class="form-control" style="max-width: 400px;">
            <option value="" disabled>Elija un empleado...</option>
            <option v-for="emp in empleados" :key="emp.carnetEmpleado" :value="emp.carnetEmpleado">
                @{{ emp.nombre1 }} @{{ emp.apellido1 }} (CI: @{{ emp.carnetEmpleado }})
            </option>
        </select>
    </div>

    <div v-if="empleadoSeleccionado" class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">
            <template v-if="modoEdicion">✏️ Editar Turno</template>
            <template v-else>⏱️ Asignar Nuevo Turno</template>
        </h3>
        
        <form @submit.prevent="guardarHorario" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: start;">
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Día de la Semana</label>
                <select v-model="formulario.diaSemana" class="form-control" required>
                    <option value="" disabled>Seleccione...</option>
                    <option value="Lunes">Lunes</option>
                    <option value="Martes">Martes</option>
                    <option value="Miércoles">Miércoles</option>
                    <option value="Jueves">Jueves</option>
                    <option value="Viernes">Viernes</option>
                    <option value="Sábado">Sábado</option>
                    <option value="Domingo">Domingo</option>
                </select>
                <small v-if="errores.diaSemana" style="color:#ef4444;">@{{ errores.diaSemana }}</small>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Hora de Entrada</label>
                <input type="time" v-model="formulario.horaEntrada" class="form-control" required>
                <small v-if="errores.horaEntrada" style="color:#ef4444;">@{{ errores.horaEntrada }}</small>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Hora de Salida</label>
                <input type="time" v-model="formulario.horaSalida" class="form-control" required>
                <small v-if="errores.horaSalida" style="color:#ef4444;">@{{ errores.horaSalida }}</small>
            </div>

            <div style="grid-column: span 3; margin-top: 10px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <template v-if="guardando">⏳ Guardando...</template>
                    <template v-else>@{{ modoEdicion ? '💾 Actualizar Horario' : '➕ Agregar Horario' }}</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary">❌ Cancelar</button>
            </div>
        </form>
    </div>

    <div v-if="empleadoSeleccionado" class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">📋 Horarios Asignados</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Día</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Entrada</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Salida</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="h in horarios" :key="h.idHorario" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px; font-weight: bold; color: #0f172a;">@{{ h.diaSemana }}</td>
                    <td style="padding: 12px; color: #059669;">🟢 @{{ h.horaEntrada }}</td>
                    <td style="padding: 12px; color: #dc2626;">🔴 @{{ h.horaSalida }}</td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarHorario(h)" class="btn btn-sm btn-info" style="margin-right: 5px;">✏️</button>
                        <button @click="eliminarHorario(h.idHorario)" class="btn btn-sm btn-danger">🗑️</button>
                    </td>
                </tr>
                <tr v-if="horarios.length === 0">
                    <td colspan="4" style="text-align: center; padding: 20px; color: #64748b;">No hay horarios registrados para este empleado.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    const { createApp, ref } = Vue;

    createApp({
        setup() {
            const empleados = ref(window.listaEmpleados || []);
            const empleadoSeleccionado = ref('');
            const horarios = ref([]);
            const modoEdicion = ref(false);
            const idActual = ref(null);
            const guardando = ref(false);
            const errores = ref({});
            
            const formBase = { diaSemana: '', horaEntrada: '', horaSalida: '' };
            const formulario = ref({ ...formBase });

            const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' };

            const cargarHorarios = async () => {
                cancelarEdicion();
                if (!empleadoSeleccionado.value) return;
                const res = await fetch(`/admin/horarios/listar/${empleadoSeleccionado.value}`);
                horarios.value = await res.json();
            };

            const guardarHorario = async () => {
                guardando.value = true;
                errores.value = {};
                try {
                    const url = modoEdicion.value ? `/admin/horarios/${idActual.value}` : `/admin/horarios`;
                    const metodo = modoEdicion.value ? 'PUT' : 'POST';
                    const payload = { ...formulario.value, carnetEmpleado: empleadoSeleccionado.value };
                    
                    const res = await fetch(url, { method: metodo, headers: headers, body: JSON.stringify(payload) });
                    const data = await res.json();
                    
                    if (res.ok && data.success) {
                        alert(data.message);
                        cargarHorarios();
                    } else if (res.status === 422) {
                        for (const campo in data.errors) errores.value[campo] = data.errors[campo][0];
                    } else {
                        alert(data.message || 'Error inesperado');
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    guardando.value = false;
                }
            };

            const editarHorario = (h) => {
                modoEdicion.value = true;
                idActual.value = h.idHorario;
                formulario.value = { diaSemana: h.diaSemana, horaEntrada: h.horaEntrada, horaSalida: h.horaSalida };
                errores.value = {};
            };

            const cancelarEdicion = () => {
                modoEdicion.value = false;
                idActual.value = null;
                formulario.value = { ...formBase };
                errores.value = {};
            };

            const eliminarHorario = async (id) => {
                if (confirm("¿Eliminar este turno?")) {
                    await fetch(`/admin/horarios/${id}`, { method: 'DELETE', headers: headers });
                    cargarHorarios();
                }
            };

            return { empleados, empleadoSeleccionado, horarios, formulario, errores, modoEdicion, guardando, cargarHorarios, guardarHorario, editarHorario, eliminarHorario, cancelarEdicion };
        }
    }).mount('#appHorarios');
</script>
@endsection
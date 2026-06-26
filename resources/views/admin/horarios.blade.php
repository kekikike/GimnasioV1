@extends('layouts.admin')
@section('title', 'Gestión de Horarios')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
    window.listaEmpleados = @json($empleados ?? []);
</script>

<div id="appHorarios">
    <div class="card" style="padding: 20px; margin-bottom: 20px; background-color: #f8fafc; border: 1px solid #e2e8f0;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">Seleccionar Empleado</h3>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; max-width: 400px;">
                <input type="text" v-model="termino" @input="buscar" @keydown.enter="buscarNow" @keydown.escape="resultados = []" placeholder="Buscar por CI, nombre o apellido..." class="form-control">
                <button @click="buscarNow" class="btn btn-primary" style="position: absolute; right: 4px; top: 4px; bottom: 4px; padding: 4px 12px; font-size: 0.85rem;">Buscar</button>
                <div v-if="resultados.length > 0" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #cbd5e1; border-top: none; border-radius: 0 0 6px 6px; max-height: 300px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div v-for="emp in resultados" :key="emp.carnetEmpleado" @click="seleccionarEmpleado(emp)" style="padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                        <span>@{{ emp.nombre1 }} @{{ emp.nombre2 ? emp.nombre2 + ' ' : '' }}@{{ emp.apellido1 }} @{{ emp.apellido2 ? emp.apellido2 : '' }}</span>
                        <span style="font-size: 0.8rem; color: #64748b;">CI: @{{ emp.carnetEmpleado }}</span>
                    </div>
                </div>
                <div v-if="sinResultados" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #cbd5e1; border-top: none; border-radius: 0 0 6px 6px; padding: 10px 12px; color: #94a3b8; font-style: italic; z-index: 1000;">No se encontraron empleados.</div>
            </div>
        </div>
    </div>

    <div v-if="empleadoSeleccionado" class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">
            <template v-if="modoEdicion">Editar Turno Existente</template>
            <template v-else>Asignar Nuevo Turno</template>
        </h3>
        
        <form @submit.prevent="guardarHorario" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: start;">
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Día de la Semana <span style="color:#ef4444;">*</span></label>
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
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Hora de Entrada <span style="color:#ef4444;">*</span></label>
                <input type="time" v-model="formulario.horaEntrada" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.85rem;">Hora de Salida <span style="color:#ef4444;">*</span></label>
                <input type="time" v-model="formulario.horaSalida" class="form-control" required>
            </div>

            <div style="grid-column: span 3; margin-top: 10px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <template v-if="guardando">Guardando...</template>
                    <template v-else>@{{ modoEdicion ? 'Actualizar Turno' : 'Agregar Turno' }}</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary" style="margin-left: 10px;">Cancelar</button>
            </div>
        </form>
    </div>

    <div v-if="empleadoSeleccionado" class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">Cronograma de Turnos Asignados</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; width: 20%;">Día de la Semana</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; width: 80%;">Horarios Asignados</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="grupo in horariosAgrupados" :key="grupo.diaSemana" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px; font-weight: bold; color: #0f172a; font-size: 1.1rem; vertical-align: top;">
                        @{{ grupo.diaSemana }}
                    </td>
                    <td style="padding: 12px;">
                        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                            <div v-for="(turno, index) in grupo.turnos" :key="turno.idHorario" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; min-width: 220px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; margin-bottom: 6px;">
                                    <span style="font-size: 0.75rem; background-color: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-weight: bold;">
                                        @{{ index + 1 }}er Turno
                                    </span>
                                    <div>
                                        <button @click="editarHorario(turno)" class="btn btn-sm btn-info" style="padding: 2px 6px; font-size: 0.75rem; margin-right: 4px;" title="Editar este turno">E</button>
                                        <button @click="eliminarHorario(turno.idHorario)" class="btn btn-sm btn-danger" style="padding: 2px 6px; font-size: 0.75rem;" title="Eliminar este turno">X</button>
                                    </div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.95rem;">
                                    <span style="color: #059669; font-weight: 600;">@{{ turno.horaEntrada.substring(0,5) }}</span>
                                    <span style="color: #94a3b8; font-size: 0.8em;">-</span>
                                    <span style="color: #dc2626; font-weight: 600;">@{{ turno.horaSalida.substring(0,5) }}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr v-if="horariosAgrupados.length === 0">
                    <td colspan="2" style="text-align: center; padding: 20px; color: #64748b; font-style: italic;">No hay turnos registrados para este empleado.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    const { createApp, ref, computed } = Vue;

    createApp({
        setup() {
            const termino = ref('');
            const resultados = ref([]);
            const sinResultados = ref(false);
            const empleadoSeleccionado = ref('');
            let timeoutId = null;

            const buscar = () => {
                sinResultados.value = false;
                if (timeoutId) clearTimeout(timeoutId);
                if (termino.value.length < 1) {
                    resultados.value = [];
                    sinResultados.value = false;
                    return;
                }
                timeoutId = setTimeout(async () => {
                    try {
                        const res = await fetch('/admin/horarios/buscar?q=' + encodeURIComponent(termino.value));
                        const data = await res.json();
                        resultados.value = data;
                        sinResultados.value = data.length === 0;
                    } catch (e) {
                        console.error('Error en busqueda:', e);
                    }
                }, 300);
            };

            const buscarNow = async () => {
                sinResultados.value = false;
                if (timeoutId) clearTimeout(timeoutId);
                if (termino.value.length < 1) {
                    resultados.value = [];
                    sinResultados.value = false;
                    return;
                }
                try {
                    const res = await fetch('/admin/horarios/buscar?q=' + encodeURIComponent(termino.value));
                    const data = await res.json();
                    resultados.value = data;
                    sinResultados.value = data.length === 0;
                } catch (e) {
                    console.error('Error en busqueda:', e);
                }
            };

            const seleccionarEmpleado = (emp) => {
                empleadoSeleccionado.value = emp.carnetEmpleado;
                termino.value = emp.nombre1 + ' ' + (emp.nombre2 ? emp.nombre2 + ' ' : '') + emp.apellido1 + (emp.apellido2 ? ' ' + emp.apellido2 : '');
                resultados.value = [];
                sinResultados.value = false;
                cargarHorarios();
            };

            const horariosAgrupados = ref([]); // La variable ahora guarda los días agrupados
            const modoEdicion = ref(false);
            const idActual = ref(null);
            const guardando = ref(false);
            const errores = ref({});
            
            const formBase = { diaSemana: '', horaEntrada: '', horaSalida: '' };
            const formulario = ref({ ...formBase });

            const headers = { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' 
            };

            const ordenDias = { 'Lunes': 1, 'Martes': 2, 'Miércoles': 3, 'Jueves': 4, 'Viernes': 5, 'Sábado': 6, 'Domingo': 7 };

            const cargarHorarios = async () => {
                cancelarEdicion();
                if (!empleadoSeleccionado.value) return;
                try {
                    const res = await fetch(`/admin/horarios/listar/${empleadoSeleccionado.value}`);
                    let data = await res.json();
                    
                    // Magia de Vue: Agrupamos los turnos crudos por día de la semana
                    let agrupado = {};
                    data.forEach(h => {
                        if (!agrupado[h.diaSemana]) agrupado[h.diaSemana] = [];
                        agrupado[h.diaSemana].push(h);
                    });

                    // Lo convertimos en un formato que la tabla entienda y ordenamos por hora
                    let resultado = Object.keys(agrupado).map(dia => ({
                        diaSemana: dia,
                        turnos: agrupado[dia].sort((a, b) => a.horaEntrada.localeCompare(b.horaEntrada))
                    }));

                    // Ordenamos los días de Lunes a Domingo
                    resultado.sort((a, b) => ordenDias[a.diaSemana] - ordenDias[b.diaSemana]);

                    horariosAgrupados.value = resultado;
                } catch (e) { console.error("Error cargando horarios", e); }
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
                        let mensajesError = [];
                        for (const campo in data.errors) {
                            mensajesError.push("• " + data.errors[campo][0]);
                        }
                        alert("AVISO:\n\n" + mensajesError.join("\n"));
                    } else {
                        alert(data.message || 'Error inesperado del servidor');
                    }
                } catch (e) { 
                    console.error("Error crítico:", e); 
                    alert("Ocurrio un error de conexion.");
                } finally { 
                    guardando.value = false; 
                }
            };

            const editarHorario = (turno) => {
                modoEdicion.value = true;
                idActual.value = turno.idHorario;
                formulario.value = { 
                    diaSemana: turno.diaSemana, 
                    horaEntrada: turno.horaEntrada.substring(0,5), 
                    horaSalida: turno.horaSalida.substring(0,5) 
                };
                errores.value = {};
            };

            const cancelarEdicion = () => {
                modoEdicion.value = false;
                idActual.value = null;
                formulario.value = { ...formBase };
                errores.value = {};
            };

            const eliminarHorario = async (id) => {
                if (confirm("¿Estás seguro de eliminar este turno?")) {
                    try {
                        const res = await fetch(`/admin/horarios/${id}`, { method: 'DELETE', headers: headers });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            alert(data.message);
                            cargarHorarios();
                        } else {
                            alert(data.message || "No se pudo eliminar.");
                        }
                    } catch (e) {
                        console.error("Error al eliminar", e);
                    }
                }
            };

            return { termino, resultados, sinResultados, empleadoSeleccionado, buscar, buscarNow, seleccionarEmpleado, horariosAgrupados, formulario, errores, modoEdicion, guardando, cargarHorarios, guardarHorario, editarHorario, eliminarHorario, cancelarEdicion };
        }
    }).mount('#appHorarios');
</script>
@endsection
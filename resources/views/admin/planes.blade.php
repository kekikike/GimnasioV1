@extends('layouts.admin')
@section('title', 'Planes de Membresia')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appPlanes">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.socios.index') }}" class="btn btn-outline">
            <svg fill="none" stroke="currentColor" width="16" height="16" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver a Socios
        </a>
    </div>

    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">
            <template v-if="modoEdicion">✏️ Editar Plan</template>
            <template v-else>➕ Crear Nuevo Plan</template>
        </h3>
        <form @submit.prevent="guardarPlan" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; align-items: start;">
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Nombre del Plan</label>
                <input type="text" v-model="formulario.nombrePlan" class="form-control" required placeholder="Ej. Plan Estudiante">
                <small v-if="errores.nombrePlan" style="color:#ef4444; font-size: 0.8em; display:block; margin-top:4px;">@{{ errores.nombrePlan }}</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Duración (Días)</label>
                <input type="number" v-model="formulario.duracionDias" class="form-control" required min="1">
            </div>
            <div style="grid-column: span 2;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Descripción de los Beneficios</label>
                <textarea v-model="formulario.descripcion" class="form-control" rows="3" required placeholder="Describe qué incluye el plan (Mín. 15 caracteres)"></textarea>
                <small v-if="errores.descripcion" style="color:#ef4444; font-size: 0.8em; display:block; margin-top:4px;">@{{ errores.descripcion }}</small>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Costo Total (Bs.)</label>
                <input type="number" v-model="formulario.costoPlan" class="form-control" required min="0" step="0.1">
            </div>

            <div style="grid-column: span 2; margin-top: 10px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <template v-if="guardando">⏳ Guardando...</template>
                    <template v-else>@{{ modoEdicion ? '💾 Actualizar Plan' : '💾 Guardar Plan' }}</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary" style="margin-left: 10px;">❌ Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">📋 Planes Disponibles</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Plan y Costo</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Descripción y Duración</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="plan in planes" :key="plan.idPlan" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px;">
                        <strong>@{{ plan.nombrePlan }}</strong><br>
                        <span style="color: #059669; font-weight: bold;">Bs. @{{ plan.costoPlan }}</span>
                    </td>
                    <td style="padding: 12px;">
                        @{{ plan.descripcion }}<br>
                        <small style="color: #64748b;">⏳ @{{ plan.duracionDias }} Días</small>
                    </td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarPlan(plan)" class="btn btn-sm btn-info" style="margin-right: 5px;">✏️ Editar</button>
                        <button @click="eliminarPlan(plan.idPlan)" class="btn btn-sm btn-danger">🗑️ Baja</button>
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
            const planes = ref([]);
            const modoEdicion = ref(false);
            const idActual = ref(null);
            const guardando = ref(false);
            
            const formBase = { nombrePlan: '', descripcion: '', costoPlan: '', duracionDias: '' };
            const formulario = ref({ ...formBase });
            const errores = ref({});

            const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' };

            const cargarPlanes = async () => {
                const res = await fetch('{{ route("admin.planes.listar") }}');
                planes.value = await res.json();
            };

            const guardarPlan = async () => {
                guardando.value = true;
                errores.value = {};
                try {
                    const url = modoEdicion.value ? `/admin/planes/${idActual.value}` : `/admin/planes`;
                    const metodo = modoEdicion.value ? 'PUT' : 'POST';
                    const res = await fetch(url, { method: metodo, headers: headers, body: JSON.stringify(formulario.value) });
                    const data = await res.json();
                    
                    if(res.ok && data.success) { 
                        alert(data.message);
                        cancelarEdicion(); 
                        cargarPlanes(); 
                    } else if (res.status === 422) {
                        for (const campo in data.errors) errores.value[campo] = data.errors[campo][0];
                    } else {
                        alert(data.message || 'Error inesperado.');
                    }
                } catch(e) {
                    console.error("Error guardando plan:", e);
                } finally {
                    guardando.value = false;
                }
            };

            const editarPlan = (plan) => { 
                modoEdicion.value = true; 
                idActual.value = plan.idPlan; 
                formulario.value = { ...plan }; 
                errores.value = {};
            };
            
            const cancelarEdicion = () => { 
                modoEdicion.value = false; 
                idActual.value = null; 
                formulario.value = { ...formBase }; 
                errores.value = {};
            };

            const eliminarPlan = async (id) => {
                if(confirm("¿Dar de baja este plan?")) {
                    await fetch(`/admin/planes/${id}`, { method: 'DELETE', headers: headers });
                    cargarPlanes();
                }
            };

            onMounted(cargarPlanes);
            return { planes, formulario, errores, modoEdicion, guardando, guardarPlan, editarPlan, cancelarEdicion, eliminarPlan };
        }
    }).mount('#appPlanes');
</script>
@endsection
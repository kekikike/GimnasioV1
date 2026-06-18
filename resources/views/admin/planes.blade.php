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
            <template v-if="modoEdicion">Editar Plan</template>
            <template v-else>Crear Nuevo Plan</template>
        </h3>
        <form @submit.prevent="guardarPlan" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; align-items: end;">
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Nombre del Plan</label>
                <input type="text" v-model="formulario.nombrePlan" class="form-control" required placeholder="Ej. Premium Anual">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Duracion (en Dias)</label>
                <input type="number" v-model="formulario.duracionDias" class="form-control" required placeholder="Ej. 365">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Costo (Bs.)</label>
                <input type="number" step="0.01" v-model="formulario.costoPlan" class="form-control" required placeholder="Ej. 1200.00">
            </div>
            <div style="grid-column: span 2;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Descripcion de Beneficios</label>
                <input type="text" v-model="formulario.descripcion" class="form-control" placeholder="Ej. Acceso a todas las maquinas y piscina">
            </div>
            <div style="grid-column: span 2; display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <template v-if="modoEdicion">Actualizar</template>
                    <template v-else>Guardar</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-outline">Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">Planes Disponibles</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Plan</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Duracion</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Costo</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="plan in planes" :key="plan.idPlan" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px;"><strong>@{{ plan.nombrePlan }}</strong><br><small style="color:#64748b;">@{{ plan.descripcion }}</small></td>
                    <td style="padding: 12px;">@{{ plan.duracionDias }} dias</td>
                    <td style="padding: 12px; font-weight: bold; color: #059669;">Bs. @{{ plan.costoPlan }}</td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarPlan(plan)" class="btn btn-warning btn-sm">
                            <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Editar
                        </button>
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
            const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' };

            const cargarPlanes = async () => {
                const res = await fetch('{{ route("admin.planes.listar") }}');
                planes.value = await res.json();
            };

            const guardarPlan = async () => {
                guardando.value = true;
                try {
                    const url = modoEdicion.value ? `/admin/planes/${idActual.value}` : `/admin/planes`;
                    const metodo = modoEdicion.value ? 'PUT' : 'POST';
                    const res = await fetch(url, { method: metodo, headers: headers, body: JSON.stringify(formulario.value) });
                    const data = await res.json();
                    if(data.success) { cancelarEdicion(); cargarPlanes(); }
                } catch(e) {
                    console.error("Error guardando plan:", e);
                } finally {
                    guardando.value = false;
                }
            };

            const editarPlan = (plan) => { modoEdicion.value = true; idActual.value = plan.idPlan; formulario.value = { ...plan }; };
            const cancelarEdicion = () => { modoEdicion.value = false; idActual.value = null; formulario.value = { ...formBase }; };

            onMounted(cargarPlanes);
            return { planes, formulario, modoEdicion, guardando, guardarPlan, editarPlan, cancelarEdicion };
        }
    }).mount('#appPlanes');
</script>
@endsection

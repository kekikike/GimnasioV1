@extends('layouts.admin')
@section('title', 'Planes de Membresía')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appPlanes">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.socios.index') }}" class="btn btn-secondary">⬅️ Volver a Socios</a>
    </div>

    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">@{{ modoEdicion ? '✏️ Editar Plan' : '🏷️ Crear Nuevo Plan (RF-12)' }}</h3>
        <form @submit.prevent="guardarPlan" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; align-items: end;">
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Nombre del Plan</label>
                <input type="text" v-model="formulario.nombrePlan" class="form-control" required placeholder="Ej. Premium Anual">
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Duración (en Días)</label>
                <input type="number" v-model="formulario.duracionDias" class="form-control" required placeholder="Ej. 365">
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Costo (Bs.)</label>
                <input type="number" step="0.01" v-model="formulario.costoPlan" class="form-control" required placeholder="Ej. 1200.00">
            </div>
            <div style="grid-column: span 2;">
                <label style="font-weight: bold; font-size: 0.9em;">Descripción de Beneficios</label>
                <input type="text" v-model="formulario.descripcion" class="form-control" placeholder="Ej. Acceso a todas las máquinas y piscina">
            </div>
            <div style="grid-column: span 2;">
                <button type="submit" class="btn btn-primary">@{{ modoEdicion ? '💾 Actualizar' : '💾 Guardar' }}</button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary" style="margin-left:10px;">❌ Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">📋 Planes Disponibles</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Plan</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Duración</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Costo</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="plan in planes" :key="plan.idPlan" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px;"><strong>@{{ plan.nombrePlan }}</strong><br><small>@{{ plan.descripcion }}</small></td>
                    <td style="padding: 12px;">@{{ plan.duracionDias }} días</td>
                    <td style="padding: 12px; font-weight: bold; color: #059669;">Bs. @{{ plan.costoPlan }}</td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarPlan(plan)" class="btn btn-sm btn-info">✏️ Editar</button>
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
            const formBase = { nombrePlan: '', descripcion: '', costoPlan: '', duracionDias: '' };
            const formulario = ref({ ...formBase });
            const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' };

            const cargarPlanes = async () => {
                const res = await fetch('{{ route("admin.planes.listar") }}');
                planes.value = await res.json();
            };

            const guardarPlan = async () => {
                const url = modoEdicion.value ? `/admin/planes/${idActual.value}` : `/admin/planes`;
                const metodo = modoEdicion.value ? 'PUT' : 'POST';
                const res = await fetch(url, { method: metodo, headers: headers, body: JSON.stringify(formulario.value) });
                const data = await res.json();
                alert(data.message);
                if(data.success) { cancelarEdicion(); cargarPlanes(); }
            };

            const editarPlan = (plan) => { modoEdicion.value = true; idActual.value = plan.idPlan; formulario.value = { ...plan }; };
            const cancelarEdicion = () => { modoEdicion.value = false; idActual.value = null; formulario.value = { ...formBase }; };

            onMounted(cargarPlanes);
            return { planes, formulario, modoEdicion, guardarPlan, editarPlan, cancelarEdicion };
        }
    }).mount('#appPlanes');
</script>
@endsection
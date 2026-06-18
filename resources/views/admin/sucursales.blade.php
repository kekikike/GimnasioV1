@extends('layouts.admin')
@section('title', 'Gestion de Sucursales')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appSucursales">
    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">
            <template v-if="modoEdicion">Editar Sucursal</template>
            <template v-else>Registrar Nueva Sucursal</template>
        </h3>

        <form @submit.prevent="guardarSucursal" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: end;">
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Nombre de la Sede</label>
                <input type="text" v-model="formulario.nombre" class="form-control" required placeholder="Ej. Sede Central">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Direccion Completa</label>
                <input type="text" v-model="formulario.direccion" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Telefono</label>
                <input type="text" v-model="formulario.telefono" class="form-control">
            </div>

            <div style="grid-column: span 3; display: flex; gap: 10px; margin-top: 10px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <template v-if="modoEdicion">Actualizar Datos</template>
                    <template v-else>Guardar Sucursal</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-outline">Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">Listado de Sucursales Activas</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">ID</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Nombre</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Direccion</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Telefono</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="suc in sucursales" :key="suc.idSucursal" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px;">@{{ suc.idSucursal }}</td>
                    <td style="padding: 12px;"><strong>@{{ suc.nombre }}</strong></td>
                    <td style="padding: 12px;">@{{ suc.direccion }}</td>
                    <td style="padding: 12px;">@{{ suc.telefono }}</td>
                    <td style="padding: 12px; text-align: center;">
                        <div class="action-group" style="justify-content:center;">
                            <button @click="editarSucursal(suc)" class="btn btn-warning btn-sm" style="margin-right: 5px;">
                                <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Editar
                            </button>
                            <button @click="eliminarSucursal(suc.idSucursal)" class="btn btn-danger btn-sm">
                                <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Eliminar
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="sucursales.length === 0">
                    <td colspan="5" style="text-align: center; padding: 20px; color: #64748b;">No hay sucursales registradas en el sistema.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    const { createApp, ref, onMounted } = Vue;

    createApp({
        setup() {
            const sucursales = ref([]);
            const modoEdicion = ref(false);
            const idActual = ref(null);
            const guardando = ref(false);
            const formulario = ref({ nombre: '', direccion: '', telefono: '' });

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const headers = {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            };

            const cargarSucursales = async () => {
                try {
                    const res = await fetch('{{ route("admin.sucursales.listar") }}');
                    sucursales.value = await res.json();
                } catch (error) {
                    console.error("Error cargando sucursales:", error);
                }
            };

            const guardarSucursal = async () => {
                guardando.value = true;
                try {
                    const url = modoEdicion.value ? `/admin/sucursales/${idActual.value}` : `/admin/sucursales`;
                    const metodo = modoEdicion.value ? 'PUT' : 'POST';
                    await fetch(url, { method: metodo, headers: headers, body: JSON.stringify(formulario.value) });
                    cancelarEdicion();
                    cargarSucursales();
                } catch(e) {
                    console.error("Error guardando sucursal:", e);
                } finally {
                    guardando.value = false;
                }
            };

            const editarSucursal = (sucursal) => {
                modoEdicion.value = true;
                idActual.value = sucursal.idSucursal;
                formulario.value = { nombre: sucursal.nombre, direccion: sucursal.direccion, telefono: sucursal.telefono };
            };

            const eliminarSucursal = async (id) => {
                if(confirm("Esta accion eliminara la sucursal y quedara registrada en la auditoria. Continuar?")) {
                    await fetch(`/admin/sucursales/${id}`, { method: 'DELETE', headers: headers });
                    cargarSucursales();
                }
            };

            const cancelarEdicion = () => {
                modoEdicion.value = false;
                idActual.value = null;
                formulario.value = { nombre: '', direccion: '', telefono: '' };
            };

            onMounted(() => { cargarSucursales(); });

            return { sucursales, formulario, modoEdicion, guardando, guardarSucursal, editarSucursal, eliminarSucursal, cancelarEdicion };
        }
    }).mount('#appSucursales');
</script>
@endsection

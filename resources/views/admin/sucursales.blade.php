@extends('layouts.admin')
@section('title', 'Gestión de Sucursales')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appSucursales">
    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">@{{ modoEdicion ? '✏️ Editar Sucursal' : '🏢 Registrar Nueva Sucursal' }}</h3>
        
        <form @submit.prevent="guardarSucursal" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: end;">
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Nombre de la Sede</label>
                <input type="text" v-model="formulario.nombre" class="form-control" required placeholder="Ej. Sede Central">
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Dirección Completa</label>
                <input type="text" v-model="formulario.direccion" class="form-control" required>
            </div>
            <div>
                <label style="font-weight: bold; font-size: 0.9em;">Teléfono</label>
                <input type="text" v-model="formulario.telefono" class="form-control">
            </div>
            
            <div style="grid-column: span 3; display: flex; gap: 10px; margin-top: 10px;">
                <button type="submit" class="btn btn-primary">
                    @{{ modoEdicion ? '💾 Actualizar Datos' : '💾 Guardar Sucursal' }}
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary">
                    ❌ Cancelar
                </button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">📋 Listado de Sucursales Activas</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">ID</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Nombre</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Dirección</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Teléfono</th>
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
                        <button @click="editarSucursal(suc)" class="btn btn-sm btn-info" style="margin-right: 5px;">✏️ Editar</button>
                        <button @click="eliminarSucursal(suc.idSucursal)" class="btn btn-sm btn-danger">🗑️ Eliminar</button>
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
                const url = modoEdicion.value ? `/admin/sucursales/${idActual.value}` : `/admin/sucursales`;
                const metodo = modoEdicion.value ? 'PUT' : 'POST';

                await fetch(url, {
                    method: metodo,
                    headers: headers,
                    body: JSON.stringify(formulario.value)
                });

                cancelarEdicion();
                cargarSucursales();
            };

            const editarSucursal = (sucursal) => {
                modoEdicion.value = true;
                idActual.value = sucursal.idSucursal;
                formulario.value = { nombre: sucursal.nombre, direccion: sucursal.direccion, telefono: sucursal.telefono };
            };

            const eliminarSucursal = async (id) => {
                if(confirm("¿Estás seguro de eliminar esta sucursal? El sistema registrará esta acción en la auditoría.")) {
                    await fetch(`/admin/sucursales/${id}`, { method: 'DELETE', headers: headers });
                    cargarSucursales();
                }
            };

            const cancelarEdicion = () => {
                modoEdicion.value = false;
                idActual.value = null;
                formulario.value = { nombre: '', direccion: '', telefono: '' };
            };

            onMounted(() => {
                cargarSucursales();
            });

            return { sucursales, formulario, modoEdicion, guardarSucursal, editarSucursal, eliminarSucursal, cancelarEdicion };
        }
    }).mount('#appSucursales');
</script>
@endsection
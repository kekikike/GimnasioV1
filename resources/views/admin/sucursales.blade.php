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

        <form @submit.prevent="guardarSucursal" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: start;">
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Nombre de la Sede <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.nombre" class="form-control" required placeholder="Ej. Sede Central">
                <small v-if="errores.nombre" style="color: #ef4444; font-size: 0.8em; display: block; margin-top: 4px;">@{{ errores.nombre }}</small>
            </div>
            
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Telefono <span style="color:#ef4444;">*</span></label>
                <input type="number" v-model="formulario.telefono" @input="validarTelefono" class="form-control" required min="6000000" max="99999999" onkeydown="return event.key === 'Backspace' || event.key === 'Delete' || event.key === 'Tab' || event.key === 'Escape' || event.key === 'Enter' || (event.key.match(/^\d$/) && this.value.length < 8)">
                <small v-if="errores.telefono" style="color: #ef4444; font-size: 0.8em; display: block; margin-top: 4px;">@{{ errores.telefono }}</small>
            </div>

            <div style="grid-column: span 3;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Dirección Exacta <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.direccion" class="form-control" required placeholder="Calle, Avenida, Zona...">
                <small v-if="errores.direccion" style="color: #ef4444; font-size: 0.8em; display: block; margin-top: 4px;">@{{ errores.direccion }}</small>
            </div>

            <div style="grid-column: span 3; display: flex; gap: 10px; margin-top: 10px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <template v-if="guardando">Procesando...</template>
                    <template v-else>@{{ modoEdicion ? 'Actualizar Datos' : 'Registrar Sucursal' }}</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">Listado de Sucursales Activas</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f8fafc;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569;">Nombre</th>
                    <th style="padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569;">Dirección</th>
                    <th style="padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569;">Teléfono</th>
                    <th style="padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="sucursal in sucursales" :key="sucursal.idSucursal" style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 12px; font-weight: 600; color: #0f172a;">@{{ sucursal.nombre }}</td>
                    <td style="padding: 12px; color: #475569;">@{{ sucursal.direccion }}</td>
                    <td style="padding: 12px; color: #475569; font-family: monospace; font-size: 1.1em;">@{{ sucursal.telefono }}</td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarSucursal(sucursal)" class="btn btn-sm btn-info" style="margin-right: 5px;" title="Editar">Editar</button>
                        <a :href="'https://www.google.com/maps/search/?api=1&query=' + sucursal.direccion" target="_blank" class="btn btn-sm btn-success" style="margin-right: 5px; text-decoration: none;" title="Ver en Google Maps">Mapa</a>
                        <button @click="eliminarSucursal(sucursal.idSucursal)" class="btn btn-sm btn-danger" title="Dar de baja">Eliminar</button>
                    </td>
                </tr>
                <tr v-if="sucursales.length === 0">
                    <td colspan="4" style="text-align: center; padding: 20px; color: #64748b; font-style: italic;">No hay sucursales activas registradas en el sistema.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div v-if="inactivas.length > 0" class="card" style="padding: 20px; background-color: #fff1f2; border: 1px solid #fecdd3;">
        <h3 style="margin-bottom: 15px; color: #be123c;">Papelera: Sucursales Dadas de Baja</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #ffe4e6;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #fda4af; color: #9f1239;">Nombre</th>
                    <th style="padding: 12px; border-bottom: 2px solid #fda4af; color: #9f1239;">Dirección</th>
                    <th style="padding: 12px; border-bottom: 2px solid #fda4af; color: #9f1239;">Teléfono</th>
                    <th style="padding: 12px; border-bottom: 2px solid #fda4af; color: #9f1239; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="suc in inactivas" :key="suc.idSucursal" style="border-bottom: 1px solid #fecdd3; opacity: 0.8;">
                    <td style="padding: 12px; font-weight: 600; color: #881337;">@{{ suc.nombre }}</td>
                    <td style="padding: 12px; color: #9f1239;">@{{ suc.direccion }}</td>
                    <td style="padding: 12px; color: #9f1239;">@{{ suc.telefono }}</td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="restaurarSucursal(suc.idSucursal)" class="btn btn-sm" style="background-color: #10b981; color: white;">Reactivar</button>
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
            const sucursales = ref([]);
            const inactivas = ref([]); // Lista para guardar las dadas de baja
            const modoEdicion = ref(false);
            const idActual = ref(null);
            const guardando = ref(false);
            
            const formulario = ref({ nombre: '', direccion: '', telefono: '' });
            const errores = ref({ nombre: '', direccion: '', telefono: '' });

            const headers = { 
                'Content-Type': 'application/json', 
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
            };

            // Solo permite ingresar números en el campo de teléfono (en tiempo real)
            const validarTelefono = () => {
                let val = formulario.value.telefono;
                if (typeof val === 'string') val = val.replace(/\D/g, '');
                if (val.length > 0 && val[0] !== '6' && val[0] !== '7') val = '';
                if (val.length > 8) val = val.slice(0, 8);
                formulario.value.telefono = val;
            };

            const cargarSucursales = async () => {
                try {
                    const res = await fetch('{{ route("admin.sucursales.listar") }}');
                    sucursales.value = await res.json();
                    cargarInactivas(); // También recargamos las inactivas para mantener todo sincronizado
                } catch(e) {
                    console.error("Error al cargar sucursales:", e);
                }
            };

            const cargarInactivas = async () => {
                try {
                    const res = await fetch('/admin/sucursales/inactivas');
                    inactivas.value = await res.json();
                } catch(e) {
                    console.error("Error al cargar inactivas:", e);
                }
            };

            const guardarSucursal = async () => {
                guardando.value = true;
                errores.value = { nombre: '', direccion: '', telefono: '' };

                try {
                    const url = modoEdicion.value ? `/admin/sucursales/${idActual.value}` : '/admin/sucursales';
                    const metodo = modoEdicion.value ? 'PUT' : 'POST';

                    const res = await fetch(url, {
                        method: metodo,
                        headers: headers,
                        body: JSON.stringify(formulario.value)
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        alert(data.message);
                        cancelarEdicion();
                        cargarSucursales();
                    } else if (res.status === 422) {
                        for (let campo in data.errors) {
                            errores.value[campo] = data.errors[campo][0];
                        }
                    } else {
                        alert(data.message || 'Ocurrió un error inesperado');
                    }
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
                errores.value = { nombre: '', direccion: '', telefono: '' };
            };

            const eliminarSucursal = async (id) => {
                if(confirm("¿Esta acción dará de baja la sucursal y quedará registrada en la auditoría. Continuar?")) {
                    await fetch(`/admin/sucursales/${id}`, { method: 'DELETE', headers: headers });
                    cargarSucursales();
                }
            };

            const restaurarSucursal = async (id) => {
                if(confirm("¿Estás seguro de reactivar esta sucursal? Volverá a estar disponible en el sistema.")) {
                    const res = await fetch(`/admin/sucursales/${id}/restaurar`, { method: 'PATCH', headers: headers });
                    const data = await res.json();
                    if(data.success) {
                        alert(data.message);
                        cargarSucursales();
                    }
                }
            };

            const cancelarEdicion = () => {
                modoEdicion.value = false;
                idActual.value = null;
                formulario.value = { nombre: '', direccion: '', telefono: '' };
                errores.value = { nombre: '', direccion: '', telefono: '' };
            };

            onMounted(() => { cargarSucursales(); });

            return { sucursales, inactivas, formulario, errores, modoEdicion, guardando, validarTelefono, guardarSucursal, editarSucursal, eliminarSucursal, restaurarSucursal, cancelarEdicion, cargarInactivas };
        }
    }).mount('#appSucursales');
</script>
@endsection
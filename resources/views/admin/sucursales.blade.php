@extends('layouts.admin')
@section('title', 'Gestion de Sucursales')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appSucursales">
    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">
            <template v-if="modoEdicion">✏️ Editar Sucursal</template>
            <template v-else>🏢 Registrar Nueva Sucursal</template>
        </h3>

        <form @submit.prevent="guardarSucursal" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: start;">
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Nombre de la Sede <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.nombre" class="form-control" required placeholder="Ej. Sede Central">
                <small v-if="errores.nombre" style="color: #ef4444; font-size: 0.8em; display: block; margin-top: 4px;">@{{ errores.nombre }}</small>
            </div>
            
            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Dirección Completa <span style="color:#ef4444;">*</span></label>
                <div style="display: flex; gap: 5px;">
                    <input type="text" v-model="formulario.direccion" class="form-control" required placeholder="Ej. Av. Principal #123">
                    <a :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(formulario.direccion)" target="_blank" class="btn btn-secondary" title="Ver en Mapa" :disabled="!formulario.direccion" style="padding: 6px 10px; display: flex; align-items: center; justify-content: center; text-decoration: none;">🗺️</a>
                </div>
                <small v-if="errores.direccion" style="color: #ef4444; font-size: 0.8em; display: block; margin-top: 4px;">@{{ errores.direccion }}</small>
            </div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Teléfono de Contacto <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.telefono" @input="validarTelefono" class="form-control" required placeholder="Ej. 77712345" maxlength="15">
                <small v-if="errores.telefono" style="color: #ef4444; font-size: 0.8em; display: block; margin-top: 4px;">@{{ errores.telefono }}</small>
            </div>

            <div style="grid-column: span 3; display: flex; gap: 10px; margin-top: 10px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <template v-if="guardando">⏳ Guardando...</template>
                    <template v-else>@{{ modoEdicion ? '💾 Guardar Cambios' : '➕ Registrar Sucursal' }}</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary">❌ Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">📋 Listado de Sucursales</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f1f5f9;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Sucursal</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Ubicación</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1;">Contacto</th>
                    <th style="padding: 12px; border-bottom: 2px solid #cbd5e1; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="sucursal in sucursales" :key="sucursal.idSucursal" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px; font-weight: bold; color: #0f172a;">@{{ sucursal.nombre }}</td>
                    <td style="padding: 12px;">@{{ sucursal.direccion }}</td>
                    <td style="padding: 12px;">📞 @{{ sucursal.telefono }}</td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarSucursal(sucursal)" class="btn btn-sm btn-info" style="margin-right: 5px;">✏️ Editar</button>
                        <button @click="eliminarSucursal(sucursal.idSucursal)" class="btn btn-sm btn-danger">🗑️ Baja</button>
                    </td>
                </tr>
                <tr v-if="sucursales.length === 0">
                    <td colspan="4" style="text-align: center; padding: 20px; color: #64748b;">No hay sucursales registradas.</td>
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
            // Objeto reactivo para capturar errores de Laravel (Validaciones RF1 y RF2)
            const errores = ref({ nombre: '', direccion: '', telefono: '' });

            const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' };

            const cargarSucursales = async () => {
                try {
                    const res = await fetch('{{ route("admin.sucursales.listar") }}');
                    sucursales.value = await res.json();
                } catch(e) {
                    console.error("Error cargando sucursales:", e);
                }
            };

            const validarTelefono = (e) => {
                // Elimina cualquier letra o símbolo mientras el usuario teclea
                let valor = e.target.value.replace(/[^0-9]/g, '');
                formulario.value.telefono = valor.substring(0, 15);
            };

            const guardarSucursal = async () => {
                guardando.value = true;
                errores.value = { nombre: '', direccion: '', telefono: '' }; // Limpiar errores

                try {
                    const url = modoEdicion.value ? `/admin/sucursales/${idActual.value}` : `/admin/sucursales`;
                    const metodo = modoEdicion.value ? 'PUT' : 'POST';
                    const res = await fetch(url, { method: metodo, headers: headers, body: JSON.stringify(formulario.value) });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        alert(data.message);
                        cancelarEdicion();
                        cargarSucursales();
                    } else if (res.status === 422) {
                        // Atrapamos las validaciones de Laravel y las pintamos de rojo en la interfaz
                        for (const campo in data.errors) {
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
                if(confirm("¿Esta acción eliminará la sucursal y quedará registrada en la auditoría. Continuar?")) {
                    await fetch(`/admin/sucursales/${id}`, { method: 'DELETE', headers: headers });
                    cargarSucursales();
                }
            };

            const cancelarEdicion = () => {
                modoEdicion.value = false;
                idActual.value = null;
                formulario.value = { nombre: '', direccion: '', telefono: '' };
                errores.value = { nombre: '', direccion: '', telefono: '' };
            };

            onMounted(() => { cargarSucursales(); });

            return { sucursales, formulario, errores, modoEdicion, guardando, validarTelefono, guardarSucursal, editarSucursal, eliminarSucursal, cancelarEdicion };
        }
    }).mount('#appSucursales');
</script>
@endsection
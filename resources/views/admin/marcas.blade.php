@extends('layouts.admin')
@section('title', 'Gestion de Marcas')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appMarcas">
    <div class="card" style="padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">
            <template v-if="modoEdicion">Editar Marca</template>
            <template v-else>Registrar Nueva Marca</template>
        </h3>

        <form @submit.prevent="guardarMarca" novalidate style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; align-items: start; max-width: 600px;">
            <div style="grid-column: span 2;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #374151;">Nombre de la Marca <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.nombreMarca" class="form-control" placeholder="Ej. Life Fitness" maxlength="100">
                <small v-if="errores.nombreMarca" style="color: #ef4444; font-size: 0.8em; display: block; margin-top: 4px;">@{{ errores.nombreMarca }}</small>
            </div>

            <div style="grid-column: span 2; display: flex; gap: 10px; margin-top: 10px;">
                <button type="submit" class="btn btn-primary" :disabled="guardando">
                    <template v-if="guardando">Procesando...</template>
                    <template v-else>@{{ modoEdicion ? 'Actualizar Marca' : 'Registrar Marca' }}</template>
                </button>
                <button type="button" v-if="modoEdicion" @click="cancelarEdicion" class="btn btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>

    <div class="card" style="padding: 20px;">
        <h3 style="margin-bottom: 15px; color: #1e293b;">Listado de Marcas</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background-color: #f8fafc;">
                <tr>
                    <th style="padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569;">ID</th>
                    <th style="padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569;">Nombre</th>
                    <th style="padding: 12px; border-bottom: 2px solid #e2e8f0; color: #475569; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="marca in marcas" :key="marca.idMarca" style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 12px; color: #64748b; font-family: monospace;">@{{ marca.idMarca }}</td>
                    <td style="padding: 12px; font-weight: 600; color: #0f172a;">@{{ marca.nombreMarca }}</td>
                    <td style="padding: 12px; text-align: center;">
                        <button @click="editarMarca(marca)" class="btn btn-sm btn-info" style="margin-right: 5px;">Editar</button>
                        <button @click="eliminarMarca(marca.idMarca)" class="btn btn-sm btn-danger">Eliminar</button>
                    </td>
                </tr>
                <tr v-if="marcas.length === 0">
                    <td colspan="3" style="text-align: center; padding: 20px; color: #64748b; font-style: italic;">No hay marcas registradas.</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    const { createApp, ref, onMounted } = Vue;

    createApp({
        setup() {
            const marcas = ref([]);
            const modoEdicion = ref(false);
            const guardando = ref(false);
            const errores = ref({});
            const formulario = ref({ nombreMarca: '' });

            const cargarMarcas = async () => {
                const res = await fetch('{{ route("admin.marcas.listar") }}');
                marcas.value = await res.json();
            };

            const guardarMarca = async () => {
                guardando.value = true;
                errores.value = {};

                if (!formulario.value.nombreMarca.trim()) {
                    errores.value.nombreMarca = 'El nombre de la marca no puede estar vacio.';
                    guardando.value = false;
                    return;
                }

                try {
                    const url = modoEdicion.value ? `/admin/marcas/${formulario.value._id}` : '/admin/marcas';
                    const method = modoEdicion.value ? 'PUT' : 'POST';
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(formulario.value)
                    });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        mostrarToast(data.message, 'success');
                        cancelarEdicion();
                        await cargarMarcas();
                    } else if (res.status === 422) {
                        for (const campo in data.errors) errores.value[campo] = data.errors[campo][0];
                    } else {
                        mostrarToast(data.message || 'Error inesperado.', 'error');
                    }
                } catch (e) {
                    mostrarToast('Error de conexion.', 'error');
                } finally {
                    guardando.value = false;
                }
            };

            const editarMarca = (marca) => {
                modoEdicion.value = true;
                errores.value = {};
                formulario.value = {
                    _id: marca.idMarca,
                    nombreMarca: marca.nombreMarca
                };
            };

            const cancelarEdicion = () => {
                modoEdicion.value = false;
                errores.value = {};
                formulario.value = { nombreMarca: '' };
            };

            const eliminarMarca = async (id) => {
                confirmarAccion('Eliminar esta marca?', async function () {
                    const res = await fetch(`/admin/marcas/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    const data = await res.json();
                    mostrarToast(data.message, data.success ? 'success' : 'error');
                    if (data.success) await cargarMarcas();
                });
            };

            onMounted(cargarMarcas);

            return { marcas, modoEdicion, guardando, errores, formulario, guardarMarca, editarMarca, cancelarEdicion, eliminarMarca };
        }
    }).mount('#appMarcas');
</script>
@endsection

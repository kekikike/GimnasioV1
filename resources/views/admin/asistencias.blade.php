@extends('layouts.admin')
@section('title', 'Control de Asistencias')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="appAsistencias" style="max-width: 600px; margin: 0 auto; text-align: center;">
    <div class="card" style="padding: 40px 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #1e293b; margin-bottom: 5px;">Reloj de Asistencia</h2>
        <p style="color: #64748b; margin-bottom: 25px;">Módulo de marcado para el personal del gimnasio</p>
        
        <div style="font-size: 3rem; font-weight: bold; color: #3b82f6; font-family: monospace; margin-bottom: 30px;">
            @{{ horaActual }}
        </div>

        <div v-if="mensaje" :style="{ padding: '15px', borderRadius: '8px', marginBottom: '20px', backgroundColor: mensajeTipo === 'success' ? '#dcfce3' : '#fee2e2', color: mensajeTipo === 'success' ? '#166534' : '#991b1b', fontWeight: 'bold' }">
            @{{ mensaje }}
        </div>

        <form @submit.prevent>
            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 10px; color: #374151;">Ingrese su Nro. de Carnet (CI)</label>
                <input type="text" v-model="carnetEmpleado" class="form-control" placeholder="Ej. 1234567" required style="font-size: 1.2rem; text-align: center; max-width: 300px; margin: 0 auto; border: 2px solid #cbd5e1;">
            </div>

            <div style="display: flex; gap: 15px; justify-content: center;">
                <button type="button" @click="marcar('entrada')" class="btn" style="background-color: #10b981; color: white; padding: 12px 24px; font-size: 1.1rem; font-weight: bold;" :disabled="!carnetEmpleado || cargando">
                    <template v-if="cargando && tipoActual === 'entrada'">⏳ Procesando...</template>
                    <template v-else>🟢 Marcar Entrada</template>
                </button>
                
                <button type="button" @click="marcar('salida')" class="btn" style="background-color: #ef4444; color: white; padding: 12px 24px; font-size: 1.1rem; font-weight: bold;" :disabled="!carnetEmpleado || cargando">
                    <template v-if="cargando && tipoActual === 'salida'">⏳ Procesando...</template>
                    <template v-else>🔴 Marcar Salida</template>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const { createApp, ref, onMounted, onUnmounted } = Vue;

    createApp({
        setup() {
            const carnetEmpleado = ref('');
            const mensaje = ref('');
            const mensajeTipo = ref('');
            const cargando = ref(false);
            const tipoActual = ref('');
            const horaActual = ref('');
            let intervaloReloj = null;

            const actualizarReloj = () => {
                const ahora = new Date();
                horaActual.value = ahora.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            };

            const marcar = async (tipo) => {
                cargando.value = true;
                tipoActual.value = tipo;
                mensaje.value = '';

                try {
                    const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') };
                    // El tipo define a qué ruta del controlador apuntar (entrada o salida)
                    const res = await fetch(`/admin/asistencias/${tipo}`, {
                        method: 'POST',
                        headers: headers,
                        body: JSON.stringify({ carnetEmpleado: carnetEmpleado.value })
                    });
                    
                    const data = await res.json();
                    
                    mensajeTipo.value = res.ok ? 'success' : 'error';
                    mensaje.value = data.message || (res.ok ? 'Operación exitosa.' : 'Ocurrió un error.');
                    
                    if (res.ok) carnetEmpleado.value = ''; // Limpiar input si fue exitoso

                } catch (error) {
                    mensajeTipo.value = 'error';
                    mensaje.value = 'Error de conexión con el servidor.';
                } finally {
                    cargando.value = false;
                    tipoActual.value = '';
                    
                    // Borrar el mensaje después de 5 segundos
                    setTimeout(() => { mensaje.value = ''; }, 5000);
                }
            };

            onMounted(() => {
                actualizarReloj();
                intervaloReloj = setInterval(actualizarReloj, 1000);
            });

            onUnmounted(() => {
                clearInterval(intervaloReloj);
            });

            return { carnetEmpleado, mensaje, mensajeTipo, cargando, tipoActual, horaActual, marcar };
        }
    }).mount('#appAsistencias');
</script>
@endsection
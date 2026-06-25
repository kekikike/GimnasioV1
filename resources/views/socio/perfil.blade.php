@extends('layouts.socio')
@section('title', 'Mi Perfil')
@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<style>
    .form-control {
        display: block;
        width: 100%;
        padding: 0.6rem 0.75rem;
        font-size: 0.95rem;
        line-height: 1.5;
        color: #334155;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        box-sizing: border-box;
        margin-top: 0.3rem; /* Espacio entre el título y la caja */
    }
    .form-control:focus {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25); /* Resplandor azul al hacer clic */
    }
    .form-control:disabled {
        background-color: #f8fafc;
        color: #94a3b8;
        opacity: 1;
        cursor: not-allowed;
    }
    label {
        display: block; /* Obliga a que el texto esté arriba de la caja */
        margin-bottom: 0.2rem;
        color: #1e293b;
    }
    .btn {
        transition: all 0.2s;
    }
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
</style>

<script>
    // Laravel le pasa los datos actuales del socio a Vue
    window.socioData = @json($socio ?? null);
</script>

<div id="appPerfil">
    <div class="card" style="padding: 25px; max-width: 900px; margin: 0 auto; background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
        <h3 style="margin-bottom: 20px; color: #0f172a; border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; font-size: 1.25rem; font-weight: 700;">
            👤 Editar Mis Datos Personales
        </h3>

        <form @submit.prevent="guardarPerfil" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start;">
            
            <div style="grid-column: span 2;">
                <label style="font-weight: 600; font-size: 0.85rem; color: #64748b;">Nro. Carnet (CI) - <small>No modificable</small></label>
                <input type="text" :value="formulario.carnetSocio" class="form-control" disabled>
            </div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Primer Nombre <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.nombre1" @input="validarLetras('nombre1')" class="form-control" required placeholder="Tu primer nombre">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Segundo Nombre</label>
                <input type="text" v-model="formulario.nombre2" @input="validarLetras('nombre2')" class="form-control" placeholder="Opcional">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Apellido Paterno <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.apellido1" @input="validarLetras('apellido1')" class="form-control" required placeholder="Tu apellido paterno">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Apellido Materno</label>
                <input type="text" v-model="formulario.apellido2" @input="validarLetras('apellido2')" class="form-control" placeholder="Opcional">
            </div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Correo Electrónico <span style="color:#ef4444;">*</span></label>
                <input type="email" v-model="formulario.correo" class="form-control" required placeholder="ejemplo@correo.com">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Teléfono Móvil <span style="color:#ef4444;">*</span></label>
                <input type="text" v-model="formulario.telefono" @input="validarTelefono('telefono')" class="form-control" required maxlength="8" placeholder="Ej: 71234567">
            </div>
            <div style="grid-column: span 2;">
                <label style="font-weight: 600; font-size: 0.85rem;">Dirección Exacta</label>
                <input type="text" v-model="formulario.direccion" class="form-control" placeholder="Calle, Avenida, Zona...">
            </div>

            <div style="grid-column: span 2; margin-top: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 5px;">
                <h4 style="color: #3b82f6; font-size: 1.05rem; margin: 0; font-weight: 700;">Contacto de Emergencia</h4>
            </div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Nombre Familiar/Amigo</label>
                <input type="text" v-model="formulario.contacto_emergencia_nombre" @input="validarLetras('contacto_emergencia_nombre')" class="form-control" placeholder="¿A quién llamamos?">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Teléfono de Emergencia</label>
                <input type="text" v-model="formulario.contacto_emergencia_telefono" @input="validarTelefono('contacto_emergencia_telefono')" class="form-control" maxlength="8" placeholder="Solo números">
            </div>

            <div style="grid-column: span 2; margin-top: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 5px;">
                <h4 style="color: #ef4444; font-size: 1.05rem; margin: 0; font-weight: 700;">Seguridad de la Cuenta</h4>
            </div>

            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Nueva Contraseña <small style="color:#64748b; font-weight: normal;">(Dejar en blanco para no cambiar)</small></label>
                <input type="password" v-model="formulario.contrasena" class="form-control" placeholder="Mínimo 8 caracteres">
            </div>
            <div>
                <label style="font-weight: 600; font-size: 0.85rem;">Confirmar Nueva Contraseña</label>
                <input type="password" v-model="formulario.contrasena_confirmation" class="form-control" :required="formulario.contrasena !== ''" placeholder="Repite tu contraseña">
            </div>

            <div style="grid-column: span 2; margin-top: 20px; text-align: right;">
                <button type="submit" class="btn" style="background:#3b82f6; color:#fff; padding: 12px 30px; font-weight: bold; border-radius: 8px; border:none; cursor:pointer; font-size: 1rem;" :disabled="guardando">
                    <template v-if="guardando">⏳ Guardando...</template>
                    <template v-else>💾 Actualizar Perfil</template>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const { createApp, ref } = Vue;

    createApp({
        setup() {
            const guardando = ref(false);
            const socio = window.socioData || {};

            const formulario = ref({
                carnetSocio: socio.carnetSocio || '',
                nombre1: socio.nombre1 || '',
                nombre2: socio.nombre2 || '',
                apellido1: socio.apellido1 || '',
                apellido2: socio.apellido2 || '',
                correo: socio.correo || '',
                telefono: socio.telefono || '',
                direccion: socio.direccion || '',
                contacto_emergencia_nombre: socio.nombreContactoEmergencia || '',
                contacto_emergencia_telefono: socio.telefonoContactoEmergencia || '',
                contrasena: '',
                contrasena_confirmation: ''
            });

            const validarLetras = (campo) => {
                formulario.value[campo] = formulario.value[campo].replace(/[^a-zA-Z\sñÑáéíóúÁÉÍÓÚüÜ]/g, '');
            };

            const validarTelefono = (campo) => {
                formulario.value[campo] = formulario.value[campo].replace(/[^0-9]/g, '').slice(0, 8);
            };

            const guardarPerfil = async () => {
                guardando.value = true;
                
                try {
                    const res = await fetch('/socio/perfil', {
                        method: 'PUT',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: JSON.stringify(formulario.value)
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        alert(data.message);
                        formulario.value.contrasena = '';
                        formulario.value.contrasena_confirmation = '';
                        window.location.reload(); 
                    } else if (res.status === 422) {
                        let mensajesError = [];
                        for (const campo in data.errors) {
                            mensajesError.push("• " + data.errors[campo][0]);
                        }
                        alert("⚠️ Error al actualizar:\n\n" + mensajesError.join("\n"));
                    } else {
                        alert(data.message || 'Error inesperado del servidor');
                    }
                } catch (e) {
                    console.error("Error crítico:", e);
                    alert("⚠️ Ocurrió un error de conexión.");
                } finally {
                    guardando.value = false;
                }
            };

            return { formulario, guardando, validarLetras, validarTelefono, guardarPerfil };
        }
    }).mount('#appPerfil');
</script>
@endsection
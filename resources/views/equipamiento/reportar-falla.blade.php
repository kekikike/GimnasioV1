@extends('layouts.admin')
@section('title', 'Reportar Fallas')
@section('content')
<div class="card" style="max-width:640px; padding:1.5rem;">
    <h3 style="margin-bottom:0.25rem; color:#0f172a;">Reportar Falla de Equipo</h3>
    <p style="color:#64748b; font-size:0.9rem; margin-bottom:1.5rem;">Seleccione un equipo operativo para reportar una falla.</p>

    @if(session('success'))
        <div class="alert alert-success" style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; border-radius:0.5rem; margin-bottom:1rem;">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:inherit;">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; border-radius:0.5rem; margin-bottom:1rem;">
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; font-size:1.2rem; color:inherit;">&times;</button>
        </div>
    @endif

    @if(empty($equipos))
        <div class="empty-state" style="text-align:center; padding:2rem 1rem; color:#94a3b8;">
            <p>No hay equipos operativos disponibles.</p>
        </div>
    @else
        <form action="{{ route('equipamiento.reportar-falla.store') }}" method="POST" novalidate>
            @csrf

            <div class="form-group">
                <label for="busquedaEquipo">Buscar equipo</label>
                <input type="text" id="busquedaEquipo" class="form-control" placeholder="Escriba para filtrar equipos..." oninput="filtrarEquipos()">
            </div>

            <div class="form-group">
                <label for="idEquipo">Equipo</label>
                <select name="idEquipo" id="idEquipo" class="form-control @error('idEquipo') is-invalid @enderror" required>
                    <option value="">-- Seleccione un equipo --</option>
                    @foreach($equipos as $eq)
                        <option value="{{ $eq->idEquipo }}" data-nombre="{{ mb_strtolower($eq->nombreEquipo . ' ' . ($eq->sucursal ?? '')) }}" {{ old('idEquipo') == $eq->idEquipo ? 'selected' : '' }}>{{ $eq->nombreEquipo }} {{ !empty($eq->sucursal) ? '- ' . $eq->sucursal : '' }}</option>
                    @endforeach
                </select>
                @error('idEquipo') <small style="color:#ef4444; font-size:0.8em; display:block; margin-top:4px;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="gravedad">Gravedad</label>
                <select name="gravedad" id="gravedad" class="form-control @error('gravedad') is-invalid @enderror" required>
                    <option value="">-- Seleccione --</option>
                    <option value="Baja" {{ old('gravedad') == 'Baja' ? 'selected' : '' }}>Baja</option>
                    <option value="Media" {{ old('gravedad') == 'Media' ? 'selected' : '' }}>Media</option>
                    <option value="Alta" {{ old('gravedad') == 'Alta' ? 'selected' : '' }}>Alta</option>
                    <option value="Critica" {{ old('gravedad') == 'Critica' ? 'selected' : '' }}>Cr&iacute;tica</option>
                </select>
                @error('gravedad') <small style="color:#ef4444; font-size:0.8em; display:block; margin-top:4px;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="descripcionFalla">Descripci&oacute;n de la Falla <small style="color:#94a3b8;">(m&aacute;x. 255 caracteres)</small></label>
                <textarea name="descripcionFalla" id="descripcionFalla" class="form-control @error('descripcionFalla') is-invalid @enderror" rows="4" required maxlength="255" placeholder="Describa el problema detectado...">{{ old('descripcionFalla') }}</textarea>
                <small id="contadorDesc" style="color:#94a3b8; font-size:0.75rem; display:block; text-align:right; margin-top:2px;">0/255</small>
                @error('descripcionFalla') <small style="color:#ef4444; font-size:0.8em; display:block; margin-top:4px;">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center;">
                <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                Reportar Falla
            </button>
        </form>
    @endif
</div>

<script>
function filtrarEquipos() {
    var input = document.getElementById('busquedaEquipo').value.toLowerCase();
    var select = document.getElementById('idEquipo');
    var options = select.options;
    for (var i = 0; i < options.length; i++) {
        var nombre = options[i].getAttribute('data-nombre') || options[i].text.toLowerCase();
        options[i].style.display = nombre.indexOf(input) === -1 ? 'none' : '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var desc = document.getElementById('descripcionFalla');
    var contador = document.getElementById('contadorDesc');
    if (desc && contador) {
        contador.textContent = desc.value.length + '/255';
        desc.addEventListener('input', function() {
            contador.textContent = this.value.length + '/255';
        });
    }
});
</script>
@endsection

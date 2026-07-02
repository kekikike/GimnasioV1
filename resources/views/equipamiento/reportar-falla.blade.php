@extends('layouts.admin')
@section('title', 'Reportar Fallas')
@section('content')
<div class="card" style="max-width:640px; padding:1.5rem;">
    <h3 style="margin-bottom:0.25rem; color:#0f172a;">Reportar Falla de Equipo</h3>
    <p style="color:#64748b; font-size:0.9rem; margin-bottom:1.5rem;">Seleccione un equipo operativo de su sucursal para reportar una falla.</p>

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

            <div class="form-group" style="position:relative;">
                <label for="busquedaEquipo">Equipo</label>
                <input type="text" id="busquedaEquipo" class="form-control"
                       placeholder="Escriba para buscar equipo..."
                       autocomplete="off"
                       oninput="buscarEquipo(this.value)"
                       onfocus="if(this.value.trim()) buscarEquipo(this.value)">
                <input type="hidden" name="idEquipo" id="idEquipo" value="{{ old('idEquipo') }}">
                <div id="resultadosEquipos" class="search-results" style="display:none;"></div>
                <div id="equipoSeleccionado" class="equipo-seleccionado" style="display:none;">
                    <span id="equipoSeleccionadoTexto"></span>
                    <button type="button" onclick="limpiarEquipo()" class="btn-clear-selected"><span aria-hidden="true">&times;</span></button>
                </div>
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

<style>
.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 100;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    max-height: 210px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.search-results .result-item {
    padding: 0.6rem 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
    font-size: 0.9rem;
    color: #1e293b;
}
.search-results .result-item:last-child {
    border-bottom: none;
}
.search-results .result-item:hover {
    background: #f1f5f9;
}
.search-results .result-item strong {
    color: #0f172a;
}
.search-results .result-item .sucursal-tag {
    color: #64748b;
    font-size: 0.8rem;
}
.search-results .empty-item {
    padding: 0.75rem;
    color: #94a3b8;
    text-align: center;
    font-size: 0.85rem;
}
.equipo-seleccionado {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #dbeafe;
    border-radius: 0.5rem;
    margin-top: 0.35rem;
    font-size: 0.9rem;
    color: #1e40af;
    font-weight: 500;
}
.btn-clear-selected {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1.2rem;
    color: #1e40af;
    padding: 0;
    line-height: 1;
    display: inline-flex;
    align-items: center;
}
.btn-clear-selected:hover {
    color: #1e3a5f;
}
</style>

<script>
var equipos = @json($equipos);

document.addEventListener('click', function(e) {
    var contenedor = document.getElementById('resultadosEquipos');
    var input = document.getElementById('busquedaEquipo');
    if (!contenedor || !input) return;
    if (!contenedor.contains(e.target) && e.target !== input) {
        contenedor.style.display = 'none';
    }
});

function buscarEquipo(valor) {
    var contenedor = document.getElementById('resultadosEquipos');
    if (!contenedor) return;

    if (!valor.trim()) {
        contenedor.style.display = 'none';
        return;
    }

    var termino = valor.toLowerCase();
    var resultados = equipos.filter(function(eq) {
        return (eq.nombreEquipo + ' ' + (eq.sucursal || '')).toLowerCase().indexOf(termino) !== -1;
    });

    if (resultados.length === 0) {
        contenedor.innerHTML = '<div class="empty-item">Sin resultados</div>';
        contenedor.style.display = 'block';
        return;
    }

    var html = '';
    resultados.forEach(function(eq) {
        var texto = eq.nombreEquipo + (eq.sucursal ? ' - ' + eq.sucursal : '');
        html += '<div class="result-item" data-id="' + eq.idEquipo + '" onclick="seleccionarEquipo(' + eq.idEquipo + ', \'' + texto.replace(/'/g, "\\'") + '\')">';
        html += '<strong>' + eq.nombreEquipo + '</strong>';
        if (eq.sucursal) html += ' <span class="sucursal-tag">- ' + eq.sucursal + '</span>';
        html += '</div>';
    });
    contenedor.innerHTML = html;
    contenedor.style.display = 'block';
}

function seleccionarEquipo(id, texto) {
    document.getElementById('idEquipo').value = id;
    document.getElementById('busquedaEquipo').value = '';
    document.getElementById('equipoSeleccionadoTexto').textContent = texto;
    document.getElementById('equipoSeleccionado').style.display = 'inline-flex';
    document.getElementById('resultadosEquipos').style.display = 'none';
}

function limpiarEquipo() {
    document.getElementById('idEquipo').value = '';
    document.getElementById('equipoSeleccionado').style.display = 'none';
    document.getElementById('busquedaEquipo').value = '';
    document.getElementById('resultadosEquipos').style.display = 'none';
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

    var oldEquipo = document.getElementById('idEquipo').value;
    if (oldEquipo) {
        var eq = equipos.find(function(e) { return e.idEquipo == oldEquipo; });
        if (eq) {
            var texto = eq.nombreEquipo + (eq.sucursal ? ' - ' + eq.sucursal : '');
            document.getElementById('equipoSeleccionadoTexto').textContent = texto;
            document.getElementById('equipoSeleccionado').style.display = 'inline-flex';
        }
    }
});
</script>
@endsection

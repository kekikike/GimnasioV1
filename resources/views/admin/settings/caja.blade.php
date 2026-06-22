@extends('layouts.admin')

@section('title', 'Configuración - Caja')

@section('content')
<div class="card" style="padding:1.25rem; max-width:720px;">
    <h3>Modo Pruebas - Caja</h3>
    <p style="margin-top:0.5rem; color:#475569">Controla la restricción de apertura/cierre para pruebas.</p>

    <div style="margin-top:1rem; display:flex; gap:1rem; align-items:center;">
        <div>
            <strong>Estado actual:</strong>
            <div id="estado" style="margin-top:0.25rem;">{{ $enabled ? 'Modo PRUEBAS (habilitado)' : 'Modo PRODUCCIÓN (deshabilitado)' }}</div>
        </div>
        <div style="margin-left:auto;">
            <button id="btn-toggle" class="btn {{ $enabled ? 'btn-danger' : 'btn-success' }}">{{ $enabled ? 'Desactivar modo pruebas' : 'Activar modo pruebas' }}</button>
        </div>
    </div>

    <div id="msg" style="margin-top:1rem;"></div>
</div>

<script>
document.getElementById('btn-toggle').addEventListener('click', async () => {
    const currently = {{ $enabled ? 'true' : 'false' }};
    const target = !currently;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch('{{ route('admin.settings.caja.toggle') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ enabled: target })
    });
    const j = await res.json();
    if (j.success) {
        document.getElementById('estado').innerText = j.enabled ? 'Modo PRUEBAS (habilitado)' : 'Modo PRODUCCIÓN (deshabilitado)';
        document.getElementById('btn-toggle').innerText = j.enabled ? 'Desactivar modo pruebas' : 'Activar modo pruebas';
        document.getElementById('btn-toggle').className = j.enabled ? 'btn btn-danger' : 'btn btn-success';
        document.getElementById('msg').innerHTML = '<div class="alert alert-success">Guardado.</div>';
    } else {
        document.getElementById('msg').innerHTML = '<div class="alert alert-danger">Error: '+(j.message || 'Error')+'</div>';
    }
});
</script>

@endsection

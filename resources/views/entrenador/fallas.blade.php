@extends('layouts.entrenador')
@section('title', 'Reportar Fallas')
@section('content')
<div class="card" style="max-width:600px;">
    <h3 style="margin-bottom:1.5rem; color:#0f172a;">Reportar Falla de Equipo</h3>

    <form action="{{ route('entrenador.fallas.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="idEquipo">Equipo</label>
            <select name="idEquipo" id="idEquipo" class="form-control" required>
                <option value="">-- Seleccione un equipo --</option>
                @foreach($equipos as $eq)
                    <option value="{{ $eq->idEquipo }}">{{ $eq->nombreEquipo }} - {{ $eq->sucursal ?? '' }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="gravedad">Gravedad</label>
            <select name="gravedad" id="gravedad" class="form-control" required>
                <option value="">-- Seleccione --</option>
                <option value="Baja">Baja</option>
                <option value="Media">Media</option>
                <option value="Alta">Alta</option>
                <option value="Critica">Cr&iacute;tica</option>
            </select>
        </div>

        <div class="form-group">
            <label for="descripcionFalla">Descripci&oacute;n de la Falla</label>
            <textarea name="descripcionFalla" id="descripcionFalla" class="form-control" rows="4" required placeholder="Describa el problema detectado..."></textarea>
        </div>

        <button type="submit" class="btn btn-danger" style="width:100%; justify-content:center;">
            <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            Reportar Falla
        </button>
    </form>
</div>

@if(session('error'))
    <div class="alert alert-danger" style="margin-top:1rem; background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:0.75rem 1rem; border-radius:0.5rem;">
        {{ session('error') }}
    </div>
@endif
@endsection

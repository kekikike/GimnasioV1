@extends('layouts.admin')
@section('title', 'Registrar Clase')
@section('content')

<div class="card" style="padding: 2rem; max-width: 860px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
        <h3 style="font-size:1.15rem; font-weight:600; color:#0f172a; display:flex; align-items:center; gap:0.5rem; margin:0;">
            <svg fill="none" stroke="#3b82f6" width="24" height="24" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Registrar Nueva Clase Grupal
        </h3>
    </div>

    @if(session('error'))
    <div class="alert alert-danger" style="display: flex; justify-content: space-between; align-items: center;">
        <span>{{ session('error') }}</span>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: inherit;">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0; padding-left:1.2rem;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.clases.store') }}" id="formCrearClase" novalidate>
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Actividad *</label>
                <select id="idActividad" name="idActividad" class="form-control @error('idActividad') is-invalid @enderror" required>
                    <option value="" disabled {{ old('idActividad') ? '' : 'selected' }}>Seleccione actividad...</option>
                    @foreach($actividades as $a)
                    <option value="{{ $a->idActividad }}" {{ old('idActividad') == $a->idActividad ? 'selected' : '' }}>{{ $a->nombreActividad }}</option>
                    @endforeach
                </select>
                @error('idActividad')<small style="color:#ef4444; font-size:0.8em; display:block; margin-top:4px;">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label>Instructor *</label>
                <select id="carnetEmpleado" name="carnetEmpleado" class="form-control @error('carnetEmpleado') is-invalid @enderror" required>
                    <option value="" disabled {{ old('carnetEmpleado') ? '' : 'selected' }}>Seleccione instructor...</option>
                    @foreach($empleados as $e)
                    <option value="{{ $e->carnetEmpleado }}" {{ old('carnetEmpleado') == $e->carnetEmpleado ? 'selected' : '' }}>{{ $e->nombre1 }} {{ $e->apellido1 }}</option>
                    @endforeach
                </select>
                @error('carnetEmpleado')<small style="color:#ef4444; font-size:0.8em; display:block; margin-top:4px;">{{ $message }}</small>@enderror
                <small style="color: #64748b;">Solo se listan usuarios con rol Entrenador.</small>
            </div>

            <div class="form-group">
                <label>Sucursal</label>
                <div style="padding: 0.6rem 0.75rem; background: #f1f5f9; border-radius: 0.5rem; font-size: 0.9rem; color: #0f172a; border: 2px solid #e2e8f0;">
                    <strong>{{ $adminSucursalNombre ?? 'No definida' }}</strong>
                    <small style="color: #64748b; display: block;">Sucursal asignada según tu perfil.</small>
                </div>
            </div>

            <div class="form-group">
                <label for="fecha">Fecha *</label>
                <input type="date" id="fecha" name="fecha" class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', date('Y-m-d')) }}" required>
                @error('fecha')<small style="color:#ef4444; font-size:0.8em; display:block; margin-top:4px;">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="horaInicio">Hora Inicio *</label>
                <input type="time" id="horaInicio" name="horaInicio" class="form-control @error('horaInicio') is-invalid @enderror" value="{{ old('horaInicio', date('H:i', strtotime('+1 hour'))) }}" required>
                @error('horaInicio')<small style="color:#ef4444; font-size:0.8em; display:block; margin-top:4px;">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="horaFin">Hora Fin *</label>
                <input type="time" id="horaFin" name="horaFin" class="form-control @error('horaFin') is-invalid @enderror" value="{{ old('horaFin', date('H:i', strtotime('+2 hours'))) }}" required>
                @error('horaFin')<small style="color:#ef4444; font-size:0.8em; display:block; margin-top:4px;">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="cupoMaximo">Cupo Máximo *</label>
                <input type="number" id="cupoMaximo" name="cupoMaximo" class="form-control @error('cupoMaximo') is-invalid @enderror" value="{{ old('cupoMaximo', 20) }}" required min="1" max="100">
                @error('cupoMaximo')<small style="color:#ef4444; font-size:0.8em; display:block; margin-top:4px;">{{ $message }}</small>@enderror
            </div>
        </div>

        <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e2e8f0;">
            <button type="submit" class="btn btn-success" id="btnGuardar">
                <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Registrar Clase
            </button>
            <a href="{{ route('admin.clases.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<style>
    .alert {
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }

    .alert-danger {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        font-size: 0.85rem;
        color: #0f172a;
        margin-bottom: 0.35rem;
    }

    .form-control {
        width: 100%;
        padding: 0.6rem 0.75rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.5rem;
        font-size: 0.9rem;
        outline: none;
        transition: border-color 0.2s;
        background: white;
    }

    .form-control:focus {
        border-color: #3b82f6;
    }

    .form-control.is-invalid {
        border-color: #ef4444;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-success {
        background: #10b981;
        color: white;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-outline {
        background: transparent;
        color: #475569;
        border: 2px solid #e2e8f0;
    }

    .btn-outline:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
</style>

<script>
    document.getElementById('formCrearClase')?.addEventListener('submit', function(e) {
        var btn = document.getElementById('btnGuardar');
        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span> Guardando...';
    });
</script>
@endsection
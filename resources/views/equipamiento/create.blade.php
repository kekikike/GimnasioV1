@extends('layouts.admin')

@section('title', 'Nuevo Equipo')

@section('content')
<div class="card" style="padding: 2rem; max-width: 720px;">
    <h3 style="font-size:1.15rem; font-weight:600; color:#0f172a; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem;">
        <svg fill="none" stroke="#3b82f6" width="24" height="24" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Registrar Nuevo Equipo
    </h3>

    <form method="POST" action="{{ route('equipamiento.store') }}" novalidate>
        @csrf

        <div class="grid-2">
            <div class="form-group">
                <label for="nombreEquipo">Nombre del Equipo</label>
                <input type="text" id="nombreEquipo" name="nombreEquipo" class="form-control" value="{{ old('nombreEquipo') }}" required placeholder="Ej: Cinta de Correr">
                @error('nombreEquipo') <small style="color:#ef4444;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="modelo">Modelo</label>
                <input type="text" id="modelo" name="modelo" class="form-control" value="{{ old('modelo') }}" placeholder="Ej: Run 700">
                @error('modelo') <small style="color:#ef4444;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="idMarca">Marca</label>
                <select id="idMarca" name="idMarca" class="form-control" required>
                    <option value="">Seleccione una marca</option>
                    @foreach($marcas as $m)
                        <option value="{{ $m->idMarca }}" {{ old('idMarca') == $m->idMarca ? 'selected' : '' }}>{{ $m->nombreMarca }}</option>
                    @endforeach
                </select>
                @error('idMarca') <small style="color:#ef4444;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="idSucursal">Sucursal</label>
                <select id="idSucursal" name="idSucursal" class="form-control" required>
                    <option value="">Seleccione una sucursal</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->idSucursal }}" {{ old('idSucursal') == $s->idSucursal ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
                @error('idSucursal') <small style="color:#ef4444;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="fechaAdquisicion">Fecha de Adquisición</label>
                <input type="date" id="fechaAdquisicion" name="fechaAdquisicion" class="form-control" value="{{ old('fechaAdquisicion') }}">
                @error('fechaAdquisicion') <small style="color:#ef4444;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="estadoEquipo">Estado del Equipo</label>
                <select id="estadoEquipo" name="estadoEquipo" class="form-control" required>
                    <option value="">Seleccione un estado</option>
                    <option value="Operativo" {{ old('estadoEquipo') == 'Operativo' ? 'selected' : '' }}>Operativo</option>
                    <option value="Fuera de Servicio" {{ old('estadoEquipo') == 'Fuera de Servicio' ? 'selected' : '' }}>Fuera de Servicio</option>
                </select>
                @error('estadoEquipo') <small style="color:#ef4444;">{{ $message }}</small> @enderror
            </div>
        </div>

        <div style="display:flex; gap:0.75rem; margin-top:1.5rem; padding-top:1.5rem; border-top:1px solid #e2e8f0;">
            <button type="submit" class="btn btn-success">
                <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Guardar Equipo
            </button>
            <a href="{{ route('equipamiento.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection

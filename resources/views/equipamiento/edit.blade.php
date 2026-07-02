@extends('layouts.admin')

@section('title', 'Editar Equipo')

@section('content')
<div class="card" style="padding: 2rem; max-width: 720px;">
    <h3 style="font-size:1.15rem; font-weight:600; color:#0f172a; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.5rem;">
        <svg fill="none" stroke="#f59e0b" width="24" height="24" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Editar Equipo: {{ $equipo->nombreEquipo }}
    </h3>

    <form method="POST" action="{{ route('equipamiento.update', $equipo->idEquipo) }}" novalidate>
        @csrf
        @method('PUT')

        <div class="grid-2">
            <div class="form-group">
                <label for="nombreEquipo">Nombre del Equipo</label>
                <input type="text" id="nombreEquipo" name="nombreEquipo" class="form-control" value="{{ old('nombreEquipo', $equipo->nombreEquipo) }}" required>
                @error('nombreEquipo') <small style="color:#ef4444;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="modelo">Modelo</label>
                <input type="text" id="modelo" name="modelo" class="form-control" value="{{ old('modelo', $equipo->modelo) }}">
                @error('modelo') <small style="color:#ef4444;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="idMarca">Marca</label>
                <select id="idMarca" name="idMarca" class="form-control" required>
                    <option value="">Seleccione una marca</option>
                    @foreach($marcas as $m)
                        <option value="{{ $m->idMarca }}" {{ old('idMarca', $equipo->idMarca) == $m->idMarca ? 'selected' : '' }}>{{ $m->nombreMarca }}</option>
                    @endforeach
                </select>
                @error('idMarca') <small style="color:#ef4444;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="idSucursal">Sucursal</label>
                <select id="idSucursal" name="idSucursal" class="form-control" required>
                    <option value="">Seleccione una sucursal</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->idSucursal }}" {{ old('idSucursal', $equipo->idSucursal) == $s->idSucursal ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
                @error('idSucursal') <small style="color:#ef4444;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="fechaAdquisicion">Fecha de Adquisición</label>
                <input type="date" id="fechaAdquisicion" name="fechaAdquisicion" class="form-control" value="{{ old('fechaAdquisicion', $equipo->fechaAdquisicion) }}">
                @error('fechaAdquisicion') <small style="color:#ef4444;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="estadoEquipo">Estado del Equipo</label>
                <select id="estadoEquipo" name="estadoEquipo" class="form-control" required>
                    <option value="Operativo" {{ old('estadoEquipo', $equipo->estadoEquipo) == 'Operativo' ? 'selected' : '' }}>Operativo</option>
                    <option value="Fuera de Servicio" {{ old('estadoEquipo', $equipo->estadoEquipo) == 'Fuera de Servicio' ? 'selected' : '' }}>Fuera de Servicio</option>
                </select>
                @error('estadoEquipo') <small style="color:#ef4444;">{{ $message }}</small> @enderror
            </div>
        </div>

        <div style="display:flex; gap:0.75rem; margin-top:1.5rem; padding-top:1.5rem; border-top:1px solid #e2e8f0;">
            <button type="submit" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Actualizar Equipo
            </button>
            <a href="{{ route('equipamiento.index') }}" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
@endsection

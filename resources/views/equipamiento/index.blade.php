@extends('layouts.admin')

@section('title', 'Equipamiento')

@section('content')
<div class="page-actions">
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <span style="font-size:0.9rem; color:#64748b;">{{ count($equipos) }} equipo(s)</span>
    </div>
    <a href="{{ route('equipamiento.create') }}" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" width="18" height="18" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Nuevo Equipo
    </a>
</div>

<div class="card" style="overflow:hidden;">
    @if(empty($equipos))
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>No hay equipos registrados.</p>
            <a href="{{ route('equipamiento.create') }}" class="btn btn-primary" style="margin-top:1rem; display:inline-flex;">Registrar primer equipo</a>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Equipo</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Sucursal</th>
                        <th>Adquisición</th>
                        <th>Estado</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equipos as $eq)
                    @php $marca = $marcas[$eq->idMarca] ?? null; $suc = $sucursales[$eq->idSucursal] ?? null; @endphp
                    <tr>
                        <td style="color:#64748b; font-weight:500;">#{{ $eq->idEquipo }}</td>
                        <td style="font-weight:600;">{{ $eq->nombreEquipo }}</td>
                        <td>{{ $marca->nombreMarca ?? '-' }}</td>
                        <td>{{ $eq->modelo ?? '-' }}</td>
                        <td>{{ $suc->nombre ?? '-' }}</td>
                        <td>{{ $eq->fechaAdquisicion ? \Carbon\Carbon::parse($eq->fechaAdquisicion)->format('d/m/Y') : '-' }}</td>
                        <td>
                            <span class="badge {{ $eq->estadoEquipo == 'Operativo' ? 'badge-success' : ($eq->estadoEquipo == 'Mantenimiento' ? 'badge-warning' : 'badge-danger') }}">
                                {{ $eq->estadoEquipo }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <div class="action-group" style="justify-content:center;">
                                <a href="{{ route('equipamiento.edit', $eq->idEquipo) }}" class="btn btn-warning btn-sm">
                                    <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Editar
                                </a>
                                <form action="{{ route('equipamiento.destroy', $eq->idEquipo) }}" method="POST" onsubmit="return confirm('¿Desactivar este equipo?')" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <svg fill="none" stroke="currentColor" width="14" height="14" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

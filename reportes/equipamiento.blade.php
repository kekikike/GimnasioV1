<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Equipamiento - GimnasioV1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #0a0a0a;
            color: #e0e0e0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .bg-gym {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a2a1a 50%, #0a0a0a 100%);
            min-height: 100vh;
            padding: 30px 0;
        }
        .card-gym {
            background: linear-gradient(145deg, #111811 0%, #0d150d 100%);
            border: 1px solid #1a3a1a;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .card-gym .card-header {
            background: transparent;
            border-bottom: 2px solid #1a3a1a;
            color: #00ff41;
            font-weight: 700;
            padding: 12px 0 15px 0;
            font-size: 1.2rem;
            letter-spacing: 1px;
        }
        .btn-gym-primary {
            background: transparent;
            border: 2px solid #00ff41;
            color: #00ff41;
            padding: 8px 25px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-gym-primary:hover {
            background: #00ff41;
            color: #0a0a0a;
            box-shadow: 0 0 30px rgba(0, 255, 65, 0.3);
        }
        .btn-gym-secondary {
            background: transparent;
            border: 2px solid #446644;
            color: #88aa88;
            padding: 8px 25px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-gym-secondary:hover {
            background: #446644;
            color: #ffffff;
        }
        .form-gym {
            background: #0d150d;
            border: 1px solid #1a3a1a;
            border-radius: 8px;
            color: #e0e0e0;
            padding: 10px 15px;
        }
        .form-gym:focus {
            border-color: #00ff41;
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.15);
            outline: none;
        }
        .form-gym option {
            background: #0a0a0a;
            color: #e0e0e0;
        }
        .table-gym {
            color: #e0e0e0;
            border-color: #1a3a1a;
        }
        .table-gym thead th {
            border-bottom: 2px solid #00ff41;
            color: #00ff41;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .table-gym tbody tr {
            border-color: #0d1f0d;
            transition: background 0.3s;
        }
        .table-gym tbody tr:hover {
            background: #0d1f0d;
        }
        .table-gym td {
            vertical-align: middle;
            padding: 12px 10px;
        }
        .stat-card {
            background: linear-gradient(145deg, #111811 0%, #0d150d 100%);
            border: 1px solid #1a3a1a;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #00ff41;
        }
        .stat-card .label {
            color: #88aa88;
            font-size: 0.9rem;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .stat-card.border-green { border-color: #00ff41; }
        .stat-card.border-blue { border-color: #3399ff; }
        .stat-card.border-gold { border-color: #ffaa00; }
        .stat-card.border-red { border-color: #ff3333; }
        .text-gym-gray { color: #88aa88; }
        .back-link {
            color: #88aa88;
            text-decoration: none;
            transition: color 0.3s;
        }
        .back-link:hover {
            color: #00ff41;
        }
        .btn-print {
            background: transparent;
            border: 2px solid #446644;
            color: #88aa88;
            padding: 10px 30px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-print:hover {
            background: #446644;
            color: #ffffff;
        }
        .badge-gym-operativo { background: #00ff41; color: #0a0a0a; padding: 5px 15px; border-radius: 20px; font-weight: 600; }
        .badge-gym-mantenimiento { background: #ffaa00; color: #0a0a0a; padding: 5px 15px; border-radius: 20px; font-weight: 600; }
        .badge-gym-fuera { background: #ff3333; color: #ffffff; padding: 5px 15px; border-radius: 20px; font-weight: 600; }
        .badge-gym-baja { background: #666666; color: #ffffff; padding: 5px 15px; border-radius: 20px; font-weight: 600; }
        .alert-gym-danger {
            background: #1a0d0d;
            border: 1px solid #ff3333;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        .alert-gym-danger h3 {
            color: #ff3333;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; color: black; }
            .bg-gym { background: white; }
            .card-gym { border-color: #ccc; }
            .stat-card { border-color: #ccc; }
            .table-gym thead th { border-color: #000; color: #000; }
            .alert-gym-danger { border-color: #dc3545; }
            .alert-gym-danger h3 { color: #dc3545; }
            .badge-gym-operativo { background: #28a745; color: white; }
            .badge-gym-mantenimiento { background: #ffc107; color: black; }
            .badge-gym-fuera { background: #dc3545; color: white; }
            .badge-gym-baja { background: #6c757d; color: white; }
        }
    </style>
</head>
<body>
    <div class="bg-gym">
        <div class="container">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 style="color: #00ff41; font-weight: 700;">
                    <i class="fas fa-tools me-3"></i>Equipamiento
                </h2>
                <a href="{{ route('reportes.index') }}" class="back-link no-print">
                    <i class="fas fa-arrow-left me-2"></i>Volver
                </a>
            </div>

            <!-- FILTROS -->
            <div class="card-gym no-print">
                <div class="card-header"><i class="fas fa-filter me-2"></i> Filtros</div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-gym-gray">Estado</label>
                            <select name="estado" class="form-gym form-select">
                                <option value="">Todos</option>
                                <option value="Operativo" {{ request('estado') == 'Operativo' ? 'selected' : '' }}>Operativo</option>
                                <option value="En Mantenimiento" {{ request('estado') == 'En Mantenimiento' ? 'selected' : '' }}>En Mantenimiento</option>
                                <option value="Fuera de Servicio" {{ request('estado') == 'Fuera de Servicio' ? 'selected' : '' }}>Fuera de Servicio</option>
                                <option value="De Baja" {{ request('estado') == 'De Baja' ? 'selected' : '' }}>De Baja</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn-gym-primary">Filtrar</button>
                            <a href="{{ route('reportes.equipamiento') }}" class="btn-gym-secondary">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ESTADÍSTICAS -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stat-card border-green">
                        <div class="number">{{ $estadisticas['total'] ?? 0 }}</div>
                        <div class="label">Total Equipos</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-green">
                        <div class="number">{{ $estadisticas['operativos'] ?? 0 }}</div>
                        <div class="label">Operativos</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-gold">
                        <div class="number">{{ $estadisticas['mantenimiento'] ?? 0 }}</div>
                        <div class="label">En Mantenimiento</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card border-red">
                        <div class="number">{{ $estadisticas['fuera_servicio'] ?? 0 }}</div>
                        <div class="label">Fuera de Servicio</div>
                    </div>
                </div>
            </div>

            <!-- FALLAS RECIENTES -->
            <div class="card-gym">
                <div class="card-header"><i class="fas fa-exclamation-triangle me-2"></i> Fallas Recientes (Últimos 30 días)</div>
                <div class="card-body">
                    <div class="alert-gym-danger">
                        <h3>{{ $estadisticas['fallas_recientes'] ?? 0 }} fallas reportadas</h3>
                    </div>
                </div>
            </div>

            <!-- TABLA -->
            <div class="card-gym">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-gym">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Modelo</th>
                                    <th>Estado</th>
                                    <th>Fecha Adquisición</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($equipos as $equipo)
                                <tr>
                                    <td>{{ $equipo->idEquipo }}</td>
                                    <td>{{ $equipo->nombreEquipo }}</td>
                                    <td>{{ $equipo->modelo ?? 'N/A' }}</td>
                                    <td>
                                        @if($equipo->estadoEquipo == 'Operativo')
                                            <span class="badge-gym-operativo">Operativo</span>
                                        @elseif($equipo->estadoEquipo == 'En Mantenimiento')
                                            <span class="badge-gym-mantenimiento">Mantenimiento</span>
                                        @elseif($equipo->estadoEquipo == 'Fuera de Servicio')
                                            <span class="badge-gym-fuera">Fuera de Servicio</span>
                                        @else
                                            <span class="badge-gym-baja">De Baja</span>
                                        @endif
                                    </td>
                                    <td>{{ $equipo->fechaAdquisicion ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-gym-gray">No hay equipos registrados</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- BOTONES -->
            <div class="d-flex gap-3 mt-4 no-print">
                <button onclick="window.print()" class="btn-print"><i class="fas fa-print me-2"></i>Imprimir</button>
            </div>

            <!-- FOOTER -->
            <div class="text-center mt-5 pt-4" style="border-top: 1px solid #1a3a1a;">
                <p style="color: #446644; font-size: 0.8rem; letter-spacing: 2px;">
                    <i class="fas fa-copyright me-1"></i> 2026 · GimnasioV1 · Reporte de Equipamiento
                </p>
            </div>

        </div>
    </div>
</body>
</html>
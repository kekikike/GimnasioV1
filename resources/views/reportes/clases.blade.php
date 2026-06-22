<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Clases - GimnasioV1</title>
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
        .progress {
            background: #0d150d;
            height: 20px;
            border-radius: 10px;
            border: 1px solid #1a3a1a;
        }
        .progress-bar {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 20px;
        }
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
        @media print {
            .no-print { display: none !important; }
            body { background: white; color: black; }
            .bg-gym { background: white; }
            .card-gym { border-color: #ccc; }
            .table-gym thead th { border-color: #000; color: #000; }
            .progress { border-color: #ccc; }
            .progress-bar { background-color: #28a745 !important; color: white !important; }
        }
    </style>
</head>
<body>
    <div class="bg-gym">
        <div class="container">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 style="color: #00ff41; font-weight: 700;">
                    <i class="fas fa-dumbbell me-3"></i>Clases Grupales
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
                        <div class="col-md-4">
                            <label class="form-label text-gym-gray">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-gym form-control" value="{{ $fechaInicio ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-gym-gray">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-gym form-control" value="{{ $fechaFin ?? '' }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn-gym-primary">Filtrar</button>
                            <a href="{{ route('reportes.clases') }}" class="btn-gym-secondary">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABLA -->
            <div class="card-gym">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-gym">
                            <thead>
                                <tr>
                                    <th>Clase</th>
                                    <th>Instructor</th>
                                    <th>Fecha</th>
                                    <th>Capacidad</th>
                                    <th>Reservados</th>
                                    <th>Asistieron</th>
                                    <th>Ocupación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estadisticas as $clase)
                                <tr>
                                    <td>{{ $clase['nombre'] }}</td>
                                    <td>{{ $clase['instructor'] }}</td>
                                    <td>{{ $clase['fecha'] }}</td>
                                    <td>{{ $clase['capacidad'] }}</td>
                                    <td>{{ $clase['reservados'] }}</td>
                                    <td>{{ $clase['asistieron'] }}</td>
                                    <td style="min-width: 120px;">
                                        <div class="progress">
                                            <div class="progress-bar bg-{{ $clase['ocupacion'] > 80 ? 'danger' : ($clase['ocupacion'] > 50 ? 'warning' : 'success') }}"
                                                 style="width: {{ $clase['ocupacion'] }}%; color: {{ $clase['ocupacion'] > 50 ? '#000' : '#fff' }}">
                                                {{ $clase['ocupacion'] }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-gym-gray">No hay clases registradas en este período</td></tr>
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
                    <i class="fas fa-copyright me-1"></i> 2026 · GimnasioV1 · Reporte de Clases
                </p>
            </div>

        </div>
    </div>
</body>
</html>
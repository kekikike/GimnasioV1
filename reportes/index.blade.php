<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Reportes - System Access</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Importamos Bootstrap Icons para los logotipos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #0a0a0a;
            color: #d1d1d1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container { max-width: 1000px; }

        .section-title {
            color: #ffc107; /* Amarillo formal */
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 700;
            border-bottom: 2px solid #ffc107;
            display: inline-block;
            padding-bottom: 10px;
        }

        .card-reporte {
            background-color: #1a1a1a;
            border: 1px solid #333;
            transition: all 0.3s ease;
        }

        .card-reporte:hover {
            border-color: #ffc107;
            background-color: #222;
        }

        .icon-box {
            font-size: 2.5rem;
            color: #ffc107;
            margin-bottom: 15px;
        }

        .btn-custom {
            background-color: transparent;
            border: 1px solid #ffc107;
            color: #ffc107;
            padding: 8px 20px;
            text-transform: uppercase;
            font-size: 0.8rem;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-custom:hover {
            background-color: #ffc107;
            color: #000;
        }

        .card-title { color: #fff; font-weight: 600; }
        .card-text { font-size: 0.9rem; color: #888; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="text-center mb-5">
            <h1 class="section-title">Módulo de Reportes</h1>
            <p class="mt-3">Seleccione el parámetro de análisis del sistema</p>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <!-- Socio -->
            <div class="col">
                <div class="card card-reporte h-100 text-center p-3">
                    <div class="icon-box"><i class="bi bi-people-fill"></i></div>
                    <h5 class="card-title">Socios</h5>
                    <p class="card-text">Base de datos de miembros activos y vencidos.</p>
                    <div class="mt-auto"><a href="{{ route('reportes.socios') }}" class="btn btn-custom">Ver Reporte</a></div>
                </div>
            </div>

            <!-- Financiero -->
            <div class="col">
                <div class="card card-reporte h-100 text-center p-3">
                    <div class="icon-box"><i class="bi bi-cash-stack"></i></div>
                    <h5 class="card-title">Financiero</h5>
                    <p class="card-text">Flujo de caja, ingresos y métricas contables.</p>
                    <div class="mt-auto"><a href="{{ route('reportes.financiero') }}" class="btn btn-custom">Ver Reporte</a></div>
                </div>
            </div>

            <!-- Asistencia -->
            <div class="col">
                <div class="card card-reporte h-100 text-center p-3">
                    <div class="icon-box"><i class="bi bi-calendar-check"></i></div>
                    <h5 class="card-title">Asistencia</h5>
                    <p class="card-text">Registro detallado de accesos por socio.</p>
                    <div class="mt-auto"><a href="{{ route('reportes.asistencia') }}" class="btn btn-custom">Ver Reporte</a></div>
                </div>
            </div>

            <!-- Clases -->
            <div class="col">
                <div class="card card-reporte h-100 text-center p-3">
                    <div class="icon-box"><i class="bi bi-graph-up-arrow"></i></div>
                    <h5 class="card-title">Clases</h5>
                    <p class="card-text">Estadísticas de ocupación y rendimiento.</p>
                    <div class="mt-auto"><a href="{{ route('reportes.clases') }}" class="btn btn-custom">Ver Reporte</a></div>
                </div>
            </div>

            <!-- Equipamiento -->
            <div class="col">
                <div class="card card-reporte h-100 text-center p-3">
                    <div class="icon-box"><i class="bi bi-tools"></i></div>
                    <h5 class="card-title">Equipamiento</h5>
                    <p class="card-text">Control de inventario y estado técnico.</p>
                    <div class="mt-auto"><a href="{{ route('reportes.equipamiento') }}" class="btn btn-custom">Ver Reporte</a></div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
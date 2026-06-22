<?php

use App\Http\Controllers\Admin\SucursalController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\PersonalController;
use App\Http\Controllers\Admin\SocioController;
use App\Http\Controllers\Admin\ClaseController;
use App\Http\Controllers\Admin\ClaseGrupalController;
use App\Http\Controllers\Admin\MembresiaController;
use App\Http\Controllers\Admin\CajaController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\AlertasMantenimientoController;
use App\Http\Controllers\Admin\MantenimientoController;
use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Admin\HorarioController;
use App\Http\Controllers\Admin\AsistenciaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipamientoController;
use App\Http\Controllers\Recepcionista\RecepcionistaController;
use App\Http\Controllers\Recepcionista\ControlIngresoController;
use App\Http\Controllers\Entrenador\EntrenadorController;
use App\Http\Controllers\Socio\SocioPortalController;
use App\Http\Controllers\Socio\ReservaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Redirección inicial al Login
Route::get('/', function () {
    return redirect()->route('login');
});

// Servir fotos de socios desde storage/fotos_socios/
Route::get('storage/fotos_socios/{filename}', function ($filename) {
    $path = storage_path('fotos_socios/' . basename($filename));
    if (!file_exists($path)) abort(404);
    return response()->file($path);
})->where('filename', '.*');

// Rutas Públicas de Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth.usuario');

// Grupo de rutas protegidas por el Middleware de seguridad
Route::middleware('auth.usuario')->group(function () {
    
    // Panel de Administrador (idRol = 1)
    Route::middleware('role:1')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('admin/sucursales')->name('admin.sucursales.')->group(function () {
            Route::get('/', [SucursalController::class, 'index'])->name('index'); 
            Route::get('/listar', [SucursalController::class, 'listar'])->name('listar'); 
            Route::post('/', [SucursalController::class, 'store'])->name('store');
            Route::put('/{id}', [SucursalController::class, 'update'])->name('update');
            Route::delete('/{id}', [SucursalController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('admin/personal')->name('admin.personal.')->group(function () {
            Route::get('/', [PersonalController::class, 'index'])->name('index');
            Route::get('/listar', [PersonalController::class, 'listar'])->name('listar');
            Route::post('/', [PersonalController::class, 'store'])->name('store');
            Route::put('/{id}', [PersonalController::class, 'update'])->name('update');
            Route::delete('/{id}', [PersonalController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('admin/socios')->name('admin.socios.')->group(function () {
            Route::get('/', [SocioController::class, 'index'])->name('index');
            Route::get('/listar', [SocioController::class, 'listar'])->name('listar');
            Route::post('/', [SocioController::class, 'store'])->name('store');
            Route::put('/{id}', [SocioController::class, 'update'])->name('update');
            Route::patch('/{id}/congelar', [SocioController::class, 'congelar'])->name('congelar');
            Route::delete('/{id}', [SocioController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('admin/planes')->name('admin.planes.')->group(function () {
            Route::get('/', [PlanController::class, 'index'])->name('index');
            Route::get('/listar', [PlanController::class, 'listar'])->name('listar');
            Route::post('/', [PlanController::class, 'store'])->name('store');
            Route::put('/{id}', [PlanController::class, 'update'])->name('update');
            Route::delete('/{id}', [PlanController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('admin/clases')->name('admin.clases.')->group(function () {
            Route::get('/', [ClaseGrupalController::class, 'index'])->name('index');
            Route::get('/listar', [ClaseGrupalController::class, 'listar'])->name('listar');
            Route::post('/', [ClaseGrupalController::class, 'store'])->name('store');
            Route::put('/{id}', [ClaseGrupalController::class, 'update'])->name('update');
            Route::delete('/{id}', [ClaseGrupalController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/reservas', [ClaseGrupalController::class, 'listarReservas'])->name('reservas');
            Route::post('/marcar-asistencia', [ClaseGrupalController::class, 'marcarAsistencia'])->name('asistencia');
            Route::get('/reporte/ocupacion', [ClaseGrupalController::class, 'reporteOcupacion'])->name('reporte');
        });
        Route::get('/admin/caja', [CajaController::class, 'index'])->name('admin.caja');
        Route::get('/admin/caja/estado', [CajaController::class, 'estado'])->name('admin.caja.estado');
        Route::post('/admin/caja/abrir', [CajaController::class, 'abrir'])->name('admin.caja.abrir');
        Route::post('/admin/caja/{id}/cerrar', [CajaController::class, 'cerrar'])->name('admin.caja.cerrar');
        Route::get('/admin/caja/movimientos', [CajaController::class, 'movimientos'])->name('admin.caja.movimientos');
        Route::post('/admin/caja/recibo', [CajaController::class, 'crearRecibo'])->name('admin.caja.recibo');
        Route::get('/admin/caja/recibo/{id}', [CajaController::class, 'mostrarRecibo'])->name('admin.caja.recibo.mostrar');
        Route::get('/admin/reportes', [ReporteController::class, 'index'])->name('admin.reportes');
        Route::get('/admin/reportes/financiero', [ReporteController::class, 'reporteFinanciero'])->name('admin.reportes.financiero');
        Route::get('/admin/reportes/equipos', [ReporteController::class, 'reporteEquipos'])->name('admin.reportes.equipos');
        Route::get('/admin/alertas', [AlertasMantenimientoController::class, 'index'])->name('admin.alertas');

        Route::prefix('admin/mantenimientos')->name('admin.mantenimientos.')->group(function () {
            Route::get('/', [MantenimientoController::class, 'index'])->name('index');
            Route::put('/{id}', [MantenimientoController::class, 'update'])->name('update');
            Route::delete('/{id}', [MantenimientoController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/json', [MantenimientoController::class, 'getJson'])->name('json');
        });

        Route::get('/admin/auditoria', [AuditoriaController::class, 'index'])->name('admin.auditoria');
        Route::get('/admin/settings/caja', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('admin.settings.caja');
        Route::post('/admin/settings/caja', [\App\Http\Controllers\Admin\SettingsController::class, 'toggle'])->name('admin.settings.caja.toggle');

        Route::prefix('equipamiento')->name('equipamiento.')->group(function () {
            Route::get('/', [EquipamientoController::class, 'index'])->name('index');
            Route::get('/create', [EquipamientoController::class, 'create'])->name('create');
            Route::post('/', [EquipamientoController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [EquipamientoController::class, 'edit'])->name('edit');
            Route::put('/{id}', [EquipamientoController::class, 'update'])->name('update');
            Route::get('/{id}/toggle-estado', [EquipamientoController::class, 'toggleEstado'])->name('toggleEstado');
            Route::post('/{id}/iniciar-mantenimiento', [EquipamientoController::class, 'iniciarMantenimiento'])->name('iniciarMantenimiento');
            Route::delete('/{id}', [EquipamientoController::class, 'destroy'])->name('destroy');
            Route::get('/reportar-falla', [EquipamientoController::class, 'reportarFallaForm'])->name('reportar-falla');
            Route::post('/reportar-falla', [EquipamientoController::class, 'reportarFallaStore'])->name('reportar-falla.store');
            Route::get('/fallas-sin-mantenimiento', [EquipamientoController::class, 'fallasSinMantenimiento'])->name('fallas-sin-mantenimiento');
        });
    });

    // Portal de Recepcionista (idRol = 2)
    Route::middleware('role:2')->group(function () {
        Route::get('/recepcionista', [RecepcionistaController::class, 'dashboard'])->name('recepcionista.dashboard');
        Route::get('/recepcionista/caja', [RecepcionistaController::class, 'caja'])->name('recepcionista.caja');
        Route::get('/recepcionista/socios', [RecepcionistaController::class, 'socios'])->name('recepcionista.socios');

        Route::prefix('recepcionista/ingreso')->name('recepcionista.ingreso.')->group(function () {
            Route::get('/todos', [ControlIngresoController::class, 'listarTodos'])->name('todos');
            Route::get('/buscar', [ControlIngresoController::class, 'buscarSocio'])->name('buscar');
            Route::get('/detalle/{carnet}', [ControlIngresoController::class, 'detalleSocio'])->name('detalle');
            Route::post('/registrar', [ControlIngresoController::class, 'registrarAcceso'])->name('registrar');
        });

        // Rutas de operaciones de caja para recepcionista
        Route::get('/recepcionista/caja/estado', [RecepcionistaController::class, 'estado'])->name('recepcionista.caja.estado');
        Route::post('/recepcionista/caja/abrir', [RecepcionistaController::class, 'abrir'])->name('recepcionista.caja.abrir');
        Route::post('/recepcionista/caja/cerrar/{id}', [RecepcionistaController::class, 'cerrar'])->name('recepcionista.caja.cerrar');
        Route::get('/recepcionista/caja/movimientos', [RecepcionistaController::class, 'movimientos'])->name('recepcionista.caja.movimientos');
        Route::post('/recepcionista/caja/recibo', [RecepcionistaController::class, 'crearRecibo'])->name('recepcionista.caja.recibo');
        Route::get('/recepcionista/caja/recibo/{id}', [RecepcionistaController::class, 'mostrarRecibo'])->name('recepcionista.caja.mostrar_recibo');
    });

    // Portal de Entrenador (idRol = 3)
    Route::middleware('role:3')->group(function () {
        Route::get('/entrenador', [EntrenadorController::class, 'dashboard'])->name('entrenador.dashboard');
        Route::get('/entrenador/clases', [EntrenadorController::class, 'misClases'])->name('entrenador.clases');
        Route::get('/entrenador/clases/{id}/participantes', [EntrenadorController::class, 'participantes'])->name('entrenador.clases.participantes');
        Route::get('/entrenador/fallas', [EntrenadorController::class, 'fallas'])->name('entrenador.fallas');
        Route::post('/entrenador/fallas', [EntrenadorController::class, 'reportarFalla'])->name('entrenador.fallas.store');
    });

    // Portal del Socio (idRol = 4)
    Route::middleware('role:4')->group(function () {
        Route::get('/socio', [SocioPortalController::class, 'dashboard'])->name('socio.dashboard');
        Route::get('/socio/perfil', [SocioPortalController::class, 'perfil'])->name('socio.perfil');
        Route::get('/socio/asistencias', [SocioPortalController::class, 'asistencias'])->name('socio.asistencias');

        Route::prefix('socio/reservas')->name('socio.reservas.')->group(function () {
            Route::get('/', [SocioPortalController::class, 'reservas'])->name('index');
            Route::get('/mis-reservas', [ReservaController::class, 'misReservas'])->name('mis');
            Route::get('/disponibles', [ReservaController::class, 'disponibles'])->name('disponibles');
            Route::post('/reservar', [ReservaController::class, 'reservar'])->name('reservar');
            Route::post('/cancelar', [ReservaController::class, 'cancelar'])->name('cancelar');
        });
    });
});

// Módulo de Reportes (independiente, sin auth)
Route::prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/', [App\Http\Controllers\ReporteController::class, 'index'])->name('index');
    Route::get('/socios', [App\Http\Controllers\ReporteController::class, 'socios'])->name('socios');
    Route::get('/financiero', [App\Http\Controllers\ReporteController::class, 'financiero'])->name('financiero');
    Route::get('/asistencia', [App\Http\Controllers\ReporteController::class, 'asistencia'])->name('asistencia');
    Route::get('/clases', [App\Http\Controllers\ReporteController::class, 'clases'])->name('clases');
    Route::get('/equipamiento', [App\Http\Controllers\ReporteController::class, 'equipamiento'])->name('equipamiento');
    Route::get('/personal', [App\Http\Controllers\ReporteController::class, 'personalDesempeno'])->name('personal');
});

// ==========================================
// CONTROL DE HORARIOS Y ASISTENCIAS (RF5, RF6, RF7)
// ==========================================
Route::get('/admin/horarios', [App\Http\Controllers\Admin\HorarioController::class, 'index'])->name('admin.horarios.index');
Route::get('/admin/horarios/listar/{carnetEmpleado}', [App\Http\Controllers\Admin\HorarioController::class, 'listar']);
Route::post('/admin/horarios', [App\Http\Controllers\Admin\HorarioController::class, 'store']);
Route::put('/admin/horarios/{id}', [App\Http\Controllers\Admin\HorarioController::class, 'update']);
Route::delete('/admin/horarios/{id}', [App\Http\Controllers\Admin\HorarioController::class, 'destroy']);

Route::get('/admin/asistencias', function () { return view('admin.asistencias'); })->name('admin.asistencias.index');
Route::post('/admin/asistencias/entrada', [App\Http\Controllers\Admin\AsistenciaController::class, 'registrarEntrada']);
Route::post('/admin/asistencias/salida', [App\Http\Controllers\Admin\AsistenciaController::class, 'registrarSalida']);

Route::get('/test', function () {
    return "¡Hola! El servidor funciona correctamente.";
});
<?php

use App\Http\Controllers\Admin\SucursalController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\PersonalController;
use App\Http\Controllers\Admin\SocioController;
use App\Http\Controllers\Admin\ClaseController;
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
use App\Http\Controllers\Entrenador\EntrenadorController;
use App\Http\Controllers\Socio\SocioPortalController;
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

        Route::get('/admin/clases', [ClaseController::class, 'index'])->name('admin.clases');
        Route::get('/admin/caja', [CajaController::class, 'index'])->name('admin.caja');
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

        Route::prefix('equipamiento')->name('equipamiento.')->group(function () {
            Route::get('/', [EquipamientoController::class, 'index'])->name('index');
            Route::get('/create', [EquipamientoController::class, 'create'])->name('create');
            Route::post('/', [EquipamientoController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [EquipamientoController::class, 'edit'])->name('edit');
            Route::put('/{id}', [EquipamientoController::class, 'update'])->name('update');
            Route::get('/{id}/toggle-estado', [EquipamientoController::class, 'toggleEstado'])->name('toggleEstado');
            Route::post('/{id}/iniciar-mantenimiento', [EquipamientoController::class, 'iniciarMantenimiento'])->name('iniciarMantenimiento');
            Route::delete('/{id}', [EquipamientoController::class, 'destroy'])->name('destroy');
        });
    });

    // Portal de Recepcionista (idRol = 2)
    Route::middleware('role:2')->group(function () {
        Route::get('/recepcionista', [RecepcionistaController::class, 'dashboard'])->name('recepcionista.dashboard');
        Route::get('/recepcionista/caja', [RecepcionistaController::class, 'caja'])->name('recepcionista.caja');
        Route::get('/recepcionista/socios', [RecepcionistaController::class, 'socios'])->name('recepcionista.socios');
    });

    // Portal de Entrenador (idRol = 3)
    Route::middleware('role:3')->group(function () {
        Route::get('/entrenador', [EntrenadorController::class, 'dashboard'])->name('entrenador.dashboard');
        Route::get('/entrenador/fallas', [EntrenadorController::class, 'fallas'])->name('entrenador.fallas');
        Route::post('/entrenador/fallas', [EntrenadorController::class, 'reportarFalla'])->name('entrenador.fallas.store');
    });

    // Portal del Socio (idRol = 4)
    Route::middleware('role:4')->group(function () {
        Route::get('/socio', [SocioPortalController::class, 'dashboard'])->name('socio.dashboard');
        Route::get('/socio/perfil', [SocioPortalController::class, 'perfil'])->name('socio.perfil');
        Route::get('/socio/asistencias', [SocioPortalController::class, 'asistencias'])->name('socio.asistencias');
        Route::get('/socio/reservas', [SocioPortalController::class, 'reservas'])->name('socio.reservas');
    });
});
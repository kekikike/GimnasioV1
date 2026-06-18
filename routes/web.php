<?php

use App\Http\Controllers\Admin\SucursalController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\PersonalController;
use App\Http\Controllers\Admin\SocioController;
use App\Http\Controllers\Admin\ClaseController;
use App\Http\Controllers\Admin\MembresiaController;
use App\Http\Controllers\Admin\CajaController;
use App\Http\Controllers\Admin\ReporteController;
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

// Redirección inicial al Login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas Públicas de Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth.usuario');

// Grupo de rutas protegidas por el Middleware de seguridad
Route::middleware('auth.usuario')->group(function () {
    
    // Panel Principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // MÓDULO 1: Gestión de Sucursales (CRUD Completo)
    Route::prefix('admin/sucursales')->name('admin.sucursales.')->group(function () {
        Route::get('/', [SucursalController::class, 'index'])->name('index'); 
        Route::get('/listar', [SucursalController::class, 'listar'])->name('listar'); 
        Route::post('/', [SucursalController::class, 'store'])->name('store');
        Route::put('/{id}', [SucursalController::class, 'update'])->name('update');
        Route::delete('/{id}', [SucursalController::class, 'destroy'])->name('destroy');
    });

    // MÓDULO 1: Gestión de Personal (CRUD Completo)
    Route::prefix('admin/personal')->name('admin.personal.')->group(function () {
        Route::get('/', [PersonalController::class, 'index'])->name('index');
        Route::get('/listar', [PersonalController::class, 'listar'])->name('listar');
        Route::post('/', [PersonalController::class, 'store'])->name('store');
        Route::put('/{id}', [PersonalController::class, 'update'])->name('update');
        Route::delete('/{id}', [PersonalController::class, 'destroy'])->name('destroy');
    });

    // MÓDULO 2: Gestión de Socios y Membresías (CRUD Completo)
    Route::prefix('admin/socios')->name('admin.socios.')->group(function () {
        Route::get('/', [SocioController::class, 'index'])->name('index');
        Route::get('/listar', [SocioController::class, 'listar'])->name('listar');
        Route::post('/', [SocioController::class, 'store'])->name('store');
        Route::put('/{id}', [SocioController::class, 'update'])->name('update');
        Route::patch('/{id}/congelar', [SocioController::class, 'congelar'])->name('congelar');
        Route::delete('/{id}', [SocioController::class, 'destroy'])->name('destroy');
    });

    // MÓDULO 2.1: Gestión de Planes de Membresía (RF-12)
    Route::prefix('admin/planes')->name('admin.planes.')->group(function () {
        Route::get('/', [PlanController::class, 'index'])->name('index');
        Route::get('/listar', [PlanController::class, 'listar'])->name('listar');
        Route::post('/', [PlanController::class, 'store'])->name('store');
        Route::put('/{id}', [PlanController::class, 'update'])->name('update');
        Route::delete('/{id}', [PlanController::class, 'destroy'])->name('destroy');
    });

    // Rutas Base de los otros módulos (Garantizan que cargue el menú lateral)
    Route::get('/admin/clases', [ClaseController::class, 'index'])->name('admin.clases');
    Route::get('/admin/caja', [CajaController::class, 'index'])->name('admin.caja');
    Route::get('/admin/reportes', [ReporteController::class, 'index'])->name('admin.reportes');
    Route::get('/admin/auditoria', [AuditoriaController::class, 'index'])->name('admin.auditoria');

    // Módulo de Equipamiento (Desarrollo de Kike)
    Route::prefix('equipamiento')->name('equipamiento.')->group(function () {
        Route::get('/', [EquipamientoController::class, 'index'])->name('index');
        Route::get('/create', [EquipamientoController::class, 'create'])->name('create');
        Route::post('/', [EquipamientoController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [EquipamientoController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EquipamientoController::class, 'update'])->name('update');
        Route::delete('/{id}', [EquipamientoController::class, 'destroy'])->name('destroy');
    });

    // Rutas del portal de Recepcionista
    Route::get('/recepcionista', [RecepcionistaController::class, 'dashboard'])->name('recepcionista.dashboard');
    Route::get('/recepcionista/caja', [RecepcionistaController::class, 'caja'])->name('recepcionista.caja');
    Route::get('/recepcionista/socios', [RecepcionistaController::class, 'socios'])->name('recepcionista.socios');

    // Rutas del portal de Entrenador
    Route::get('/entrenador', [EntrenadorController::class, 'dashboard'])->name('entrenador.dashboard');
    Route::get('/entrenador/fallas', [EntrenadorController::class, 'fallas'])->name('entrenador.fallas');

    // Rutas del portal del Socio (Cliente)
    Route::get('/socio', [SocioPortalController::class, 'dashboard'])->name('socio.dashboard');
    Route::get('/socio/perfil', [SocioPortalController::class, 'perfil'])->name('socio.perfil');
    Route::get('/socio/asistencias', [SocioPortalController::class, 'asistencias'])->name('socio.asistencias');
});
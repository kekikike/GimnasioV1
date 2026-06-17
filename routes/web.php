<?php

use App\Http\Controllers\Admin\SucursalController;
use App\Http\Controllers\Admin\PersonalController;
use App\Http\Controllers\Admin\SocioController;
use App\Http\Controllers\Admin\ClaseController;
use App\Http\Controllers\Admin\CajaController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipamientoController;
use App\Http\Controllers\Recepcionista\RecepcionistaController;
use App\Http\Controllers\Entrenador\EntrenadorController;
use App\Http\Controllers\Socio\SocioPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth.usuario');

Route::middleware('auth.usuario')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin
    Route::get('/admin/sucursales', [SucursalController::class, 'index'])->name('admin.sucursales');
    Route::get('/admin/personal', [PersonalController::class, 'index'])->name('admin.personal');
    Route::get('/admin/socios', [SocioController::class, 'index'])->name('admin.socios');
    Route::get('/admin/clases', [ClaseController::class, 'index'])->name('admin.clases');
    Route::get('/admin/caja', [CajaController::class, 'index'])->name('admin.caja');
    Route::get('/admin/reportes', [ReporteController::class, 'index'])->name('admin.reportes');
    Route::get('/admin/auditoria', [AuditoriaController::class, 'index'])->name('admin.auditoria');

    // Equipamiento
    Route::prefix('equipamiento')->name('equipamiento.')->group(function () {
        Route::get('/', [EquipamientoController::class, 'index'])->name('index');
        Route::get('/create', [EquipamientoController::class, 'create'])->name('create');
        Route::post('/', [EquipamientoController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [EquipamientoController::class, 'edit'])->name('edit');
        Route::put('/{id}', [EquipamientoController::class, 'update'])->name('update');
        Route::delete('/{id}', [EquipamientoController::class, 'destroy'])->name('destroy');
    });

    // Recepcionista
    Route::get('/recepcionista', [RecepcionistaController::class, 'dashboard'])->name('recepcionista.dashboard');
    Route::get('/recepcionista/caja', [RecepcionistaController::class, 'caja'])->name('recepcionista.caja');
    Route::get('/recepcionista/socios', [RecepcionistaController::class, 'socios'])->name('recepcionista.socios');

    // Entrenador
    Route::get('/entrenador', [EntrenadorController::class, 'dashboard'])->name('entrenador.dashboard');
    Route::get('/entrenador/fallas', [EntrenadorController::class, 'fallas'])->name('entrenador.fallas');

    // Socio portal
    Route::get('/socio', [SocioPortalController::class, 'dashboard'])->name('socio.dashboard');
    Route::get('/socio/perfil', [SocioPortalController::class, 'perfil'])->name('socio.perfil');
    Route::get('/socio/asistencias', [SocioPortalController::class, 'asistencias'])->name('socio.asistencias');
    Route::get('/socio/reservas', [SocioPortalController::class, 'reservas'])->name('socio.reservas');
});

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
use App\Http\Controllers\Admin\MarcaController;
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
use App\Http\Controllers\PerfilController;
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

    // Perfil de usuario (accesible por cualquier rol autenticado)
    Route::get('/perfil', [PerfilController::class, 'perfil'])->name('perfil');
    Route::put('/perfil', [PerfilController::class, 'updatePerfil']);

    // Panel de Administrador (idRol = 1)
    Route::middleware('role:1')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('admin/sucursales')->name('admin.sucursales.')->group(function () {
            Route::get('/', [SucursalController::class, 'index'])->name('index'); 
            Route::get('/listar', [SucursalController::class, 'listar'])->name('listar');
            Route::get('/inactivas', [SucursalController::class, 'listarInactivas']);
            Route::patch('/{id}/restaurar', [SucursalController::class, 'restaurar']);Route::post('/', [SucursalController::class, 'store'])->name('store');
            Route::put('/{id}', [SucursalController::class, 'update'])->name('update');
            Route::delete('/{id}', [SucursalController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('admin/personal')->name('admin.personal.')->group(function () {
            Route::get('/', [PersonalController::class, 'index'])->name('index');
            Route::get('/listar', [PersonalController::class, 'listar'])->name('listar');
            Route::get('/listar-inactivos', [PersonalController::class, 'listarInactivos'])->name('listar-inactivos');
            Route::get('/{id}/detalle', [PersonalController::class, 'detalle'])->name('detalle');
            Route::post('/', [PersonalController::class, 'store'])->name('store');
            Route::put('/{id}', [PersonalController::class, 'update'])->name('update');
            Route::delete('/{id}', [PersonalController::class, 'destroy'])->name('destroy');
            Route::put('/{id}/acabar-contrato', [PersonalController::class, 'acabarContrato'])->name('acabar-contrato');
            Route::put('/{id}/reactivar', [PersonalController::class, 'reactivar'])->name('reactivar');
        });

        Route::prefix('admin/socios')->name('admin.socios.')->group(function () {
            Route::get('/', [SocioController::class, 'index'])->name('index');
            Route::get('/listar', [SocioController::class, 'listar'])->name('listar');
            Route::get('/{carnet}/detalle', [SocioController::class, 'detalle'])->name('detalle');
            Route::post('/', [SocioController::class, 'store'])->name('store');
            Route::put('/{id}', [SocioController::class, 'update'])->name('update');

            Route::post('/{carnet}/congelar-membresia', [SocioController::class, 'congelarMembresia'])->name('congelarMembresia');
            Route::post('/{carnet}/activar-membresia', [SocioController::class, 'activarMembresia'])->name('activarMembresia');
            Route::get('/{id}/notificaciones', [SocioController::class, 'notificaciones'])->name('notificaciones');
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
            Route::get('/create', [ClaseGrupalController::class, 'create'])->name('create');
            Route::get('/listar', [ClaseGrupalController::class, 'listar'])->name('listar');
            Route::post('/', [ClaseGrupalController::class, 'store'])->name('store');
            Route::put('/{id}', [ClaseGrupalController::class, 'update'])->name('update');
            Route::delete('/{id}', [ClaseGrupalController::class, 'destroy'])->name('destroy');
            Route::put('/{id}/reactivar', [ClaseGrupalController::class, 'reactivar'])->name('reactivar');
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
        Route::post('/admin/caja/salidas', [CajaController::class, 'salidasStore'])->name('admin.caja.salidas.store');
        Route::get('/admin/caja/salidas', [CajaController::class, 'salidasListar'])->name('admin.caja.salidas.listar');
        Route::get('/admin/caja/buscar-socio/{carnet}', [CajaController::class, 'buscarSocio'])->name('admin.caja.buscarSocio');
        Route::get('/admin/caja/planes', [CajaController::class, 'planes'])->name('admin.caja.planes');
        Route::get('/admin/reportes', [ReporteController::class, 'index'])->name('admin.reportes');
        Route::get('/admin/reportes/financiero', [ReporteController::class, 'reporteFinanciero'])->name('admin.reportes.financiero');
        Route::get('/admin/reportes/equipos', [ReporteController::class, 'reporteEquipos'])->name('admin.reportes.equipos');
        Route::get('/admin/reportes/membresias', [ReporteController::class, 'reporteMembresias'])->name('admin.reportes.membresias');
        Route::get('/admin/reportes/renovaciones', [ReporteController::class, 'reporteRenovaciones'])->name('admin.reportes.renovaciones');
        Route::get('/admin/alertas', [AlertasMantenimientoController::class, 'index'])->name('admin.alertas');

        Route::prefix('admin/mantenimientos')->name('admin.mantenimientos.')->group(function () {
            Route::get('/', [MantenimientoController::class, 'index'])->name('index');
            Route::put('/{id}', [MantenimientoController::class, 'update'])->name('update');
            Route::delete('/{id}', [MantenimientoController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/json', [MantenimientoController::class, 'getJson'])->name('json');
        });

        Route::get('/admin/auditoria', [AuditoriaController::class, 'index'])->name('admin.auditoria');


        Route::prefix('admin/marcas')->name('admin.marcas.')->group(function () {
            Route::get('/', [MarcaController::class, 'index'])->name('index');
            Route::get('/listar', [MarcaController::class, 'listar'])->name('listar');
            Route::post('/', [MarcaController::class, 'store'])->name('store');
            Route::put('/{id}', [MarcaController::class, 'update'])->name('update');
            Route::delete('/{id}', [MarcaController::class, 'destroy'])->name('destroy');
        });

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
            Route::post('/bloquear', [ControlIngresoController::class, 'bloquearSocio'])->name('bloquear');
            Route::get('/{carnet}/reservas-hoy', [ControlIngresoController::class, 'reservasHoy'])->name('reservas-hoy');
            Route::post('/marcar-asistencia-clase', [ControlIngresoController::class, 'marcarAsistenciaClase'])->name('marcar-asistencia-clase');
        });

        // Rutas de operaciones de caja para recepcionista
        Route::get('/recepcionista/caja/estado', [RecepcionistaController::class, 'estado'])->name('recepcionista.caja.estado');
        Route::post('/recepcionista/caja/abrir', [RecepcionistaController::class, 'abrir'])->name('recepcionista.caja.abrir');
        Route::post('/recepcionista/caja/{id}/cerrar', [RecepcionistaController::class, 'cerrar'])->name('recepcionista.caja.cerrar');
        Route::get('/recepcionista/caja/movimientos', [RecepcionistaController::class, 'movimientos'])->name('recepcionista.caja.movimientos');
        Route::post('/recepcionista/caja/recibo', [RecepcionistaController::class, 'crearRecibo'])->name('recepcionista.caja.recibo');
        Route::get('/recepcionista/caja/recibo/{id}', [RecepcionistaController::class, 'mostrarRecibo'])->name('recepcionista.caja.mostrar_recibo');
        Route::post('/recepcionista/caja/salidas', [RecepcionistaController::class, 'salidasStore'])->name('recepcionista.caja.salidas.store');
        Route::get('/recepcionista/caja/salidas', [RecepcionistaController::class, 'salidasListar'])->name('recepcionista.caja.salidas.listar');
        Route::get('/recepcionista/caja/buscar-socio/{carnet}', [RecepcionistaController::class, 'buscarSocio'])->name('recepcionista.caja.buscarSocio');
        Route::get('/recepcionista/caja/planes', [RecepcionistaController::class, 'planes'])->name('recepcionista.caja.planes');
    });

    // Portal de Entrenador (idRol = 3)
    Route::middleware('role:3')->group(function () {
        Route::get('/entrenador', [EntrenadorController::class, 'dashboard'])->name('entrenador.dashboard');
        Route::get('/entrenador/clases', [EntrenadorController::class, 'misClases'])->name('entrenador.clases');
        Route::get('/entrenador/clases/{id}/participantes', [EntrenadorController::class, 'participantes'])->name('entrenador.clases.participantes');
        Route::get('/entrenador/fallas', [EntrenadorController::class, 'fallas'])->name('entrenador.fallas');
        Route::post('/entrenador/fallas', [EntrenadorController::class, 'reportarFalla'])->name('entrenador.fallas.store');

        Route::get('/entrenador/asistencias-clase', [EntrenadorController::class, 'asistenciasClase'])->name('entrenador.asistencias');
        Route::get('/entrenador/asistencias-clase/hoy', [EntrenadorController::class, 'clasesHoy'])->name('entrenador.asistencias.hoy');
        Route::get('/entrenador/asistencias-clase/{id}/alumnos', [EntrenadorController::class, 'alumnosClase'])->name('entrenador.asistencias.alumnos');
        Route::post('/entrenador/asistencias-clase/marcar', [EntrenadorController::class, 'marcarAsistencia'])->name('entrenador.asistencias.marcar');
    });

    // Portal del Socio (idRol = 4)
    Route::middleware('role:4')->group(function () {
        Route::get('/socio', [SocioPortalController::class, 'dashboard'])->name('socio.dashboard');
        Route::get('/socio/perfil', [SocioPortalController::class, 'perfil'])->name('socio.perfil');
        Route::put('/socio/perfil', [SocioPortalController::class, 'updatePerfil']);
        Route::get('/socio/asistencias', [SocioPortalController::class, 'asistencias'])->name('socio.asistencias');
        Route::get('/socio/historial-membresias', [SocioPortalController::class, 'historialMembresias'])->name('socio.historial-membresias');
        Route::get('/socio/notificaciones', [SocioPortalController::class, 'notificaciones'])->name('socio.notificaciones');

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
    Route::get('/socios/{carnet}', [App\Http\Controllers\ReporteController::class, 'detalle'])->name('socios.detalle');
    Route::get('/financiero/{idCaja}', [App\Http\Controllers\ReporteController::class, 'financieroDetalle'])->name('financiero.detalle');
    Route::get('/clases/{idClase}', [App\Http\Controllers\ReporteController::class, 'claseDetalle'])->name('clases.detalle');
    Route::post('/generar-pdf', [App\Http\Controllers\ReporteController::class, 'generarPDF'])->name('generar.pdf');
});

// ==========================================
// CONTROL DE HORARIOS Y ASISTENCIAS (RF5, RF6, RF7)
// ==========================================
Route::get('/admin/horarios', [App\Http\Controllers\Admin\HorarioController::class, 'index'])->name('admin.horarios.index');
Route::get('/admin/horarios/listar/{carnetEmpleado}', [App\Http\Controllers\Admin\HorarioController::class, 'listar']);
Route::get('/admin/horarios/buscar', [App\Http\Controllers\Admin\HorarioController::class, 'buscar']);
Route::post('/admin/horarios', [App\Http\Controllers\Admin\HorarioController::class, 'store']);
Route::put('/admin/horarios/{id}', [App\Http\Controllers\Admin\HorarioController::class, 'update']);
Route::delete('/admin/horarios/{id}', [App\Http\Controllers\Admin\HorarioController::class, 'destroy']);

Route::get('/admin/asistencias', function () { return view('admin.asistencias'); })->name('admin.asistencias.index');
Route::post('/admin/asistencias/entrada', [App\Http\Controllers\Admin\AsistenciaController::class, 'registrarEntrada']);
Route::post('/admin/asistencias/salida', [App\Http\Controllers\Admin\AsistenciaController::class, 'registrarSalida']);

Route::get('/test', function () {
    return "¡Hola! El servidor funciona correctamente.";
});
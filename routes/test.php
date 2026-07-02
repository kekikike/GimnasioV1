<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/test-listar', function() {
    try {
        DB::statement('CALL sp_TClaseGrupales_ActualizarEstados()');
        echo "SP OK\n";
        
        $clases = DB::table('TClaseGrupales as cg')
            ->join('TActividades as a', 'cg.idActividad', '=', 'a.idActividad')
            ->join('TEmpleados as e', 'cg.carnetEmpleado', '=', 'e.carnetEmpleado')
            ->join('TUsuarios as u', 'e.idUsuario', '=', 'u.idUsuario')
            ->join('TSucursales as s', 'cg.idSucursal', '=', 's.idSucursal')
            ->where('cg.estadoA', 1)
            ->select(
                'cg.*',
                'a.nombreActividad',
                'u.nombre1',
                'u.apellido1',
                's.nombre as nombreSucursal',
                DB::raw("CASE
                    WHEN CONCAT(cg.fecha, ' ', cg.horaFin) < NOW() THEN 'Finalizada'
                    WHEN CONCAT(cg.fecha, ' ', cg.horaInicio) <= NOW()
                         AND CONCAT(cg.fecha, ' ', cg.horaFin) >= NOW() THEN 'Cursandose'
                    ELSE cg.estadoClase
                END as estadoDinamico")
            )
            ->orderBy('cg.fecha', 'desc')
            ->orderBy('cg.horaInicio', 'desc')
            ->get();
        echo "Query OK, rows: " . count($clases) . "\n";
        return response()->json($clases);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage(), 'sql' => $e->getSql() ?? null], 500);
    }
});

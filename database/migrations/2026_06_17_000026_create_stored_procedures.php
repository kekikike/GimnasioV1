<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables;

    public function __construct()
    {
        $this->tables = [
            'TRoles' => ['pk' => 'idRol', 'cols' => ['nombreRol'], 'auditCols' => ['nombreRol'], 'autoinc' => true],
            'TSucursales' => ['pk' => 'idSucursal', 'cols' => ['nombre', 'direccion', 'telefono', 'estado'], 'auditCols' => ['nombre', 'direccion', 'telefono', 'estado'], 'autoinc' => true],
            'TPlanes' => ['pk' => 'idPlan', 'cols' => ['nombrePlan', 'descripcion', 'costoPlan', 'duracionDias'], 'auditCols' => ['nombrePlan', 'descripcion', 'costoPlan', 'duracionDias'], 'autoinc' => true],
            'TActividades' => ['pk' => 'idActividad', 'cols' => ['nombreActividad', 'descripcionActividad', 'estado'], 'auditCols' => ['nombreActividad', 'descripcionActividad', 'estado'], 'autoinc' => true],
            'TMarcas' => ['pk' => 'idMarca', 'cols' => ['nombreMarca'], 'auditCols' => ['nombreMarca'], 'autoinc' => true],
            'TUsuarios' => ['pk' => 'idUsuario', 'cols' => ['idRol', 'nombre1', 'nombre2', 'apellido1', 'apellido2', 'correo', 'telefono', 'contrasena', 'estado'], 'auditCols' => ['idRol', 'nombre1', 'nombre2', 'apellido1', 'apellido2', 'correo', 'telefono', 'contrasena', 'estado'], 'autoinc' => true],
            'TEmpleados' => ['pk' => 'carnetEmpleado', 'cols' => ['idUsuario', 'idSucursal', 'sueldo', 'especialidad', 'fechaContratoInicio', 'fechaContratoFin'], 'auditCols' => ['idUsuario', 'idSucursal', 'sueldo', 'especialidad', 'fechaContratoInicio', 'fechaContratoFin'], 'autoinc' => false],
            'TSocios' => ['pk' => 'carnetSocio', 'cols' => ['idUsuario', 'direccion', 'fotografiaUrl', 'nombreContactoEmergencia', 'telefonoContactoEmergencia', 'observacionesMedicas', 'estadoSocio', 'strikes'], 'auditCols' => ['idUsuario', 'direccion', 'fotografiaUrl', 'nombreContactoEmergencia', 'telefonoContactoEmergencia', 'observacionesMedicas', 'estadoSocio', 'strikes'], 'autoinc' => false],
            'THorarioLaborales' => ['pk' => 'idHorario', 'cols' => ['carnetEmpleado', 'diaSemana', 'horaEntradaEsperada', 'horaSalidaEsperada'], 'auditCols' => ['carnetEmpleado', 'diaSemana', 'horaEntradaEsperada', 'horaSalidaEsperada'], 'autoinc' => true],

            'TEsquemaSueldos' => ['pk' => 'idEsquemaSueldo', 'cols' => ['carnetEmpleado', 'modalidadPago', 'montoBase', 'tarifaHoraOClase'], 'auditCols' => ['carnetEmpleado', 'modalidadPago', 'montoBase', 'tarifaHoraOClase'], 'autoinc' => true],
            'TMembresias' => ['pk' => 'idMembresia', 'cols' => ['idPlan', 'carnetSocio', 'idSucursal', 'fechaInicioMembresia', 'fechaFinMembresia', 'estadoMembresia'], 'auditCols' => ['idPlan', 'carnetSocio', 'idSucursal', 'fechaInicioMembresia', 'fechaFinMembresia', 'estadoMembresia'], 'autoinc' => true],
            'TControlAccesos' => ['pk' => 'idControlAcceso', 'cols' => ['carnetSocio', 'idSucursal', 'fechaAcceso', 'horaAcceso', 'bloqueo', 'motivoDenegacion'], 'auditCols' => ['carnetSocio', 'idSucursal', 'fechaAcceso', 'horaAcceso', 'bloqueo', 'motivoDenegacion'], 'autoinc' => true],
            'TPenalizaciones' => ['pk' => 'idPenalizacion', 'cols' => ['carnetSocio', 'idReserva', 'fecha', 'estado'], 'auditCols' => ['carnetSocio', 'idReserva', 'fecha', 'estado'], 'autoinc' => true],
            'TNotificaciones' => ['pk' => 'idNotificacion', 'cols' => ['carnetSocio', 'tipoNotificacion', 'mensaje', 'fechaEnvio', 'estado'], 'auditCols' => ['carnetSocio', 'tipoNotificacion', 'mensaje', 'fechaEnvio', 'estado'], 'autoinc' => true],
            'TClaseGrupales' => ['pk' => 'idClaseGrupal', 'cols' => ['idActividad', 'carnetEmpleado', 'idSucursal', 'fecha', 'horaInicio', 'horaFin', 'cupoMaximo', 'estadoClase'], 'auditCols' => ['idActividad', 'carnetEmpleado', 'idSucursal', 'fecha', 'horaInicio', 'horaFin', 'cupoMaximo', 'estadoClase'], 'autoinc' => true],
            'TReservas' => ['pk' => 'idReserva', 'cols' => ['idClaseGrupal', 'carnetSocio', 'fechaReserva', 'estadoReserva'], 'auditCols' => ['idClaseGrupal', 'carnetSocio', 'fechaReserva', 'estadoReserva'], 'autoinc' => true],
            'TCajas' => ['pk' => 'idCaja', 'cols' => ['idSucursal', 'carnetEmpleado', 'fechaApertura', 'horaApertura', 'montoApertura', 'montoCierre', 'montoCierreCalculado', 'diferenciaArqueo', 'estadoCaja'], 'auditCols' => ['idSucursal', 'carnetEmpleado', 'fechaApertura', 'horaApertura', 'montoApertura', 'montoCierre', 'montoCierreCalculado', 'diferenciaArqueo', 'estadoCaja'], 'autoinc' => true],
            'TRecibos' => ['pk' => 'idRecibo', 'cols' => ['idCaja', 'idMembresia', 'montoTotal', 'fechaPago', 'estadoRecibo'], 'auditCols' => ['idCaja', 'idMembresia', 'montoTotal', 'fechaPago', 'estadoRecibo'], 'autoinc' => true],
            'TDetalleMetodoPagos' => ['pk' => 'idMetodoPago', 'cols' => ['idRecibo', 'idMetodoPagoFK', 'monto'], 'auditCols' => ['idRecibo', 'idMetodoPagoFK', 'monto'], 'autoinc' => true],
            'TEquipamientos' => ['pk' => 'idEquipo', 'cols' => ['idSucursal', 'idMarca', 'nombreEquipo', 'modelo', 'fechaAdquisicion', 'estadoEquipo'], 'auditCols' => ['idSucursal', 'idMarca', 'nombreEquipo', 'modelo', 'fechaAdquisicion', 'estadoEquipo'], 'autoinc' => true],
            'TMantenimientoPreventivos' => ['pk' => 'idMantenimiento', 'cols' => ['idEquipo', 'fechaProgramada', 'fechaRealizada', 'descripcionMantenimiento', 'costoMantenimiento', 'tecnicoAsignado', 'estadoMantenimiento'], 'auditCols' => ['idEquipo', 'fechaProgramada', 'fechaRealizada', 'descripcionMantenimiento', 'costoMantenimiento', 'tecnicoAsignado', 'estadoMantenimiento'], 'autoinc' => true],
            'TReporteFallas' => ['pk' => 'idReporteFalla', 'cols' => ['idEquipo', 'carnetEmpleado', 'fechaReporte', 'descripcionFalla', 'gravedad', 'estadoReporte'], 'auditCols' => ['idEquipo', 'carnetEmpleado', 'fechaReporte', 'descripcionFalla', 'gravedad', 'estadoReporte'], 'autoinc' => true],
            'TAuditorias' => ['pk' => 'idAuditoria', 'cols' => ['tablaNombre', 'registroId', 'accion', 'campo', 'valorAnterior', 'valorNuevo', 'usuarioA', 'fechaA', 'direccionIP', 'detalles'], 'auditCols' => [], 'autoinc' => true],
            'TMetodoPagos' => ['pk' => 'idMetodoPago', 'cols' => ['nombreMetodoPago'], 'auditCols' => ['nombreMetodoPago'], 'autoinc' => true],
            'TPagoSueldos' => ['pk' => 'idPagoSueldo', 'cols' => ['carnetEmpleado', 'fechaPago', 'monto'], 'auditCols' => ['carnetEmpleado', 'fechaPago', 'monto'], 'autoinc' => true],
        ];
    }

    public function up(): void
    {
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

        foreach ($this->tables as $table => $def) {
            if ($table !== 'TAuditorias') {
                $this->dropAndCreate($pdo, $table, 'Insert', $this->buildInsert($table, $def));
                $this->dropAndCreate($pdo, $table, 'Update', $this->buildUpdate($table, $def));
                $this->dropAndCreate($pdo, $table, 'Delete', $this->buildDelete($table, $def));
            }
            $this->dropAndCreate($pdo, $table, 'Select', $this->buildSelect($table, $def));
            $this->dropAndCreate($pdo, $table, 'SelectById', $this->buildSelectById($table, $def));
        }
        $this->dropAndCreate($pdo, 'TUsuarios', 'Login', $this->buildLogin());
        $this->dropAndCreate($pdo, 'TUsuarios', 'FindByEmail', $this->buildFindByEmail());

        $this->dropAndCreate($pdo, 'TSocios', 'GetByUserId', $this->buildGetSocioByUserId());
        $this->dropAndCreate($pdo, 'TMembresias', 'GetActiveBySocio', $this->buildGetMembresiaBySocio());
        $this->dropAndCreate($pdo, 'TControlAccesos', 'GetBySocio', $this->buildGetAccesosBySocio());
        $this->dropAndCreate($pdo, 'TReservas', 'GetBySocio', $this->buildGetReservasBySocio());
        $this->dropAndCreate($pdo, 'TClaseGrupales', 'GetAvailable', $this->buildGetClasesAvailable());

        $this->dropAndCreate($pdo, 'TEquipamientos', 'GetOperativosWithDetails', $this->buildGetEquiposOperativos());
        $this->dropAndCreate($pdo, 'TEquipamientos', 'GetOperativosBySucursal', $this->buildGetEquiposOperativosBySucursal());
        $this->dropAndCreate($pdo, 'TEquipamientos', 'GetByEstado', $this->buildGetEquiposByEstado());
        $this->dropAndCreate($pdo, 'TMantenimientoPreventivos', 'CountRealizadoByEquipo', $this->buildCountRealizadoByEquipo());
        $this->dropAndCreate($pdo, 'TMantenimientoPreventivos', 'GetProximos', $this->buildGetProximosMantenimientos());
        $this->dropAndCreate($pdo, 'TMantenimientoPreventivos', 'GetAlertasPendientes', $this->buildGetAlertasPendientes());
        $this->dropAndCreate($pdo, 'TMantenimientoPreventivos', 'GetResumen', $this->buildGetResumenMantenimientos());
        $this->dropAndCreate($pdo, 'TMantenimientoPreventivos', 'GetFiltered', $this->buildGetMantenimientosFiltered());
        $this->dropAndCreate($pdo, 'TEmpleados', 'GetAllWithDetails', $this->buildGetEmpleadosWithDetails());
        $this->dropAndCreate($pdo, 'TSocios', 'GetAllWithUsers', $this->buildGetSociosWithUsers());
        $this->dropAndCreate($pdo, 'TUsuarios', 'GetCajeros', $this->buildGetCajeros());
        $this->dropAndCreate($pdo, 'TRecibos', 'GetReporteFinanciero', $this->buildGetReporteFinanciero());
        $this->dropAndCreate($pdo, 'TReporteFallas', 'GetHistorial', $this->buildGetHistorialFallas());
        $this->dropAndCreate($pdo, 'TReporteFallas', 'GetByEmpleado', $this->buildGetReportesByEmpleado());
        $this->dropAndCreate($pdo, 'TEquipamientos', 'GetFallasSinMantenimiento', $this->buildGetFallasSinMantenimiento());
        $this->dropAndCreate($pdo, 'TMantenimientoPreventivos', 'GetHistorial', $this->buildGetHistorialMantenimientos());

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }

    private function dropAndCreate($pdo, string $table, string $action, string $createSql): void
    {
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_{$table}_{$action}");
        $pdo->exec($createSql);
    }

    public function down(): void
    {
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

        foreach ($this->tables as $table => $def) {
            if ($table !== 'TAuditorias') {
                $pdo->exec("DROP PROCEDURE IF EXISTS sp_{$table}_Insert");
                $pdo->exec("DROP PROCEDURE IF EXISTS sp_{$table}_Update");
                $pdo->exec("DROP PROCEDURE IF EXISTS sp_{$table}_Delete");
            }
            $pdo->exec("DROP PROCEDURE IF EXISTS sp_{$table}_Select");
            $pdo->exec("DROP PROCEDURE IF EXISTS sp_{$table}_SelectById");
        }
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TUsuarios_Login");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TUsuarios_FindByEmail");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TEquipamientos_GetOperativosWithDetails");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TEquipamientos_GetOperativosBySucursal");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TEquipamientos_GetByEstado");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TMantenimientoPreventivos_CountRealizadoByEquipo");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TMantenimientoPreventivos_GetProximos");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TMantenimientoPreventivos_GetAlertasPendientes");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TMantenimientoPreventivos_GetResumen");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TMantenimientoPreventivos_GetFiltered");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TEmpleados_GetAllWithDetails");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TSocios_GetAllWithUsers");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TUsuarios_GetCajeros");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TRecibos_GetReporteFinanciero");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TReporteFallas_GetHistorial");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TReporteFallas_GetByEmpleado");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TEquipamientos_GetFallasSinMantenimiento");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TMantenimientoPreventivos_GetHistorial");

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }

    private function buildInsert(string $table, array $def): string
    {
        $pk = $def['pk'];
        $cols = $def['cols'];
        $auditCols = $def['auditCols'];
        $autoinc = $def['autoinc'];

        $insertCols = array_merge($cols, ['estadoA', 'usuarioA']);
        $insertVals = array_merge(
            array_map(fn($c) => "p_{$c}", $cols),
            ['1', 'p_usuarioA']
        );
        $insertColsStr = implode(', ', $insertCols);
        $insertValsStr = implode(', ', $insertVals);

        $paramParts = [];
        if (!$autoinc) {
            $paramParts[] = "IN p_{$pk} VARCHAR(500)";
        }
        foreach ($cols as $col) {
            $paramParts[] = "IN p_{$col} VARCHAR(500)";
        }
        $paramParts[] = 'IN p_usuarioA INT';
        $paramParts[] = 'IN p_direccionIP VARCHAR(50)';
        $params = implode(",\n    ", $paramParts);

        $auditParts = array_map(fn($c) => "COALESCE(p_{$c}, '')", $auditCols);
        $auditVals = empty($auditParts) ? "''" : 'CONCAT_WS(\'|\', ' . implode(', ', $auditParts) . ')';

        return "CREATE PROCEDURE sp_{$table}_Insert(\n    {$params}\n)\n"
            . "BEGIN\n"
            . "    DECLARE v_newId INT;\n\n"
            . "    INSERT INTO {$table} ({$insertColsStr})\n"
            . "    VALUES ({$insertValsStr});\n\n"
            . "    SET v_newId = LAST_INSERT_ID();\n\n"
            . "    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)\n"
            . "    VALUES ('{$table}', v_newId, 'I', 'insercion', NULL, {$auditVals}, p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');\n\n"
            . "    SELECT v_newId AS id;\n"
            . "END";
    }

    private function buildUpdate(string $table, array $def): string
    {
        $pk = $def['pk'];
        $cols = $def['cols'];
        $auditCols = $def['auditCols'];

        $paramParts = ["IN p_{$pk} INT"];
        foreach ($cols as $col) {
            $paramParts[] = "IN p_{$col} VARCHAR(500)";
        }
        $paramParts[] = 'IN p_usuarioA INT';
        $paramParts[] = 'IN p_direccionIP VARCHAR(50)';
        $params = implode(",\n    ", $paramParts);

        $setParts = array_map(fn($c) => "{$c} = p_{$c}", $cols);
        $setStr = implode(",\n        ", $setParts);

        $declareLines = array_map(fn($c) => "DECLARE v_old_{$c} VARCHAR(500);", $auditCols);
        $declareStr = implode("\n    ", $declareLines);

        $selectParts = array_map(fn($c) => "COALESCE({$c}, '')", $auditCols);
        $selectStr = implode(", ", $selectParts);

        $intoParts = array_map(fn($c) => "v_old_{$c}", $auditCols);
        $intoStr = implode(", ", $intoParts);

        $ifBlocks = [];
        foreach ($auditCols as $col) {
            $ifBlocks[] = "    IF v_old_{$col} <> p_{$col} OR (v_old_{$col} IS NULL AND p_{$col} IS NOT NULL) OR (v_old_{$col} IS NOT NULL AND p_{$col} IS NULL) THEN\n"
                . "        SET v_campo = CONCAT_WS('|', v_campo, '{$col}');\n"
                . "        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_{$col}, ''));\n"
                . "        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_{$col}, ''));\n"
                . "    END IF;";
        }
        $ifStr = implode("\n", $ifBlocks);

        return "CREATE PROCEDURE sp_{$table}_Update(\n    {$params}\n)\n"
            . "BEGIN\n"
            . "    {$declareStr}\n"
            . "    DECLARE v_campo TEXT DEFAULT NULL;\n"
            . "    DECLARE v_viejo TEXT DEFAULT NULL;\n"
            . "    DECLARE v_nuevo TEXT DEFAULT NULL;\n\n"
            . "    SELECT {$selectStr}\n"
            . "    INTO {$intoStr}\n"
            . "    FROM {$table} WHERE {$pk} = p_{$pk};\n\n"
            . "    UPDATE {$table}\n"
            . "    SET {$setStr}\n"
            . "    WHERE {$pk} = p_{$pk};\n\n"
            . "    {$ifStr}\n\n"
            . "    IF LENGTH(v_campo) > 0 THEN\n"
            . "        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)\n"
            . "        VALUES ('{$table}', p_{$pk}, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,\n"
            . "            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));\n"
            . "    END IF;\n"
            . "END";
    }

    private function buildDelete(string $table, array $def): string
    {
        $pk = $def['pk'];
        return "CREATE PROCEDURE sp_{$table}_Delete(\n    IN p_{$pk} INT,\n    IN p_usuarioA INT,\n    IN p_direccionIP VARCHAR(50)\n)\n"
            . "BEGIN\n"
            . "    UPDATE {$table}\n"
            . "    SET estadoA = 0\n"
            . "    WHERE {$pk} = p_{$pk};\n\n"
            . "    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)\n"
            . "    VALUES ('{$table}', p_{$pk}, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');\n"
            . "END";
    }

    private function buildSelect(string $table, array $def): string
    {
        $pk = $def['pk'];
        return "CREATE PROCEDURE sp_{$table}_Select()\n"
            . "BEGIN\n"
            . "    SELECT * FROM {$table}\n"
            . "    WHERE estadoA = 1\n"
            . "    ORDER BY {$pk} DESC;\n"
            . "END";
    }

    private function buildSelectById(string $table, array $def): string
    {
        $pk = $def['pk'];
        return "CREATE PROCEDURE sp_{$table}_SelectById(\n    IN p_{$pk} INT\n)\n"
            . "BEGIN\n"
            . "    SELECT * FROM {$table}\n"
            . "    WHERE {$pk} = p_{$pk};\n"
            . "END";
    }

    private function buildLogin(): string
    {
        return "CREATE PROCEDURE sp_TUsuarios_Login(\n"
            . "    IN p_correo VARCHAR(150),\n"
            . "    IN p_contrasena VARCHAR(255)\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT u.*, r.nombreRol\n"
            . "    FROM TUsuarios u\n"
            . "    INNER JOIN TRoles r ON r.idRol = u.idRol\n"
            . "    WHERE u.correo = p_correo COLLATE utf8mb4_unicode_ci\n"
            . "      AND u.contrasena = p_contrasena\n"
            . "      AND u.estado = 1\n"
            . "      AND u.estadoA = 1\n"
            . "    LIMIT 1;\n"
            . "END";
    }

    private function buildFindByEmail(): string
    {
        return "CREATE PROCEDURE sp_TUsuarios_FindByEmail(\n"
            . "    IN p_correo VARCHAR(150)\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT u.*, r.nombreRol\n"
            . "    FROM TUsuarios u\n"
            . "    INNER JOIN TRoles r ON r.idRol = u.idRol\n"
            . "    WHERE u.correo = p_correo COLLATE utf8mb4_unicode_ci\n"
            . "      AND u.estado = 1\n"
            . "      AND u.estadoA = 1\n"
            . "    LIMIT 1;\n"
            . "END";
    }

    private function buildGetSocioByUserId(): string
    {
        return "CREATE PROCEDURE sp_TSocios_GetByUserId(\n"
            . "    IN p_idUsuario INT\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT s.*, u.nombre1, u.nombre2, u.apellido1, u.apellido2, u.correo, u.telefono\n"
            . "    FROM TSocios s\n"
            . "    INNER JOIN TUsuarios u ON u.idUsuario = s.idUsuario\n"
            . "    WHERE u.idUsuario = p_idUsuario\n"
            . "      AND s.estadoA = 1\n"
            . "    LIMIT 1;\n"
            . "END";
    }

    private function buildGetMembresiaBySocio(): string
    {
        return "CREATE PROCEDURE sp_TMembresias_GetActiveBySocio(\n"
            . "    IN p_carnetSocio INT\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT m.*, p.nombrePlan, p.descripcion AS descripcionPlan, p.costoPlan, p.duracionDias\n"
            . "    FROM TMembresias m\n"
            . "    INNER JOIN TPlanes p ON p.idPlan = m.idPlan\n"
            . "    WHERE m.carnetSocio = p_carnetSocio\n"
            . "      AND m.estadoA = 1\n"
            . "    ORDER BY m.idMembresia DESC\n"
            . "    LIMIT 1;\n"
            . "END";
    }

    private function buildGetAccesosBySocio(): string
    {
        return "CREATE PROCEDURE sp_TControlAccesos_GetBySocio(\n"
            . "    IN p_carnetSocio INT\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT ca.*, s.nombre AS sucursal\n"
            . "    FROM TControlAccesos ca\n"
            . "    LEFT JOIN TSucursales s ON s.idSucursal = ca.idSucursal\n"
            . "    WHERE ca.carnetSocio = p_carnetSocio\n"
            . "      AND ca.estadoA = 1\n"
            . "    ORDER BY ca.fechaAcceso DESC, ca.horaAcceso DESC\n"
            . "    LIMIT 20;\n"
            . "END";
    }

    private function buildGetReservasBySocio(): string
    {
        return "CREATE PROCEDURE sp_TReservas_GetBySocio(\n"
            . "    IN p_carnetSocio INT\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT r.*, cg.fecha, cg.horaInicio, cg.horaFin, a.nombreActividad,\n"
            . "           s.nombre AS sucursalNombre,\n"
            . "           CONCAT(u.nombre1, ' ', u.apellido1) AS entrenador\n"
            . "    FROM TReservas r\n"
            . "    INNER JOIN TClaseGrupales cg ON cg.idClaseGrupal = r.idClaseGrupal\n"
            . "    INNER JOIN TActividades a ON a.idActividad = cg.idActividad\n"
            . "    INNER JOIN TSucursales s ON s.idSucursal = cg.idSucursal\n"
            . "    INNER JOIN TEmpleados e ON e.carnetEmpleado = cg.carnetEmpleado\n"
            . "    INNER JOIN TUsuarios u ON u.idUsuario = e.idUsuario\n"
            . "    WHERE r.carnetSocio = p_carnetSocio\n"
            . "      AND r.estadoA = 1\n"
            . "    ORDER BY cg.fecha DESC, cg.horaInicio DESC\n"
            . "    LIMIT 10;\n"
            . "END";
    }

    private function buildGetClasesAvailable(): string
    {
        return "CREATE PROCEDURE sp_TClaseGrupales_GetAvailable()\n"
            . "BEGIN\n"
            . "    SELECT cg.*, a.nombreActividad, a.descripcionActividad,\n"
            . "           s.nombre AS sucursalNombre,\n"
            . "           CONCAT(u.nombre1, ' ', u.apellido1) AS entrenador\n"
            . "    FROM TClaseGrupales cg\n"
            . "    INNER JOIN TActividades a ON a.idActividad = cg.idActividad\n"
            . "    INNER JOIN TSucursales s ON s.idSucursal = cg.idSucursal\n"
            . "    INNER JOIN TEmpleados e ON e.carnetEmpleado = cg.carnetEmpleado\n"
            . "    INNER JOIN TUsuarios u ON u.idUsuario = e.idUsuario\n"
            . "    WHERE cg.fecha >= CURDATE()\n"
            . "      AND cg.estadoA = 1\n"
            . "      AND cg.estadoClase = 'Programada'\n"
            . "    ORDER BY cg.fecha ASC, cg.horaInicio ASC\n"
            . "    LIMIT 10;\n"
            . "END";
    }

    private function buildGetEquiposOperativos(): string
    {
        return "CREATE PROCEDURE sp_TEquipamientos_GetOperativosWithDetails()\n"
            . "BEGIN\n"
            . "    SELECT e.idEquipo, e.nombreEquipo, e.modelo, m.nombreMarca,\n"
            . "           s.nombre AS sucursal\n"
            . "    FROM TEquipamientos e\n"
            . "    LEFT JOIN TMarcas m ON m.idMarca = e.idMarca\n"
            . "    LEFT JOIN TSucursales s ON s.idSucursal = e.idSucursal\n"
            . "    WHERE e.estadoA = 1 AND e.estadoEquipo = 'Operativo'\n"
            . "    ORDER BY e.nombreEquipo ASC;\n"
            . "END";
    }

    private function buildGetEquiposOperativosBySucursal(): string
    {
        return "CREATE PROCEDURE sp_TEquipamientos_GetOperativosBySucursal(\n"
            . "    IN p_idSucursal INT\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT e.idEquipo, e.nombreEquipo, e.modelo, m.nombreMarca,\n"
            . "           s.nombre AS sucursal\n"
            . "    FROM TEquipamientos e\n"
            . "    LEFT JOIN TMarcas m ON m.idMarca = e.idMarca\n"
            . "    LEFT JOIN TSucursales s ON s.idSucursal = e.idSucursal\n"
            . "    WHERE e.estadoA = 1 AND e.estadoEquipo = 'Operativo'\n"
            . "      AND e.idSucursal = p_idSucursal\n"
            . "    ORDER BY e.nombreEquipo ASC;\n"
            . "END";
    }

    private function buildGetEquiposByEstado(): string
    {
        return "CREATE PROCEDURE sp_TEquipamientos_GetByEstado(\n"
            . "    IN p_estado VARCHAR(50)\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT e.*, m.nombreMarca, s.nombre AS sucursal\n"
            . "    FROM TEquipamientos e\n"
            . "    LEFT JOIN TMarcas m ON m.idMarca = e.idMarca\n"
            . "    LEFT JOIN TSucursales s ON s.idSucursal = e.idSucursal\n"
            . "    WHERE e.estadoA = 1 AND e.estadoEquipo = p_estado COLLATE utf8mb4_unicode_ci\n"
            . "    ORDER BY e.nombreEquipo;\n"
            . "END";
    }

    private function buildCountRealizadoByEquipo(): string
    {
        return "CREATE PROCEDURE sp_TMantenimientoPreventivos_CountRealizadoByEquipo(\n"
            . "    IN p_idEquipo INT\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT COUNT(*) AS c\n"
            . "    FROM TMantenimientoPreventivos\n"
            . "    WHERE idEquipo = p_idEquipo\n"
            . "      AND estadoMantenimiento = 'Realizado'\n"
            . "      AND estadoA = 1;\n"
            . "END";
    }

    private function buildGetProximosMantenimientos(): string
    {
        return "CREATE PROCEDURE sp_TMantenimientoPreventivos_GetProximos(\n"
            . "    IN p_limit INT\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT mp.*, e.nombreEquipo,\n"
            . "           DATEDIFF(mp.fechaProgramada, CURDATE()) AS diasRestantes\n"
            . "    FROM TMantenimientoPreventivos mp\n"
            . "    INNER JOIN TEquipamientos e ON e.idEquipo = mp.idEquipo\n"
            . "    WHERE mp.estadoA = 1\n"
            . "      AND mp.estadoMantenimiento = 'Pendiente'\n"
            . "      AND mp.fechaProgramada >= CURDATE()\n"
            . "    ORDER BY mp.fechaProgramada ASC\n"
            . "    LIMIT p_limit;\n"
            . "END";
    }

    private function buildGetAlertasPendientes(): string
    {
        return "CREATE PROCEDURE sp_TMantenimientoPreventivos_GetAlertasPendientes()\n"
            . "BEGIN\n"
            . "    SELECT mp.*, e.nombreEquipo, e.estadoEquipo,\n"
            . "           DATEDIFF(mp.fechaProgramada, CURDATE()) AS diasRestantes\n"
            . "    FROM TMantenimientoPreventivos mp\n"
            . "    INNER JOIN TEquipamientos e ON e.idEquipo = mp.idEquipo\n"
            . "    WHERE mp.estadoA = 1\n"
            . "      AND mp.estadoMantenimiento = 'Pendiente'\n"
            . "      AND mp.fechaProgramada >= CURDATE()\n"
            . "    ORDER BY mp.fechaProgramada ASC;\n"
            . "END";
    }

    private function buildGetResumenMantenimientos(): string
    {
        return "CREATE PROCEDURE sp_TMantenimientoPreventivos_GetResumen()\n"
            . "BEGIN\n"
            . "    SELECT estadoMantenimiento, COUNT(*) AS cantidad\n"
            . "    FROM TMantenimientoPreventivos\n"
            . "    WHERE estadoA = 1\n"
            . "    GROUP BY estadoMantenimiento;\n"
            . "END";
    }

    private function buildGetMantenimientosFiltered(): string
    {
        return "CREATE PROCEDURE sp_TMantenimientoPreventivos_GetFiltered(\n"
            . "    IN p_estado VARCHAR(50),\n"
            . "    IN p_fecha_desde DATE,\n"
            . "    IN p_fecha_hasta DATE\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT mp.*, e.nombreEquipo, e.estadoEquipo, e.modelo\n"
            . "    FROM TMantenimientoPreventivos mp\n"
            . "    INNER JOIN TEquipamientos e ON e.idEquipo = mp.idEquipo\n"
            . "    WHERE mp.estadoA = 1\n"
            . "      AND (p_estado IS NULL OR mp.estadoMantenimiento = p_estado COLLATE utf8mb4_unicode_ci)\n"
            . "      AND (p_fecha_desde IS NULL OR mp.fechaProgramada >= p_fecha_desde)\n"
            . "      AND (p_fecha_hasta IS NULL OR mp.fechaProgramada <= p_fecha_hasta)\n"
            . "    ORDER BY\n"
            . "        CASE mp.estadoMantenimiento\n"
            . "            WHEN 'Pendiente' THEN 1\n"
            . "            WHEN 'Cancelado' THEN 2\n"
            . "            WHEN 'Realizado' THEN 3\n"
            . "        END,\n"
            . "        mp.fechaProgramada DESC;\n"
            . "END";
    }

    private function buildGetEmpleadosWithDetails(): string
    {
        return "CREATE PROCEDURE sp_TEmpleados_GetAllWithDetails()\n"
            . "BEGIN\n"
            . "    SELECT e.carnetEmpleado, e.idUsuario, e.idSucursal, e.sueldo,\n"
            . "           e.fechaContratoInicio,\n"
            . "           u.idRol, u.nombre1, u.apellido1, u.correo, u.telefono,\n"
            . "           r.nombreRol, s.nombre AS nombreSucursal\n"
            . "    FROM TEmpleados e\n"
            . "    INNER JOIN TUsuarios u ON e.idUsuario = u.idUsuario\n"
            . "    INNER JOIN TRoles r ON u.idRol = r.idRol\n"
            . "    INNER JOIN TSucursales s ON e.idSucursal = s.idSucursal\n"
            . "    WHERE e.estadoA = 1;\n"
            . "END";
    }

    private function buildGetSociosWithUsers(): string
    {
        return "CREATE PROCEDURE sp_TSocios_GetAllWithUsers()\n"
            . "BEGIN\n"
            . "    SELECT s.carnetSocio, s.idUsuario, s.direccion,\n"
            . "           s.nombreContactoEmergencia, s.telefonoContactoEmergencia,\n"
            . "           s.estadoSocio,\n"
            . "           u.nombre1, u.apellido1, u.correo, u.telefono\n"
            . "    FROM TSocios s\n"
            . "    INNER JOIN TUsuarios u ON s.idUsuario = u.idUsuario\n"
            . "    WHERE s.estadoA = 1;\n"
            . "END";
    }

    private function buildGetCajeros(): string
    {
        return "CREATE PROCEDURE sp_TUsuarios_GetCajeros()\n"
            . "BEGIN\n"
            . "    SELECT DISTINCT e.carnetEmpleado, u.nombre1, u.apellido1\n"
            . "    FROM TUsuarios u\n"
            . "    INNER JOIN TEmpleados e ON e.idUsuario = u.idUsuario\n"
            . "    INNER JOIN TCajas c ON c.carnetEmpleado = e.carnetEmpleado\n"
            . "    WHERE u.estadoA = 1\n"
            . "    ORDER BY u.nombre1;\n"
            . "END";
    }

    private function buildGetReporteFinanciero(): string
    {
        return "CREATE PROCEDURE sp_TRecibos_GetReporteFinanciero(\n"
            . "    IN p_fecha_desde DATE,\n"
            . "    IN p_fecha_hasta DATE,\n"
            . "    IN p_idSucursal INT,\n"
            . "    IN p_idMetodoPago INT,\n"
            . "    IN p_carnetEmpleado INT\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT r.idRecibo, r.montoTotal, r.fechaPago,\n"
            . "           r.estadoRecibo,\n"
            . "           c.idSucursal, c.carnetEmpleado,\n"
            . "           s.nombre AS sucursal,\n"
            . "           u.nombre1, u.apellido1,\n"
            . "           m.carnetSocio,\n"
            . "           (SELECT GROUP_CONCAT(CONCAT(mp.nombreMetodoPago, ': Bs. ', dmp.monto) SEPARATOR ' | ')\n"
            . "              FROM TDetalleMetodoPagos dmp\n"
            . "              JOIN TMetodoPagos mp ON mp.idMetodoPago = dmp.idMetodoPagoFK\n"
            . "             WHERE dmp.idRecibo = r.idRecibo) AS metodos_pago\n"
            . "    FROM TRecibos r\n"
            . "    INNER JOIN TCajas c ON c.idCaja = r.idCaja\n"
            . "    INNER JOIN TSucursales s ON s.idSucursal = c.idSucursal\n"
            . "    INNER JOIN TMembresias m ON m.idMembresia = r.idMembresia\n"
            . "    INNER JOIN TSocios so ON so.carnetSocio = m.carnetSocio\n"
            . "    INNER JOIN TUsuarios u ON u.idUsuario = so.idUsuario\n"
            . "    LEFT JOIN TUsuarios uc ON uc.idUsuario = c.carnetEmpleado\n"
            . "    WHERE r.estadoA = 1\n"
            . "      AND (p_fecha_desde IS NULL OR r.fechaPago >= p_fecha_desde)\n"
            . "      AND (p_fecha_hasta IS NULL OR r.fechaPago < DATE_ADD(p_fecha_hasta, INTERVAL 1 DAY))\n"
            . "      AND (p_idSucursal IS NULL OR c.idSucursal = p_idSucursal)\n"
            . "      AND (p_idMetodoPago IS NULL OR EXISTS (\n"
            . "          SELECT 1 FROM TDetalleMetodoPagos dmp2\n"
            . "          WHERE dmp2.idRecibo = r.idRecibo AND dmp2.idMetodoPagoFK = p_idMetodoPago\n"
            . "      ))\n"
            . "      AND (p_carnetEmpleado IS NULL OR c.carnetEmpleado = p_carnetEmpleado)\n"
            . "    ORDER BY r.fechaPago DESC;\n"
            . "END";
    }

    private function buildGetHistorialFallas(): string
    {
        return "CREATE PROCEDURE sp_TReporteFallas_GetHistorial(\n"
            . "    IN p_limit INT\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT rf.*, e.nombreEquipo, e.estadoEquipo\n"
            . "    FROM TReporteFallas rf\n"
            . "    INNER JOIN TEquipamientos e ON e.idEquipo = rf.idEquipo\n"
            . "    WHERE rf.estadoA = 1\n"
            . "    ORDER BY rf.fechaReporte DESC\n"
            . "    LIMIT p_limit;\n"
            . "END";
    }

    private function buildGetReportesByEmpleado(): string
    {
        return "CREATE PROCEDURE sp_TReporteFallas_GetByEmpleado(\n"
            . "    IN p_carnetEmpleado INT\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT rf.*, e.nombreEquipo, e.estadoEquipo\n"
            . "    FROM TReporteFallas rf\n"
            . "    INNER JOIN TEquipamientos e ON e.idEquipo = rf.idEquipo\n"
            . "    WHERE rf.estadoA = 1\n"
            . "      AND rf.carnetEmpleado = p_carnetEmpleado\n"
            . "    ORDER BY rf.fechaReporte DESC;\n"
            . "END";
    }

    private function buildGetFallasSinMantenimiento(): string
    {
        return "CREATE PROCEDURE sp_TEquipamientos_GetFallasSinMantenimiento(\n"
            . "    IN p_fechaDesde VARCHAR(10),\n"
            . "    IN p_fechaHasta VARCHAR(10)\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT e.*,\n"
            . "           latest_rf.idReporteFalla, latest_rf.fechaReporte,\n"
            . "           latest_rf.descripcionFalla, latest_rf.gravedad,\n"
            . "           latest_rf.estadoReporte,\n"
            . "           s.nombre AS nombreSucursal\n"
            . "    FROM TEquipamientos e\n"
            . "    INNER JOIN (\n"
            . "        SELECT rf1.*\n"
            . "        FROM TReporteFallas rf1\n"
            . "        INNER JOIN (\n"
            . "            SELECT idEquipo, MAX(fechaReporte) AS maxFecha\n"
            . "            FROM TReporteFallas\n"
            . "            WHERE estadoA = 1\n"
            . "            GROUP BY idEquipo\n"
            . "        ) sub ON rf1.idEquipo = sub.idEquipo AND rf1.fechaReporte = sub.maxFecha\n"
            . "        WHERE rf1.estadoA = 1\n"
            . "    ) latest_rf ON latest_rf.idEquipo = e.idEquipo\n"
            . "    LEFT JOIN TSucursales s ON s.idSucursal = e.idSucursal\n"
            . "    WHERE e.estadoA = 1\n"
            . "      AND NOT EXISTS (\n"
            . "          SELECT 1 FROM TMantenimientoPreventivos mp\n"
            . "          WHERE mp.idEquipo = e.idEquipo AND mp.estadoA = 1\n"
            . "      )\n"
            . "      AND (p_fechaDesde IS NULL OR p_fechaDesde = '' OR latest_rf.fechaReporte >= p_fechaDesde)\n"
            . "      AND (p_fechaHasta IS NULL OR p_fechaHasta = '' OR latest_rf.fechaReporte <= p_fechaHasta)\n"
            . "    ORDER BY latest_rf.fechaReporte DESC;\n"
            . "END";
    }

    private function buildGetHistorialMantenimientos(): string
    {
        return "CREATE PROCEDURE sp_TMantenimientoPreventivos_GetHistorial(\n"
            . "    IN p_limit INT\n"
            . ")\n"
            . "BEGIN\n"
            . "    SELECT mp.*, e.nombreEquipo, e.estadoEquipo\n"
            . "    FROM TMantenimientoPreventivos mp\n"
            . "    INNER JOIN TEquipamientos e ON e.idEquipo = mp.idEquipo\n"
            . "    WHERE mp.estadoA = 1\n"
            . "    ORDER BY mp.fechaProgramada DESC\n"
            . "    LIMIT p_limit;\n"
            . "END";
    }
};

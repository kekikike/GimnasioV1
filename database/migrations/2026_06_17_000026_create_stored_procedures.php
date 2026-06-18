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
            'TSocios' => ['pk' => 'carnetSocio', 'cols' => ['idUsuario', 'codigoAcceso', 'direccion', 'fotografiaUrl', 'nombreContactoEmergencia', 'telefonoContactoEmergencia', 'observacionesMedicas', 'estadoSocio', 'Asistencias', 'Faltas', 'strikes'], 'auditCols' => ['idUsuario', 'codigoAcceso', 'direccion', 'fotografiaUrl', 'nombreContactoEmergencia', 'telefonoContactoEmergencia', 'observacionesMedicas', 'estadoSocio', 'Asistencias', 'Faltas', 'strikes'], 'autoinc' => false],
            'THorarioLaborales' => ['pk' => 'idHorario', 'cols' => ['carnetEmpleado', 'diaSemana', 'horaEntradaEsperada', 'horaSalidaEsperada'], 'auditCols' => ['carnetEmpleado', 'diaSemana', 'horaEntradaEsperada', 'horaSalidaEsperada'], 'autoinc' => true],
            'TControlAsistencias' => ['pk' => 'idAsistencia', 'cols' => ['carnetEmpleado', 'fecha', 'horaEntrada', 'horaSalida', 'estadoAsistencia'], 'auditCols' => ['carnetEmpleado', 'fecha', 'horaEntrada', 'horaSalida', 'estadoAsistencia'], 'autoinc' => true],
            'TEsquemaSueldos' => ['pk' => 'idEsquemaSueldo', 'cols' => ['carnetEmpleado', 'modalidadPago', 'montoBase', 'tarifaHoraOClase'], 'auditCols' => ['carnetEmpleado', 'modalidadPago', 'montoBase', 'tarifaHoraOClase'], 'autoinc' => true],
            'TMembresias' => ['pk' => 'idMembresia', 'cols' => ['idPlan', 'carnetSocio', 'idSucursal', 'fechaInicioMembresia', 'fechaFinMembresia', 'estadoMembresia'], 'auditCols' => ['idPlan', 'carnetSocio', 'idSucursal', 'fechaInicioMembresia', 'fechaFinMembresia', 'estadoMembresia'], 'autoinc' => true],
            'TControlAccesos' => ['pk' => 'idControlAcceso', 'cols' => ['carnetSocio', 'idSucursal', 'fechaAcceso', 'horaAcceso', 'bloqueo', 'motivoDenegacion'], 'auditCols' => ['carnetSocio', 'idSucursal', 'fechaAcceso', 'horaAcceso', 'bloqueo', 'motivoDenegacion'], 'autoinc' => true],
            'TPenalizaciones' => ['pk' => 'idPenalizacion', 'cols' => ['carnetSocio', 'fecha', 'estado'], 'auditCols' => ['carnetSocio', 'fecha', 'estado'], 'autoinc' => true],
            'TNotificaciones' => ['pk' => 'idNotificacion', 'cols' => ['carnetSocio', 'tipoNotificacion', 'mensaje', 'canal', 'fechaEnvio', 'estado'], 'auditCols' => ['carnetSocio', 'tipoNotificacion', 'mensaje', 'canal', 'fechaEnvio', 'estado'], 'autoinc' => true],
            'TClaseGrupales' => ['pk' => 'idClaseGrupal', 'cols' => ['idActividad', 'carnetEmpleado', 'idSucursal', 'fecha', 'horaInicio', 'horaFin', 'cupoMaximo', 'cupoDisponible', 'estadoClase'], 'auditCols' => ['idActividad', 'carnetEmpleado', 'idSucursal', 'fecha', 'horaInicio', 'horaFin', 'cupoMaximo', 'cupoDisponible', 'estadoClase'], 'autoinc' => true],
            'TReservas' => ['pk' => 'idReserva', 'cols' => ['idClaseGrupal', 'carnetSocio', 'fechaReserva', 'horaReserva', 'estadoReserva'], 'auditCols' => ['idClaseGrupal', 'carnetSocio', 'fechaReserva', 'horaReserva', 'estadoReserva'], 'autoinc' => true],
            'TCajas' => ['pk' => 'idCaja', 'cols' => ['idSucursal', 'carnetEmpleado', 'fechaApertura', 'horaApertura', 'montoApertura', 'montoCierre', 'montoCierreCalculado', 'diferenciaArqueo', 'estadoCaja'], 'auditCols' => ['idSucursal', 'carnetEmpleado', 'fechaApertura', 'horaApertura', 'montoApertura', 'montoCierre', 'montoCierreCalculado', 'diferenciaArqueo', 'estadoCaja'], 'autoinc' => true],
            'TRecibos' => ['pk' => 'idRecibo', 'cols' => ['idCaja', 'idMembresia', 'nroRecibo', 'montoTotal', 'fechaPago', 'horaPago', 'estadoRecibo'], 'auditCols' => ['idCaja', 'idMembresia', 'nroRecibo', 'montoTotal', 'fechaPago', 'horaPago', 'estadoRecibo'], 'autoinc' => true],
            'TDetalleMetodoPagos' => ['pk' => 'idMetodoPago', 'cols' => ['idRecibo', 'tipoPago', 'monto'], 'auditCols' => ['idRecibo', 'tipoPago', 'monto'], 'autoinc' => true],
            'TEquipamientos' => ['pk' => 'idEquipo', 'cols' => ['idSucursal', 'idMarca', 'nombreEquipo', 'modelo', 'fechaAdquisicion', 'estadoEquipo'], 'auditCols' => ['idSucursal', 'idMarca', 'nombreEquipo', 'modelo', 'fechaAdquisicion', 'estadoEquipo'], 'autoinc' => true],
            'TMantenimientoPreventivos' => ['pk' => 'idMantenimiento', 'cols' => ['idEquipo', 'fechaProgramada', 'fechaRealizada', 'descripcionMantenimiento', 'costoMantenimiento', 'tecnicoAsignado', 'estadoMantenimiento'], 'auditCols' => ['idEquipo', 'fechaProgramada', 'fechaRealizada', 'descripcionMantenimiento', 'costoMantenimiento', 'tecnicoAsignado', 'estadoMantenimiento'], 'autoinc' => true],
            'TReporteFallas' => ['pk' => 'idReporteFalla', 'cols' => ['idEquipo', 'carnetEmpleado', 'fechaReporte', 'horaReporte', 'descripcionFalla', 'gravedad', 'estadoReporte'], 'auditCols' => ['idEquipo', 'carnetEmpleado', 'fechaReporte', 'horaReporte', 'descripcionFalla', 'gravedad', 'estadoReporte'], 'autoinc' => true],
            'TAuditorias' => ['pk' => 'idAuditoria', 'cols' => ['tablaNombre', 'registroId', 'accion', 'campo', 'valorAnterior', 'valorNuevo', 'usuarioA', 'fechaA', 'direccionIP', 'detalles'], 'auditCols' => [], 'autoinc' => true],
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
};

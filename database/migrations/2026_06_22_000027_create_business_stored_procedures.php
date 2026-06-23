<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

        // =========================================================================
        // 1. sp_TControlAccesos_Registrar
        //    Registra ingreso con validación: socio activo, membresía vigente,
        //    strikes < 3, sin penalización activa, sin duplicado reciente (5 min).
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TControlAccesos_Registrar");
        $pdo->exec("
CREATE PROCEDURE sp_TControlAccesos_Registrar(
    IN p_carnetSocio INT,
    IN p_idSucursal INT,
    IN p_usuarioA INT,
    IN p_direccionIP VARCHAR(50)
)
BEGIN
    DECLARE v_estadoSocio VARCHAR(20);
    DECLARE v_strikes INT DEFAULT 0;
    DECLARE v_membresiaValida INT DEFAULT 0;
    DECLARE v_membresiaEstado VARCHAR(20);
    DECLARE v_fechaFin DATE;
    DECLARE v_penalizacionActiva INT DEFAULT 0;
    DECLARE v_duplicadoReciente INT DEFAULT 0;
    DECLARE v_bloqueo INT DEFAULT 0;
    DECLARE v_motivo TEXT DEFAULT NULL;
    DECLARE v_nuevoId INT;
    DECLARE v_fechaHoy DATE;
    DECLARE v_horaAhora TIME;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SET v_fechaHoy = CURDATE();
    SET v_horaAhora = CURTIME();

    -- 1. Validar que el socio exista y esté activo
    SELECT estadoSocio, strikes
    INTO v_estadoSocio, v_strikes
    FROM TSocios
    WHERE carnetSocio = p_carnetSocio AND estadoA = 1
    LIMIT 1;

    IF v_estadoSocio IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Socio no encontrado.';
    END IF;

    IF v_estadoSocio != 'Activo' THEN
        SET v_bloqueo = 1;
        SET v_motivo = CONCAT('Socio con estado: ', v_estadoSocio);
    END IF;

    -- 2. Validar membresía activa y no vencida
    SELECT COUNT(*), estadoMembresia, fechaFinMembresia
    INTO v_membresiaValida, v_membresiaEstado, v_fechaFin
    FROM TMembresias
    WHERE carnetSocio = p_carnetSocio
      AND estadoA = 1
      AND estadoMembresia = 'Activa'
      AND fechaInicioMembresia <= v_fechaHoy
      AND fechaFinMembresia >= v_fechaHoy
    ORDER BY idMembresia DESC
    LIMIT 1;

    IF v_membresiaValida = 0 THEN
        SET v_bloqueo = 1;
        IF v_motivo IS NOT NULL THEN
            SET v_motivo = CONCAT(v_motivo, '. Membresía no vigente o vencida.');
        ELSE
            SET v_motivo = 'Membresía no vigente o vencida.';
        END IF;
    END IF;

    -- 3. Validar strikes y penalización activa
    IF v_strikes >= 3 THEN
        SELECT COUNT(*)
        INTO v_penalizacionActiva
        FROM TPenalizaciones
        WHERE carnetSocio = p_carnetSocio
          AND estado = 1
          AND estadoA = 1
          AND fecha >= DATE_SUB(v_fechaHoy, INTERVAL 7 DAY);

        IF v_penalizacionActiva > 0 THEN
            SET v_bloqueo = 1;
            IF v_motivo IS NOT NULL THEN
                SET v_motivo = CONCAT(v_motivo, '. Acceso suspendido por acumular 3 strikes. Penalización vigente 7 días.');
            ELSE
                SET v_motivo = 'Acceso suspendido por acumular 3 strikes. Penalización vigente 7 días.';
            END IF;
        END IF;
    END IF;

    -- 4. Validar duplicado reciente (últimos 5 minutos)
    SELECT COUNT(*)
    INTO v_duplicadoReciente
    FROM TControlAccesos
    WHERE carnetSocio = p_carnetSocio
      AND idSucursal = p_idSucursal
      AND fechaAcceso = v_fechaHoy
      AND horaAcceso >= DATE_SUB(v_horaAhora, INTERVAL 5 MINUTE)
      AND bloqueo = 0
      AND estadoA = 1;

    IF v_duplicadoReciente > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El socio ya registró un ingreso en los últimos 5 minutos.';
    END IF;

    -- 5. Insertar registro de acceso
    INSERT INTO TControlAccesos (carnetSocio, idSucursal, fechaAcceso, horaAcceso, bloqueo, motivoDenegacion, usuarioA)
    VALUES (p_carnetSocio, p_idSucursal, v_fechaHoy, v_horaAhora, v_bloqueo, v_motivo, p_usuarioA);

    SET v_nuevoId = LAST_INSERT_ID();

    -- 6. Auditoría
    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES (
        'TControlAccesos', v_nuevoId, 'I',
        'carnetSocio,idSucursal,fechaAcceso,horaAcceso,bloqueo,motivoDenegacion',
        NULL,
        CONCAT_WS('|', p_carnetSocio, p_idSucursal, v_fechaHoy, v_horaAhora, v_bloqueo, COALESCE(v_motivo, '')),
        p_usuarioA, NOW(), p_direccionIP,
        IF(v_bloqueo=1, CONCAT('Acceso DENEGADO: ', v_motivo), 'Ingreso registrado correctamente.')
    );

    COMMIT;

    -- 7. Devolver resultado
    SELECT
        IF(v_bloqueo=0, TRUE, FALSE) AS success,
        v_bloqueo AS bloqueo,
        IF(v_bloqueo=1, CONCAT('ACCESO DENEGADO: ', v_motivo), 'Ingreso registrado correctamente.') AS message,
        v_nuevoId AS idControlAcceso;
END
        ");

        // =========================================================================
        // 2. sp_TSocios_Bloquear
        //    Bloquea manualmente a un socio (estadoSocio = 'Inactivo') + auditoría.
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TSocios_Bloquear");
        $pdo->exec("
CREATE PROCEDURE sp_TSocios_Bloquear(
    IN p_carnetSocio INT,
    IN p_usuarioA INT,
    IN p_direccionIP VARCHAR(50)
)
BEGIN
    DECLARE v_estadoActual VARCHAR(20);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT estadoSocio INTO v_estadoActual
    FROM TSocios
    WHERE carnetSocio = p_carnetSocio AND estadoA = 1
    LIMIT 1;

    IF v_estadoActual IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Socio no encontrado.';
    END IF;

    IF v_estadoActual = 'Inactivo' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El socio ya se encuentra bloqueado.';
    END IF;

    UPDATE TSocios
    SET estadoSocio = 'Inactivo'
    WHERE carnetSocio = p_carnetSocio;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES (
        'TSocios', p_carnetSocio, 'U',
        'estadoSocio',
        v_estadoActual,
        'Inactivo',
        p_usuarioA, NOW(), p_direccionIP,
        CONCAT('Bloqueo manual de socio. Estado anterior: ', v_estadoActual, ' -> Inactivo')
    );

    COMMIT;

    SELECT TRUE AS success, 'Socio bloqueado correctamente.' AS message;
END
        ");

        // =========================================================================
        // 3. sp_TClaseGrupales_Insert_Validated
        //    Crea clase grupal validando: fecha >= hoy, hora_fin > hora_inicio.
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TClaseGrupales_Insert_Validated");
        $pdo->exec("
CREATE PROCEDURE sp_TClaseGrupales_Insert_Validated(
    IN p_idActividad INT,
    IN p_carnetEmpleado INT,
    IN p_idSucursal INT,
    IN p_fecha DATE,
    IN p_horaInicio TIME,
    IN p_horaFin TIME,
    IN p_cupoMaximo INT,
    IN p_usuarioA INT,
    IN p_direccionIP VARCHAR(50)
)
BEGIN
    DECLARE v_nuevoId INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    -- Validaciones de negocio
    IF p_fecha < CURDATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La fecha de la clase no puede ser anterior a la fecha actual.';
    END IF;

    IF p_horaFin <= p_horaInicio THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La hora de fin debe ser posterior a la hora de inicio.';
    END IF;

    IF p_cupoMaximo < 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El cupo máximo debe ser al menos 1.';
    END IF;

    START TRANSACTION;

    INSERT INTO TClaseGrupales (idActividad, carnetEmpleado, idSucursal, fecha, horaInicio, horaFin, cupoMaximo, estadoClase, usuarioA)
    VALUES (p_idActividad, p_carnetEmpleado, p_idSucursal, p_fecha, p_horaInicio, p_horaFin, p_cupoMaximo, 'Programada', p_usuarioA);

    SET v_nuevoId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES (
        'TClaseGrupales', v_nuevoId, 'I',
        'idActividad,carnetEmpleado,idSucursal,fecha,horaInicio,horaFin,cupoMaximo,estadoClase',
        NULL,
        CONCAT_WS('|', p_idActividad, p_carnetEmpleado, p_idSucursal, p_fecha, p_horaInicio, p_horaFin, p_cupoMaximo, 'Programada'),
        p_usuarioA, NOW(), p_direccionIP,
        CONCAT('Clase grupal creada: actividad ', p_idActividad, ' fecha ', p_fecha)
    );

    COMMIT;

    SELECT TRUE AS success, 'Clase grupal registrada correctamente.' AS message, v_nuevoId AS id;
END
        ");

        // =========================================================================
        // 4. sp_TReservas_Cancelar_Validated
        //    Cancela reserva validando estrictamente las 2h de anticipación.
        //    Si no cumple => aplica strike y penalización.
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TReservas_Cancelar_Validated");
        $pdo->exec("
CREATE PROCEDURE sp_TReservas_Cancelar_Validated(
    IN p_idReserva INT,
    IN p_carnetSocio INT,
    IN p_usuarioA INT,
    IN p_direccionIP VARCHAR(50)
)
BEGIN
    DECLARE v_idClaseGrupal INT;
    DECLARE v_fechaClase DATE;
    DECLARE v_horaInicio TIME;
    DECLARE v_estadoActual VARCHAR(20);
    DECLARE v_fechaHoy DATE;
    DECLARE v_horaAhora TIME;
    DECLARE v_claseDateTime DATETIME;
    DECLARE v_ahoraDateTime DATETIME;
    DECLARE v_horasDiff INT;
    DECLARE v_estadoFinal VARCHAR(20);
    DECLARE v_mensaje TEXT;
    DECLARE v_mensajeError TEXT;
    DECLARE v_penalizado INT DEFAULT 0;
    DECLARE v_nuevosStrikes INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SET v_fechaHoy = CURDATE();
    SET v_horaAhora = CURTIME();

    -- Obtener datos de la reserva y la clase
    SELECT r.estadoReserva, cg.idClaseGrupal, cg.fecha, cg.horaInicio
    INTO v_estadoActual, v_idClaseGrupal, v_fechaClase, v_horaInicio
    FROM TReservas r
    INNER JOIN TClaseGrupales cg ON r.idClaseGrupal = cg.idClaseGrupal
    WHERE r.idReserva = p_idReserva
      AND r.carnetSocio = p_carnetSocio
      AND r.estadoA = 1;

    IF v_estadoActual IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Reserva no encontrada o ya cancelada.';
    END IF;

    IF v_estadoActual != 'Reservado' THEN
        SET v_mensajeError = CONCAT('La reserva ya fue ', v_estadoActual, '. No se puede cancelar.');
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensajeError;
    END IF;

    -- Validar que la clase no haya ocurrido ya
    SET v_claseDateTime = CONCAT(v_fechaClase, ' ', v_horaInicio);
    SET v_ahoraDateTime = CONCAT(v_fechaHoy, ' ', v_horaAhora);

    IF v_claseDateTime < v_ahoraDateTime THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La clase ya ha ocurrido. No es posible cancelar la reserva.';
    END IF;

    -- Calcular horas de anticipación
    SET v_horasDiff = TIMESTAMPDIFF(HOUR, v_ahoraDateTime, v_claseDateTime);

    IF v_horasDiff < 2 THEN
        SET v_estadoFinal = 'Penalizado';
        SET v_mensaje = 'Cancelación fuera del tiempo permitido (mín. 2h antes). Se aplicó un strike.';
        SET v_penalizado = 1;

        INSERT INTO TPenalizaciones (carnetSocio, idReserva, fecha, estado, usuarioA)
        VALUES (p_carnetSocio, p_idReserva, v_fechaHoy, TRUE, p_usuarioA);

        UPDATE TSocios
        SET strikes = strikes + 1
        WHERE carnetSocio = p_carnetSocio;

        SELECT strikes INTO v_nuevosStrikes
        FROM TSocios
        WHERE carnetSocio = p_carnetSocio;

        IF v_nuevosStrikes >= 3 THEN
            INSERT INTO TPenalizaciones (carnetSocio, idReserva, fecha, estado, usuarioA)
            VALUES (p_carnetSocio, NULL, v_fechaHoy, TRUE, p_usuarioA);

            SET v_mensaje = CONCAT(v_mensaje, ' Has acumulado 3 strikes. Acceso suspendido por 1 semana.');
        END IF;
    ELSE
        SET v_estadoFinal = 'Cancelado';
        SET v_mensaje = 'Reserva cancelada correctamente.';
    END IF;

    -- Actualizar estado de la reserva
    UPDATE TReservas
    SET estadoReserva = v_estadoFinal
    WHERE idReserva = p_idReserva;

    -- Auditoría
    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES (
        'TReservas', p_idReserva, 'U',
        'estadoReserva',
        v_estadoActual,
        v_estadoFinal,
        p_usuarioA, NOW(), p_direccionIP,
        v_mensaje
    );

    COMMIT;

    SELECT TRUE AS success, v_mensaje AS message, v_penalizado AS penalizado;
END
        ");

        // =========================================================================
        // 5. sp_TReservas_MarcarAsistencia_Integrado
        //    Marca asistencia a clase desde recepción:
        //      a) Cambia estadoReserva a 'Asistido'
        //      b) Inserta en TControlAccesos
        //      c) Auditoría
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TReservas_MarcarAsistencia_Integrado");
        $pdo->exec("
CREATE PROCEDURE sp_TReservas_MarcarAsistencia_Integrado(
    IN p_idReserva INT,
    IN p_carnetSocio INT,
    IN p_idSucursal INT,
    IN p_usuarioA INT,
    IN p_direccionIP VARCHAR(50)
)
BEGIN
    DECLARE v_estadoActual VARCHAR(20);
    DECLARE v_idControlAcceso INT;
    DECLARE v_fechaHoy DATE;
    DECLARE v_horaAhora TIME;
    DECLARE v_mensajeError TEXT;
    DECLARE v_detalleAudit TEXT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SET v_fechaHoy = CURDATE();
    SET v_horaAhora = CURTIME();

    -- Validar reserva
    SELECT estadoReserva
    INTO v_estadoActual
    FROM TReservas
    WHERE idReserva = p_idReserva
      AND carnetSocio = p_carnetSocio
      AND estadoA = 1;

    IF v_estadoActual IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Reserva no encontrada.';
    END IF;

    IF v_estadoActual != 'Reservado' THEN
        SET v_mensajeError = CONCAT('La reserva ya fue ', v_estadoActual, '. No se puede marcar asistencia.');
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensajeError;
    END IF;

    -- a) Cambiar estado de la reserva
    UPDATE TReservas
    SET estadoReserva = 'Asistido'
    WHERE idReserva = p_idReserva;

    -- b) Insertar en control de accesos
    INSERT INTO TControlAccesos (carnetSocio, idSucursal, fechaAcceso, horaAcceso, bloqueo, motivoDenegacion, usuarioA)
    VALUES (p_carnetSocio, p_idSucursal, v_fechaHoy, v_horaAhora, FALSE, NULL, p_usuarioA);

    SET v_idControlAcceso = LAST_INSERT_ID();

    -- c) Auditoría para la reserva
    SET v_detalleAudit = CONCAT('Asistencia a clase marcada desde recepción. Reserva #', p_idReserva);
    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES (
        'TReservas', p_idReserva, 'U',
        'estadoReserva',
        v_estadoActual,
        'Asistido',
        p_usuarioA, NOW(), p_direccionIP,
        v_detalleAudit
    );

    -- d) Auditoría para el control de acceso
    SET v_detalleAudit = CONCAT('Acceso generado automáticamente por asistencia a clase reservada #', p_idReserva);
    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES (
        'TControlAccesos', v_idControlAcceso, 'I',
        'carnetSocio,idSucursal,fechaAcceso,horaAcceso,bloqueo',
        NULL,
        CONCAT_WS('|', p_carnetSocio, p_idSucursal, v_fechaHoy, v_horaAhora, '0'),
        p_usuarioA, NOW(), p_direccionIP,
        v_detalleAudit
    );

    COMMIT;

    SELECT TRUE AS success, 'Asistencia registrada y acceso confirmado.' AS message;
END
        ");

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }

    public function down(): void
    {
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TControlAccesos_Registrar");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TSocios_Bloquear");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TClaseGrupales_Insert_Validated");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TReservas_Cancelar_Validated");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TReservas_MarcarAsistencia_Integrado");

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }
};

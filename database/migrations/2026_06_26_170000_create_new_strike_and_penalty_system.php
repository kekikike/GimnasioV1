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
        // 1. sp_TSocios_VerificarSuspension
        //    Verifica si un socio está suspendido por strikes y gestiona la
        //    limpieza automática del historial.
        //
        //    Suspensión progresiva (desde la fecha del ÚLTIMO strike):
        //      - 1 strike: 5 días de suspensión
        //      - 2 strikes: 7 días de suspensión
        //      - 3 strikes: 14 días de suspensión
        //
        //    Limpieza en bloque (NO uno por uno):
        //      - 1 strike total: 30 días sin faltas → todo a 0
        //      - 2 strikes total: 60 días sin faltas → todo a 0
        //      - 3 strikes total: 90 días sin faltas → todo a 0
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TSocios_VerificarSuspension");
        $pdo->exec("
CREATE PROCEDURE sp_TSocios_VerificarSuspension(
    IN p_carnetSocio INT,
    IN p_fechaHoy DATE,
    OUT p_enSuspension BOOLEAN,
    OUT p_motivo TEXT,
    OUT p_strikesActuales INT
)
BEGIN
    DECLARE v_strikes INT DEFAULT 0;
    DECLARE v_ultimaFechaPenalizacion DATE;
    DECLARE v_diasSuspension INT;
    DECLARE v_diasLimpieza INT;
    DECLARE v_diasDesdeUltimoStrike INT;
    DECLARE v_auditDetalle TEXT;

    -- Obtener strikes actuales del socio
    SELECT strikes INTO v_strikes
    FROM TSocios
    WHERE carnetSocio = p_carnetSocio AND estadoA = 1;

    -- Obtener la fecha del último strike activo
    SELECT MAX(fecha) INTO v_ultimaFechaPenalizacion
    FROM TPenalizaciones
    WHERE carnetSocio = p_carnetSocio AND estado = 1 AND estadoA = 1;

    SET p_strikesActuales = COALESCE(v_strikes, 0);

    -- Si no hay strikes o no hay penalizaciones activas, no hay suspensión
    IF p_strikesActuales = 0 OR v_ultimaFechaPenalizacion IS NULL THEN
        SET p_enSuspension = FALSE;
        SET p_motivo = NULL;
    ELSE
        SET v_diasDesdeUltimoStrike = DATEDIFF(p_fechaHoy, v_ultimaFechaPenalizacion);

        -- Calcular días de suspensión según el total de strikes acumulados
        CASE p_strikesActuales
            WHEN 1 THEN SET v_diasSuspension = 5;
            WHEN 2 THEN SET v_diasSuspension = 7;
            WHEN 3 THEN SET v_diasSuspension = 14;
            ELSE SET v_diasSuspension = 14;
        END CASE;

        -- Calcular días para limpieza en bloque
        CASE p_strikesActuales
            WHEN 1 THEN SET v_diasLimpieza = 30;
            WHEN 2 THEN SET v_diasLimpieza = 60;
            WHEN 3 THEN SET v_diasLimpieza = 90;
            ELSE SET v_diasLimpieza = 90;
        END CASE;

        -- Verificar si ya pasó el periodo de limpieza
        IF v_diasDesdeUltimoStrike >= v_diasLimpieza THEN
            -- Limpieza en bloque: todos los strikes se eliminan simultáneamente
            UPDATE TSocios
            SET strikes = 0
            WHERE carnetSocio = p_carnetSocio;

            UPDATE TPenalizaciones
            SET estado = 0
            WHERE carnetSocio = p_carnetSocio AND estado = 1;

            SET p_enSuspension = FALSE;
            SET p_motivo = CONCAT(
                'Historial de strikes limpiado automáticamente tras ',
                v_diasLimpieza, ' días sin faltas.'
            );
            SET p_strikesActuales = 0;

            -- Auditoría de limpieza
            SET v_auditDetalle = CONCAT(
                'Limpieza automática de strikes. Socio: ', p_carnetSocio,
                '. Strikes eliminados: ', v_strikes,
                '. Período sin faltas: ', v_diasLimpieza, ' días.'
            );
            INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
            VALUES ('TSocios', p_carnetSocio, 'U', 'strikes',
                    CAST(v_strikes AS CHAR), '0',
                    1, NOW(), '127.0.0.1', v_auditDetalle);
        ELSE
            -- Verificar si aún está en periodo de suspensión
            IF v_diasDesdeUltimoStrike < v_diasSuspension THEN
                SET p_enSuspension = TRUE;
                SET p_motivo = CONCAT(
                    'Acceso suspendido por ', v_diasSuspension, ' días. ',
                    'Strikes acumulados: ', p_strikesActuales, '. ',
                    'Suspensión vigente hasta: ',
                    DATE_ADD(v_ultimaFechaPenalizacion, INTERVAL v_diasSuspension DAY), '. ',
                    'Limpieza automática en ',
                    (v_diasLimpieza - v_diasDesdeUltimoStrike), ' días.'
                );
            ELSE
                SET p_enSuspension = FALSE;
                SET p_motivo = CONCAT(
                    'Strikes acumulados: ', p_strikesActuales, '. ',
                    'Periodo de suspensión cumplido. ',
                    'Limpieza automática en ',
                    (v_diasLimpieza - v_diasDesdeUltimoStrike), ' días.'
                );
            END IF;
        END IF;
    END IF;
END
        ");

        // =========================================================================
        // 2. sp_TSocios_AplicarStrike
        //    Registra un strike y actualiza el contador.
        //    Centraliza la lógica para evitar duplicación en múltiples SPs.
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TSocios_AplicarStrike");
        $pdo->exec("
CREATE PROCEDURE sp_TSocios_AplicarStrike(
    IN p_carnetSocio INT,
    IN p_idReserva INT,
    IN p_fechaHoy DATE,
    IN p_usuarioA INT,
    IN p_direccionIP VARCHAR(50),
    OUT p_nuevosStrikes INT,
    OUT p_mensaje TEXT
)
BEGIN
    DECLARE v_diasSuspension INT;

    -- Insertar registro de penalización
    INSERT INTO TPenalizaciones (carnetSocio, idReserva, fecha, estado, usuarioA)
    VALUES (p_carnetSocio, p_idReserva, p_fechaHoy, TRUE, p_usuarioA);

    -- Incrementar contador de strikes
    UPDATE TSocios
    SET strikes = strikes + 1
    WHERE carnetSocio = p_carnetSocio;

    -- Obtener nuevo total
    SELECT strikes INTO p_nuevosStrikes
    FROM TSocios
    WHERE carnetSocio = p_carnetSocio;

    -- Calcular días de suspensión según el nuevo total
    CASE p_nuevosStrikes
        WHEN 1 THEN SET v_diasSuspension = 5;
        WHEN 2 THEN SET v_diasSuspension = 7;
        WHEN 3 THEN SET v_diasSuspension = 14;
        ELSE SET v_diasSuspension = 14;
    END CASE;

    SET p_mensaje = CONCAT(
        'Strike #', p_nuevosStrikes, ' acumulado. ',
        'Acceso suspendido por ', v_diasSuspension, ' día(s). ',
        'Fecha de levantamiento: ', DATE_ADD(p_fechaHoy, INTERVAL v_diasSuspension DAY), '.'
    );

    -- Auditoría
    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES (
        'TPenalizaciones', LAST_INSERT_ID(), 'I',
        'carnetSocio,idReserva,fecha,estado',
        NULL,
        CONCAT_WS('|', p_carnetSocio, COALESCE(p_idReserva, 0), p_fechaHoy, '1'),
        p_usuarioA, NOW(), p_direccionIP,
        CONCAT('Nuevo strike aplicado. Socio: ', p_carnetSocio,
               '. Total strikes: ', p_nuevosStrikes,
               '. Suspensión: ', v_diasSuspension, ' días.')
    );
END
        ");

        // =========================================================================
        // 3. sp_TControlAccesos_Registrar (RECREADO)
        //    Reemplaza la vieja validación de strikes (7 días fijos) por la nueva
        //    verificación dinámica con sp_TSocios_VerificarSuspension.
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
    DECLARE v_membresiaEncontrada INT DEFAULT 0;
    DECLARE v_membresiaEstado VARCHAR(20);
    DECLARE v_fechaFin DATE;
    DECLARE v_duplicadoReciente INT DEFAULT 0;
    DECLARE v_bloqueo INT DEFAULT 0;
    DECLARE v_motivo TEXT DEFAULT NULL;
    DECLARE v_nuevoId INT;
    DECLARE v_fechaCong DATETIME;
    DECLARE v_fechaHoy DATE;
    DECLARE v_horaAhora TIME;
    DECLARE v_enSuspension BOOLEAN DEFAULT FALSE;
    DECLARE v_motivoSuspension TEXT;

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

    -- 2. Validar membresía activa o congelada
    SELECT 1, estadoMembresia, fechaFinMembresia, fechaCongelamiento
    INTO v_membresiaEncontrada, v_membresiaEstado, v_fechaFin, v_fechaCong
    FROM TMembresias
    WHERE carnetSocio = p_carnetSocio
      AND estadoA = 1
      AND estadoMembresia IN ('Activa', 'Congelada')
      AND fechaInicioMembresia <= v_fechaHoy
    ORDER BY idMembresia DESC
    LIMIT 1;

    IF v_membresiaEncontrada = 1 AND v_membresiaEstado = 'Congelada' AND v_fechaCong IS NOT NULL THEN
        UPDATE TMembresias
        SET estadoMembresia = 'Activa',
            fechaCongelamiento = NULL
        WHERE carnetSocio = p_carnetSocio AND estadoA = 1
        ORDER BY idMembresia DESC
        LIMIT 1;
        SET v_membresiaEstado = 'Activa';
    END IF;

    IF v_membresiaEncontrada = 0 OR (v_membresiaEstado = 'Activa' AND v_fechaFin < v_fechaHoy) THEN
        SET v_bloqueo = 1;
        IF v_motivo IS NOT NULL THEN
            SET v_motivo = CONCAT(v_motivo, '. Membresía no vigente o vencida.');
        ELSE
            SET v_motivo = 'Membresía no vigente o vencida.';
        END IF;
    END IF;

    -- 3. NUEVA validación de strikes: verificación dinámica con cooldown progresivo
    CALL sp_TSocios_VerificarSuspension(
        p_carnetSocio, v_fechaHoy,
        @tmp_enSuspension, @tmp_motivoSuspension, @tmp_strikes
    );

    SET v_enSuspension = @tmp_enSuspension;
    SET v_motivoSuspension = @tmp_motivoSuspension;

    -- Si hubo limpieza automática, refrescar el valor de strikes
    SELECT strikes INTO v_strikes
    FROM TSocios WHERE carnetSocio = p_carnetSocio;

    IF v_enSuspension THEN
        SET v_bloqueo = 1;
        IF v_motivo IS NOT NULL THEN
            SET v_motivo = CONCAT(v_motivo, '. ', v_motivoSuspension);
        ELSE
            SET v_motivo = v_motivoSuspension;
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
        // 4. sp_TReservas_Cancelar_Validated (RECREADO)
        //    Reemplaza la lógica antigua de strikes por sp_TSocios_AplicarStrike.
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
    DECLARE v_mensajeStrike TEXT;

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

        -- Aplicar strike mediante el nuevo SP centralizado
        CALL sp_TSocios_AplicarStrike(
            p_carnetSocio, p_idReserva, v_fechaHoy, p_usuarioA, p_direccionIP,
            @strike_nuevos, @strike_mensaje
        );

        SET v_mensaje = CONCAT(v_mensaje, ' ', @strike_mensaje);
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
        // 5. sp_TReservas_MarcarAsistencia_Entrenador (RECREADO)
        //    Reemplaza la lógica antigua de strikes por sp_TSocios_AplicarStrike.
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TReservas_MarcarAsistencia_Entrenador");
        $pdo->exec("
CREATE PROCEDURE sp_TReservas_MarcarAsistencia_Entrenador(
    IN p_idReserva INT,
    IN p_nuevoEstado VARCHAR(20),
    IN p_usuarioA INT,
    IN p_direccionIP VARCHAR(50)
)
BEGIN
    DECLARE v_estadoActual VARCHAR(20);
    DECLARE v_idClaseGrupal INT;
    DECLARE v_fechaClase DATE;
    DECLARE v_horaInicio TIME;
    DECLARE v_horaFin TIME;
    DECLARE v_carnetSocio INT;
    DECLARE v_estadoClase VARCHAR(20);
    DECLARE v_fechaHoy DATE;
    DECLARE v_horaAhora TIME;
    DECLARE v_mensajeError TEXT;
    DECLARE v_mensajeStrike TEXT;
    DECLARE v_strikesResult INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SET v_fechaHoy = CURDATE();
    SET v_horaAhora = CURTIME();

    -- Obtener datos de la reserva y la clase vinculada
    SELECT r.estadoReserva, r.idClaseGrupal, r.carnetSocio,
           cg.fecha, cg.horaInicio, cg.horaFin, cg.estadoClase
    INTO v_estadoActual, v_idClaseGrupal, v_carnetSocio,
         v_fechaClase, v_horaInicio, v_horaFin, v_estadoClase
    FROM TReservas r
    INNER JOIN TClaseGrupales cg ON r.idClaseGrupal = cg.idClaseGrupal
    WHERE r.idReserva = p_idReserva
      AND r.estadoA = 1
      AND cg.estadoA = 1;

    IF v_estadoActual IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Reserva no encontrada o la clase ya no está disponible.';
    END IF;

    IF v_estadoActual != 'Reservado' THEN
        SET v_mensajeError = CONCAT('La reserva ya fue ', v_estadoActual, '. No se puede modificar.');
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensajeError;
    END IF;

    IF v_fechaClase != v_fechaHoy THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La clase no es del día de hoy. No se puede tomar asistencia.';
    END IF;

    IF v_horaInicio IS NOT NULL AND v_horaAhora < v_horaInicio THEN
        SET v_mensajeError = CONCAT('La clase aún no ha comenzado. Inicio: ', v_horaInicio, ' - Hora actual: ', v_horaAhora);
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensajeError;
    END IF;

    IF v_horaFin IS NOT NULL AND v_horaAhora > v_horaFin THEN
        SET v_mensajeError = CONCAT('La clase ya finalizó. Fin: ', v_horaFin, ' - Hora actual: ', v_horaAhora);
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensajeError;
    END IF;

    IF v_horaAhora >= v_horaInicio AND v_horaAhora <= v_horaFin THEN
        IF v_estadoClase != 'Cursandose' THEN
            UPDATE TClaseGrupales
            SET estadoClase = 'Cursandose', fechaA = NOW()
            WHERE idClaseGrupal = v_idClaseGrupal;
        END IF;
    END IF;

    IF p_nuevoEstado NOT IN ('Asistido', 'Penalizado') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Estado de asistencia no válido.';
    END IF;

    -- Actualizar estado de la reserva
    UPDATE TReservas
    SET estadoReserva = p_nuevoEstado
    WHERE idReserva = p_idReserva;

    -- Si es 'Penalizado', aplicar strike mediante el nuevo SP centralizado
    IF p_nuevoEstado = 'Penalizado' THEN
        CALL sp_TSocios_AplicarStrike(
            v_carnetSocio, p_idReserva, v_fechaHoy, p_usuarioA, p_direccionIP,
            @strike_entrenador_nuevos, @strike_entrenador_mensaje
        );
    END IF;

    -- Auditoría
    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES (
        'TReservas', p_idReserva, 'U',
        'estadoReserva',
        v_estadoActual,
        p_nuevoEstado,
        p_usuarioA, NOW(), p_direccionIP,
        CONCAT('Asistencia marcada por entrenador. Reserva #', p_idReserva,
               ' - Socio #', v_carnetSocio,
               ' - Estado: ', p_nuevoEstado,
               IF(p_nuevoEstado = 'Penalizado', CONCAT('. ', @strike_entrenador_mensaje), ''))
    );

    COMMIT;

    SELECT TRUE AS success,
           IF(p_nuevoEstado = 'Penalizado', @strike_entrenador_mensaje, 'Asistencia registrada correctamente.') AS message;
END
        ");

        // =========================================================================
        // 6. sp_TReservas_MarcarAsistencia_Integrado (RECREADO)
        //    No usa strikes pero se actualiza por consistencia
        //    (sin cambios funcionales).
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

    UPDATE TReservas
    SET estadoReserva = 'Asistido'
    WHERE idReserva = p_idReserva;

    INSERT INTO TControlAccesos (carnetSocio, idSucursal, fechaAcceso, horaAcceso, bloqueo, motivoDenegacion, usuarioA)
    VALUES (p_carnetSocio, p_idSucursal, v_fechaHoy, v_horaAhora, FALSE, NULL, p_usuarioA);

    SET v_idControlAcceso = LAST_INSERT_ID();

    SET v_detalleAudit = CONCAT('Asistencia a clase marcada desde recepción. Reserva #', p_idReserva);
    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TReservas', p_idReserva, 'U', 'estadoReserva', v_estadoActual, 'Asistido', p_usuarioA, NOW(), p_direccionIP, v_detalleAudit);

    SET v_detalleAudit = CONCAT('Acceso generado automáticamente por asistencia a clase reservada #', p_idReserva);
    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TControlAccesos', v_idControlAcceso, 'I', 'carnetSocio,idSucursal,fechaAcceso,horaAcceso,bloqueo', NULL, CONCAT_WS('|', p_carnetSocio, p_idSucursal, v_fechaHoy, v_horaAhora, '0'), p_usuarioA, NOW(), p_direccionIP, v_detalleAudit);

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

        // Restaurar SPs originales (definidos en migraciones anteriores)

        // sp_TControlAccesos_Registrar - versión original (strikes fijos 7 días)
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
    DECLARE v_membresiaEncontrada INT DEFAULT 0;
    DECLARE v_membresiaEstado VARCHAR(20);
    DECLARE v_fechaFin DATE;
    DECLARE v_penalizacionActiva INT DEFAULT 0;
    DECLARE v_duplicadoReciente INT DEFAULT 0;
    DECLARE v_bloqueo INT DEFAULT 0;
    DECLARE v_motivo TEXT DEFAULT NULL;
    DECLARE v_nuevoId INT;
    DECLARE v_fechaCong DATETIME;
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

    SELECT 1, estadoMembresia, fechaFinMembresia, fechaCongelamiento
    INTO v_membresiaEncontrada, v_membresiaEstado, v_fechaFin, v_fechaCong
    FROM TMembresias
    WHERE carnetSocio = p_carnetSocio
      AND estadoA = 1
      AND estadoMembresia IN ('Activa', 'Congelada')
      AND fechaInicioMembresia <= v_fechaHoy
    ORDER BY idMembresia DESC
    LIMIT 1;

    IF v_membresiaEncontrada = 1 AND v_membresiaEstado = 'Congelada' AND v_fechaCong IS NOT NULL THEN
        UPDATE TMembresias
        SET estadoMembresia = 'Activa',
            fechaCongelamiento = NULL
        WHERE carnetSocio = p_carnetSocio AND estadoA = 1
        ORDER BY idMembresia DESC
        LIMIT 1;
        SET v_membresiaEstado = 'Activa';
    END IF;

    IF v_membresiaEncontrada = 0 OR (v_membresiaEstado = 'Activa' AND v_fechaFin < v_fechaHoy) THEN
        SET v_bloqueo = 1;
        IF v_motivo IS NOT NULL THEN
            SET v_motivo = CONCAT(v_motivo, '. Membresía no vigente o vencida.');
        ELSE
            SET v_motivo = 'Membresía no vigente o vencida.';
        END IF;
    END IF;

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

    INSERT INTO TControlAccesos (carnetSocio, idSucursal, fechaAcceso, horaAcceso, bloqueo, motivoDenegacion, usuarioA)
    VALUES (p_carnetSocio, p_idSucursal, v_fechaHoy, v_horaAhora, v_bloqueo, v_motivo, p_usuarioA);

    SET v_nuevoId = LAST_INSERT_ID();

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

    SELECT
        IF(v_bloqueo=0, TRUE, FALSE) AS success,
        v_bloqueo AS bloqueo,
        IF(v_bloqueo=1, CONCAT('ACCESO DENEGADO: ', v_motivo), 'Ingreso registrado correctamente.') AS message,
        v_nuevoId AS idControlAcceso;
END
        ");

        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TSocios_AplicarStrike");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TSocios_VerificarSuspension");

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =========================================================================
        // 1. Agregar columna cuposOcupados a TClaseGrupales
        // =========================================================================
        Schema::table('TClaseGrupales', function (Blueprint $table) {
            $table->integer('cuposOcupados')->default(0)->after('cupoMaximo');
        });

        // Backfill: poblar cuposOcupados con la cuenta actual de reservas activas
        DB::statement("
            UPDATE TClaseGrupales cg
            SET cg.cuposOcupados = (
                SELECT COUNT(*)
                FROM TReservas r
                WHERE r.idClaseGrupal = cg.idClaseGrupal
                  AND r.estadoReserva IN ('Reservado')
                  AND r.estadoA = 1
            )
        ");

        // =========================================================================
        // 2. sp_TReservas_Cancelar_Validated (RECREADO)
        //    Agregado: cuando la cancelación es exitosa (estadoFinal = 'Cancelado'),
        //    decrementa cuposOcupados en TClaseGrupales.
        // =========================================================================
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

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

    SET v_claseDateTime = CONCAT(v_fechaClase, ' ', v_horaInicio);
    SET v_ahoraDateTime = CONCAT(v_fechaHoy, ' ', v_horaAhora);

    IF v_claseDateTime < v_ahoraDateTime THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La clase ya ha ocurrido. No es posible cancelar la reserva.';
    END IF;

    SET v_horasDiff = TIMESTAMPDIFF(HOUR, v_ahoraDateTime, v_claseDateTime);

    IF v_horasDiff < 2 THEN
        SET v_estadoFinal = 'Penalizado';
        SET v_mensaje = 'Cancelación fuera del tiempo permitido (mín. 2h antes). Se aplicó un strike.';
        SET v_penalizado = 1;

        CALL sp_TSocios_AplicarStrike(
            p_carnetSocio, p_idReserva, v_fechaHoy, p_usuarioA, p_direccionIP,
            @strike_nuevos, @strike_mensaje
        );

        SET v_mensaje = CONCAT(v_mensaje, ' ', @strike_mensaje);
    ELSE
        SET v_estadoFinal = 'Cancelado';
        SET v_mensaje = 'Reserva cancelada correctamente.';

        -- Restaurar cupo disponible en la clase grupal
        UPDATE TClaseGrupales
        SET cuposOcupados = GREATEST(cuposOcupados - 1, 0)
        WHERE idClaseGrupal = v_idClaseGrupal;
    END IF;

    UPDATE TReservas
    SET estadoReserva = v_estadoFinal
    WHERE idReserva = p_idReserva;

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
        // 3. sp_TReservas_MarcarAsistencia_Entrenador (RECREADO)
        //    Agregado: decrementa cuposOcupados al marcar Asistido o Penalizado.
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

    UPDATE TReservas
    SET estadoReserva = p_nuevoEstado
    WHERE idReserva = p_idReserva;

    -- Liberar cupo (la reserva ya no está en estado 'Reservado')
    UPDATE TClaseGrupales
    SET cuposOcupados = GREATEST(cuposOcupados - 1, 0)
    WHERE idClaseGrupal = v_idClaseGrupal;

    IF p_nuevoEstado = 'Penalizado' THEN
        CALL sp_TSocios_AplicarStrike(
            v_carnetSocio, p_idReserva, v_fechaHoy, p_usuarioA, p_direccionIP,
            @strike_entrenador_nuevos, @strike_entrenador_mensaje
        );
    END IF;

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
        // 4. sp_TReservas_MarcarAsistencia_Integrado (RECREADO)
        //    Agregado: decrementa cuposOcupados al marcar Asistido.
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
    DECLARE v_idClaseGrupal INT;
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

    SELECT estadoReserva, idClaseGrupal
    INTO v_estadoActual, v_idClaseGrupal
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

    -- Liberar cupo (la reserva ya no está en estado 'Reservado')
    UPDATE TClaseGrupales
    SET cuposOcupados = GREATEST(cuposOcupados - 1, 0)
    WHERE idClaseGrupal = v_idClaseGrupal;

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
        Schema::table('TClaseGrupales', function (Blueprint $table) {
            $table->dropColumn('cuposOcupados');
        });

        // Restaurar sp_TReservas_Cancelar_Validated (versión anterior sin cupo)
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

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
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La reserva ya no se puede cancelar.';
    END IF;

    SET v_claseDateTime = CONCAT(v_fechaClase, ' ', v_horaInicio);
    SET v_ahoraDateTime = CONCAT(v_fechaHoy, ' ', v_horaAhora);

    IF v_claseDateTime < v_ahoraDateTime THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La clase ya ha ocurrido.';
    END IF;

    SET v_horasDiff = TIMESTAMPDIFF(HOUR, v_ahoraDateTime, v_claseDateTime);

    IF v_horasDiff < 2 THEN
        SET v_estadoFinal = 'Penalizado';
        SET v_mensaje = 'Cancelación fuera del tiempo permitido. Se aplicó un strike.';
        SET v_penalizado = 1;

        CALL sp_TSocios_AplicarStrike(
            p_carnetSocio, p_idReserva, v_fechaHoy, p_usuarioA, p_direccionIP,
            @strike_nuevos, @strike_mensaje
        );

        SET v_mensaje = CONCAT(v_mensaje, ' ', @strike_mensaje);
    ELSE
        SET v_estadoFinal = 'Cancelado';
        SET v_mensaje = 'Reserva cancelada correctamente.';
    END IF;

    UPDATE TReservas
    SET estadoReserva = v_estadoFinal
    WHERE idReserva = p_idReserva;

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

        // Restaurar sp_TReservas_MarcarAsistencia_Entrenador (versión anterior sin cupo)
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

    UPDATE TReservas
    SET estadoReserva = p_nuevoEstado
    WHERE idReserva = p_idReserva;

    IF p_nuevoEstado = 'Penalizado' THEN
        CALL sp_TSocios_AplicarStrike(
            v_carnetSocio, p_idReserva, v_fechaHoy, p_usuarioA, p_direccionIP,
            @strike_entrenador_nuevos, @strike_entrenador_mensaje
        );
    END IF;

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

        // Restaurar sp_TReservas_MarcarAsistencia_Integrado (versión anterior sin cupo)
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
};

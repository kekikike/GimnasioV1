<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

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
    DECLARE v_nuevosStrikes INT;
    DECLARE v_mensajeError TEXT;

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

    -- Validar que la reserva esté en estado 'Reservado'
    IF v_estadoActual != 'Reservado' THEN
        SET v_mensajeError = CONCAT('La reserva ya fue ', v_estadoActual, '. No se puede modificar.');
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensajeError;
    END IF;

    -- Validar que la clase sea del día de HOY
    IF v_fechaClase != v_fechaHoy THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La clase no es del día de hoy. No se puede tomar asistencia.';
    END IF;

    -- Validar ventana horaria inclusiva [horaInicio, horaFin]
    -- Bloquea SOLO si la hora actual es ESTRICTAMENTE anterior al inicio
    IF v_horaInicio IS NOT NULL AND v_horaAhora < v_horaInicio THEN
        SET v_mensajeError = CONCAT(
            'La clase aún no ha comenzado. Inicio: ',
            v_horaInicio, ' - Hora actual: ', v_horaAhora
        );
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensajeError;
    END IF;

    -- Bloquea SOLO si la hora actual SUPERA (estrictamente después) el horario de fin
    -- Esto garantiza que el intervalo [horaInicio, horaFin] sea completamente operativo
    IF v_horaFin IS NOT NULL AND v_horaAhora > v_horaFin THEN
        SET v_mensajeError = CONCAT(
            'La clase ya finalizó. Fin: ',
            v_horaFin, ' - Hora actual: ', v_horaAhora
        );
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensajeError;
    END IF;

    -- Sincronizar estadoClase en TClaseGrupales según la hora actual
    IF v_horaAhora >= v_horaInicio AND v_horaAhora <= v_horaFin THEN
        IF v_estadoClase != 'Cursandose' THEN
            UPDATE TClaseGrupales
            SET estadoClase = 'Cursandose',
                fechaA = NOW()
            WHERE idClaseGrupal = v_idClaseGrupal;
        END IF;
    END IF;

    -- Validar que el nuevo estado sea permitido
    IF p_nuevoEstado NOT IN ('Asistido', 'Penalizado') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Estado de asistencia no válido.';
    END IF;

    -- Actualizar estado de la reserva
    UPDATE TReservas
    SET estadoReserva = p_nuevoEstado
    WHERE idReserva = p_idReserva;

    -- Si es 'Penalizado', aplicar strike al socio
    IF p_nuevoEstado = 'Penalizado' THEN
        INSERT INTO TPenalizaciones (carnetSocio, idReserva, fecha, estado, usuarioA)
        VALUES (v_carnetSocio, p_idReserva, v_fechaHoy, TRUE, p_usuarioA);

        UPDATE TSocios
        SET strikes = strikes + 1
        WHERE carnetSocio = v_carnetSocio;

        SELECT strikes INTO v_nuevosStrikes
        FROM TSocios
        WHERE carnetSocio = v_carnetSocio;

        IF v_nuevosStrikes >= 3 THEN
            INSERT INTO TPenalizaciones (carnetSocio, idReserva, fecha, estado, usuarioA)
            VALUES (v_carnetSocio, NULL, v_fechaHoy, TRUE, p_usuarioA);
        END IF;
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
               ' - Estado: ', p_nuevoEstado)
    );

    COMMIT;

    SELECT TRUE AS success, 'Asistencia registrada correctamente.' AS message;
END
        ");

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }

    public function down(): void
    {
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TReservas_MarcarAsistencia_Entrenador");

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);

        (new class extends Migration
        {
            public function up(): void
            {
                $pdo = DB::getPdo();
                $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

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
    DECLARE v_fechaHoy DATE;
    DECLARE v_horaAhora TIME;
    DECLARE v_nuevosStrikes INT;
    DECLARE v_mensajeError TEXT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SET v_fechaHoy = CURDATE();
    SET v_horaAhora = CURTIME();

    SELECT r.estadoReserva, r.idClaseGrupal, r.carnetSocio,
           cg.fecha, cg.horaInicio, cg.horaFin
    INTO v_estadoActual, v_idClaseGrupal, v_carnetSocio,
         v_fechaClase, v_horaInicio, v_horaFin
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

    IF v_horaAhora < v_horaInicio THEN
        SET v_mensajeError = CONCAT(
            'La clase aún no ha comenzado. Inicio: ',
            v_horaInicio, ' - Hora actual: ', v_horaAhora
        );
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensajeError;
    END IF;

    IF v_horaAhora > v_horaFin THEN
        SET v_mensajeError = CONCAT(
            'La clase ya finalizó. Fin: ',
            v_horaFin, ' - Hora actual: ', v_horaAhora
        );
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensajeError;
    END IF;

    IF p_nuevoEstado NOT IN ('Asistido', 'Penalizado') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Estado de asistencia no válido.';
    END IF;

    UPDATE TReservas
    SET estadoReserva = p_nuevoEstado
    WHERE idReserva = p_idReserva;

    IF p_nuevoEstado = 'Penalizado' THEN
        INSERT INTO TPenalizaciones (carnetSocio, idReserva, fecha, estado, usuarioA)
        VALUES (v_carnetSocio, p_idReserva, v_fechaHoy, TRUE, p_usuarioA);

        UPDATE TSocios
        SET strikes = strikes + 1
        WHERE carnetSocio = v_carnetSocio;

        SELECT strikes INTO v_nuevosStrikes
        FROM TSocios
        WHERE carnetSocio = v_carnetSocio;

        IF v_nuevosStrikes >= 3 THEN
            INSERT INTO TPenalizaciones (carnetSocio, idReserva, fecha, estado, usuarioA)
            VALUES (v_carnetSocio, NULL, v_fechaHoy, TRUE, p_usuarioA);
        END IF;
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
               ' - Estado: ', p_nuevoEstado)
    );

    COMMIT;

    SELECT TRUE AS success, 'Asistencia registrada correctamente.' AS message;
END
                ");

                $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
            }
            public function down(): void {}
        })->up();
    }
};

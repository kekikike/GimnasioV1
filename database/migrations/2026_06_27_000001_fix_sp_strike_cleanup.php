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
        // 1. sp_TSocios_LimpiarStrikesVencidos (NUEVO)
        //    Barre todos los socios con strikes > 0 y limpia aquellos cuyo
        //    período de limpieza en bloque (30/60/90 días sin faltas) ya expiró.
        //    Diseñado para ejecutarse desde endpoints de listado/detalle.
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TSocios_LimpiarStrikesVencidos");
        $pdo->exec("
CREATE PROCEDURE sp_TSocios_LimpiarStrikesVencidos()
BEGIN
    DECLARE v_carnet INT;
    DECLARE v_strikes INT;
    DECLARE v_ultimaFecha DATE;
    DECLARE v_diasDesdeUltimo INT;
    DECLARE v_diasLimpieza INT;
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_limpiados INT DEFAULT 0;
    DECLARE v_fechaHoy DATE;

    DECLARE cur CURSOR FOR
        SELECT s.carnetSocio, s.strikes,
               COALESCE(
                   (SELECT MAX(p.fecha) FROM TPenalizaciones p
                    WHERE p.carnetSocio = s.carnetSocio
                      AND p.estado = 1 AND p.estadoA = 1),
                   '1900-01-01'
               ) AS ultimaFecha
        FROM TSocios s
        WHERE s.estadoA = 1 AND s.strikes > 0;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET v_fechaHoy = CURDATE();

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_carnet, v_strikes, v_ultimaFecha;
        IF done THEN
            LEAVE read_loop;
        END IF;

        SET v_diasDesdeUltimo = DATEDIFF(v_fechaHoy, v_ultimaFecha);

        CASE v_strikes
            WHEN 1 THEN SET v_diasLimpieza = 30;
            WHEN 2 THEN SET v_diasLimpieza = 60;
            WHEN 3 THEN SET v_diasLimpieza = 90;
            ELSE SET v_diasLimpieza = 90;
        END CASE;

        IF v_diasDesdeUltimo >= v_diasLimpieza THEN
            UPDATE TSocios
            SET strikes = 0
            WHERE carnetSocio = v_carnet;

            UPDATE TPenalizaciones
            SET estado = 0
            WHERE carnetSocio = v_carnet AND estado = 1;

            SET v_limpiados = v_limpiados + 1;
        END IF;
    END LOOP;

    CLOSE cur;

    IF v_limpiados > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TSocios', 0, 'U', 'strikes',
                CONCAT('Lotes con ', v_limpiados, ' socio(s) con strikes pendientes'),
                CONCAT(v_limpiados, ' socio(s) limpiado(s) a 0'),
                1, NOW(), '127.0.0.1',
                CONCAT('Limpieza masiva de strikes vencidos. Socios limpiados: ', v_limpiados, '. Fecha: ', v_fechaHoy));
    END IF;
END
        ");

        // =========================================================================
        // 2. sp_TSocios_VerificarSuspension (RECREADO)
        //    Eliminado el parámetro IN p_fechaHoy. Ahora usa CURDATE() internamente
        //    para garantizar que siempre evalúe contra la fecha real del sistema,
        //    independientemente del valor que pase el llamante.
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TSocios_VerificarSuspension");
        $pdo->exec("
CREATE PROCEDURE sp_TSocios_VerificarSuspension(
    IN p_carnetSocio INT,
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
    DECLARE v_fechaHoy DATE;
    DECLARE v_auditDetalle TEXT;

    SET v_fechaHoy = CURDATE();

    SELECT strikes INTO v_strikes
    FROM TSocios
    WHERE carnetSocio = p_carnetSocio AND estadoA = 1;

    SELECT MAX(fecha) INTO v_ultimaFechaPenalizacion
    FROM TPenalizaciones
    WHERE carnetSocio = p_carnetSocio AND estado = 1 AND estadoA = 1;

    SET p_strikesActuales = COALESCE(v_strikes, 0);

    IF p_strikesActuales = 0 OR v_ultimaFechaPenalizacion IS NULL THEN
        SET p_enSuspension = FALSE;
        SET p_motivo = NULL;
    ELSE
        SET v_diasDesdeUltimoStrike = DATEDIFF(v_fechaHoy, v_ultimaFechaPenalizacion);

        CASE p_strikesActuales
            WHEN 1 THEN SET v_diasSuspension = 5;
            WHEN 2 THEN SET v_diasSuspension = 7;
            WHEN 3 THEN SET v_diasSuspension = 14;
            ELSE SET v_diasSuspension = 14;
        END CASE;

        CASE p_strikesActuales
            WHEN 1 THEN SET v_diasLimpieza = 30;
            WHEN 2 THEN SET v_diasLimpieza = 60;
            WHEN 3 THEN SET v_diasLimpieza = 90;
            ELSE SET v_diasLimpieza = 90;
        END CASE;

        IF v_diasDesdeUltimoStrike >= v_diasLimpieza THEN
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
        // 3. sp_TControlAccesos_Registrar (RECREADO)
        //    Actualizada la llamada a sp_TSocios_VerificarSuspension: ya no pasa
        //    p_fechaHoy (el SP ahora usa CURDATE() internamente).
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

    CALL sp_TSocios_VerificarSuspension(
        p_carnetSocio,
        @tmp_enSuspension, @tmp_motivoSuspension, @tmp_strikes
    );

    SET v_enSuspension = @tmp_enSuspension;
    SET v_motivoSuspension = @tmp_motivoSuspension;

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

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }

    public function down(): void
    {
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TSocios_LimpiarStrikesVencidos");

        // Restaurar sp_TSocios_VerificarSuspension con p_fechaHoy (versión anterior)
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

    SELECT strikes INTO v_strikes
    FROM TSocios
    WHERE carnetSocio = p_carnetSocio AND estadoA = 1;

    SELECT MAX(fecha) INTO v_ultimaFechaPenalizacion
    FROM TPenalizaciones
    WHERE carnetSocio = p_carnetSocio AND estado = 1 AND estadoA = 1;

    SET p_strikesActuales = COALESCE(v_strikes, 0);

    IF p_strikesActuales = 0 OR v_ultimaFechaPenalizacion IS NULL THEN
        SET p_enSuspension = FALSE;
        SET p_motivo = NULL;
    ELSE
        SET v_diasDesdeUltimoStrike = DATEDIFF(p_fechaHoy, v_ultimaFechaPenalizacion);

        CASE p_strikesActuales
            WHEN 1 THEN SET v_diasSuspension = 5;
            WHEN 2 THEN SET v_diasSuspension = 7;
            WHEN 3 THEN SET v_diasSuspension = 14;
            ELSE SET v_diasSuspension = 14;
        END CASE;

        CASE p_strikesActuales
            WHEN 1 THEN SET v_diasLimpieza = 30;
            WHEN 2 THEN SET v_diasLimpieza = 60;
            WHEN 3 THEN SET v_diasLimpieza = 90;
            ELSE SET v_diasLimpieza = 90;
        END CASE;

        IF v_diasDesdeUltimoStrike >= v_diasLimpieza THEN
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

        // Restaurar sp_TControlAccesos_Registrar con la llamada anterior (con p_fechaHoy)
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

    CALL sp_TSocios_VerificarSuspension(
        p_carnetSocio, v_fechaHoy,
        @tmp_enSuspension, @tmp_motivoSuspension, @tmp_strikes
    );

    SET v_enSuspension = @tmp_enSuspension;
    SET v_motivoSuspension = @tmp_motivoSuspension;

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

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }
};

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-06-2026 a las 15:38:03
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gimnasio_db`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TActividades_Delete` (IN `p_idActividad` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TActividades
    SET estadoA = 0
    WHERE idActividad = p_idActividad;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TActividades', p_idActividad, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TActividades_Insert` (IN `p_nombreActividad` VARCHAR(500), IN `p_descripcionActividad` VARCHAR(500), IN `p_estado` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TActividades (nombreActividad, descripcionActividad, estado, estadoA, usuarioA)
    VALUES (p_nombreActividad, p_descripcionActividad, p_estado, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TActividades', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_nombreActividad, ''), COALESCE(p_descripcionActividad, ''), COALESCE(p_estado, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TActividades_Select` ()   BEGIN
    SELECT * FROM TActividades
    WHERE estadoA = 1
    ORDER BY idActividad DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TActividades_SelectById` (IN `p_idActividad` INT)   BEGIN
    SELECT * FROM TActividades
    WHERE idActividad = p_idActividad;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TActividades_Update` (IN `p_idActividad` INT, IN `p_nombreActividad` VARCHAR(500), IN `p_descripcionActividad` VARCHAR(500), IN `p_estado` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_nombreActividad VARCHAR(500);
    DECLARE v_old_descripcionActividad VARCHAR(500);
    DECLARE v_old_estado VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(nombreActividad, ''), COALESCE(descripcionActividad, ''), COALESCE(estado, '')
    INTO v_old_nombreActividad, v_old_descripcionActividad, v_old_estado
    FROM TActividades WHERE idActividad = p_idActividad;

    UPDATE TActividades
    SET nombreActividad = p_nombreActividad,
        descripcionActividad = p_descripcionActividad,
        estado = p_estado
    WHERE idActividad = p_idActividad;

        IF v_old_nombreActividad <> p_nombreActividad OR (v_old_nombreActividad IS NULL AND p_nombreActividad IS NOT NULL) OR (v_old_nombreActividad IS NOT NULL AND p_nombreActividad IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'nombreActividad');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_nombreActividad, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_nombreActividad, ''));
    END IF;
    IF v_old_descripcionActividad <> p_descripcionActividad OR (v_old_descripcionActividad IS NULL AND p_descripcionActividad IS NOT NULL) OR (v_old_descripcionActividad IS NOT NULL AND p_descripcionActividad IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'descripcionActividad');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_descripcionActividad, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_descripcionActividad, ''));
    END IF;
    IF v_old_estado <> p_estado OR (v_old_estado IS NULL AND p_estado IS NOT NULL) OR (v_old_estado IS NOT NULL AND p_estado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estado, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TActividades', p_idActividad, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TAuditorias_Select` ()   BEGIN
    SELECT * FROM TAuditorias
    WHERE estadoA = 1
    ORDER BY idAuditoria DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TAuditorias_SelectById` (IN `p_idAuditoria` INT)   BEGIN
    SELECT * FROM TAuditorias
    WHERE idAuditoria = p_idAuditoria;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TCajas_Delete` (IN `p_idCaja` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TCajas
    SET estadoA = 0
    WHERE idCaja = p_idCaja;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TCajas', p_idCaja, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TCajas_Insert` (IN `p_idSucursal` VARCHAR(500), IN `p_carnetEmpleado` VARCHAR(500), IN `p_fechaApertura` VARCHAR(500), IN `p_horaApertura` VARCHAR(500), IN `p_montoApertura` VARCHAR(500), IN `p_montoCierre` VARCHAR(500), IN `p_montoCierreCalculado` VARCHAR(500), IN `p_diferenciaArqueo` VARCHAR(500), IN `p_estadoCaja` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TCajas (idSucursal, carnetEmpleado, fechaApertura, horaApertura, montoApertura, montoCierre, montoCierreCalculado, diferenciaArqueo, estadoCaja, estadoA, usuarioA)
    VALUES (p_idSucursal, p_carnetEmpleado, p_fechaApertura, p_horaApertura, p_montoApertura, p_montoCierre, p_montoCierreCalculado, p_diferenciaArqueo, p_estadoCaja, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TCajas', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_idSucursal, ''), COALESCE(p_carnetEmpleado, ''), COALESCE(p_fechaApertura, ''), COALESCE(p_horaApertura, ''), COALESCE(p_montoApertura, ''), COALESCE(p_montoCierre, ''), COALESCE(p_montoCierreCalculado, ''), COALESCE(p_diferenciaArqueo, ''), COALESCE(p_estadoCaja, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TCajas_Select` ()   BEGIN
    SELECT * FROM TCajas
    WHERE estadoA = 1
    ORDER BY idCaja DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TCajas_SelectById` (IN `p_idCaja` INT)   BEGIN
    SELECT * FROM TCajas
    WHERE idCaja = p_idCaja;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TCajas_Update` (IN `p_idCaja` INT, IN `p_idSucursal` VARCHAR(500), IN `p_carnetEmpleado` VARCHAR(500), IN `p_fechaApertura` VARCHAR(500), IN `p_horaApertura` VARCHAR(500), IN `p_montoApertura` VARCHAR(500), IN `p_montoCierre` VARCHAR(500), IN `p_montoCierreCalculado` VARCHAR(500), IN `p_diferenciaArqueo` VARCHAR(500), IN `p_estadoCaja` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_idSucursal VARCHAR(500);
    DECLARE v_old_carnetEmpleado VARCHAR(500);
    DECLARE v_old_fechaApertura VARCHAR(500);
    DECLARE v_old_horaApertura VARCHAR(500);
    DECLARE v_old_montoApertura VARCHAR(500);
    DECLARE v_old_montoCierre VARCHAR(500);
    DECLARE v_old_montoCierreCalculado VARCHAR(500);
    DECLARE v_old_diferenciaArqueo VARCHAR(500);
    DECLARE v_old_estadoCaja VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(idSucursal, ''), COALESCE(carnetEmpleado, ''), COALESCE(fechaApertura, ''), COALESCE(horaApertura, ''), COALESCE(montoApertura, ''), COALESCE(montoCierre, ''), COALESCE(montoCierreCalculado, ''), COALESCE(diferenciaArqueo, ''), COALESCE(estadoCaja, '')
    INTO v_old_idSucursal, v_old_carnetEmpleado, v_old_fechaApertura, v_old_horaApertura, v_old_montoApertura, v_old_montoCierre, v_old_montoCierreCalculado, v_old_diferenciaArqueo, v_old_estadoCaja
    FROM TCajas WHERE idCaja = p_idCaja;

    UPDATE TCajas
    SET idSucursal = p_idSucursal,
        carnetEmpleado = p_carnetEmpleado,
        fechaApertura = p_fechaApertura,
        horaApertura = p_horaApertura,
        montoApertura = p_montoApertura,
        montoCierre = p_montoCierre,
        montoCierreCalculado = p_montoCierreCalculado,
        diferenciaArqueo = p_diferenciaArqueo,
        estadoCaja = p_estadoCaja
    WHERE idCaja = p_idCaja;

        IF v_old_idSucursal <> p_idSucursal OR (v_old_idSucursal IS NULL AND p_idSucursal IS NOT NULL) OR (v_old_idSucursal IS NOT NULL AND p_idSucursal IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idSucursal');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idSucursal, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idSucursal, ''));
    END IF;
    IF v_old_carnetEmpleado <> p_carnetEmpleado OR (v_old_carnetEmpleado IS NULL AND p_carnetEmpleado IS NOT NULL) OR (v_old_carnetEmpleado IS NOT NULL AND p_carnetEmpleado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'carnetEmpleado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_carnetEmpleado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_carnetEmpleado, ''));
    END IF;
    IF v_old_fechaApertura <> p_fechaApertura OR (v_old_fechaApertura IS NULL AND p_fechaApertura IS NOT NULL) OR (v_old_fechaApertura IS NOT NULL AND p_fechaApertura IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaApertura');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaApertura, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaApertura, ''));
    END IF;
    IF v_old_horaApertura <> p_horaApertura OR (v_old_horaApertura IS NULL AND p_horaApertura IS NOT NULL) OR (v_old_horaApertura IS NOT NULL AND p_horaApertura IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'horaApertura');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_horaApertura, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_horaApertura, ''));
    END IF;
    IF v_old_montoApertura <> p_montoApertura OR (v_old_montoApertura IS NULL AND p_montoApertura IS NOT NULL) OR (v_old_montoApertura IS NOT NULL AND p_montoApertura IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'montoApertura');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_montoApertura, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_montoApertura, ''));
    END IF;
    IF v_old_montoCierre <> p_montoCierre OR (v_old_montoCierre IS NULL AND p_montoCierre IS NOT NULL) OR (v_old_montoCierre IS NOT NULL AND p_montoCierre IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'montoCierre');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_montoCierre, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_montoCierre, ''));
    END IF;
    IF v_old_montoCierreCalculado <> p_montoCierreCalculado OR (v_old_montoCierreCalculado IS NULL AND p_montoCierreCalculado IS NOT NULL) OR (v_old_montoCierreCalculado IS NOT NULL AND p_montoCierreCalculado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'montoCierreCalculado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_montoCierreCalculado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_montoCierreCalculado, ''));
    END IF;
    IF v_old_diferenciaArqueo <> p_diferenciaArqueo OR (v_old_diferenciaArqueo IS NULL AND p_diferenciaArqueo IS NOT NULL) OR (v_old_diferenciaArqueo IS NOT NULL AND p_diferenciaArqueo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'diferenciaArqueo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_diferenciaArqueo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_diferenciaArqueo, ''));
    END IF;
    IF v_old_estadoCaja <> p_estadoCaja OR (v_old_estadoCaja IS NULL AND p_estadoCaja IS NOT NULL) OR (v_old_estadoCaja IS NOT NULL AND p_estadoCaja IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estadoCaja');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estadoCaja, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estadoCaja, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TCajas', p_idCaja, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TClaseGrupales_Delete` (IN `p_idClaseGrupal` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TClaseGrupales
    SET estadoA = 0
    WHERE idClaseGrupal = p_idClaseGrupal;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TClaseGrupales', p_idClaseGrupal, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TClaseGrupales_GetAvailable` ()   BEGIN
    SELECT cg.*, a.nombreActividad, a.descripcionActividad,
           s.nombre AS sucursalNombre,
           CONCAT(u.nombre1, ' ', u.apellido1) AS entrenador
    FROM TClaseGrupales cg
    INNER JOIN TActividades a ON a.idActividad = cg.idActividad
    INNER JOIN TSucursales s ON s.idSucursal = cg.idSucursal
    INNER JOIN TEmpleados e ON e.carnetEmpleado = cg.carnetEmpleado
    INNER JOIN TUsuarios u ON u.idUsuario = e.idUsuario
    WHERE cg.fecha >= CURDATE()
      AND cg.estadoA = 1
      AND cg.estadoClase = 'Programada'
    ORDER BY cg.fecha ASC, cg.horaInicio ASC
    LIMIT 10;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TClaseGrupales_Insert` (IN `p_idActividad` VARCHAR(500), IN `p_carnetEmpleado` VARCHAR(500), IN `p_idSucursal` VARCHAR(500), IN `p_fecha` VARCHAR(500), IN `p_horaInicio` VARCHAR(500), IN `p_horaFin` VARCHAR(500), IN `p_cupoMaximo` VARCHAR(500), IN `p_estadoClase` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TClaseGrupales (idActividad, carnetEmpleado, idSucursal, fecha, horaInicio, horaFin, cupoMaximo, estadoClase, estadoA, usuarioA)
    VALUES (p_idActividad, p_carnetEmpleado, p_idSucursal, p_fecha, p_horaInicio, p_horaFin, p_cupoMaximo, p_estadoClase, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TClaseGrupales', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_idActividad, ''), COALESCE(p_carnetEmpleado, ''), COALESCE(p_idSucursal, ''), COALESCE(p_fecha, ''), COALESCE(p_horaInicio, ''), COALESCE(p_horaFin, ''), COALESCE(p_cupoMaximo, ''), COALESCE(p_estadoClase, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TClaseGrupales_Insert_Validated` (IN `p_idActividad` INT, IN `p_carnetEmpleado` INT, IN `p_idSucursal` INT, IN `p_fecha` DATE, IN `p_horaInicio` TIME, IN `p_horaFin` TIME, IN `p_cupoMaximo` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TClaseGrupales_Select` ()   BEGIN
    SELECT * FROM TClaseGrupales
    WHERE estadoA = 1
    ORDER BY idClaseGrupal DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TClaseGrupales_SelectById` (IN `p_idClaseGrupal` INT)   BEGIN
    SELECT * FROM TClaseGrupales
    WHERE idClaseGrupal = p_idClaseGrupal;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TClaseGrupales_Update` (IN `p_idClaseGrupal` INT, IN `p_idActividad` VARCHAR(500), IN `p_carnetEmpleado` VARCHAR(500), IN `p_idSucursal` VARCHAR(500), IN `p_fecha` VARCHAR(500), IN `p_horaInicio` VARCHAR(500), IN `p_horaFin` VARCHAR(500), IN `p_cupoMaximo` VARCHAR(500), IN `p_estadoClase` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_idActividad VARCHAR(500);
    DECLARE v_old_carnetEmpleado VARCHAR(500);
    DECLARE v_old_idSucursal VARCHAR(500);
    DECLARE v_old_fecha VARCHAR(500);
    DECLARE v_old_horaInicio VARCHAR(500);
    DECLARE v_old_horaFin VARCHAR(500);
    DECLARE v_old_cupoMaximo VARCHAR(500);
    DECLARE v_old_estadoClase VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(idActividad, ''), COALESCE(carnetEmpleado, ''), COALESCE(idSucursal, ''), COALESCE(fecha, ''), COALESCE(horaInicio, ''), COALESCE(horaFin, ''), COALESCE(cupoMaximo, ''), COALESCE(estadoClase, '')
    INTO v_old_idActividad, v_old_carnetEmpleado, v_old_idSucursal, v_old_fecha, v_old_horaInicio, v_old_horaFin, v_old_cupoMaximo, v_old_estadoClase
    FROM TClaseGrupales WHERE idClaseGrupal = p_idClaseGrupal;

    UPDATE TClaseGrupales
    SET idActividad = p_idActividad,
        carnetEmpleado = p_carnetEmpleado,
        idSucursal = p_idSucursal,
        fecha = p_fecha,
        horaInicio = p_horaInicio,
        horaFin = p_horaFin,
        cupoMaximo = p_cupoMaximo,
        estadoClase = p_estadoClase
    WHERE idClaseGrupal = p_idClaseGrupal;

        IF v_old_idActividad <> p_idActividad OR (v_old_idActividad IS NULL AND p_idActividad IS NOT NULL) OR (v_old_idActividad IS NOT NULL AND p_idActividad IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idActividad');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idActividad, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idActividad, ''));
    END IF;
    IF v_old_carnetEmpleado <> p_carnetEmpleado OR (v_old_carnetEmpleado IS NULL AND p_carnetEmpleado IS NOT NULL) OR (v_old_carnetEmpleado IS NOT NULL AND p_carnetEmpleado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'carnetEmpleado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_carnetEmpleado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_carnetEmpleado, ''));
    END IF;
    IF v_old_idSucursal <> p_idSucursal OR (v_old_idSucursal IS NULL AND p_idSucursal IS NOT NULL) OR (v_old_idSucursal IS NOT NULL AND p_idSucursal IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idSucursal');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idSucursal, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idSucursal, ''));
    END IF;
    IF v_old_fecha <> p_fecha OR (v_old_fecha IS NULL AND p_fecha IS NOT NULL) OR (v_old_fecha IS NOT NULL AND p_fecha IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fecha');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fecha, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fecha, ''));
    END IF;
    IF v_old_horaInicio <> p_horaInicio OR (v_old_horaInicio IS NULL AND p_horaInicio IS NOT NULL) OR (v_old_horaInicio IS NOT NULL AND p_horaInicio IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'horaInicio');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_horaInicio, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_horaInicio, ''));
    END IF;
    IF v_old_horaFin <> p_horaFin OR (v_old_horaFin IS NULL AND p_horaFin IS NOT NULL) OR (v_old_horaFin IS NOT NULL AND p_horaFin IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'horaFin');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_horaFin, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_horaFin, ''));
    END IF;
    IF v_old_cupoMaximo <> p_cupoMaximo OR (v_old_cupoMaximo IS NULL AND p_cupoMaximo IS NOT NULL) OR (v_old_cupoMaximo IS NOT NULL AND p_cupoMaximo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'cupoMaximo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_cupoMaximo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_cupoMaximo, ''));
    END IF;
    IF v_old_estadoClase <> p_estadoClase OR (v_old_estadoClase IS NULL AND p_estadoClase IS NOT NULL) OR (v_old_estadoClase IS NOT NULL AND p_estadoClase IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estadoClase');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estadoClase, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estadoClase, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TClaseGrupales', p_idClaseGrupal, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TControlAccesos_Delete` (IN `p_idControlAcceso` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TControlAccesos
    SET estadoA = 0
    WHERE idControlAcceso = p_idControlAcceso;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TControlAccesos', p_idControlAcceso, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TControlAccesos_GetBySocio` (IN `p_carnetSocio` INT)   BEGIN
    SELECT ca.*, s.nombre AS sucursal
    FROM TControlAccesos ca
    LEFT JOIN TSucursales s ON s.idSucursal = ca.idSucursal
    WHERE ca.carnetSocio = p_carnetSocio
      AND ca.estadoA = 1
    ORDER BY ca.fechaAcceso DESC, ca.horaAcceso DESC
    LIMIT 20;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TControlAccesos_Insert` (IN `p_carnetSocio` VARCHAR(500), IN `p_idSucursal` VARCHAR(500), IN `p_fechaAcceso` VARCHAR(500), IN `p_horaAcceso` VARCHAR(500), IN `p_bloqueo` VARCHAR(500), IN `p_motivoDenegacion` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TControlAccesos (carnetSocio, idSucursal, fechaAcceso, horaAcceso, bloqueo, motivoDenegacion, estadoA, usuarioA)
    VALUES (p_carnetSocio, p_idSucursal, p_fechaAcceso, p_horaAcceso, p_bloqueo, p_motivoDenegacion, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TControlAccesos', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_carnetSocio, ''), COALESCE(p_idSucursal, ''), COALESCE(p_fechaAcceso, ''), COALESCE(p_horaAcceso, ''), COALESCE(p_bloqueo, ''), COALESCE(p_motivoDenegacion, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TControlAccesos_Registrar` (IN `p_carnetSocio` INT, IN `p_idSucursal` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
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
        -- Auto-reactivar: sumar días congelados a fechaFin
        UPDATE TMembresias
        SET fechaFinMembresia = DATE_ADD(fechaFinMembresia, INTERVAL DATEDIFF(v_fechaHoy, v_fechaCong) DAY),
            estadoMembresia = 'Activa',
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TControlAccesos_Select` ()   BEGIN
    SELECT * FROM TControlAccesos
    WHERE estadoA = 1
    ORDER BY idControlAcceso DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TControlAccesos_SelectById` (IN `p_idControlAcceso` INT)   BEGIN
    SELECT * FROM TControlAccesos
    WHERE idControlAcceso = p_idControlAcceso;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TControlAccesos_Update` (IN `p_idControlAcceso` INT, IN `p_carnetSocio` VARCHAR(500), IN `p_idSucursal` VARCHAR(500), IN `p_fechaAcceso` VARCHAR(500), IN `p_horaAcceso` VARCHAR(500), IN `p_bloqueo` VARCHAR(500), IN `p_motivoDenegacion` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_carnetSocio VARCHAR(500);
    DECLARE v_old_idSucursal VARCHAR(500);
    DECLARE v_old_fechaAcceso VARCHAR(500);
    DECLARE v_old_horaAcceso VARCHAR(500);
    DECLARE v_old_bloqueo VARCHAR(500);
    DECLARE v_old_motivoDenegacion VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(carnetSocio, ''), COALESCE(idSucursal, ''), COALESCE(fechaAcceso, ''), COALESCE(horaAcceso, ''), COALESCE(bloqueo, ''), COALESCE(motivoDenegacion, '')
    INTO v_old_carnetSocio, v_old_idSucursal, v_old_fechaAcceso, v_old_horaAcceso, v_old_bloqueo, v_old_motivoDenegacion
    FROM TControlAccesos WHERE idControlAcceso = p_idControlAcceso;

    UPDATE TControlAccesos
    SET carnetSocio = p_carnetSocio,
        idSucursal = p_idSucursal,
        fechaAcceso = p_fechaAcceso,
        horaAcceso = p_horaAcceso,
        bloqueo = p_bloqueo,
        motivoDenegacion = p_motivoDenegacion
    WHERE idControlAcceso = p_idControlAcceso;

        IF v_old_carnetSocio <> p_carnetSocio OR (v_old_carnetSocio IS NULL AND p_carnetSocio IS NOT NULL) OR (v_old_carnetSocio IS NOT NULL AND p_carnetSocio IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'carnetSocio');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_carnetSocio, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_carnetSocio, ''));
    END IF;
    IF v_old_idSucursal <> p_idSucursal OR (v_old_idSucursal IS NULL AND p_idSucursal IS NOT NULL) OR (v_old_idSucursal IS NOT NULL AND p_idSucursal IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idSucursal');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idSucursal, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idSucursal, ''));
    END IF;
    IF v_old_fechaAcceso <> p_fechaAcceso OR (v_old_fechaAcceso IS NULL AND p_fechaAcceso IS NOT NULL) OR (v_old_fechaAcceso IS NOT NULL AND p_fechaAcceso IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaAcceso');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaAcceso, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaAcceso, ''));
    END IF;
    IF v_old_horaAcceso <> p_horaAcceso OR (v_old_horaAcceso IS NULL AND p_horaAcceso IS NOT NULL) OR (v_old_horaAcceso IS NOT NULL AND p_horaAcceso IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'horaAcceso');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_horaAcceso, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_horaAcceso, ''));
    END IF;
    IF v_old_bloqueo <> p_bloqueo OR (v_old_bloqueo IS NULL AND p_bloqueo IS NOT NULL) OR (v_old_bloqueo IS NOT NULL AND p_bloqueo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'bloqueo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_bloqueo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_bloqueo, ''));
    END IF;
    IF v_old_motivoDenegacion <> p_motivoDenegacion OR (v_old_motivoDenegacion IS NULL AND p_motivoDenegacion IS NOT NULL) OR (v_old_motivoDenegacion IS NOT NULL AND p_motivoDenegacion IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'motivoDenegacion');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_motivoDenegacion, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_motivoDenegacion, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TControlAccesos', p_idControlAcceso, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TControlAsistencias_Delete` (IN `p_idAsistencia` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TControlAsistencias
    SET estadoA = 0
    WHERE idAsistencia = p_idAsistencia;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TControlAsistencias', p_idAsistencia, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TControlAsistencias_Insert` (IN `p_carnetEmpleado` VARCHAR(500), IN `p_fecha` VARCHAR(500), IN `p_horaEntrada` VARCHAR(500), IN `p_horaSalida` VARCHAR(500), IN `p_estadoAsistencia` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TControlAsistencias (carnetEmpleado, fecha, horaEntrada, horaSalida, estadoAsistencia, estadoA, usuarioA)
    VALUES (p_carnetEmpleado, p_fecha, p_horaEntrada, p_horaSalida, p_estadoAsistencia, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TControlAsistencias', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_carnetEmpleado, ''), COALESCE(p_fecha, ''), COALESCE(p_horaEntrada, ''), COALESCE(p_horaSalida, ''), COALESCE(p_estadoAsistencia, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TControlAsistencias_Select` ()   BEGIN
    SELECT * FROM TControlAsistencias
    WHERE estadoA = 1
    ORDER BY idAsistencia DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TControlAsistencias_SelectById` (IN `p_idAsistencia` INT)   BEGIN
    SELECT * FROM TControlAsistencias
    WHERE idAsistencia = p_idAsistencia;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TControlAsistencias_Update` (IN `p_idAsistencia` INT, IN `p_carnetEmpleado` VARCHAR(500), IN `p_fecha` VARCHAR(500), IN `p_horaEntrada` VARCHAR(500), IN `p_horaSalida` VARCHAR(500), IN `p_estadoAsistencia` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_carnetEmpleado VARCHAR(500);
    DECLARE v_old_fecha VARCHAR(500);
    DECLARE v_old_horaEntrada VARCHAR(500);
    DECLARE v_old_horaSalida VARCHAR(500);
    DECLARE v_old_estadoAsistencia VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(carnetEmpleado, ''), COALESCE(fecha, ''), COALESCE(horaEntrada, ''), COALESCE(horaSalida, ''), COALESCE(estadoAsistencia, '')
    INTO v_old_carnetEmpleado, v_old_fecha, v_old_horaEntrada, v_old_horaSalida, v_old_estadoAsistencia
    FROM TControlAsistencias WHERE idAsistencia = p_idAsistencia;

    UPDATE TControlAsistencias
    SET carnetEmpleado = p_carnetEmpleado,
        fecha = p_fecha,
        horaEntrada = p_horaEntrada,
        horaSalida = p_horaSalida,
        estadoAsistencia = p_estadoAsistencia
    WHERE idAsistencia = p_idAsistencia;

        IF v_old_carnetEmpleado <> p_carnetEmpleado OR (v_old_carnetEmpleado IS NULL AND p_carnetEmpleado IS NOT NULL) OR (v_old_carnetEmpleado IS NOT NULL AND p_carnetEmpleado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'carnetEmpleado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_carnetEmpleado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_carnetEmpleado, ''));
    END IF;
    IF v_old_fecha <> p_fecha OR (v_old_fecha IS NULL AND p_fecha IS NOT NULL) OR (v_old_fecha IS NOT NULL AND p_fecha IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fecha');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fecha, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fecha, ''));
    END IF;
    IF v_old_horaEntrada <> p_horaEntrada OR (v_old_horaEntrada IS NULL AND p_horaEntrada IS NOT NULL) OR (v_old_horaEntrada IS NOT NULL AND p_horaEntrada IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'horaEntrada');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_horaEntrada, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_horaEntrada, ''));
    END IF;
    IF v_old_horaSalida <> p_horaSalida OR (v_old_horaSalida IS NULL AND p_horaSalida IS NOT NULL) OR (v_old_horaSalida IS NOT NULL AND p_horaSalida IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'horaSalida');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_horaSalida, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_horaSalida, ''));
    END IF;
    IF v_old_estadoAsistencia <> p_estadoAsistencia OR (v_old_estadoAsistencia IS NULL AND p_estadoAsistencia IS NOT NULL) OR (v_old_estadoAsistencia IS NOT NULL AND p_estadoAsistencia IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estadoAsistencia');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estadoAsistencia, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estadoAsistencia, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TControlAsistencias', p_idAsistencia, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TDetalleMetodoPagos_Delete` (IN `p_idMetodoPago` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TDetalleMetodoPagos
    SET estadoA = 0
    WHERE idMetodoPago = p_idMetodoPago;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TDetalleMetodoPagos', p_idMetodoPago, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TDetalleMetodoPagos_Insert` (IN `p_idRecibo` VARCHAR(500), IN `p_idMetodoPagoFK` VARCHAR(500), IN `p_monto` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TDetalleMetodoPagos (idRecibo, idMetodoPagoFK, monto, estadoA, usuarioA)
    VALUES (p_idRecibo, p_idMetodoPagoFK, p_monto, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TDetalleMetodoPagos', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_idRecibo, ''), COALESCE(p_idMetodoPagoFK, ''), COALESCE(p_monto, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TDetalleMetodoPagos_Select` ()   BEGIN
    SELECT * FROM TDetalleMetodoPagos
    WHERE estadoA = 1
    ORDER BY idMetodoPago DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TDetalleMetodoPagos_SelectById` (IN `p_idMetodoPago` INT)   BEGIN
    SELECT * FROM TDetalleMetodoPagos
    WHERE idMetodoPago = p_idMetodoPago;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TDetalleMetodoPagos_Update` (IN `p_idMetodoPago` INT, IN `p_idRecibo` VARCHAR(500), IN `p_idMetodoPagoFK` VARCHAR(500), IN `p_monto` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_idRecibo VARCHAR(500);
    DECLARE v_old_idMetodoPagoFK VARCHAR(500);
    DECLARE v_old_monto VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(idRecibo, ''), COALESCE(idMetodoPagoFK, ''), COALESCE(monto, '')
    INTO v_old_idRecibo, v_old_idMetodoPagoFK, v_old_monto
    FROM TDetalleMetodoPagos WHERE idMetodoPago = p_idMetodoPago;

    UPDATE TDetalleMetodoPagos
    SET idRecibo = p_idRecibo,
        idMetodoPagoFK = p_idMetodoPagoFK,
        monto = p_monto
    WHERE idMetodoPago = p_idMetodoPago;

        IF v_old_idRecibo <> p_idRecibo OR (v_old_idRecibo IS NULL AND p_idRecibo IS NOT NULL) OR (v_old_idRecibo IS NOT NULL AND p_idRecibo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idRecibo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idRecibo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idRecibo, ''));
    END IF;
    IF v_old_idMetodoPagoFK <> p_idMetodoPagoFK OR (v_old_idMetodoPagoFK IS NULL AND p_idMetodoPagoFK IS NOT NULL) OR (v_old_idMetodoPagoFK IS NOT NULL AND p_idMetodoPagoFK IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idMetodoPagoFK');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idMetodoPagoFK, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idMetodoPagoFK, ''));
    END IF;
    IF v_old_monto <> p_monto OR (v_old_monto IS NULL AND p_monto IS NOT NULL) OR (v_old_monto IS NOT NULL AND p_monto IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'monto');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_monto, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_monto, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TDetalleMetodoPagos', p_idMetodoPago, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEmpleados_Delete` (IN `p_carnetEmpleado` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TEmpleados
    SET estadoA = 0
    WHERE carnetEmpleado = p_carnetEmpleado;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TEmpleados', p_carnetEmpleado, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEmpleados_GetAllWithDetails` ()   BEGIN
    SELECT e.carnetEmpleado, e.idUsuario, e.idSucursal, e.sueldo,
           e.fechaContratoInicio,
           u.idRol, u.nombre1, u.apellido1, u.correo, u.telefono,
           r.nombreRol, s.nombre AS nombreSucursal
    FROM TEmpleados e
    INNER JOIN TUsuarios u ON e.idUsuario = u.idUsuario
    INNER JOIN TRoles r ON u.idRol = r.idRol
    INNER JOIN TSucursales s ON e.idSucursal = s.idSucursal
    WHERE e.estadoA = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEmpleados_Insert` (IN `p_carnetEmpleado` VARCHAR(500), IN `p_idUsuario` VARCHAR(500), IN `p_idSucursal` VARCHAR(500), IN `p_sueldo` VARCHAR(500), IN `p_especialidad` VARCHAR(500), IN `p_fechaContratoInicio` VARCHAR(500), IN `p_fechaContratoFin` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TEmpleados (idUsuario, idSucursal, sueldo, especialidad, fechaContratoInicio, fechaContratoFin, estadoA, usuarioA)
    VALUES (p_idUsuario, p_idSucursal, p_sueldo, p_especialidad, p_fechaContratoInicio, p_fechaContratoFin, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TEmpleados', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_idUsuario, ''), COALESCE(p_idSucursal, ''), COALESCE(p_sueldo, ''), COALESCE(p_especialidad, ''), COALESCE(p_fechaContratoInicio, ''), COALESCE(p_fechaContratoFin, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEmpleados_Select` ()   BEGIN
    SELECT * FROM TEmpleados
    WHERE estadoA = 1
    ORDER BY carnetEmpleado DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEmpleados_SelectById` (IN `p_carnetEmpleado` INT)   BEGIN
    SELECT * FROM TEmpleados
    WHERE carnetEmpleado = p_carnetEmpleado;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEmpleados_Update` (IN `p_carnetEmpleado` INT, IN `p_idUsuario` VARCHAR(500), IN `p_idSucursal` VARCHAR(500), IN `p_sueldo` VARCHAR(500), IN `p_especialidad` VARCHAR(500), IN `p_fechaContratoInicio` VARCHAR(500), IN `p_fechaContratoFin` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_idUsuario VARCHAR(500);
    DECLARE v_old_idSucursal VARCHAR(500);
    DECLARE v_old_sueldo VARCHAR(500);
    DECLARE v_old_especialidad VARCHAR(500);
    DECLARE v_old_fechaContratoInicio VARCHAR(500);
    DECLARE v_old_fechaContratoFin VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(idUsuario, ''), COALESCE(idSucursal, ''), COALESCE(sueldo, ''), COALESCE(especialidad, ''), COALESCE(fechaContratoInicio, ''), COALESCE(fechaContratoFin, '')
    INTO v_old_idUsuario, v_old_idSucursal, v_old_sueldo, v_old_especialidad, v_old_fechaContratoInicio, v_old_fechaContratoFin
    FROM TEmpleados WHERE carnetEmpleado = p_carnetEmpleado;

    UPDATE TEmpleados
    SET idUsuario = p_idUsuario,
        idSucursal = p_idSucursal,
        sueldo = p_sueldo,
        especialidad = p_especialidad,
        fechaContratoInicio = p_fechaContratoInicio,
        fechaContratoFin = p_fechaContratoFin
    WHERE carnetEmpleado = p_carnetEmpleado;

        IF v_old_idUsuario <> p_idUsuario OR (v_old_idUsuario IS NULL AND p_idUsuario IS NOT NULL) OR (v_old_idUsuario IS NOT NULL AND p_idUsuario IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idUsuario');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idUsuario, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idUsuario, ''));
    END IF;
    IF v_old_idSucursal <> p_idSucursal OR (v_old_idSucursal IS NULL AND p_idSucursal IS NOT NULL) OR (v_old_idSucursal IS NOT NULL AND p_idSucursal IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idSucursal');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idSucursal, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idSucursal, ''));
    END IF;
    IF v_old_sueldo <> p_sueldo OR (v_old_sueldo IS NULL AND p_sueldo IS NOT NULL) OR (v_old_sueldo IS NOT NULL AND p_sueldo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'sueldo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_sueldo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_sueldo, ''));
    END IF;
    IF v_old_especialidad <> p_especialidad OR (v_old_especialidad IS NULL AND p_especialidad IS NOT NULL) OR (v_old_especialidad IS NOT NULL AND p_especialidad IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'especialidad');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_especialidad, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_especialidad, ''));
    END IF;
    IF v_old_fechaContratoInicio <> p_fechaContratoInicio OR (v_old_fechaContratoInicio IS NULL AND p_fechaContratoInicio IS NOT NULL) OR (v_old_fechaContratoInicio IS NOT NULL AND p_fechaContratoInicio IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaContratoInicio');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaContratoInicio, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaContratoInicio, ''));
    END IF;
    IF v_old_fechaContratoFin <> p_fechaContratoFin OR (v_old_fechaContratoFin IS NULL AND p_fechaContratoFin IS NOT NULL) OR (v_old_fechaContratoFin IS NOT NULL AND p_fechaContratoFin IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaContratoFin');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaContratoFin, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaContratoFin, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TEmpleados', p_carnetEmpleado, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEquipamientos_Delete` (IN `p_idEquipo` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TEquipamientos
    SET estadoA = 0
    WHERE idEquipo = p_idEquipo;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TEquipamientos', p_idEquipo, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEquipamientos_GetByEstado` (IN `p_estado` VARCHAR(50))   BEGIN
    SELECT e.*, m.nombreMarca, s.nombre AS sucursal
    FROM TEquipamientos e
    LEFT JOIN TMarcas m ON m.idMarca = e.idMarca
    LEFT JOIN TSucursales s ON s.idSucursal = e.idSucursal
    WHERE e.estadoA = 1 AND e.estadoEquipo = p_estado
    ORDER BY e.nombreEquipo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEquipamientos_GetOperativosWithDetails` ()   BEGIN
    SELECT e.idEquipo, e.nombreEquipo, e.modelo, m.nombreMarca,
           s.nombre AS sucursal
    FROM TEquipamientos e
    LEFT JOIN TMarcas m ON m.idMarca = e.idMarca
    LEFT JOIN TSucursales s ON s.idSucursal = e.idSucursal
    WHERE e.estadoA = 1 AND e.estadoEquipo = 'Operativo'
    ORDER BY e.nombreEquipo ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEquipamientos_Insert` (IN `p_idSucursal` VARCHAR(500), IN `p_idMarca` VARCHAR(500), IN `p_nombreEquipo` VARCHAR(500), IN `p_modelo` VARCHAR(500), IN `p_fechaAdquisicion` VARCHAR(500), IN `p_estadoEquipo` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TEquipamientos (idSucursal, idMarca, nombreEquipo, modelo, fechaAdquisicion, estadoEquipo, estadoA, usuarioA)
    VALUES (p_idSucursal, p_idMarca, p_nombreEquipo, p_modelo, p_fechaAdquisicion, p_estadoEquipo, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TEquipamientos', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_idSucursal, ''), COALESCE(p_idMarca, ''), COALESCE(p_nombreEquipo, ''), COALESCE(p_modelo, ''), COALESCE(p_fechaAdquisicion, ''), COALESCE(p_estadoEquipo, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEquipamientos_Select` ()   BEGIN
    SELECT * FROM TEquipamientos
    WHERE estadoA = 1
    ORDER BY idEquipo DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEquipamientos_SelectById` (IN `p_idEquipo` INT)   BEGIN
    SELECT * FROM TEquipamientos
    WHERE idEquipo = p_idEquipo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEquipamientos_Update` (IN `p_idEquipo` INT, IN `p_idSucursal` VARCHAR(500), IN `p_idMarca` VARCHAR(500), IN `p_nombreEquipo` VARCHAR(500), IN `p_modelo` VARCHAR(500), IN `p_fechaAdquisicion` VARCHAR(500), IN `p_estadoEquipo` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_idSucursal VARCHAR(500);
    DECLARE v_old_idMarca VARCHAR(500);
    DECLARE v_old_nombreEquipo VARCHAR(500);
    DECLARE v_old_modelo VARCHAR(500);
    DECLARE v_old_fechaAdquisicion VARCHAR(500);
    DECLARE v_old_estadoEquipo VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(idSucursal, ''), COALESCE(idMarca, ''), COALESCE(nombreEquipo, ''), COALESCE(modelo, ''), COALESCE(fechaAdquisicion, ''), COALESCE(estadoEquipo, '')
    INTO v_old_idSucursal, v_old_idMarca, v_old_nombreEquipo, v_old_modelo, v_old_fechaAdquisicion, v_old_estadoEquipo
    FROM TEquipamientos WHERE idEquipo = p_idEquipo;

    UPDATE TEquipamientos
    SET idSucursal = p_idSucursal,
        idMarca = p_idMarca,
        nombreEquipo = p_nombreEquipo,
        modelo = p_modelo,
        fechaAdquisicion = p_fechaAdquisicion,
        estadoEquipo = p_estadoEquipo
    WHERE idEquipo = p_idEquipo;

        IF v_old_idSucursal <> p_idSucursal OR (v_old_idSucursal IS NULL AND p_idSucursal IS NOT NULL) OR (v_old_idSucursal IS NOT NULL AND p_idSucursal IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idSucursal');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idSucursal, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idSucursal, ''));
    END IF;
    IF v_old_idMarca <> p_idMarca OR (v_old_idMarca IS NULL AND p_idMarca IS NOT NULL) OR (v_old_idMarca IS NOT NULL AND p_idMarca IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idMarca');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idMarca, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idMarca, ''));
    END IF;
    IF v_old_nombreEquipo <> p_nombreEquipo OR (v_old_nombreEquipo IS NULL AND p_nombreEquipo IS NOT NULL) OR (v_old_nombreEquipo IS NOT NULL AND p_nombreEquipo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'nombreEquipo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_nombreEquipo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_nombreEquipo, ''));
    END IF;
    IF v_old_modelo <> p_modelo OR (v_old_modelo IS NULL AND p_modelo IS NOT NULL) OR (v_old_modelo IS NOT NULL AND p_modelo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'modelo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_modelo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_modelo, ''));
    END IF;
    IF v_old_fechaAdquisicion <> p_fechaAdquisicion OR (v_old_fechaAdquisicion IS NULL AND p_fechaAdquisicion IS NOT NULL) OR (v_old_fechaAdquisicion IS NOT NULL AND p_fechaAdquisicion IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaAdquisicion');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaAdquisicion, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaAdquisicion, ''));
    END IF;
    IF v_old_estadoEquipo <> p_estadoEquipo OR (v_old_estadoEquipo IS NULL AND p_estadoEquipo IS NOT NULL) OR (v_old_estadoEquipo IS NOT NULL AND p_estadoEquipo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estadoEquipo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estadoEquipo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estadoEquipo, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TEquipamientos', p_idEquipo, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEsquemaSueldos_Delete` (IN `p_idEsquemaSueldo` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TEsquemaSueldos
    SET estadoA = 0
    WHERE idEsquemaSueldo = p_idEsquemaSueldo;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TEsquemaSueldos', p_idEsquemaSueldo, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEsquemaSueldos_Insert` (IN `p_carnetEmpleado` VARCHAR(500), IN `p_modalidadPago` VARCHAR(500), IN `p_montoBase` VARCHAR(500), IN `p_tarifaHoraOClase` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TEsquemaSueldos (carnetEmpleado, modalidadPago, montoBase, tarifaHoraOClase, estadoA, usuarioA)
    VALUES (p_carnetEmpleado, p_modalidadPago, p_montoBase, p_tarifaHoraOClase, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TEsquemaSueldos', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_carnetEmpleado, ''), COALESCE(p_modalidadPago, ''), COALESCE(p_montoBase, ''), COALESCE(p_tarifaHoraOClase, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEsquemaSueldos_Select` ()   BEGIN
    SELECT * FROM TEsquemaSueldos
    WHERE estadoA = 1
    ORDER BY idEsquemaSueldo DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEsquemaSueldos_SelectById` (IN `p_idEsquemaSueldo` INT)   BEGIN
    SELECT * FROM TEsquemaSueldos
    WHERE idEsquemaSueldo = p_idEsquemaSueldo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TEsquemaSueldos_Update` (IN `p_idEsquemaSueldo` INT, IN `p_carnetEmpleado` VARCHAR(500), IN `p_modalidadPago` VARCHAR(500), IN `p_montoBase` VARCHAR(500), IN `p_tarifaHoraOClase` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_carnetEmpleado VARCHAR(500);
    DECLARE v_old_modalidadPago VARCHAR(500);
    DECLARE v_old_montoBase VARCHAR(500);
    DECLARE v_old_tarifaHoraOClase VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(carnetEmpleado, ''), COALESCE(modalidadPago, ''), COALESCE(montoBase, ''), COALESCE(tarifaHoraOClase, '')
    INTO v_old_carnetEmpleado, v_old_modalidadPago, v_old_montoBase, v_old_tarifaHoraOClase
    FROM TEsquemaSueldos WHERE idEsquemaSueldo = p_idEsquemaSueldo;

    UPDATE TEsquemaSueldos
    SET carnetEmpleado = p_carnetEmpleado,
        modalidadPago = p_modalidadPago,
        montoBase = p_montoBase,
        tarifaHoraOClase = p_tarifaHoraOClase
    WHERE idEsquemaSueldo = p_idEsquemaSueldo;

        IF v_old_carnetEmpleado <> p_carnetEmpleado OR (v_old_carnetEmpleado IS NULL AND p_carnetEmpleado IS NOT NULL) OR (v_old_carnetEmpleado IS NOT NULL AND p_carnetEmpleado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'carnetEmpleado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_carnetEmpleado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_carnetEmpleado, ''));
    END IF;
    IF v_old_modalidadPago <> p_modalidadPago OR (v_old_modalidadPago IS NULL AND p_modalidadPago IS NOT NULL) OR (v_old_modalidadPago IS NOT NULL AND p_modalidadPago IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'modalidadPago');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_modalidadPago, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_modalidadPago, ''));
    END IF;
    IF v_old_montoBase <> p_montoBase OR (v_old_montoBase IS NULL AND p_montoBase IS NOT NULL) OR (v_old_montoBase IS NOT NULL AND p_montoBase IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'montoBase');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_montoBase, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_montoBase, ''));
    END IF;
    IF v_old_tarifaHoraOClase <> p_tarifaHoraOClase OR (v_old_tarifaHoraOClase IS NULL AND p_tarifaHoraOClase IS NOT NULL) OR (v_old_tarifaHoraOClase IS NOT NULL AND p_tarifaHoraOClase IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'tarifaHoraOClase');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_tarifaHoraOClase, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_tarifaHoraOClase, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TEsquemaSueldos', p_idEsquemaSueldo, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_THorarioLaborales_Delete` (IN `p_idHorario` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE THorarioLaborales
    SET estadoA = 0
    WHERE idHorario = p_idHorario;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('THorarioLaborales', p_idHorario, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_THorarioLaborales_Insert` (IN `p_carnetEmpleado` VARCHAR(500), IN `p_diaSemana` VARCHAR(500), IN `p_horaEntradaEsperada` VARCHAR(500), IN `p_horaSalidaEsperada` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO THorarioLaborales (carnetEmpleado, diaSemana, horaEntradaEsperada, horaSalidaEsperada, estadoA, usuarioA)
    VALUES (p_carnetEmpleado, p_diaSemana, p_horaEntradaEsperada, p_horaSalidaEsperada, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('THorarioLaborales', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_carnetEmpleado, ''), COALESCE(p_diaSemana, ''), COALESCE(p_horaEntradaEsperada, ''), COALESCE(p_horaSalidaEsperada, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_THorarioLaborales_Select` ()   BEGIN
    SELECT * FROM THorarioLaborales
    WHERE estadoA = 1
    ORDER BY idHorario DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_THorarioLaborales_SelectById` (IN `p_idHorario` INT)   BEGIN
    SELECT * FROM THorarioLaborales
    WHERE idHorario = p_idHorario;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_THorarioLaborales_Update` (IN `p_idHorario` INT, IN `p_carnetEmpleado` VARCHAR(500), IN `p_diaSemana` VARCHAR(500), IN `p_horaEntradaEsperada` VARCHAR(500), IN `p_horaSalidaEsperada` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_carnetEmpleado VARCHAR(500);
    DECLARE v_old_diaSemana VARCHAR(500);
    DECLARE v_old_horaEntradaEsperada VARCHAR(500);
    DECLARE v_old_horaSalidaEsperada VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(carnetEmpleado, ''), COALESCE(diaSemana, ''), COALESCE(horaEntradaEsperada, ''), COALESCE(horaSalidaEsperada, '')
    INTO v_old_carnetEmpleado, v_old_diaSemana, v_old_horaEntradaEsperada, v_old_horaSalidaEsperada
    FROM THorarioLaborales WHERE idHorario = p_idHorario;

    UPDATE THorarioLaborales
    SET carnetEmpleado = p_carnetEmpleado,
        diaSemana = p_diaSemana,
        horaEntradaEsperada = p_horaEntradaEsperada,
        horaSalidaEsperada = p_horaSalidaEsperada
    WHERE idHorario = p_idHorario;

        IF v_old_carnetEmpleado <> p_carnetEmpleado OR (v_old_carnetEmpleado IS NULL AND p_carnetEmpleado IS NOT NULL) OR (v_old_carnetEmpleado IS NOT NULL AND p_carnetEmpleado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'carnetEmpleado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_carnetEmpleado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_carnetEmpleado, ''));
    END IF;
    IF v_old_diaSemana <> p_diaSemana OR (v_old_diaSemana IS NULL AND p_diaSemana IS NOT NULL) OR (v_old_diaSemana IS NOT NULL AND p_diaSemana IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'diaSemana');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_diaSemana, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_diaSemana, ''));
    END IF;
    IF v_old_horaEntradaEsperada <> p_horaEntradaEsperada OR (v_old_horaEntradaEsperada IS NULL AND p_horaEntradaEsperada IS NOT NULL) OR (v_old_horaEntradaEsperada IS NOT NULL AND p_horaEntradaEsperada IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'horaEntradaEsperada');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_horaEntradaEsperada, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_horaEntradaEsperada, ''));
    END IF;
    IF v_old_horaSalidaEsperada <> p_horaSalidaEsperada OR (v_old_horaSalidaEsperada IS NULL AND p_horaSalidaEsperada IS NOT NULL) OR (v_old_horaSalidaEsperada IS NOT NULL AND p_horaSalidaEsperada IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'horaSalidaEsperada');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_horaSalidaEsperada, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_horaSalidaEsperada, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('THorarioLaborales', p_idHorario, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMantenimientoPreventivos_CountRealizadoByEquipo` (IN `p_idEquipo` INT)   BEGIN
    SELECT COUNT(*) AS c
    FROM TMantenimientoPreventivos
    WHERE idEquipo = p_idEquipo
      AND estadoMantenimiento = 'Realizado'
      AND estadoA = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMantenimientoPreventivos_Delete` (IN `p_idMantenimiento` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TMantenimientoPreventivos
    SET estadoA = 0
    WHERE idMantenimiento = p_idMantenimiento;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TMantenimientoPreventivos', p_idMantenimiento, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMantenimientoPreventivos_GetAlertasPendientes` ()   BEGIN
    SELECT mp.*, e.nombreEquipo, e.estadoEquipo,
           DATEDIFF(mp.fechaProgramada, CURDATE()) AS diasRestantes
    FROM TMantenimientoPreventivos mp
    INNER JOIN TEquipamientos e ON e.idEquipo = mp.idEquipo
    WHERE mp.estadoA = 1
      AND mp.estadoMantenimiento = 'Pendiente'
      AND mp.fechaProgramada >= CURDATE()
    ORDER BY mp.fechaProgramada ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMantenimientoPreventivos_GetFiltered` (IN `p_estado` VARCHAR(50), IN `p_fecha_desde` DATE, IN `p_fecha_hasta` DATE)   BEGIN
    SELECT mp.*, e.nombreEquipo, e.estadoEquipo, e.modelo
    FROM TMantenimientoPreventivos mp
    INNER JOIN TEquipamientos e ON e.idEquipo = mp.idEquipo
    WHERE mp.estadoA = 1
      AND (p_estado IS NULL OR mp.estadoMantenimiento = p_estado)
      AND (p_fecha_desde IS NULL OR mp.fechaProgramada >= p_fecha_desde)
      AND (p_fecha_hasta IS NULL OR mp.fechaProgramada <= p_fecha_hasta)
    ORDER BY
        CASE mp.estadoMantenimiento
            WHEN 'Pendiente' THEN 1
            WHEN 'Cancelado' THEN 2
            WHEN 'Realizado' THEN 3
        END,
        mp.fechaProgramada DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMantenimientoPreventivos_GetHistorial` (IN `p_limit` INT)   BEGIN
    SELECT mp.*, e.nombreEquipo, e.estadoEquipo
    FROM TMantenimientoPreventivos mp
    INNER JOIN TEquipamientos e ON e.idEquipo = mp.idEquipo
    WHERE mp.estadoA = 1
    ORDER BY mp.fechaProgramada DESC
    LIMIT p_limit;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMantenimientoPreventivos_GetProximos` (IN `p_limit` INT)   BEGIN
    SELECT mp.*, e.nombreEquipo,
           DATEDIFF(mp.fechaProgramada, CURDATE()) AS diasRestantes
    FROM TMantenimientoPreventivos mp
    INNER JOIN TEquipamientos e ON e.idEquipo = mp.idEquipo
    WHERE mp.estadoA = 1
      AND mp.estadoMantenimiento = 'Pendiente'
      AND mp.fechaProgramada >= CURDATE()
    ORDER BY mp.fechaProgramada ASC
    LIMIT p_limit;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMantenimientoPreventivos_GetResumen` ()   BEGIN
    SELECT estadoMantenimiento, COUNT(*) AS cantidad
    FROM TMantenimientoPreventivos
    WHERE estadoA = 1
    GROUP BY estadoMantenimiento;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMantenimientoPreventivos_Insert` (IN `p_idEquipo` VARCHAR(500), IN `p_fechaProgramada` VARCHAR(500), IN `p_fechaRealizada` VARCHAR(500), IN `p_descripcionMantenimiento` VARCHAR(500), IN `p_costoMantenimiento` VARCHAR(500), IN `p_tecnicoAsignado` VARCHAR(500), IN `p_estadoMantenimiento` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TMantenimientoPreventivos (idEquipo, fechaProgramada, fechaRealizada, descripcionMantenimiento, costoMantenimiento, tecnicoAsignado, estadoMantenimiento, estadoA, usuarioA)
    VALUES (p_idEquipo, p_fechaProgramada, p_fechaRealizada, p_descripcionMantenimiento, p_costoMantenimiento, p_tecnicoAsignado, p_estadoMantenimiento, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TMantenimientoPreventivos', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_idEquipo, ''), COALESCE(p_fechaProgramada, ''), COALESCE(p_fechaRealizada, ''), COALESCE(p_descripcionMantenimiento, ''), COALESCE(p_costoMantenimiento, ''), COALESCE(p_tecnicoAsignado, ''), COALESCE(p_estadoMantenimiento, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMantenimientoPreventivos_Select` ()   BEGIN
    SELECT * FROM TMantenimientoPreventivos
    WHERE estadoA = 1
    ORDER BY idMantenimiento DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMantenimientoPreventivos_SelectById` (IN `p_idMantenimiento` INT)   BEGIN
    SELECT * FROM TMantenimientoPreventivos
    WHERE idMantenimiento = p_idMantenimiento;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMantenimientoPreventivos_Update` (IN `p_idMantenimiento` INT, IN `p_idEquipo` VARCHAR(500), IN `p_fechaProgramada` VARCHAR(500), IN `p_fechaRealizada` VARCHAR(500), IN `p_descripcionMantenimiento` VARCHAR(500), IN `p_costoMantenimiento` VARCHAR(500), IN `p_tecnicoAsignado` VARCHAR(500), IN `p_estadoMantenimiento` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_idEquipo VARCHAR(500);
    DECLARE v_old_fechaProgramada VARCHAR(500);
    DECLARE v_old_fechaRealizada VARCHAR(500);
    DECLARE v_old_descripcionMantenimiento VARCHAR(500);
    DECLARE v_old_costoMantenimiento VARCHAR(500);
    DECLARE v_old_tecnicoAsignado VARCHAR(500);
    DECLARE v_old_estadoMantenimiento VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(idEquipo, ''), COALESCE(fechaProgramada, ''), COALESCE(fechaRealizada, ''), COALESCE(descripcionMantenimiento, ''), COALESCE(costoMantenimiento, ''), COALESCE(tecnicoAsignado, ''), COALESCE(estadoMantenimiento, '')
    INTO v_old_idEquipo, v_old_fechaProgramada, v_old_fechaRealizada, v_old_descripcionMantenimiento, v_old_costoMantenimiento, v_old_tecnicoAsignado, v_old_estadoMantenimiento
    FROM TMantenimientoPreventivos WHERE idMantenimiento = p_idMantenimiento;

    UPDATE TMantenimientoPreventivos
    SET idEquipo = p_idEquipo,
        fechaProgramada = p_fechaProgramada,
        fechaRealizada = p_fechaRealizada,
        descripcionMantenimiento = p_descripcionMantenimiento,
        costoMantenimiento = p_costoMantenimiento,
        tecnicoAsignado = p_tecnicoAsignado,
        estadoMantenimiento = p_estadoMantenimiento
    WHERE idMantenimiento = p_idMantenimiento;

        IF v_old_idEquipo <> p_idEquipo OR (v_old_idEquipo IS NULL AND p_idEquipo IS NOT NULL) OR (v_old_idEquipo IS NOT NULL AND p_idEquipo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idEquipo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idEquipo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idEquipo, ''));
    END IF;
    IF v_old_fechaProgramada <> p_fechaProgramada OR (v_old_fechaProgramada IS NULL AND p_fechaProgramada IS NOT NULL) OR (v_old_fechaProgramada IS NOT NULL AND p_fechaProgramada IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaProgramada');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaProgramada, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaProgramada, ''));
    END IF;
    IF v_old_fechaRealizada <> p_fechaRealizada OR (v_old_fechaRealizada IS NULL AND p_fechaRealizada IS NOT NULL) OR (v_old_fechaRealizada IS NOT NULL AND p_fechaRealizada IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaRealizada');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaRealizada, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaRealizada, ''));
    END IF;
    IF v_old_descripcionMantenimiento <> p_descripcionMantenimiento OR (v_old_descripcionMantenimiento IS NULL AND p_descripcionMantenimiento IS NOT NULL) OR (v_old_descripcionMantenimiento IS NOT NULL AND p_descripcionMantenimiento IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'descripcionMantenimiento');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_descripcionMantenimiento, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_descripcionMantenimiento, ''));
    END IF;
    IF v_old_costoMantenimiento <> p_costoMantenimiento OR (v_old_costoMantenimiento IS NULL AND p_costoMantenimiento IS NOT NULL) OR (v_old_costoMantenimiento IS NOT NULL AND p_costoMantenimiento IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'costoMantenimiento');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_costoMantenimiento, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_costoMantenimiento, ''));
    END IF;
    IF v_old_tecnicoAsignado <> p_tecnicoAsignado OR (v_old_tecnicoAsignado IS NULL AND p_tecnicoAsignado IS NOT NULL) OR (v_old_tecnicoAsignado IS NOT NULL AND p_tecnicoAsignado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'tecnicoAsignado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_tecnicoAsignado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_tecnicoAsignado, ''));
    END IF;
    IF v_old_estadoMantenimiento <> p_estadoMantenimiento OR (v_old_estadoMantenimiento IS NULL AND p_estadoMantenimiento IS NOT NULL) OR (v_old_estadoMantenimiento IS NOT NULL AND p_estadoMantenimiento IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estadoMantenimiento');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estadoMantenimiento, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estadoMantenimiento, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TMantenimientoPreventivos', p_idMantenimiento, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMarcas_Delete` (IN `p_idMarca` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TMarcas
    SET estadoA = 0
    WHERE idMarca = p_idMarca;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TMarcas', p_idMarca, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMarcas_Insert` (IN `p_nombreMarca` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TMarcas (nombreMarca, estadoA, usuarioA)
    VALUES (p_nombreMarca, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TMarcas', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_nombreMarca, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMarcas_Select` ()   BEGIN
    SELECT * FROM TMarcas
    WHERE estadoA = 1
    ORDER BY idMarca DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMarcas_SelectById` (IN `p_idMarca` INT)   BEGIN
    SELECT * FROM TMarcas
    WHERE idMarca = p_idMarca;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMarcas_Update` (IN `p_idMarca` INT, IN `p_nombreMarca` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_nombreMarca VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(nombreMarca, '')
    INTO v_old_nombreMarca
    FROM TMarcas WHERE idMarca = p_idMarca;

    UPDATE TMarcas
    SET nombreMarca = p_nombreMarca
    WHERE idMarca = p_idMarca;

        IF v_old_nombreMarca <> p_nombreMarca OR (v_old_nombreMarca IS NULL AND p_nombreMarca IS NOT NULL) OR (v_old_nombreMarca IS NOT NULL AND p_nombreMarca IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'nombreMarca');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_nombreMarca, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_nombreMarca, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TMarcas', p_idMarca, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMembresias_Delete` (IN `p_idMembresia` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TMembresias
    SET estadoA = 0
    WHERE idMembresia = p_idMembresia;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TMembresias', p_idMembresia, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMembresias_GetActiveBySocio` (IN `p_carnetSocio` INT)   BEGIN
    SELECT m.*, p.nombrePlan, p.descripcion AS descripcionPlan, p.costoPlan, p.duracionDias
    FROM TMembresias m
    INNER JOIN TPlanes p ON p.idPlan = m.idPlan
    WHERE m.carnetSocio = p_carnetSocio
      AND m.estadoA = 1
    ORDER BY m.idMembresia DESC
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMembresias_Insert` (IN `p_idPlan` VARCHAR(500), IN `p_carnetSocio` VARCHAR(500), IN `p_idSucursal` VARCHAR(500), IN `p_fechaInicioMembresia` VARCHAR(500), IN `p_fechaFinMembresia` VARCHAR(500), IN `p_estadoMembresia` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TMembresias (idPlan, carnetSocio, idSucursal, fechaInicioMembresia, fechaFinMembresia, estadoMembresia, estadoA, usuarioA)
    VALUES (p_idPlan, p_carnetSocio, p_idSucursal, p_fechaInicioMembresia, p_fechaFinMembresia, p_estadoMembresia, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TMembresias', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_idPlan, ''), COALESCE(p_carnetSocio, ''), COALESCE(p_idSucursal, ''), COALESCE(p_fechaInicioMembresia, ''), COALESCE(p_fechaFinMembresia, ''), COALESCE(p_estadoMembresia, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMembresias_Select` ()   BEGIN
    SELECT * FROM TMembresias
    WHERE estadoA = 1
    ORDER BY idMembresia DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMembresias_SelectById` (IN `p_idMembresia` INT)   BEGIN
    SELECT * FROM TMembresias
    WHERE idMembresia = p_idMembresia;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMembresias_Update` (IN `p_idMembresia` INT, IN `p_idPlan` VARCHAR(500), IN `p_carnetSocio` VARCHAR(500), IN `p_idSucursal` VARCHAR(500), IN `p_fechaInicioMembresia` VARCHAR(500), IN `p_fechaFinMembresia` VARCHAR(500), IN `p_estadoMembresia` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_idPlan VARCHAR(500);
    DECLARE v_old_carnetSocio VARCHAR(500);
    DECLARE v_old_idSucursal VARCHAR(500);
    DECLARE v_old_fechaInicioMembresia VARCHAR(500);
    DECLARE v_old_fechaFinMembresia VARCHAR(500);
    DECLARE v_old_estadoMembresia VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(idPlan, ''), COALESCE(carnetSocio, ''), COALESCE(idSucursal, ''), COALESCE(fechaInicioMembresia, ''), COALESCE(fechaFinMembresia, ''), COALESCE(estadoMembresia, '')
    INTO v_old_idPlan, v_old_carnetSocio, v_old_idSucursal, v_old_fechaInicioMembresia, v_old_fechaFinMembresia, v_old_estadoMembresia
    FROM TMembresias WHERE idMembresia = p_idMembresia;

    UPDATE TMembresias
    SET idPlan = p_idPlan,
        carnetSocio = p_carnetSocio,
        idSucursal = p_idSucursal,
        fechaInicioMembresia = p_fechaInicioMembresia,
        fechaFinMembresia = p_fechaFinMembresia,
        estadoMembresia = p_estadoMembresia
    WHERE idMembresia = p_idMembresia;

        IF v_old_idPlan <> p_idPlan OR (v_old_idPlan IS NULL AND p_idPlan IS NOT NULL) OR (v_old_idPlan IS NOT NULL AND p_idPlan IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idPlan');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idPlan, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idPlan, ''));
    END IF;
    IF v_old_carnetSocio <> p_carnetSocio OR (v_old_carnetSocio IS NULL AND p_carnetSocio IS NOT NULL) OR (v_old_carnetSocio IS NOT NULL AND p_carnetSocio IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'carnetSocio');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_carnetSocio, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_carnetSocio, ''));
    END IF;
    IF v_old_idSucursal <> p_idSucursal OR (v_old_idSucursal IS NULL AND p_idSucursal IS NOT NULL) OR (v_old_idSucursal IS NOT NULL AND p_idSucursal IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idSucursal');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idSucursal, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idSucursal, ''));
    END IF;
    IF v_old_fechaInicioMembresia <> p_fechaInicioMembresia OR (v_old_fechaInicioMembresia IS NULL AND p_fechaInicioMembresia IS NOT NULL) OR (v_old_fechaInicioMembresia IS NOT NULL AND p_fechaInicioMembresia IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaInicioMembresia');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaInicioMembresia, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaInicioMembresia, ''));
    END IF;
    IF v_old_fechaFinMembresia <> p_fechaFinMembresia OR (v_old_fechaFinMembresia IS NULL AND p_fechaFinMembresia IS NOT NULL) OR (v_old_fechaFinMembresia IS NOT NULL AND p_fechaFinMembresia IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaFinMembresia');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaFinMembresia, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaFinMembresia, ''));
    END IF;
    IF v_old_estadoMembresia <> p_estadoMembresia OR (v_old_estadoMembresia IS NULL AND p_estadoMembresia IS NOT NULL) OR (v_old_estadoMembresia IS NOT NULL AND p_estadoMembresia IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estadoMembresia');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estadoMembresia, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estadoMembresia, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TMembresias', p_idMembresia, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMetodoPagos_Delete` (IN `p_idMetodoPago` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TMetodoPagos
    SET estadoA = 0
    WHERE idMetodoPago = p_idMetodoPago;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TMetodoPagos', p_idMetodoPago, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMetodoPagos_Insert` (IN `p_nombreMetodoPago` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TMetodoPagos (nombreMetodoPago, estadoA, usuarioA)
    VALUES (p_nombreMetodoPago, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TMetodoPagos', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_nombreMetodoPago, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMetodoPagos_Select` ()   BEGIN
    SELECT * FROM TMetodoPagos
    WHERE estadoA = 1
    ORDER BY idMetodoPago DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMetodoPagos_SelectById` (IN `p_idMetodoPago` INT)   BEGIN
    SELECT * FROM TMetodoPagos
    WHERE idMetodoPago = p_idMetodoPago;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TMetodoPagos_Update` (IN `p_idMetodoPago` INT, IN `p_nombreMetodoPago` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_nombreMetodoPago VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(nombreMetodoPago, '')
    INTO v_old_nombreMetodoPago
    FROM TMetodoPagos WHERE idMetodoPago = p_idMetodoPago;

    UPDATE TMetodoPagos
    SET nombreMetodoPago = p_nombreMetodoPago
    WHERE idMetodoPago = p_idMetodoPago;

        IF v_old_nombreMetodoPago <> p_nombreMetodoPago OR (v_old_nombreMetodoPago IS NULL AND p_nombreMetodoPago IS NOT NULL) OR (v_old_nombreMetodoPago IS NOT NULL AND p_nombreMetodoPago IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'nombreMetodoPago');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_nombreMetodoPago, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_nombreMetodoPago, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TMetodoPagos', p_idMetodoPago, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TNotificaciones_Delete` (IN `p_idNotificacion` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TNotificaciones
    SET estadoA = 0
    WHERE idNotificacion = p_idNotificacion;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TNotificaciones', p_idNotificacion, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TNotificaciones_Insert` (IN `p_carnetSocio` VARCHAR(500), IN `p_tipoNotificacion` VARCHAR(500), IN `p_mensaje` VARCHAR(500), IN `p_canal` VARCHAR(500), IN `p_fechaEnvio` VARCHAR(500), IN `p_estado` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TNotificaciones (carnetSocio, tipoNotificacion, mensaje, canal, fechaEnvio, estado, estadoA, usuarioA)
    VALUES (p_carnetSocio, p_tipoNotificacion, p_mensaje, p_canal, p_fechaEnvio, p_estado, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TNotificaciones', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_carnetSocio, ''), COALESCE(p_tipoNotificacion, ''), COALESCE(p_mensaje, ''), COALESCE(p_canal, ''), COALESCE(p_fechaEnvio, ''), COALESCE(p_estado, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TNotificaciones_Select` ()   BEGIN
    SELECT * FROM TNotificaciones
    WHERE estadoA = 1
    ORDER BY idNotificacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TNotificaciones_SelectById` (IN `p_idNotificacion` INT)   BEGIN
    SELECT * FROM TNotificaciones
    WHERE idNotificacion = p_idNotificacion;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TNotificaciones_Update` (IN `p_idNotificacion` INT, IN `p_carnetSocio` VARCHAR(500), IN `p_tipoNotificacion` VARCHAR(500), IN `p_mensaje` VARCHAR(500), IN `p_canal` VARCHAR(500), IN `p_fechaEnvio` VARCHAR(500), IN `p_estado` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_carnetSocio VARCHAR(500);
    DECLARE v_old_tipoNotificacion VARCHAR(500);
    DECLARE v_old_mensaje VARCHAR(500);
    DECLARE v_old_canal VARCHAR(500);
    DECLARE v_old_fechaEnvio VARCHAR(500);
    DECLARE v_old_estado VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(carnetSocio, ''), COALESCE(tipoNotificacion, ''), COALESCE(mensaje, ''), COALESCE(canal, ''), COALESCE(fechaEnvio, ''), COALESCE(estado, '')
    INTO v_old_carnetSocio, v_old_tipoNotificacion, v_old_mensaje, v_old_canal, v_old_fechaEnvio, v_old_estado
    FROM TNotificaciones WHERE idNotificacion = p_idNotificacion;

    UPDATE TNotificaciones
    SET carnetSocio = p_carnetSocio,
        tipoNotificacion = p_tipoNotificacion,
        mensaje = p_mensaje,
        canal = p_canal,
        fechaEnvio = p_fechaEnvio,
        estado = p_estado
    WHERE idNotificacion = p_idNotificacion;

        IF v_old_carnetSocio <> p_carnetSocio OR (v_old_carnetSocio IS NULL AND p_carnetSocio IS NOT NULL) OR (v_old_carnetSocio IS NOT NULL AND p_carnetSocio IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'carnetSocio');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_carnetSocio, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_carnetSocio, ''));
    END IF;
    IF v_old_tipoNotificacion <> p_tipoNotificacion OR (v_old_tipoNotificacion IS NULL AND p_tipoNotificacion IS NOT NULL) OR (v_old_tipoNotificacion IS NOT NULL AND p_tipoNotificacion IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'tipoNotificacion');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_tipoNotificacion, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_tipoNotificacion, ''));
    END IF;
    IF v_old_mensaje <> p_mensaje OR (v_old_mensaje IS NULL AND p_mensaje IS NOT NULL) OR (v_old_mensaje IS NOT NULL AND p_mensaje IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'mensaje');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_mensaje, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_mensaje, ''));
    END IF;
    IF v_old_canal <> p_canal OR (v_old_canal IS NULL AND p_canal IS NOT NULL) OR (v_old_canal IS NOT NULL AND p_canal IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'canal');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_canal, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_canal, ''));
    END IF;
    IF v_old_fechaEnvio <> p_fechaEnvio OR (v_old_fechaEnvio IS NULL AND p_fechaEnvio IS NOT NULL) OR (v_old_fechaEnvio IS NOT NULL AND p_fechaEnvio IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaEnvio');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaEnvio, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaEnvio, ''));
    END IF;
    IF v_old_estado <> p_estado OR (v_old_estado IS NULL AND p_estado IS NOT NULL) OR (v_old_estado IS NOT NULL AND p_estado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estado, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TNotificaciones', p_idNotificacion, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPagoSueldos_Delete` (IN `p_idPagoSueldo` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TPagoSueldos
    SET estadoA = 0
    WHERE idPagoSueldo = p_idPagoSueldo;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TPagoSueldos', p_idPagoSueldo, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPagoSueldos_Insert` (IN `p_carnetEmpleado` VARCHAR(500), IN `p_fechaPago` VARCHAR(500), IN `p_monto` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TPagoSueldos (carnetEmpleado, fechaPago, monto, estadoA, usuarioA)
    VALUES (p_carnetEmpleado, p_fechaPago, p_monto, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TPagoSueldos', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_carnetEmpleado, ''), COALESCE(p_fechaPago, ''), COALESCE(p_monto, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPagoSueldos_Select` ()   BEGIN
    SELECT * FROM TPagoSueldos
    WHERE estadoA = 1
    ORDER BY idPagoSueldo DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPagoSueldos_SelectById` (IN `p_idPagoSueldo` INT)   BEGIN
    SELECT * FROM TPagoSueldos
    WHERE idPagoSueldo = p_idPagoSueldo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPagoSueldos_Update` (IN `p_idPagoSueldo` INT, IN `p_carnetEmpleado` VARCHAR(500), IN `p_fechaPago` VARCHAR(500), IN `p_monto` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_carnetEmpleado VARCHAR(500);
    DECLARE v_old_fechaPago VARCHAR(500);
    DECLARE v_old_monto VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(carnetEmpleado, ''), COALESCE(fechaPago, ''), COALESCE(monto, '')
    INTO v_old_carnetEmpleado, v_old_fechaPago, v_old_monto
    FROM TPagoSueldos WHERE idPagoSueldo = p_idPagoSueldo;

    UPDATE TPagoSueldos
    SET carnetEmpleado = p_carnetEmpleado,
        fechaPago = p_fechaPago,
        monto = p_monto
    WHERE idPagoSueldo = p_idPagoSueldo;

        IF v_old_carnetEmpleado <> p_carnetEmpleado OR (v_old_carnetEmpleado IS NULL AND p_carnetEmpleado IS NOT NULL) OR (v_old_carnetEmpleado IS NOT NULL AND p_carnetEmpleado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'carnetEmpleado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_carnetEmpleado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_carnetEmpleado, ''));
    END IF;
    IF v_old_fechaPago <> p_fechaPago OR (v_old_fechaPago IS NULL AND p_fechaPago IS NOT NULL) OR (v_old_fechaPago IS NOT NULL AND p_fechaPago IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaPago');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaPago, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaPago, ''));
    END IF;
    IF v_old_monto <> p_monto OR (v_old_monto IS NULL AND p_monto IS NOT NULL) OR (v_old_monto IS NOT NULL AND p_monto IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'monto');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_monto, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_monto, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TPagoSueldos', p_idPagoSueldo, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPenalizaciones_Delete` (IN `p_idPenalizacion` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TPenalizaciones
    SET estadoA = 0
    WHERE idPenalizacion = p_idPenalizacion;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TPenalizaciones', p_idPenalizacion, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPenalizaciones_Insert` (IN `p_carnetSocio` VARCHAR(500), IN `p_idReserva` VARCHAR(500), IN `p_fecha` VARCHAR(500), IN `p_estado` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TPenalizaciones (carnetSocio, idReserva, fecha, estado, estadoA, usuarioA)
    VALUES (p_carnetSocio, p_idReserva, p_fecha, p_estado, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TPenalizaciones', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_carnetSocio, ''), COALESCE(p_idReserva, ''), COALESCE(p_fecha, ''), COALESCE(p_estado, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPenalizaciones_Select` ()   BEGIN
    SELECT * FROM TPenalizaciones
    WHERE estadoA = 1
    ORDER BY idPenalizacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPenalizaciones_SelectById` (IN `p_idPenalizacion` INT)   BEGIN
    SELECT * FROM TPenalizaciones
    WHERE idPenalizacion = p_idPenalizacion;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPenalizaciones_Update` (IN `p_idPenalizacion` INT, IN `p_carnetSocio` VARCHAR(500), IN `p_idReserva` VARCHAR(500), IN `p_fecha` VARCHAR(500), IN `p_estado` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_carnetSocio VARCHAR(500);
    DECLARE v_old_idReserva VARCHAR(500);
    DECLARE v_old_fecha VARCHAR(500);
    DECLARE v_old_estado VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(carnetSocio, ''), COALESCE(idReserva, ''), COALESCE(fecha, ''), COALESCE(estado, '')
    INTO v_old_carnetSocio, v_old_idReserva, v_old_fecha, v_old_estado
    FROM TPenalizaciones WHERE idPenalizacion = p_idPenalizacion;

    UPDATE TPenalizaciones
    SET carnetSocio = p_carnetSocio,
        idReserva = p_idReserva,
        fecha = p_fecha,
        estado = p_estado
    WHERE idPenalizacion = p_idPenalizacion;

        IF v_old_carnetSocio <> p_carnetSocio OR (v_old_carnetSocio IS NULL AND p_carnetSocio IS NOT NULL) OR (v_old_carnetSocio IS NOT NULL AND p_carnetSocio IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'carnetSocio');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_carnetSocio, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_carnetSocio, ''));
    END IF;
    IF v_old_idReserva <> p_idReserva OR (v_old_idReserva IS NULL AND p_idReserva IS NOT NULL) OR (v_old_idReserva IS NOT NULL AND p_idReserva IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idReserva');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idReserva, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idReserva, ''));
    END IF;
    IF v_old_fecha <> p_fecha OR (v_old_fecha IS NULL AND p_fecha IS NOT NULL) OR (v_old_fecha IS NOT NULL AND p_fecha IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fecha');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fecha, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fecha, ''));
    END IF;
    IF v_old_estado <> p_estado OR (v_old_estado IS NULL AND p_estado IS NOT NULL) OR (v_old_estado IS NOT NULL AND p_estado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estado, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TPenalizaciones', p_idPenalizacion, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPlanes_Delete` (IN `p_idPlan` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TPlanes
    SET estadoA = 0
    WHERE idPlan = p_idPlan;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TPlanes', p_idPlan, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPlanes_Insert` (IN `p_nombrePlan` VARCHAR(500), IN `p_descripcion` VARCHAR(500), IN `p_costoPlan` VARCHAR(500), IN `p_duracionDias` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TPlanes (nombrePlan, descripcion, costoPlan, duracionDias, estadoA, usuarioA)
    VALUES (p_nombrePlan, p_descripcion, p_costoPlan, p_duracionDias, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TPlanes', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_nombrePlan, ''), COALESCE(p_descripcion, ''), COALESCE(p_costoPlan, ''), COALESCE(p_duracionDias, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPlanes_Select` ()   BEGIN
    SELECT * FROM TPlanes
    WHERE estadoA = 1
    ORDER BY idPlan DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPlanes_SelectById` (IN `p_idPlan` INT)   BEGIN
    SELECT * FROM TPlanes
    WHERE idPlan = p_idPlan;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TPlanes_Update` (IN `p_idPlan` INT, IN `p_nombrePlan` VARCHAR(500), IN `p_descripcion` VARCHAR(500), IN `p_costoPlan` VARCHAR(500), IN `p_duracionDias` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_nombrePlan VARCHAR(500);
    DECLARE v_old_descripcion VARCHAR(500);
    DECLARE v_old_costoPlan VARCHAR(500);
    DECLARE v_old_duracionDias VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(nombrePlan, ''), COALESCE(descripcion, ''), COALESCE(costoPlan, ''), COALESCE(duracionDias, '')
    INTO v_old_nombrePlan, v_old_descripcion, v_old_costoPlan, v_old_duracionDias
    FROM TPlanes WHERE idPlan = p_idPlan;

    UPDATE TPlanes
    SET nombrePlan = p_nombrePlan,
        descripcion = p_descripcion,
        costoPlan = p_costoPlan,
        duracionDias = p_duracionDias
    WHERE idPlan = p_idPlan;

        IF v_old_nombrePlan <> p_nombrePlan OR (v_old_nombrePlan IS NULL AND p_nombrePlan IS NOT NULL) OR (v_old_nombrePlan IS NOT NULL AND p_nombrePlan IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'nombrePlan');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_nombrePlan, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_nombrePlan, ''));
    END IF;
    IF v_old_descripcion <> p_descripcion OR (v_old_descripcion IS NULL AND p_descripcion IS NOT NULL) OR (v_old_descripcion IS NOT NULL AND p_descripcion IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'descripcion');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_descripcion, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_descripcion, ''));
    END IF;
    IF v_old_costoPlan <> p_costoPlan OR (v_old_costoPlan IS NULL AND p_costoPlan IS NOT NULL) OR (v_old_costoPlan IS NOT NULL AND p_costoPlan IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'costoPlan');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_costoPlan, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_costoPlan, ''));
    END IF;
    IF v_old_duracionDias <> p_duracionDias OR (v_old_duracionDias IS NULL AND p_duracionDias IS NOT NULL) OR (v_old_duracionDias IS NOT NULL AND p_duracionDias IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'duracionDias');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_duracionDias, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_duracionDias, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TPlanes', p_idPlan, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TRecibos_Delete` (IN `p_idRecibo` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TRecibos
    SET estadoA = 0
    WHERE idRecibo = p_idRecibo;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TRecibos', p_idRecibo, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TRecibos_GetReporteFinanciero` (IN `p_fecha_desde` DATE, IN `p_fecha_hasta` DATE, IN `p_idSucursal` INT, IN `p_idMetodoPago` INT, IN `p_carnetEmpleado` INT)   BEGIN
    SELECT r.idRecibo, r.nroRecibo, r.montoTotal, r.fechaPago,
           r.estadoRecibo,
           c.idSucursal, c.carnetEmpleado,
           s.nombre AS sucursal,
           u.nombre1, u.apellido1,
           mp.nombreMetodoPago, dmp.monto AS montoMetodo,
           m.carnetSocio
    FROM TRecibos r
    INNER JOIN TCajas c ON c.idCaja = r.idCaja
    INNER JOIN TSucursales s ON s.idSucursal = c.idSucursal
    INNER JOIN TDetalleMetodoPagos dmp ON dmp.idRecibo = r.idRecibo
    INNER JOIN TMetodoPagos mp ON mp.idMetodoPago = dmp.idMetodoPagoFK
    INNER JOIN TMembresias m ON m.idMembresia = r.idMembresia
    LEFT JOIN TUsuarios u ON u.idUsuario = c.carnetEmpleado
    WHERE r.estadoA = 1
      AND (p_fecha_desde IS NULL OR r.fechaPago >= p_fecha_desde)
      AND (p_fecha_hasta IS NULL OR r.fechaPago < DATE_ADD(p_fecha_hasta, INTERVAL 1 DAY))
      AND (p_idSucursal IS NULL OR c.idSucursal = p_idSucursal)
      AND (p_idMetodoPago IS NULL OR dmp.idMetodoPagoFK = p_idMetodoPago)
      AND (p_carnetEmpleado IS NULL OR c.carnetEmpleado = p_carnetEmpleado)
    ORDER BY r.fechaPago DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TRecibos_Insert` (IN `p_idCaja` VARCHAR(500), IN `p_idMembresia` VARCHAR(500), IN `p_nroRecibo` VARCHAR(500), IN `p_montoTotal` VARCHAR(500), IN `p_fechaPago` VARCHAR(500), IN `p_estadoRecibo` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TRecibos (idCaja, idMembresia, nroRecibo, montoTotal, fechaPago, estadoRecibo, estadoA, usuarioA)
    VALUES (p_idCaja, p_idMembresia, p_nroRecibo, p_montoTotal, p_fechaPago, p_estadoRecibo, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TRecibos', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_idCaja, ''), COALESCE(p_idMembresia, ''), COALESCE(p_nroRecibo, ''), COALESCE(p_montoTotal, ''), COALESCE(p_fechaPago, ''), COALESCE(p_estadoRecibo, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TRecibos_Select` ()   BEGIN
    SELECT * FROM TRecibos
    WHERE estadoA = 1
    ORDER BY idRecibo DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TRecibos_SelectById` (IN `p_idRecibo` INT)   BEGIN
    SELECT * FROM TRecibos
    WHERE idRecibo = p_idRecibo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TRecibos_Update` (IN `p_idRecibo` INT, IN `p_idCaja` VARCHAR(500), IN `p_idMembresia` VARCHAR(500), IN `p_nroRecibo` VARCHAR(500), IN `p_montoTotal` VARCHAR(500), IN `p_fechaPago` VARCHAR(500), IN `p_estadoRecibo` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_idCaja VARCHAR(500);
    DECLARE v_old_idMembresia VARCHAR(500);
    DECLARE v_old_nroRecibo VARCHAR(500);
    DECLARE v_old_montoTotal VARCHAR(500);
    DECLARE v_old_fechaPago VARCHAR(500);
    DECLARE v_old_estadoRecibo VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(idCaja, ''), COALESCE(idMembresia, ''), COALESCE(nroRecibo, ''), COALESCE(montoTotal, ''), COALESCE(fechaPago, ''), COALESCE(estadoRecibo, '')
    INTO v_old_idCaja, v_old_idMembresia, v_old_nroRecibo, v_old_montoTotal, v_old_fechaPago, v_old_estadoRecibo
    FROM TRecibos WHERE idRecibo = p_idRecibo;

    UPDATE TRecibos
    SET idCaja = p_idCaja,
        idMembresia = p_idMembresia,
        nroRecibo = p_nroRecibo,
        montoTotal = p_montoTotal,
        fechaPago = p_fechaPago,
        estadoRecibo = p_estadoRecibo
    WHERE idRecibo = p_idRecibo;

        IF v_old_idCaja <> p_idCaja OR (v_old_idCaja IS NULL AND p_idCaja IS NOT NULL) OR (v_old_idCaja IS NOT NULL AND p_idCaja IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idCaja');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idCaja, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idCaja, ''));
    END IF;
    IF v_old_idMembresia <> p_idMembresia OR (v_old_idMembresia IS NULL AND p_idMembresia IS NOT NULL) OR (v_old_idMembresia IS NOT NULL AND p_idMembresia IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idMembresia');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idMembresia, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idMembresia, ''));
    END IF;
    IF v_old_nroRecibo <> p_nroRecibo OR (v_old_nroRecibo IS NULL AND p_nroRecibo IS NOT NULL) OR (v_old_nroRecibo IS NOT NULL AND p_nroRecibo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'nroRecibo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_nroRecibo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_nroRecibo, ''));
    END IF;
    IF v_old_montoTotal <> p_montoTotal OR (v_old_montoTotal IS NULL AND p_montoTotal IS NOT NULL) OR (v_old_montoTotal IS NOT NULL AND p_montoTotal IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'montoTotal');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_montoTotal, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_montoTotal, ''));
    END IF;
    IF v_old_fechaPago <> p_fechaPago OR (v_old_fechaPago IS NULL AND p_fechaPago IS NOT NULL) OR (v_old_fechaPago IS NOT NULL AND p_fechaPago IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaPago');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaPago, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaPago, ''));
    END IF;
    IF v_old_estadoRecibo <> p_estadoRecibo OR (v_old_estadoRecibo IS NULL AND p_estadoRecibo IS NOT NULL) OR (v_old_estadoRecibo IS NOT NULL AND p_estadoRecibo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estadoRecibo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estadoRecibo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estadoRecibo, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TRecibos', p_idRecibo, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReporteFallas_Delete` (IN `p_idReporteFalla` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TReporteFallas
    SET estadoA = 0
    WHERE idReporteFalla = p_idReporteFalla;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TReporteFallas', p_idReporteFalla, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReporteFallas_GetHistorial` (IN `p_limit` INT)   BEGIN
    SELECT rf.*, e.nombreEquipo, e.estadoEquipo
    FROM TReporteFallas rf
    INNER JOIN TEquipamientos e ON e.idEquipo = rf.idEquipo
    WHERE rf.estadoA = 1
    ORDER BY rf.fechaReporte DESC
    LIMIT p_limit;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReporteFallas_Insert` (IN `p_idEquipo` VARCHAR(500), IN `p_carnetEmpleado` VARCHAR(500), IN `p_fechaReporte` VARCHAR(500), IN `p_descripcionFalla` VARCHAR(500), IN `p_gravedad` VARCHAR(500), IN `p_estadoReporte` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TReporteFallas (idEquipo, carnetEmpleado, fechaReporte, descripcionFalla, gravedad, estadoReporte, estadoA, usuarioA)
    VALUES (p_idEquipo, p_carnetEmpleado, p_fechaReporte, p_descripcionFalla, p_gravedad, p_estadoReporte, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TReporteFallas', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_idEquipo, ''), COALESCE(p_carnetEmpleado, ''), COALESCE(p_fechaReporte, ''), COALESCE(p_descripcionFalla, ''), COALESCE(p_gravedad, ''), COALESCE(p_estadoReporte, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReporteFallas_Select` ()   BEGIN
    SELECT * FROM TReporteFallas
    WHERE estadoA = 1
    ORDER BY idReporteFalla DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReporteFallas_SelectById` (IN `p_idReporteFalla` INT)   BEGIN
    SELECT * FROM TReporteFallas
    WHERE idReporteFalla = p_idReporteFalla;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReporteFallas_Update` (IN `p_idReporteFalla` INT, IN `p_idEquipo` VARCHAR(500), IN `p_carnetEmpleado` VARCHAR(500), IN `p_fechaReporte` VARCHAR(500), IN `p_descripcionFalla` VARCHAR(500), IN `p_gravedad` VARCHAR(500), IN `p_estadoReporte` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_idEquipo VARCHAR(500);
    DECLARE v_old_carnetEmpleado VARCHAR(500);
    DECLARE v_old_fechaReporte VARCHAR(500);
    DECLARE v_old_descripcionFalla VARCHAR(500);
    DECLARE v_old_gravedad VARCHAR(500);
    DECLARE v_old_estadoReporte VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(idEquipo, ''), COALESCE(carnetEmpleado, ''), COALESCE(fechaReporte, ''), COALESCE(descripcionFalla, ''), COALESCE(gravedad, ''), COALESCE(estadoReporte, '')
    INTO v_old_idEquipo, v_old_carnetEmpleado, v_old_fechaReporte, v_old_descripcionFalla, v_old_gravedad, v_old_estadoReporte
    FROM TReporteFallas WHERE idReporteFalla = p_idReporteFalla;

    UPDATE TReporteFallas
    SET idEquipo = p_idEquipo,
        carnetEmpleado = p_carnetEmpleado,
        fechaReporte = p_fechaReporte,
        descripcionFalla = p_descripcionFalla,
        gravedad = p_gravedad,
        estadoReporte = p_estadoReporte
    WHERE idReporteFalla = p_idReporteFalla;

        IF v_old_idEquipo <> p_idEquipo OR (v_old_idEquipo IS NULL AND p_idEquipo IS NOT NULL) OR (v_old_idEquipo IS NOT NULL AND p_idEquipo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idEquipo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idEquipo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idEquipo, ''));
    END IF;
    IF v_old_carnetEmpleado <> p_carnetEmpleado OR (v_old_carnetEmpleado IS NULL AND p_carnetEmpleado IS NOT NULL) OR (v_old_carnetEmpleado IS NOT NULL AND p_carnetEmpleado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'carnetEmpleado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_carnetEmpleado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_carnetEmpleado, ''));
    END IF;
    IF v_old_fechaReporte <> p_fechaReporte OR (v_old_fechaReporte IS NULL AND p_fechaReporte IS NOT NULL) OR (v_old_fechaReporte IS NOT NULL AND p_fechaReporte IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaReporte');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaReporte, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaReporte, ''));
    END IF;
    IF v_old_descripcionFalla <> p_descripcionFalla OR (v_old_descripcionFalla IS NULL AND p_descripcionFalla IS NOT NULL) OR (v_old_descripcionFalla IS NOT NULL AND p_descripcionFalla IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'descripcionFalla');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_descripcionFalla, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_descripcionFalla, ''));
    END IF;
    IF v_old_gravedad <> p_gravedad OR (v_old_gravedad IS NULL AND p_gravedad IS NOT NULL) OR (v_old_gravedad IS NOT NULL AND p_gravedad IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'gravedad');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_gravedad, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_gravedad, ''));
    END IF;
    IF v_old_estadoReporte <> p_estadoReporte OR (v_old_estadoReporte IS NULL AND p_estadoReporte IS NOT NULL) OR (v_old_estadoReporte IS NOT NULL AND p_estadoReporte IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estadoReporte');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estadoReporte, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estadoReporte, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TReporteFallas', p_idReporteFalla, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReservas_Cancelar_Validated` (IN `p_idReserva` INT, IN `p_carnetSocio` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReservas_Delete` (IN `p_idReserva` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TReservas
    SET estadoA = 0
    WHERE idReserva = p_idReserva;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TReservas', p_idReserva, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReservas_GetBySocio` (IN `p_carnetSocio` INT)   BEGIN
    SELECT r.*, cg.fecha, cg.horaInicio, cg.horaFin, a.nombreActividad,
           s.nombre AS sucursalNombre,
           CONCAT(u.nombre1, ' ', u.apellido1) AS entrenador
    FROM TReservas r
    INNER JOIN TClaseGrupales cg ON cg.idClaseGrupal = r.idClaseGrupal
    INNER JOIN TActividades a ON a.idActividad = cg.idActividad
    INNER JOIN TSucursales s ON s.idSucursal = cg.idSucursal
    INNER JOIN TEmpleados e ON e.carnetEmpleado = cg.carnetEmpleado
    INNER JOIN TUsuarios u ON u.idUsuario = e.idUsuario
    WHERE r.carnetSocio = p_carnetSocio
      AND r.estadoA = 1
    ORDER BY cg.fecha DESC, cg.horaInicio DESC
    LIMIT 10;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReservas_Insert` (IN `p_idClaseGrupal` VARCHAR(500), IN `p_carnetSocio` VARCHAR(500), IN `p_fechaReserva` VARCHAR(500), IN `p_estadoReserva` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TReservas (idClaseGrupal, carnetSocio, fechaReserva, estadoReserva, estadoA, usuarioA)
    VALUES (p_idClaseGrupal, p_carnetSocio, p_fechaReserva, p_estadoReserva, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TReservas', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_idClaseGrupal, ''), COALESCE(p_carnetSocio, ''), COALESCE(p_fechaReserva, ''), COALESCE(p_estadoReserva, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReservas_MarcarAsistencia_Entrenador` (IN `p_idReserva` INT, IN `p_nuevoEstado` VARCHAR(20), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
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

    -- Obtener datos de la reserva y la clase vinculada
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

    -- Validar que la reserva esté en estado 'Reservado'
    IF v_estadoActual != 'Reservado' THEN
        SET v_mensajeError = CONCAT('La reserva ya fue ', v_estadoActual, '. No se puede modificar.');
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = v_mensajeError;
    END IF;

    -- Validación estricta de tiempo: la clase debe ser HOY
    IF v_fechaClase != v_fechaHoy THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La clase no es del día de hoy. No se puede tomar asistencia.';
    END IF;

    -- Validar ventana horaria permitida
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReservas_MarcarAsistencia_Integrado` (IN `p_idReserva` INT, IN `p_carnetSocio` INT, IN `p_idSucursal` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReservas_Select` ()   BEGIN
    SELECT * FROM TReservas
    WHERE estadoA = 1
    ORDER BY idReserva DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReservas_SelectById` (IN `p_idReserva` INT)   BEGIN
    SELECT * FROM TReservas
    WHERE idReserva = p_idReserva;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TReservas_Update` (IN `p_idReserva` INT, IN `p_idClaseGrupal` VARCHAR(500), IN `p_carnetSocio` VARCHAR(500), IN `p_fechaReserva` VARCHAR(500), IN `p_estadoReserva` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_idClaseGrupal VARCHAR(500);
    DECLARE v_old_carnetSocio VARCHAR(500);
    DECLARE v_old_fechaReserva VARCHAR(500);
    DECLARE v_old_estadoReserva VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(idClaseGrupal, ''), COALESCE(carnetSocio, ''), COALESCE(fechaReserva, ''), COALESCE(estadoReserva, '')
    INTO v_old_idClaseGrupal, v_old_carnetSocio, v_old_fechaReserva, v_old_estadoReserva
    FROM TReservas WHERE idReserva = p_idReserva;

    UPDATE TReservas
    SET idClaseGrupal = p_idClaseGrupal,
        carnetSocio = p_carnetSocio,
        fechaReserva = p_fechaReserva,
        estadoReserva = p_estadoReserva
    WHERE idReserva = p_idReserva;

        IF v_old_idClaseGrupal <> p_idClaseGrupal OR (v_old_idClaseGrupal IS NULL AND p_idClaseGrupal IS NOT NULL) OR (v_old_idClaseGrupal IS NOT NULL AND p_idClaseGrupal IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idClaseGrupal');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idClaseGrupal, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idClaseGrupal, ''));
    END IF;
    IF v_old_carnetSocio <> p_carnetSocio OR (v_old_carnetSocio IS NULL AND p_carnetSocio IS NOT NULL) OR (v_old_carnetSocio IS NOT NULL AND p_carnetSocio IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'carnetSocio');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_carnetSocio, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_carnetSocio, ''));
    END IF;
    IF v_old_fechaReserva <> p_fechaReserva OR (v_old_fechaReserva IS NULL AND p_fechaReserva IS NOT NULL) OR (v_old_fechaReserva IS NOT NULL AND p_fechaReserva IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fechaReserva');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fechaReserva, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fechaReserva, ''));
    END IF;
    IF v_old_estadoReserva <> p_estadoReserva OR (v_old_estadoReserva IS NULL AND p_estadoReserva IS NOT NULL) OR (v_old_estadoReserva IS NOT NULL AND p_estadoReserva IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estadoReserva');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estadoReserva, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estadoReserva, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TReservas', p_idReserva, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TRoles_Delete` (IN `p_idRol` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TRoles
    SET estadoA = 0
    WHERE idRol = p_idRol;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TRoles', p_idRol, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TRoles_Insert` (IN `p_nombreRol` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TRoles (nombreRol, estadoA, usuarioA)
    VALUES (p_nombreRol, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TRoles', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_nombreRol, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TRoles_Select` ()   BEGIN
    SELECT * FROM TRoles
    WHERE estadoA = 1
    ORDER BY idRol DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TRoles_SelectById` (IN `p_idRol` INT)   BEGIN
    SELECT * FROM TRoles
    WHERE idRol = p_idRol;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TRoles_Update` (IN `p_idRol` INT, IN `p_nombreRol` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_nombreRol VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(nombreRol, '')
    INTO v_old_nombreRol
    FROM TRoles WHERE idRol = p_idRol;

    UPDATE TRoles
    SET nombreRol = p_nombreRol
    WHERE idRol = p_idRol;

        IF v_old_nombreRol <> p_nombreRol OR (v_old_nombreRol IS NULL AND p_nombreRol IS NOT NULL) OR (v_old_nombreRol IS NOT NULL AND p_nombreRol IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'nombreRol');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_nombreRol, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_nombreRol, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TRoles', p_idRol, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSocios_Bloquear` (IN `p_carnetSocio` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSocios_Delete` (IN `p_carnetSocio` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TSocios
    SET estadoA = 0
    WHERE carnetSocio = p_carnetSocio;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TSocios', p_carnetSocio, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSocios_GetAllWithUsers` ()   BEGIN
    SELECT s.carnetSocio, s.idUsuario, s.direccion,
           s.nombreContactoEmergencia, s.telefonoContactoEmergencia,
           s.estadoSocio,
           u.nombre1, u.apellido1, u.correo, u.telefono
    FROM TSocios s
    INNER JOIN TUsuarios u ON s.idUsuario = u.idUsuario
    WHERE s.estadoA = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSocios_GetByUserId` (IN `p_idUsuario` INT)   BEGIN
    SELECT s.*, u.nombre1, u.nombre2, u.apellido1, u.apellido2, u.correo, u.telefono
    FROM TSocios s
    INNER JOIN TUsuarios u ON u.idUsuario = s.idUsuario
    WHERE u.idUsuario = p_idUsuario
      AND s.estadoA = 1
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSocios_Insert` (IN `p_carnetSocio` VARCHAR(500), IN `p_idUsuario` VARCHAR(500), IN `p_direccion` VARCHAR(500), IN `p_fotografiaUrl` VARCHAR(500), IN `p_nombreContactoEmergencia` VARCHAR(500), IN `p_telefonoContactoEmergencia` VARCHAR(500), IN `p_observacionesMedicas` VARCHAR(500), IN `p_estadoSocio` VARCHAR(500), IN `p_strikes` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TSocios (idUsuario, direccion, fotografiaUrl, nombreContactoEmergencia, telefonoContactoEmergencia, observacionesMedicas, estadoSocio, strikes, estadoA, usuarioA)
    VALUES (p_idUsuario, p_direccion, p_fotografiaUrl, p_nombreContactoEmergencia, p_telefonoContactoEmergencia, p_observacionesMedicas, p_estadoSocio, p_strikes, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TSocios', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_idUsuario, ''), COALESCE(p_direccion, ''), COALESCE(p_fotografiaUrl, ''), COALESCE(p_nombreContactoEmergencia, ''), COALESCE(p_telefonoContactoEmergencia, ''), COALESCE(p_observacionesMedicas, ''), COALESCE(p_estadoSocio, ''), COALESCE(p_strikes, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSocios_Select` ()   BEGIN
    SELECT * FROM TSocios
    WHERE estadoA = 1
    ORDER BY carnetSocio DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSocios_SelectById` (IN `p_carnetSocio` INT)   BEGIN
    SELECT * FROM TSocios
    WHERE carnetSocio = p_carnetSocio;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSocios_Update` (IN `p_carnetSocio` INT, IN `p_idUsuario` VARCHAR(500), IN `p_direccion` VARCHAR(500), IN `p_fotografiaUrl` VARCHAR(500), IN `p_nombreContactoEmergencia` VARCHAR(500), IN `p_telefonoContactoEmergencia` VARCHAR(500), IN `p_observacionesMedicas` VARCHAR(500), IN `p_estadoSocio` VARCHAR(500), IN `p_strikes` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_idUsuario VARCHAR(500);
    DECLARE v_old_direccion VARCHAR(500);
    DECLARE v_old_fotografiaUrl VARCHAR(500);
    DECLARE v_old_nombreContactoEmergencia VARCHAR(500);
    DECLARE v_old_telefonoContactoEmergencia VARCHAR(500);
    DECLARE v_old_observacionesMedicas VARCHAR(500);
    DECLARE v_old_estadoSocio VARCHAR(500);
    DECLARE v_old_strikes VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(idUsuario, ''), COALESCE(direccion, ''), COALESCE(fotografiaUrl, ''), COALESCE(nombreContactoEmergencia, ''), COALESCE(telefonoContactoEmergencia, ''), COALESCE(observacionesMedicas, ''), COALESCE(estadoSocio, ''), COALESCE(strikes, '')
    INTO v_old_idUsuario, v_old_direccion, v_old_fotografiaUrl, v_old_nombreContactoEmergencia, v_old_telefonoContactoEmergencia, v_old_observacionesMedicas, v_old_estadoSocio, v_old_strikes
    FROM TSocios WHERE carnetSocio = p_carnetSocio;

    UPDATE TSocios
    SET idUsuario = p_idUsuario,
        direccion = p_direccion,
        fotografiaUrl = p_fotografiaUrl,
        nombreContactoEmergencia = p_nombreContactoEmergencia,
        telefonoContactoEmergencia = p_telefonoContactoEmergencia,
        observacionesMedicas = p_observacionesMedicas,
        estadoSocio = p_estadoSocio,
        strikes = p_strikes
    WHERE carnetSocio = p_carnetSocio;

        IF v_old_idUsuario <> p_idUsuario OR (v_old_idUsuario IS NULL AND p_idUsuario IS NOT NULL) OR (v_old_idUsuario IS NOT NULL AND p_idUsuario IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idUsuario');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idUsuario, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idUsuario, ''));
    END IF;
    IF v_old_direccion <> p_direccion OR (v_old_direccion IS NULL AND p_direccion IS NOT NULL) OR (v_old_direccion IS NOT NULL AND p_direccion IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'direccion');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_direccion, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_direccion, ''));
    END IF;
    IF v_old_fotografiaUrl <> p_fotografiaUrl OR (v_old_fotografiaUrl IS NULL AND p_fotografiaUrl IS NOT NULL) OR (v_old_fotografiaUrl IS NOT NULL AND p_fotografiaUrl IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'fotografiaUrl');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_fotografiaUrl, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_fotografiaUrl, ''));
    END IF;
    IF v_old_nombreContactoEmergencia <> p_nombreContactoEmergencia OR (v_old_nombreContactoEmergencia IS NULL AND p_nombreContactoEmergencia IS NOT NULL) OR (v_old_nombreContactoEmergencia IS NOT NULL AND p_nombreContactoEmergencia IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'nombreContactoEmergencia');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_nombreContactoEmergencia, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_nombreContactoEmergencia, ''));
    END IF;
    IF v_old_telefonoContactoEmergencia <> p_telefonoContactoEmergencia OR (v_old_telefonoContactoEmergencia IS NULL AND p_telefonoContactoEmergencia IS NOT NULL) OR (v_old_telefonoContactoEmergencia IS NOT NULL AND p_telefonoContactoEmergencia IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'telefonoContactoEmergencia');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_telefonoContactoEmergencia, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_telefonoContactoEmergencia, ''));
    END IF;
    IF v_old_observacionesMedicas <> p_observacionesMedicas OR (v_old_observacionesMedicas IS NULL AND p_observacionesMedicas IS NOT NULL) OR (v_old_observacionesMedicas IS NOT NULL AND p_observacionesMedicas IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'observacionesMedicas');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_observacionesMedicas, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_observacionesMedicas, ''));
    END IF;
    IF v_old_estadoSocio <> p_estadoSocio OR (v_old_estadoSocio IS NULL AND p_estadoSocio IS NOT NULL) OR (v_old_estadoSocio IS NOT NULL AND p_estadoSocio IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estadoSocio');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estadoSocio, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estadoSocio, ''));
    END IF;
    IF v_old_strikes <> p_strikes OR (v_old_strikes IS NULL AND p_strikes IS NOT NULL) OR (v_old_strikes IS NOT NULL AND p_strikes IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'strikes');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_strikes, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_strikes, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TSocios', p_carnetSocio, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSucursales_Delete` (IN `p_idSucursal` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TSucursales
    SET estadoA = 0
    WHERE idSucursal = p_idSucursal;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TSucursales', p_idSucursal, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSucursales_Insert` (IN `p_nombre` VARCHAR(500), IN `p_direccion` VARCHAR(500), IN `p_telefono` VARCHAR(500), IN `p_estado` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TSucursales (nombre, direccion, telefono, estado, estadoA, usuarioA)
    VALUES (p_nombre, p_direccion, p_telefono, p_estado, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TSucursales', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_nombre, ''), COALESCE(p_direccion, ''), COALESCE(p_telefono, ''), COALESCE(p_estado, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSucursales_Select` ()   BEGIN
    SELECT * FROM TSucursales
    WHERE estadoA = 1
    ORDER BY idSucursal DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSucursales_SelectById` (IN `p_idSucursal` INT)   BEGIN
    SELECT * FROM TSucursales
    WHERE idSucursal = p_idSucursal;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TSucursales_Update` (IN `p_idSucursal` INT, IN `p_nombre` VARCHAR(500), IN `p_direccion` VARCHAR(500), IN `p_telefono` VARCHAR(500), IN `p_estado` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_nombre VARCHAR(500);
    DECLARE v_old_direccion VARCHAR(500);
    DECLARE v_old_telefono VARCHAR(500);
    DECLARE v_old_estado VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(nombre, ''), COALESCE(direccion, ''), COALESCE(telefono, ''), COALESCE(estado, '')
    INTO v_old_nombre, v_old_direccion, v_old_telefono, v_old_estado
    FROM TSucursales WHERE idSucursal = p_idSucursal;

    UPDATE TSucursales
    SET nombre = p_nombre,
        direccion = p_direccion,
        telefono = p_telefono,
        estado = p_estado
    WHERE idSucursal = p_idSucursal;

        IF v_old_nombre <> p_nombre OR (v_old_nombre IS NULL AND p_nombre IS NOT NULL) OR (v_old_nombre IS NOT NULL AND p_nombre IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'nombre');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_nombre, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_nombre, ''));
    END IF;
    IF v_old_direccion <> p_direccion OR (v_old_direccion IS NULL AND p_direccion IS NOT NULL) OR (v_old_direccion IS NOT NULL AND p_direccion IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'direccion');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_direccion, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_direccion, ''));
    END IF;
    IF v_old_telefono <> p_telefono OR (v_old_telefono IS NULL AND p_telefono IS NOT NULL) OR (v_old_telefono IS NOT NULL AND p_telefono IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'telefono');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_telefono, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_telefono, ''));
    END IF;
    IF v_old_estado <> p_estado OR (v_old_estado IS NULL AND p_estado IS NOT NULL) OR (v_old_estado IS NOT NULL AND p_estado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estado, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TSucursales', p_idSucursal, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TUsuarios_Delete` (IN `p_idUsuario` INT, IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    UPDATE TUsuarios
    SET estadoA = 0
    WHERE idUsuario = p_idUsuario;

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TUsuarios', p_idUsuario, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', p_usuarioA, NOW(), p_direccionIP, 'Eliminado (desactivado) registro');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TUsuarios_FindByEmail` (IN `p_correo` VARCHAR(150))   BEGIN
    SELECT u.*, r.nombreRol
    FROM TUsuarios u
    INNER JOIN TRoles r ON r.idRol = u.idRol
    WHERE u.correo = p_correo COLLATE utf8mb4_unicode_ci
      AND u.estado = 1
      AND u.estadoA = 1
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TUsuarios_GetCajeros` ()   BEGIN
    SELECT DISTINCT u.idUsuario, u.nombre1, u.apellido1
    FROM TUsuarios u
    INNER JOIN TCajas c ON c.carnetEmpleado = u.idUsuario
    WHERE u.estadoA = 1
    ORDER BY u.nombre1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TUsuarios_Insert` (IN `p_idRol` VARCHAR(500), IN `p_nombre1` VARCHAR(500), IN `p_nombre2` VARCHAR(500), IN `p_apellido1` VARCHAR(500), IN `p_apellido2` VARCHAR(500), IN `p_correo` VARCHAR(500), IN `p_telefono` VARCHAR(500), IN `p_contrasena` VARCHAR(500), IN `p_estado` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_newId INT;

    INSERT INTO TUsuarios (idRol, nombre1, nombre2, apellido1, apellido2, correo, telefono, contrasena, estado, estadoA, usuarioA)
    VALUES (p_idRol, p_nombre1, p_nombre2, p_apellido1, p_apellido2, p_correo, p_telefono, p_contrasena, p_estado, 1, p_usuarioA);

    SET v_newId = LAST_INSERT_ID();

    INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
    VALUES ('TUsuarios', v_newId, 'I', 'insercion', NULL, CONCAT_WS('|', COALESCE(p_idRol, ''), COALESCE(p_nombre1, ''), COALESCE(p_nombre2, ''), COALESCE(p_apellido1, ''), COALESCE(p_apellido2, ''), COALESCE(p_correo, ''), COALESCE(p_telefono, ''), COALESCE(p_contrasena, ''), COALESCE(p_estado, '')), p_usuarioA, NOW(), p_direccionIP, 'Insercion nueva');

    SELECT v_newId AS id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TUsuarios_Login` (IN `p_correo` VARCHAR(150), IN `p_contrasena` VARCHAR(255))   BEGIN
    SELECT u.*, r.nombreRol
    FROM TUsuarios u
    INNER JOIN TRoles r ON r.idRol = u.idRol
    WHERE u.correo = p_correo COLLATE utf8mb4_unicode_ci
      AND u.contrasena = p_contrasena
      AND u.estado = 1
      AND u.estadoA = 1
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TUsuarios_Select` ()   BEGIN
    SELECT * FROM TUsuarios
    WHERE estadoA = 1
    ORDER BY idUsuario DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TUsuarios_SelectById` (IN `p_idUsuario` INT)   BEGIN
    SELECT * FROM TUsuarios
    WHERE idUsuario = p_idUsuario;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_TUsuarios_Update` (IN `p_idUsuario` INT, IN `p_idRol` VARCHAR(500), IN `p_nombre1` VARCHAR(500), IN `p_nombre2` VARCHAR(500), IN `p_apellido1` VARCHAR(500), IN `p_apellido2` VARCHAR(500), IN `p_correo` VARCHAR(500), IN `p_telefono` VARCHAR(500), IN `p_contrasena` VARCHAR(500), IN `p_estado` VARCHAR(500), IN `p_usuarioA` INT, IN `p_direccionIP` VARCHAR(50))   BEGIN
    DECLARE v_old_idRol VARCHAR(500);
    DECLARE v_old_nombre1 VARCHAR(500);
    DECLARE v_old_nombre2 VARCHAR(500);
    DECLARE v_old_apellido1 VARCHAR(500);
    DECLARE v_old_apellido2 VARCHAR(500);
    DECLARE v_old_correo VARCHAR(500);
    DECLARE v_old_telefono VARCHAR(500);
    DECLARE v_old_contrasena VARCHAR(500);
    DECLARE v_old_estado VARCHAR(500);
    DECLARE v_campo TEXT DEFAULT NULL;
    DECLARE v_viejo TEXT DEFAULT NULL;
    DECLARE v_nuevo TEXT DEFAULT NULL;

    SELECT COALESCE(idRol, ''), COALESCE(nombre1, ''), COALESCE(nombre2, ''), COALESCE(apellido1, ''), COALESCE(apellido2, ''), COALESCE(correo, ''), COALESCE(telefono, ''), COALESCE(contrasena, ''), COALESCE(estado, '')
    INTO v_old_idRol, v_old_nombre1, v_old_nombre2, v_old_apellido1, v_old_apellido2, v_old_correo, v_old_telefono, v_old_contrasena, v_old_estado
    FROM TUsuarios WHERE idUsuario = p_idUsuario;

    UPDATE TUsuarios
    SET idRol = p_idRol,
        nombre1 = p_nombre1,
        nombre2 = p_nombre2,
        apellido1 = p_apellido1,
        apellido2 = p_apellido2,
        correo = p_correo,
        telefono = p_telefono,
        contrasena = p_contrasena,
        estado = p_estado
    WHERE idUsuario = p_idUsuario;

        IF v_old_idRol <> p_idRol OR (v_old_idRol IS NULL AND p_idRol IS NOT NULL) OR (v_old_idRol IS NOT NULL AND p_idRol IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'idRol');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_idRol, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_idRol, ''));
    END IF;
    IF v_old_nombre1 <> p_nombre1 OR (v_old_nombre1 IS NULL AND p_nombre1 IS NOT NULL) OR (v_old_nombre1 IS NOT NULL AND p_nombre1 IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'nombre1');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_nombre1, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_nombre1, ''));
    END IF;
    IF v_old_nombre2 <> p_nombre2 OR (v_old_nombre2 IS NULL AND p_nombre2 IS NOT NULL) OR (v_old_nombre2 IS NOT NULL AND p_nombre2 IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'nombre2');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_nombre2, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_nombre2, ''));
    END IF;
    IF v_old_apellido1 <> p_apellido1 OR (v_old_apellido1 IS NULL AND p_apellido1 IS NOT NULL) OR (v_old_apellido1 IS NOT NULL AND p_apellido1 IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'apellido1');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_apellido1, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_apellido1, ''));
    END IF;
    IF v_old_apellido2 <> p_apellido2 OR (v_old_apellido2 IS NULL AND p_apellido2 IS NOT NULL) OR (v_old_apellido2 IS NOT NULL AND p_apellido2 IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'apellido2');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_apellido2, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_apellido2, ''));
    END IF;
    IF v_old_correo <> p_correo OR (v_old_correo IS NULL AND p_correo IS NOT NULL) OR (v_old_correo IS NOT NULL AND p_correo IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'correo');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_correo, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_correo, ''));
    END IF;
    IF v_old_telefono <> p_telefono OR (v_old_telefono IS NULL AND p_telefono IS NOT NULL) OR (v_old_telefono IS NOT NULL AND p_telefono IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'telefono');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_telefono, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_telefono, ''));
    END IF;
    IF v_old_contrasena <> p_contrasena OR (v_old_contrasena IS NULL AND p_contrasena IS NOT NULL) OR (v_old_contrasena IS NOT NULL AND p_contrasena IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'contrasena');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_contrasena, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_contrasena, ''));
    END IF;
    IF v_old_estado <> p_estado OR (v_old_estado IS NULL AND p_estado IS NOT NULL) OR (v_old_estado IS NOT NULL AND p_estado IS NULL) THEN
        SET v_campo = CONCAT_WS('|', v_campo, 'estado');
        SET v_viejo = CONCAT_WS('|', v_viejo, COALESCE(v_old_estado, ''));
        SET v_nuevo = CONCAT_WS('|', v_nuevo, COALESCE(p_estado, ''));
    END IF;

    IF LENGTH(v_campo) > 0 THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TUsuarios', p_idUsuario, 'U', TRIM(LEADING '|' FROM v_campo), TRIM(LEADING '|' FROM v_viejo), TRIM(LEADING '|' FROM v_nuevo), p_usuarioA, NOW(), p_direccionIP,
            CONCAT('Se actualizaron ', (LENGTH(v_campo) - LENGTH(REPLACE(v_campo, '|', '')) + 1), ' campo(s): ', TRIM(LEADING '|' FROM v_campo)));
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_17_000001_create_t_roles_table', 1),
(5, '2026_06_17_000002_create_t_sucursales_table', 1),
(6, '2026_06_17_000003_create_t_planes_table', 1),
(7, '2026_06_17_000004_create_t_actividades_table', 1),
(8, '2026_06_17_000005_create_t_marcas_table', 1),
(9, '2026_06_17_000006_create_t_usuarios_table', 1),
(10, '2026_06_17_000007_create_t_empleados_table', 1),
(11, '2026_06_17_000008_create_t_socios_table', 1),
(12, '2026_06_17_000009_create_t_horario_laborales_table', 1),
(13, '2026_06_17_000010_create_t_control_asistencias_table', 1),
(14, '2026_06_17_000011_create_t_esquema_sueldos_table', 1),
(15, '2026_06_17_000012_create_t_membresias_table', 1),
(16, '2026_06_17_000013_create_t_control_accesos_table', 1),
(17, '2026_06_17_000014_create_t_penalizaciones_table', 1),
(18, '2026_06_17_000015_create_t_notificaciones_table', 1),
(19, '2026_06_17_000016_create_t_clase_grupales_table', 1),
(20, '2026_06_17_000017_create_t_reservas_table', 1),
(21, '2026_06_17_000018_create_t_cajas_table', 1),
(22, '2026_06_17_000019_create_t_recibos_table', 1),
(23, '2026_06_17_000020_create_t_detalle_metodo_pagos_table', 1),
(24, '2026_06_17_000021_create_t_equipamientos_table', 1),
(25, '2026_06_17_000022_create_t_mantenimiento_preventivos_table', 1),
(26, '2026_06_17_000023_create_t_reporte_fallas_table', 1),
(27, '2026_06_17_000024_create_t_auditorias_table', 1),
(28, '2026_06_17_000025_add_usuarioa_foreign_keys_table', 1),
(29, '2026_06_17_000025b_create_t_metodo_pagos_table', 1),
(30, '2026_06_17_000026_create_stored_procedures', 1),
(31, '2026_06_18_100000_create_thorarios_table', 1),
(32, '2026_06_18_100001_create_tasistencias_personal_table', 1),
(33, '2026_06_22_000027_create_business_stored_procedures', 2),
(34, '2026_06_22_000028_drop_thorarios_table', 2),
(35, '2026_06_23_000028_create_sp_asistencia_entrenador', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('Nrip0SMtFFwGZmMooklUcHlDXw3njHJKCrVFkjpL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicThnbjB1Q1VkTnRZZG95V2V3Ynp5dW5nZFFmbHBEYXh2V1pKcXlTQiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9zb2Npb3MvbGlzdGFyIjtzOjU6InJvdXRlIjtzOjE5OiJhZG1pbi5zb2Npb3MubGlzdGFyIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo3OiJ1c3VhcmlvIjtPOjg6InN0ZENsYXNzIjoxNDp7czo5OiJpZFVzdWFyaW8iO2k6MTtzOjU6ImlkUm9sIjtpOjE7czo3OiJub21icmUxIjtzOjU6IkFkbWluIjtzOjc6Im5vbWJyZTIiO047czo5OiJhcGVsbGlkbzEiO3M6NzoiU2lzdGVtYSI7czo5OiJhcGVsbGlkbzIiO047czo2OiJjb3JyZW8iO3M6MTg6ImFkbWluQGdpbW5hc2lvLmNvbSI7czo4OiJ0ZWxlZm9ubyI7aToxMjM0NTY3ODtzOjEwOiJjb250cmFzZW5hIjtzOjYwOiIkMnkkMTIkL3h2UDdVNk5nTkRoSWtWVDEzWVBYdXh2VS5BWXBzdXRXcXdSbHkwRVZNb25IalYzUy9QUkciO3M6NjoiZXN0YWRvIjtpOjE7czo3OiJlc3RhZG9BIjtpOjE7czo2OiJmZWNoYUEiO3M6MTk6IjIwMjYtMDYtMjIgMTE6MDY6MjUiO3M6ODoidXN1YXJpb0EiO047czo5OiJub21icmVSb2wiO3M6MTM6IkFkbWluaXN0cmFkb3IiO319', 1782308139);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tactividades`
--

CREATE TABLE `tactividades` (
  `idActividad` int(10) UNSIGNED NOT NULL,
  `nombreActividad` varchar(100) NOT NULL,
  `descripcionActividad` text DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tactividades`
--

INSERT INTO `tactividades` (`idActividad`, `nombreActividad`, `descripcionActividad`, `estado`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 'Yoga', 'Clase de yoga para todos los niveles', 1, 1, '2026-06-22 11:06:29', 1),
(2, 'Spinning', 'Ciclismo indoor de alta intensidad', 1, 1, '2026-06-22 11:06:29', 1),
(3, 'CrossFit', 'Entrenamiento funcional de alta intensidad', 1, 1, '2026-06-22 11:06:29', 1),
(4, 'Pilates', 'Ejercicios de fortalecimiento y flexibilidad', 1, 1, '2026-06-22 11:06:29', 1),
(5, 'Zumba', 'Baile y fitness al ritmo de la música', 1, 1, '2026-06-22 11:06:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tasistenciaspersonal`
--

CREATE TABLE `tasistenciaspersonal` (
  `idAsistencia` bigint(20) UNSIGNED NOT NULL,
  `carnetEmpleado` int(11) NOT NULL,
  `horaEntrada` datetime NOT NULL,
  `horaSalida` datetime DEFAULT NULL,
  `usuarioA` int(10) UNSIGNED NOT NULL,
  `ipA` varchar(45) DEFAULT NULL,
  `fechaA` timestamp NOT NULL DEFAULT current_timestamp(),
  `fechaM` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tauditorias`
--

CREATE TABLE `tauditorias` (
  `idAuditoria` int(10) UNSIGNED NOT NULL,
  `tablaNombre` varchar(50) DEFAULT NULL,
  `registroId` int(11) DEFAULT NULL,
  `accion` varchar(50) DEFAULT NULL,
  `campo` varchar(100) DEFAULT NULL,
  `valorAnterior` text DEFAULT NULL,
  `valorNuevo` text DEFAULT NULL,
  `usuarioA` int(10) UNSIGNED DEFAULT NULL,
  `fechaA` datetime DEFAULT current_timestamp(),
  `direccionIP` varchar(50) DEFAULT NULL,
  `detalles` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tauditorias`
--

INSERT INTO `tauditorias` (`idAuditoria`, `tablaNombre`, `registroId`, `accion`, `campo`, `valorAnterior`, `valorNuevo`, `usuarioA`, `fechaA`, `direccionIP`, `detalles`) VALUES
(1, 'TSucursales', 2, 'I', 'insercion', NULL, 'Sucursal Sucre|Sucre|12346789|1', 1, '2026-06-22 11:14:27', '127.0.0.1', 'Insercion nueva'),
(2, 'TSucursales', 2, 'U', 'telefono', '12346789', '12346788', 1, '2026-06-22 11:14:36', '127.0.0.1', 'Se actualizaron 1 campo(s): telefono'),
(3, 'TUsuarios', 13, 'I', 'insercion', NULL, '3|Sebastian||Alca||sebas@gmail.com|87986545|$2y$12$7ipQLmddGFkSWJpVmmHBZeZvbPha8bzj3XZsG0EzpcbzA2qSPJeWK|1', 1, '2026-06-22 11:20:05', '127.0.0.1', 'Insercion nueva'),
(6, 'TUsuarios', 13, 'U', 'nombre2|apellido1|apellido2', 'Alca|', 'Alcachofa|', 1, '2026-06-22 11:22:57', '127.0.0.1', 'Se actualizaron 3 campo(s): nombre2|apellido1|apellido2'),
(9, 'TUsuarios', 16, 'I', 'insercion', NULL, '4|Juan||Calani||juan@gmail.com|87654321|$2y$12$nqZFQ7jD0YibapZEs9Mdu.Y4poWihFHbxPgcaZGRr/2Df9h6qEbe.|1', 1, '2026-06-22 11:34:32', '127.0.0.1', 'Insercion nueva'),
(10, 'TUsuarios', 16, 'U', 'nombre1|nombre2|apellido2', 'Juan||', 'Juanito||', 1, '2026-06-22 11:34:56', '127.0.0.1', 'Se actualizaron 3 campo(s): nombre1|nombre2|apellido2'),
(11, 'TSucursales', 3, 'I', 'insercion', NULL, 'Sucursal Avaroa|Plaza Avaroa|85858585|1', 1, '2026-06-23 15:16:50', '127.0.0.1', 'Insercion nueva'),
(12, 'TUsuarios', 17, 'I', 'insercion', NULL, '4|Milei|Mileo|Pamilei|Mamilei|milei@gmail.com|64758523|$2y$12$zxcOGCMccGCUgwzjODKZ8u58VueTVQjgOZjP35U2R87SV0eWaBb6i|1', 1, '2026-06-23 15:23:26', '127.0.0.1', 'Insercion nueva'),
(13, 'TSucursales', 3, 'U', 'telefono', '85858585', '85858584', 1, '2026-06-24 08:40:54', '127.0.0.1', 'Se actualizaron 1 campo(s): telefono'),
(14, 'TSucursales', 2, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', 1, '2026-06-24 08:41:02', '127.0.0.1', 'Eliminado (desactivado) registro'),
(15, 'TSucursales', 4, 'I', 'insercion', NULL, 'Sucursal Cruce|Cruce|65656565|1', 1, '2026-06-24 08:52:32', '127.0.0.1', 'Insercion nueva'),
(16, 'TSucursales', 4, 'U', 'telefono', '65656565', '65656522', 1, '2026-06-24 08:52:46', '127.0.0.1', 'Se actualizaron 1 campo(s): telefono'),
(17, 'TSucursales', 4, 'D', 'estadoA', 'estado = 1', 'estadoA = 0', 1, '2026-06-24 08:53:04', '127.0.0.1', 'Eliminado (desactivado) registro'),
(18, 'TSucursales', 5, 'I', 'insercion', NULL, 'Sucursal Cruce 2|Cruce|78787878788|1', 1, '2026-06-24 08:56:12', '127.0.0.1', 'Insercion nueva');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tcajas`
--

CREATE TABLE `tcajas` (
  `idCaja` int(10) UNSIGNED NOT NULL,
  `idSucursal` int(10) UNSIGNED NOT NULL,
  `carnetEmpleado` int(11) NOT NULL,
  `fechaApertura` date NOT NULL,
  `horaApertura` time NOT NULL,
  `montoApertura` decimal(10,2) NOT NULL,
  `montoCierre` decimal(10,2) DEFAULT NULL,
  `montoCierreCalculado` decimal(10,2) DEFAULT NULL,
  `diferenciaArqueo` decimal(10,2) DEFAULT NULL,
  `estadoCaja` enum('Abierta','Cerrada','Auditada') NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tcajas`
--

INSERT INTO `tcajas` (`idCaja`, `idSucursal`, `carnetEmpleado`, `fechaApertura`, `horaApertura`, `montoApertura`, `montoCierre`, `montoCierreCalculado`, `diferenciaArqueo`, `estadoCaja`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 1, 1002, '2026-01-01', '08:00:00', 708.00, 2105.00, 2093.00, -12.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(2, 1, 1001, '2026-01-02', '08:00:00', 443.00, 2114.00, 2103.00, -11.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(3, 1, 1002, '2026-01-05', '08:00:00', 403.00, 2794.00, 2770.00, -24.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(4, 1, 1001, '2026-01-06', '08:00:00', 677.00, 3665.00, 3683.00, 18.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(5, 1, 1002, '2026-01-07', '08:00:00', 922.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(6, 1, 1002, '2026-01-08', '08:00:00', 478.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(7, 1, 1002, '2026-01-09', '08:00:00', 368.00, 1406.00, 1361.00, -45.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(8, 1, 1002, '2026-01-12', '08:00:00', 913.00, 2983.00, 2937.00, -46.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(9, 1, 1002, '2026-01-13', '08:00:00', 500.00, 1644.00, 1644.00, 0.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(10, 1, 1001, '2026-01-14', '08:00:00', 798.00, 3254.00, 3292.00, 38.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(11, 1, 1001, '2026-01-15', '08:00:00', 338.00, 962.00, 960.00, -2.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(12, 1, 1001, '2026-01-16', '08:00:00', 283.00, 1287.00, 1266.00, -21.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(13, 1, 1001, '2026-01-19', '08:00:00', 785.00, 1672.00, 1683.00, 11.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(14, 1, 1001, '2026-01-20', '08:00:00', 562.00, 1948.00, 1934.00, -14.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(15, 1, 1001, '2026-01-21', '08:00:00', 257.00, 1680.00, 1649.00, -31.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(16, 1, 1002, '2026-01-22', '08:00:00', 432.00, 1650.00, 1629.00, -21.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(17, 1, 1002, '2026-01-23', '08:00:00', 671.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(18, 1, 1002, '2026-01-26', '08:00:00', 353.00, 783.00, 783.00, 0.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(19, 1, 1001, '2026-01-27', '08:00:00', 510.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(20, 1, 1001, '2026-01-28', '08:00:00', 651.00, 2864.00, 2824.00, -40.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(21, 1, 1001, '2026-01-29', '08:00:00', 279.00, 2898.00, 2910.00, 12.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(22, 1, 1001, '2026-01-30', '08:00:00', 415.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(23, 1, 1002, '2026-02-02', '08:00:00', 813.00, 1853.00, 1860.00, 7.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(24, 1, 1001, '2026-02-03', '08:00:00', 575.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(25, 1, 1001, '2026-02-04', '08:00:00', 884.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(26, 1, 1002, '2026-02-05', '08:00:00', 271.00, 2532.00, 2482.00, -50.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(27, 1, 1001, '2026-02-06', '08:00:00', 705.00, 1906.00, 1906.00, 0.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(28, 1, 1001, '2026-02-09', '08:00:00', 502.00, 2864.00, 2887.00, 23.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(29, 1, 1001, '2026-02-10', '08:00:00', 311.00, 2624.00, 2581.00, -43.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(30, 1, 1002, '2026-02-11', '08:00:00', 399.00, 3057.00, 3037.00, -20.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(31, 1, 1002, '2026-02-12', '08:00:00', 674.00, 2702.00, 2668.00, -34.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(32, 1, 1001, '2026-02-13', '08:00:00', 959.00, 3459.00, 3427.00, -32.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(33, 1, 1002, '2026-02-16', '08:00:00', 803.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(34, 1, 1001, '2026-02-17', '08:00:00', 763.00, 3470.00, 3508.00, 38.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(35, 1, 1001, '2026-02-18', '08:00:00', 417.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(36, 1, 1001, '2026-02-19', '08:00:00', 260.00, 1948.00, 1918.00, -30.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(37, 1, 1001, '2026-02-20', '08:00:00', 952.00, 3626.00, 3638.00, 12.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(38, 1, 1001, '2026-02-23', '08:00:00', 943.00, 1073.00, 1115.00, 42.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(39, 1, 1002, '2026-02-24', '08:00:00', 617.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(40, 1, 1001, '2026-02-25', '08:00:00', 741.00, 3040.00, 3035.00, -5.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(41, 1, 1002, '2026-02-26', '08:00:00', 260.00, 2003.00, 1987.00, -16.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(42, 1, 1002, '2026-02-27', '08:00:00', 706.00, 2423.00, 2395.00, -28.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(43, 1, 1001, '2026-03-02', '08:00:00', 896.00, 1242.00, 1263.00, 21.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(44, 1, 1001, '2026-03-03', '08:00:00', 996.00, 2406.00, 2421.00, 15.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(45, 1, 1002, '2026-03-04', '08:00:00', 387.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(46, 1, 1002, '2026-03-05', '08:00:00', 809.00, 1558.00, 1592.00, 34.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(47, 1, 1002, '2026-03-06', '08:00:00', 641.00, 1897.00, 1914.00, 17.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(48, 1, 1001, '2026-03-09', '08:00:00', 262.00, 2538.00, 2504.00, -34.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(49, 1, 1001, '2026-03-10', '08:00:00', 631.00, 2278.00, 2297.00, 19.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(50, 1, 1001, '2026-03-11', '08:00:00', 401.00, 1270.00, 1266.00, -4.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(51, 1, 1002, '2026-03-12', '08:00:00', 509.00, 1278.00, 1282.00, 4.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(52, 1, 1001, '2026-03-13', '08:00:00', 722.00, 2701.00, 2671.00, -30.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(53, 1, 1002, '2026-03-16', '08:00:00', 321.00, 596.00, 555.00, -41.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(54, 1, 1001, '2026-03-17', '08:00:00', 966.00, 2339.00, 2293.00, -46.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(55, 1, 1001, '2026-03-18', '08:00:00', 804.00, 2926.00, 2936.00, 10.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(56, 1, 1001, '2026-03-19', '08:00:00', 692.00, 1894.00, 1878.00, -16.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(57, 1, 1002, '2026-03-20', '08:00:00', 389.00, 980.00, 945.00, -35.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(58, 1, 1001, '2026-03-23', '08:00:00', 470.00, 1441.00, 1419.00, -22.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(59, 1, 1001, '2026-03-24', '08:00:00', 774.00, 905.00, 923.00, 18.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(60, 1, 1002, '2026-03-25', '08:00:00', 542.00, 3262.00, 3305.00, 43.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(61, 1, 1001, '2026-03-26', '08:00:00', 613.00, 2909.00, 2928.00, 19.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(62, 1, 1001, '2026-03-27', '08:00:00', 260.00, 1447.00, 1453.00, 6.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(63, 1, 1002, '2026-03-30', '08:00:00', 924.00, 2232.00, 2279.00, 47.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(64, 1, 1001, '2026-03-31', '08:00:00', 945.00, 1659.00, 1657.00, -2.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(65, 1, 1002, '2026-04-01', '08:00:00', 216.00, 2445.00, 2464.00, 19.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(66, 1, 1001, '2026-04-02', '08:00:00', 969.00, 3768.00, 3730.00, -38.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(67, 1, 1001, '2026-04-03', '08:00:00', 977.00, 2031.00, 2008.00, -23.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(68, 1, 1001, '2026-04-06', '08:00:00', 947.00, 3279.00, 3321.00, 42.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(69, 1, 1001, '2026-04-07', '08:00:00', 413.00, 1752.00, 1732.00, -20.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(70, 1, 1002, '2026-04-08', '08:00:00', 942.00, 2447.00, 2493.00, 46.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(71, 1, 1001, '2026-04-09', '08:00:00', 1000.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(72, 1, 1001, '2026-04-10', '08:00:00', 297.00, 2093.00, 2126.00, 33.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(73, 1, 1001, '2026-04-13', '08:00:00', 796.00, 2081.00, 2031.00, -50.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(74, 1, 1002, '2026-04-14', '08:00:00', 304.00, 1945.00, 1926.00, -19.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(75, 1, 1002, '2026-04-15', '08:00:00', 253.00, 543.00, 575.00, 32.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(76, 1, 1001, '2026-04-16', '08:00:00', 239.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(77, 1, 1002, '2026-04-17', '08:00:00', 383.00, 3051.00, 3099.00, 48.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(78, 1, 1002, '2026-04-20', '08:00:00', 791.00, 3557.00, 3549.00, -8.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(79, 1, 1002, '2026-04-21', '08:00:00', 379.00, 1742.00, 1744.00, 2.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(80, 1, 1002, '2026-04-22', '08:00:00', 322.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(81, 1, 1001, '2026-04-23', '08:00:00', 615.00, 1837.00, 1801.00, -36.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(82, 1, 1002, '2026-04-24', '08:00:00', 344.00, 719.00, 713.00, -6.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(83, 1, 1002, '2026-04-27', '08:00:00', 852.00, 2886.00, 2891.00, 5.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(84, 1, 1002, '2026-04-28', '08:00:00', 628.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(85, 1, 1002, '2026-04-29', '08:00:00', 368.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(86, 1, 1001, '2026-04-30', '08:00:00', 577.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(87, 1, 1002, '2026-05-01', '08:00:00', 255.00, 3013.00, 2976.00, -37.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(88, 1, 1001, '2026-05-04', '08:00:00', 317.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(89, 1, 1002, '2026-05-05', '08:00:00', 760.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(90, 1, 1002, '2026-05-06', '08:00:00', 633.00, 2261.00, 2252.00, -9.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(91, 1, 1001, '2026-05-07', '08:00:00', 232.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(92, 1, 1002, '2026-05-08', '08:00:00', 845.00, 2755.00, 2749.00, -6.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(93, 1, 1002, '2026-05-11', '08:00:00', 703.00, 2699.00, 2746.00, 47.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(94, 1, 1002, '2026-05-12', '08:00:00', 414.00, 1032.00, 1036.00, 4.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(95, 1, 1001, '2026-05-13', '08:00:00', 620.00, 1415.00, 1451.00, 36.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(96, 1, 1002, '2026-05-14', '08:00:00', 773.00, 2172.00, 2171.00, -1.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(97, 1, 1002, '2026-05-15', '08:00:00', 229.00, 2301.00, 2311.00, 10.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(98, 1, 1002, '2026-05-18', '08:00:00', 931.00, 2842.00, 2833.00, -9.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(99, 1, 1001, '2026-05-19', '08:00:00', 607.00, 1281.00, 1257.00, -24.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(100, 1, 1002, '2026-05-20', '08:00:00', 779.00, 3057.00, 3065.00, 8.00, 'Auditada', 1, '2026-06-22 11:06:29', 1),
(101, 1, 1002, '2026-05-21', '08:00:00', 686.00, 1110.00, 1107.00, -3.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(102, 1, 1001, '2026-05-22', '08:00:00', 927.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1),
(103, 1, 1002, '2026-05-25', '08:00:00', 581.00, 3463.00, 3436.00, -27.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(104, 1, 1001, '2026-05-26', '08:00:00', 322.00, 3147.00, 3188.00, 41.00, 'Cerrada', 1, '2026-06-22 11:06:29', 1),
(105, 1, 1001, '2026-05-27', '08:00:00', 602.00, NULL, NULL, NULL, 'Abierta', 1, '2026-06-22 11:06:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tclasegrupales`
--

CREATE TABLE `tclasegrupales` (
  `idClaseGrupal` int(10) UNSIGNED NOT NULL,
  `idActividad` int(10) UNSIGNED NOT NULL,
  `carnetEmpleado` int(11) NOT NULL,
  `idSucursal` int(10) UNSIGNED NOT NULL,
  `fecha` date NOT NULL,
  `horaInicio` time NOT NULL,
  `horaFin` time NOT NULL,
  `cupoMaximo` int(11) NOT NULL,
  `estadoClase` enum('Programada','Cursandose','Cancelada') NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tclasegrupales`
--

INSERT INTO `tclasegrupales` (`idClaseGrupal`, `idActividad`, `carnetEmpleado`, `idSucursal`, `fecha`, `horaInicio`, `horaFin`, `cupoMaximo`, `estadoClase`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 4, 1002, 1, '2026-01-01', '18:30:00', '19:30:00', 17, 'Programada', 1, '2026-06-22 11:06:29', 1),
(2, 5, 1002, 1, '2026-01-01', '17:00:00', '18:00:00', 26, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(3, 2, 1002, 1, '2026-01-01', '10:00:00', '11:00:00', 25, 'Programada', 1, '2026-06-22 11:06:29', 1),
(4, 4, 1002, 1, '2026-01-02', '17:00:00', '18:00:00', 25, 'Programada', 1, '2026-06-22 11:06:29', 1),
(5, 3, 1002, 1, '2026-01-02', '10:00:00', '11:00:00', 20, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(6, 2, 1002, 1, '2026-01-02', '08:30:00', '09:30:00', 30, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(7, 2, 1002, 1, '2026-01-05', '07:00:00', '08:00:00', 30, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(8, 5, 1002, 1, '2026-01-06', '18:30:00', '19:30:00', 23, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(9, 4, 1002, 1, '2026-01-06', '07:00:00', '08:00:00', 17, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(10, 3, 1002, 1, '2026-01-06', '08:30:00', '09:30:00', 26, 'Programada', 1, '2026-06-22 11:06:29', 1),
(11, 2, 1002, 1, '2026-01-07', '18:30:00', '19:30:00', 30, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(12, 4, 1002, 1, '2026-01-07', '18:30:00', '19:30:00', 26, 'Programada', 1, '2026-06-22 11:06:29', 1),
(13, 4, 1002, 1, '2026-01-08', '10:00:00', '11:00:00', 20, 'Programada', 1, '2026-06-22 11:06:29', 1),
(14, 4, 1002, 1, '2026-01-09', '18:30:00', '19:30:00', 20, 'Programada', 1, '2026-06-22 11:06:29', 1),
(15, 2, 1002, 1, '2026-01-09', '17:00:00', '18:00:00', 26, 'Programada', 1, '2026-06-22 11:06:29', 1),
(16, 2, 1002, 1, '2026-01-12', '17:00:00', '18:00:00', 26, 'Programada', 1, '2026-06-22 11:06:29', 1),
(17, 3, 1002, 1, '2026-01-12', '18:30:00', '19:30:00', 27, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(18, 3, 1002, 1, '2026-01-13', '15:00:00', '16:00:00', 16, 'Programada', 1, '2026-06-22 11:06:29', 1),
(19, 4, 1002, 1, '2026-01-13', '15:00:00', '16:00:00', 21, 'Programada', 1, '2026-06-22 11:06:29', 1),
(20, 4, 1002, 1, '2026-01-14', '08:30:00', '09:30:00', 16, 'Programada', 1, '2026-06-22 11:06:29', 1),
(21, 1, 1002, 1, '2026-01-15', '10:00:00', '11:00:00', 22, 'Programada', 1, '2026-06-22 11:06:29', 1),
(22, 3, 1002, 1, '2026-01-15', '17:00:00', '18:00:00', 26, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(23, 5, 1002, 1, '2026-01-16', '10:00:00', '11:00:00', 28, 'Programada', 1, '2026-06-22 11:06:29', 1),
(24, 2, 1002, 1, '2026-01-16', '15:00:00', '16:00:00', 23, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(25, 3, 1002, 1, '2026-01-16', '18:30:00', '19:30:00', 15, 'Programada', 1, '2026-06-22 11:06:29', 1),
(26, 3, 1002, 1, '2026-01-19', '08:30:00', '09:30:00', 30, 'Programada', 1, '2026-06-22 11:06:29', 1),
(27, 5, 1002, 1, '2026-01-19', '18:30:00', '19:30:00', 26, 'Programada', 1, '2026-06-22 11:06:29', 1),
(28, 4, 1002, 1, '2026-01-20', '18:30:00', '19:30:00', 15, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(29, 1, 1002, 1, '2026-01-20', '08:30:00', '09:30:00', 15, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(30, 2, 1002, 1, '2026-01-21', '08:30:00', '09:30:00', 15, 'Programada', 1, '2026-06-22 11:06:29', 1),
(31, 1, 1002, 1, '2026-01-21', '15:00:00', '16:00:00', 23, 'Programada', 1, '2026-06-22 11:06:29', 1),
(32, 5, 1002, 1, '2026-01-21', '07:00:00', '08:00:00', 22, 'Programada', 1, '2026-06-22 11:06:29', 1),
(33, 4, 1002, 1, '2026-01-22', '15:00:00', '16:00:00', 18, 'Programada', 1, '2026-06-22 11:06:29', 1),
(34, 1, 1002, 1, '2026-01-23', '18:30:00', '19:30:00', 28, 'Programada', 1, '2026-06-22 11:06:29', 1),
(35, 3, 1002, 1, '2026-01-26', '15:00:00', '16:00:00', 18, 'Programada', 1, '2026-06-22 11:06:29', 1),
(36, 3, 1002, 1, '2026-01-27', '18:30:00', '19:30:00', 15, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(37, 2, 1002, 1, '2026-01-27', '18:30:00', '19:30:00', 19, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(38, 1, 1002, 1, '2026-01-27', '08:30:00', '09:30:00', 23, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(39, 3, 1002, 1, '2026-01-28', '07:00:00', '08:00:00', 19, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(40, 5, 1002, 1, '2026-01-28', '10:00:00', '11:00:00', 20, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(41, 2, 1002, 1, '2026-01-28', '18:30:00', '19:30:00', 16, 'Programada', 1, '2026-06-22 11:06:29', 1),
(42, 4, 1002, 1, '2026-01-29', '17:00:00', '18:00:00', 17, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(43, 3, 1002, 1, '2026-01-29', '17:00:00', '18:00:00', 27, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(44, 3, 1002, 1, '2026-01-30', '07:00:00', '08:00:00', 19, 'Programada', 1, '2026-06-22 11:06:29', 1),
(45, 2, 1002, 1, '2026-01-30', '18:30:00', '19:30:00', 30, 'Programada', 1, '2026-06-22 11:06:29', 1),
(46, 4, 1002, 1, '2026-01-30', '08:30:00', '09:30:00', 18, 'Programada', 1, '2026-06-22 11:06:29', 1),
(47, 3, 1002, 1, '2026-02-02', '07:00:00', '08:00:00', 25, 'Programada', 1, '2026-06-22 11:06:29', 1),
(48, 5, 1002, 1, '2026-02-03', '17:00:00', '18:00:00', 28, 'Programada', 1, '2026-06-22 11:06:29', 1),
(49, 2, 1002, 1, '2026-02-04', '08:30:00', '09:30:00', 29, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(50, 5, 1002, 1, '2026-02-04', '15:00:00', '16:00:00', 26, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(51, 3, 1002, 1, '2026-02-04', '18:30:00', '19:30:00', 21, 'Programada', 1, '2026-06-22 11:06:29', 1),
(52, 3, 1002, 1, '2026-02-05', '15:00:00', '16:00:00', 26, 'Programada', 1, '2026-06-22 11:06:29', 1),
(53, 1, 1002, 1, '2026-02-05', '18:30:00', '19:30:00', 16, 'Programada', 1, '2026-06-22 11:06:29', 1),
(54, 2, 1002, 1, '2026-02-05', '07:00:00', '08:00:00', 15, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(55, 3, 1002, 1, '2026-02-06', '07:00:00', '08:00:00', 28, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(56, 5, 1002, 1, '2026-02-09', '17:00:00', '18:00:00', 15, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(57, 4, 1002, 1, '2026-02-09', '18:30:00', '19:30:00', 19, 'Programada', 1, '2026-06-22 11:06:29', 1),
(58, 5, 1002, 1, '2026-02-10', '07:00:00', '08:00:00', 15, 'Programada', 1, '2026-06-22 11:06:29', 1),
(59, 4, 1002, 1, '2026-02-10', '18:30:00', '19:30:00', 23, 'Programada', 1, '2026-06-22 11:06:29', 1),
(60, 5, 1002, 1, '2026-02-11', '10:00:00', '11:00:00', 28, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(61, 1, 1002, 1, '2026-02-11', '17:00:00', '18:00:00', 15, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(62, 2, 1002, 1, '2026-02-11', '15:00:00', '16:00:00', 20, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(63, 5, 1002, 1, '2026-02-12', '15:00:00', '16:00:00', 21, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(64, 1, 1002, 1, '2026-02-12', '17:00:00', '18:00:00', 23, 'Programada', 1, '2026-06-22 11:06:29', 1),
(65, 3, 1002, 1, '2026-02-13', '08:30:00', '09:30:00', 23, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(66, 5, 1002, 1, '2026-02-16', '10:00:00', '11:00:00', 19, 'Programada', 1, '2026-06-22 11:06:29', 1),
(67, 4, 1002, 1, '2026-02-16', '17:00:00', '18:00:00', 25, 'Programada', 1, '2026-06-22 11:06:29', 1),
(68, 1, 1002, 1, '2026-02-16', '18:30:00', '19:30:00', 17, 'Programada', 1, '2026-06-22 11:06:29', 1),
(69, 4, 1002, 1, '2026-02-17', '08:30:00', '09:30:00', 30, 'Programada', 1, '2026-06-22 11:06:29', 1),
(70, 5, 1002, 1, '2026-02-17', '18:30:00', '19:30:00', 23, 'Programada', 1, '2026-06-22 11:06:29', 1),
(71, 5, 1002, 1, '2026-02-18', '15:00:00', '16:00:00', 25, 'Programada', 1, '2026-06-22 11:06:29', 1),
(72, 3, 1002, 1, '2026-02-18', '10:00:00', '11:00:00', 22, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(73, 1, 1002, 1, '2026-02-19', '17:00:00', '18:00:00', 16, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(74, 2, 1002, 1, '2026-02-19', '18:30:00', '19:30:00', 25, 'Programada', 1, '2026-06-22 11:06:29', 1),
(75, 2, 1002, 1, '2026-02-20', '08:30:00', '09:30:00', 20, 'Programada', 1, '2026-06-22 11:06:29', 1),
(76, 3, 1002, 1, '2026-02-23', '18:30:00', '19:30:00', 21, 'Programada', 1, '2026-06-22 11:06:29', 1),
(77, 1, 1002, 1, '2026-02-23', '10:00:00', '11:00:00', 23, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(78, 4, 1002, 1, '2026-02-23', '08:30:00', '09:30:00', 24, 'Programada', 1, '2026-06-22 11:06:29', 1),
(79, 5, 1002, 1, '2026-02-24', '08:30:00', '09:30:00', 16, 'Programada', 1, '2026-06-22 11:06:29', 1),
(80, 2, 1002, 1, '2026-02-24', '17:00:00', '18:00:00', 21, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(81, 3, 1002, 1, '2026-02-24', '17:00:00', '18:00:00', 19, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(82, 5, 1002, 1, '2026-02-25', '08:30:00', '09:30:00', 20, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(83, 3, 1002, 1, '2026-02-25', '08:30:00', '09:30:00', 21, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(84, 2, 1002, 1, '2026-02-25', '15:00:00', '16:00:00', 28, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(85, 2, 1002, 1, '2026-02-26', '18:30:00', '19:30:00', 15, 'Programada', 1, '2026-06-22 11:06:29', 1),
(86, 1, 1002, 1, '2026-02-26', '10:00:00', '11:00:00', 30, 'Programada', 1, '2026-06-22 11:06:29', 1),
(87, 5, 1002, 1, '2026-02-26', '15:00:00', '16:00:00', 28, 'Programada', 1, '2026-06-22 11:06:29', 1),
(88, 4, 1002, 1, '2026-02-27', '07:00:00', '08:00:00', 18, 'Programada', 1, '2026-06-22 11:06:29', 1),
(89, 4, 1002, 1, '2026-03-02', '10:00:00', '11:00:00', 21, 'Programada', 1, '2026-06-22 11:06:29', 1),
(90, 3, 1002, 1, '2026-03-02', '15:00:00', '16:00:00', 29, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(91, 5, 1002, 1, '2026-03-02', '15:00:00', '16:00:00', 21, 'Programada', 1, '2026-06-22 11:06:29', 1),
(92, 5, 1002, 1, '2026-03-03', '15:00:00', '16:00:00', 23, 'Programada', 1, '2026-06-22 11:06:29', 1),
(93, 3, 1002, 1, '2026-03-03', '07:00:00', '08:00:00', 21, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(94, 1, 1002, 1, '2026-03-03', '18:30:00', '19:30:00', 17, 'Programada', 1, '2026-06-22 11:06:29', 1),
(95, 5, 1002, 1, '2026-03-04', '17:00:00', '18:00:00', 21, 'Programada', 1, '2026-06-22 11:06:29', 1),
(96, 3, 1002, 1, '2026-03-04', '15:00:00', '16:00:00', 21, 'Programada', 1, '2026-06-22 11:06:29', 1),
(97, 1, 1002, 1, '2026-03-04', '17:00:00', '18:00:00', 22, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(98, 4, 1002, 1, '2026-03-05', '10:00:00', '11:00:00', 20, 'Programada', 1, '2026-06-22 11:06:29', 1),
(99, 1, 1002, 1, '2026-03-05', '17:00:00', '18:00:00', 29, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(100, 3, 1002, 1, '2026-03-06', '08:30:00', '09:30:00', 21, 'Programada', 1, '2026-06-22 11:06:29', 1),
(101, 4, 1002, 1, '2026-03-06', '08:30:00', '09:30:00', 22, 'Cursandose', 1, '2026-06-22 11:06:29', 1),
(102, 2, 1002, 1, '2026-03-06', '08:30:00', '09:30:00', 20, 'Programada', 1, '2026-06-22 11:06:29', 1),
(103, 1, 1002, 1, '2026-03-09', '18:30:00', '19:30:00', 25, 'Programada', 1, '2026-06-22 11:06:29', 1),
(104, 5, 1002, 1, '2026-03-09', '07:00:00', '08:00:00', 29, 'Cancelada', 1, '2026-06-22 11:06:29', 1),
(105, 2, 1002, 1, '2026-03-09', '15:00:00', '16:00:00', 28, 'Cursandose', 1, '2026-06-22 11:06:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tcontrolaccesos`
--

CREATE TABLE `tcontrolaccesos` (
  `idControlAcceso` int(10) UNSIGNED NOT NULL,
  `carnetSocio` int(10) UNSIGNED NOT NULL,
  `idSucursal` int(10) UNSIGNED NOT NULL,
  `fechaAcceso` date NOT NULL,
  `horaAcceso` time NOT NULL,
  `bloqueo` tinyint(1) NOT NULL DEFAULT 0,
  `motivoDenegacion` varchar(255) DEFAULT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tcontrolaccesos`
--

INSERT INTO `tcontrolaccesos` (`idControlAcceso`, `carnetSocio`, `idSucursal`, `fechaAcceso`, `horaAcceso`, `bloqueo`, `motivoDenegacion`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 6700004, 1, '2026-04-27', '20:48:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(2, 6700004, 1, '2026-01-12', '17:49:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(3, 6700003, 1, '2026-04-04', '20:02:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(4, 6700004, 1, '2026-05-10', '09:21:00', 1, 'Membresia vencida', 1, '2026-06-22 11:06:29', 1),
(5, 6700003, 1, '2026-05-28', '20:27:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(6, 6700002, 1, '2026-06-11', '19:33:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(7, 6700005, 1, '2026-06-11', '07:32:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(8, 6700001, 1, '2026-05-01', '08:16:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(9, 6700003, 1, '2026-02-06', '12:13:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(10, 6700002, 1, '2026-01-16', '13:55:00', 1, 'Membresia vencida', 1, '2026-06-22 11:06:29', 1),
(11, 6700004, 1, '2026-05-11', '06:50:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(12, 6700001, 1, '2026-06-07', '19:51:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(13, 6700003, 1, '2026-04-17', '15:37:00', 1, 'Membresia vencida', 1, '2026-06-22 11:06:29', 1),
(14, 6700001, 1, '2026-05-15', '13:00:00', 1, 'Membresia vencida', 1, '2026-06-22 11:06:29', 1),
(15, 6700005, 1, '2026-02-12', '18:09:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(16, 6700002, 1, '2026-06-03', '17:16:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(17, 6700002, 1, '2026-03-28', '14:41:00', 1, 'Membresia vencida', 1, '2026-06-22 11:06:29', 1),
(18, 6700005, 1, '2026-04-13', '11:09:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(19, 6700001, 1, '2026-05-14', '07:18:00', 1, 'Membresia vencida', 1, '2026-06-22 11:06:29', 1),
(20, 6700002, 1, '2026-05-10', '12:14:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(21, 6700003, 1, '2026-05-25', '15:57:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(22, 6700002, 1, '2026-01-12', '17:50:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(23, 6700001, 1, '2026-02-06', '10:02:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(24, 6700003, 1, '2026-03-16', '12:01:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(25, 6700002, 1, '2026-02-21', '11:57:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(26, 6700002, 1, '2026-01-02', '20:42:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(27, 6700003, 1, '2026-04-10', '08:11:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(28, 6700003, 1, '2026-05-04', '07:15:00', 0, NULL, 1, '2026-06-22 11:06:29', 1),
(29, 6700002, 1, '2026-03-14', '06:16:00', 1, 'Membresia vencida', 1, '2026-06-22 11:06:29', 1),
(30, 6700003, 1, '2026-01-28', '08:55:00', 1, 'Membresia vencida', 1, '2026-06-22 11:06:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tcontrolasistencias`
--

CREATE TABLE `tcontrolasistencias` (
  `idAsistencia` int(10) UNSIGNED NOT NULL,
  `carnetEmpleado` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `horaEntrada` time NOT NULL,
  `horaSalida` time DEFAULT NULL,
  `estadoAsistencia` enum('Puntual','Tardanza','Falta') NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tcontrolasistencias`
--

INSERT INTO `tcontrolasistencias` (`idAsistencia`, `carnetEmpleado`, `fecha`, `horaEntrada`, `horaSalida`, `estadoAsistencia`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 1001, '2026-01-01', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(2, 1002, '2026-01-01', '06:21:00', '14:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(3, 1001, '2026-01-02', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(4, 1002, '2026-01-02', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(5, 1001, '2026-01-05', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(6, 1002, '2026-01-05', '06:13:00', '14:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(7, 1001, '2026-01-06', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(8, 1002, '2026-01-06', '06:19:00', '14:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(9, 1001, '2026-01-07', '08:27:00', '17:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(10, 1002, '2026-01-07', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(11, 1001, '2026-01-08', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(12, 1002, '2026-01-08', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(13, 1001, '2026-01-09', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(14, 1002, '2026-01-09', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(15, 1001, '2026-01-12', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(16, 1002, '2026-01-12', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(17, 1001, '2026-01-13', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(18, 1002, '2026-01-13', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(19, 1001, '2026-01-14', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(20, 1002, '2026-01-14', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(21, 1001, '2026-01-15', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(22, 1002, '2026-01-15', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(23, 1001, '2026-01-16', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(24, 1002, '2026-01-16', '06:08:00', '14:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(25, 1001, '2026-01-19', '08:07:00', '17:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(26, 1002, '2026-01-19', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(27, 1001, '2026-01-20', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(28, 1002, '2026-01-20', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(29, 1001, '2026-01-21', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(30, 1002, '2026-01-21', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(31, 1001, '2026-01-22', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(32, 1002, '2026-01-22', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(33, 1001, '2026-01-23', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(34, 1002, '2026-01-23', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(35, 1001, '2026-01-26', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(36, 1002, '2026-01-26', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(37, 1001, '2026-01-27', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(38, 1002, '2026-01-27', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(39, 1001, '2026-01-28', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(40, 1002, '2026-01-28', '06:26:00', '14:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(41, 1001, '2026-01-29', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(42, 1002, '2026-01-29', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(43, 1001, '2026-01-30', '08:13:00', '17:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(44, 1002, '2026-01-30', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(45, 1001, '2026-02-02', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(46, 1002, '2026-02-02', '06:30:00', '14:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(47, 1001, '2026-02-03', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(48, 1002, '2026-02-03', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(49, 1001, '2026-02-04', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(50, 1002, '2026-02-04', '06:09:00', '14:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(51, 1001, '2026-02-05', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(52, 1002, '2026-02-05', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(53, 1001, '2026-02-06', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(54, 1002, '2026-02-06', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(55, 1001, '2026-02-09', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(56, 1002, '2026-02-09', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(57, 1001, '2026-02-10', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(58, 1002, '2026-02-10', '06:16:00', '14:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(59, 1001, '2026-02-11', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(60, 1002, '2026-02-11', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(61, 1001, '2026-02-12', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(62, 1002, '2026-02-12', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(63, 1001, '2026-02-13', '08:22:00', '17:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(64, 1002, '2026-02-13', '06:05:00', '14:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(65, 1001, '2026-02-16', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(66, 1002, '2026-02-16', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(67, 1001, '2026-02-17', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(68, 1002, '2026-02-17', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(69, 1001, '2026-02-18', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(70, 1002, '2026-02-18', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(71, 1001, '2026-02-19', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(72, 1002, '2026-02-19', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(73, 1001, '2026-02-20', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(74, 1002, '2026-02-20', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(75, 1001, '2026-02-23', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(76, 1002, '2026-02-23', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(77, 1001, '2026-02-24', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(78, 1002, '2026-02-24', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(79, 1001, '2026-02-25', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(80, 1002, '2026-02-25', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(81, 1001, '2026-02-26', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(82, 1002, '2026-02-26', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(83, 1001, '2026-02-27', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(84, 1002, '2026-02-27', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(85, 1001, '2026-03-02', '08:19:00', '17:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(86, 1002, '2026-03-02', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(87, 1001, '2026-03-03', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(88, 1002, '2026-03-03', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(89, 1001, '2026-03-04', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(90, 1002, '2026-03-04', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(91, 1001, '2026-03-05', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(92, 1002, '2026-03-05', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(93, 1001, '2026-03-06', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(94, 1002, '2026-03-06', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(95, 1001, '2026-03-09', '08:19:00', '17:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(96, 1002, '2026-03-09', '06:07:00', '14:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(97, 1001, '2026-03-10', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(98, 1002, '2026-03-10', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(99, 1001, '2026-03-11', '08:30:00', '17:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(100, 1002, '2026-03-11', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(101, 1001, '2026-03-12', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(102, 1002, '2026-03-12', '06:00:00', '14:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(103, 1001, '2026-03-13', '08:00:00', '17:00:00', 'Puntual', 1, '2026-06-22 11:06:29', 1),
(104, 1002, '2026-03-13', '00:00:00', '00:00:00', 'Falta', 1, '2026-06-22 11:06:29', 1),
(105, 1001, '2026-03-16', '08:18:00', '17:00:00', 'Tardanza', 1, '2026-06-22 11:06:29', 1),
(106, 88998558, '2026-06-22', '16:42:25', '16:42:48', 'Puntual', 1, '2026-06-22 16:42:48', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tdetallemetodopagos`
--

CREATE TABLE `tdetallemetodopagos` (
  `idMetodoPago` int(10) UNSIGNED NOT NULL,
  `idRecibo` int(10) UNSIGNED NOT NULL,
  `idMetodoPagoFK` int(10) UNSIGNED NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tdetallemetodopagos`
--

INSERT INTO `tdetallemetodopagos` (`idMetodoPago`, `idRecibo`, `idMetodoPagoFK`, `monto`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 1, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(2, 2, 1, 500.00, 1, '2026-06-22 11:06:30', 1),
(3, 3, 4, 300.00, 1, '2026-06-22 11:06:30', 1),
(4, 4, 2, 500.00, 1, '2026-06-22 11:06:30', 1),
(5, 5, 1, 800.00, 1, '2026-06-22 11:06:30', 1),
(6, 6, 1, 500.00, 1, '2026-06-22 11:06:30', 1),
(7, 7, 2, 300.00, 1, '2026-06-22 11:06:30', 1),
(8, 8, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(9, 9, 2, 800.00, 1, '2026-06-22 11:06:30', 1),
(10, 10, 2, 300.00, 1, '2026-06-22 11:06:30', 1),
(11, 11, 1, 800.00, 1, '2026-06-22 11:06:30', 1),
(12, 12, 2, 800.00, 1, '2026-06-22 11:06:30', 1),
(13, 13, 2, 800.00, 1, '2026-06-22 11:06:30', 1),
(14, 14, 3, 300.00, 1, '2026-06-22 11:06:30', 1),
(15, 15, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(16, 16, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(17, 17, 1, 800.00, 1, '2026-06-22 11:06:30', 1),
(18, 18, 4, 500.00, 1, '2026-06-22 11:06:30', 1),
(19, 19, 4, 300.00, 1, '2026-06-22 11:06:30', 1),
(20, 20, 3, 500.00, 1, '2026-06-22 11:06:30', 1),
(21, 21, 4, 500.00, 1, '2026-06-22 11:06:30', 1),
(22, 22, 4, 300.00, 1, '2026-06-22 11:06:30', 1),
(23, 23, 3, 300.00, 1, '2026-06-22 11:06:30', 1),
(24, 24, 2, 500.00, 1, '2026-06-22 11:06:30', 1),
(25, 25, 3, 300.00, 1, '2026-06-22 11:06:30', 1),
(26, 26, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(27, 27, 4, 500.00, 1, '2026-06-22 11:06:30', 1),
(28, 28, 3, 500.00, 1, '2026-06-22 11:06:30', 1),
(29, 29, 3, 500.00, 1, '2026-06-22 11:06:30', 1),
(30, 30, 3, 300.00, 1, '2026-06-22 11:06:30', 1),
(31, 31, 3, 800.00, 1, '2026-06-22 11:06:30', 1),
(32, 32, 3, 300.00, 1, '2026-06-22 11:06:30', 1),
(33, 33, 2, 500.00, 1, '2026-06-22 11:06:30', 1),
(34, 34, 2, 300.00, 1, '2026-06-22 11:06:30', 1),
(35, 35, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(36, 36, 2, 500.00, 1, '2026-06-22 11:06:30', 1),
(37, 37, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(38, 38, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(39, 39, 2, 500.00, 1, '2026-06-22 11:06:30', 1),
(40, 40, 2, 500.00, 1, '2026-06-22 11:06:30', 1),
(41, 41, 2, 800.00, 1, '2026-06-22 11:06:30', 1),
(42, 42, 3, 800.00, 1, '2026-06-22 11:06:30', 1),
(43, 43, 3, 800.00, 1, '2026-06-22 11:06:30', 1),
(44, 44, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(45, 45, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(46, 46, 1, 800.00, 1, '2026-06-22 11:06:30', 1),
(47, 47, 2, 500.00, 1, '2026-06-22 11:06:30', 1),
(48, 48, 1, 800.00, 1, '2026-06-22 11:06:30', 1),
(49, 49, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(50, 50, 3, 500.00, 1, '2026-06-22 11:06:30', 1),
(51, 51, 2, 500.00, 1, '2026-06-22 11:06:30', 1),
(52, 52, 3, 300.00, 1, '2026-06-22 11:06:30', 1),
(53, 53, 2, 300.00, 1, '2026-06-22 11:06:30', 1),
(54, 54, 2, 300.00, 1, '2026-06-22 11:06:30', 1),
(55, 55, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(56, 56, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(57, 57, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(58, 58, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(59, 59, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(60, 60, 2, 800.00, 1, '2026-06-22 11:06:30', 1),
(61, 61, 4, 300.00, 1, '2026-06-22 11:06:30', 1),
(62, 62, 1, 500.00, 1, '2026-06-22 11:06:30', 1),
(63, 63, 4, 500.00, 1, '2026-06-22 11:06:30', 1),
(64, 64, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(65, 65, 2, 500.00, 1, '2026-06-22 11:06:30', 1),
(66, 66, 2, 500.00, 1, '2026-06-22 11:06:30', 1),
(67, 67, 3, 300.00, 1, '2026-06-22 11:06:30', 1),
(68, 68, 1, 800.00, 1, '2026-06-22 11:06:30', 1),
(69, 69, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(70, 70, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(71, 71, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(72, 72, 3, 800.00, 1, '2026-06-22 11:06:30', 1),
(73, 73, 4, 300.00, 1, '2026-06-22 11:06:30', 1),
(74, 74, 1, 800.00, 1, '2026-06-22 11:06:30', 1),
(75, 75, 2, 300.00, 1, '2026-06-22 11:06:30', 1),
(76, 76, 2, 300.00, 1, '2026-06-22 11:06:30', 1),
(77, 77, 2, 300.00, 1, '2026-06-22 11:06:30', 1),
(78, 78, 2, 500.00, 1, '2026-06-22 11:06:30', 1),
(79, 79, 3, 500.00, 1, '2026-06-22 11:06:30', 1),
(80, 80, 1, 800.00, 1, '2026-06-22 11:06:30', 1),
(81, 81, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(82, 82, 3, 300.00, 1, '2026-06-22 11:06:30', 1),
(83, 83, 3, 300.00, 1, '2026-06-22 11:06:30', 1),
(84, 84, 4, 500.00, 1, '2026-06-22 11:06:30', 1),
(85, 85, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(86, 86, 4, 500.00, 1, '2026-06-22 11:06:30', 1),
(87, 87, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(88, 88, 3, 500.00, 1, '2026-06-22 11:06:30', 1),
(89, 89, 2, 500.00, 1, '2026-06-22 11:06:30', 1),
(90, 90, 2, 300.00, 1, '2026-06-22 11:06:30', 1),
(91, 91, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(92, 92, 1, 500.00, 1, '2026-06-22 11:06:30', 1),
(93, 93, 4, 500.00, 1, '2026-06-22 11:06:30', 1),
(94, 94, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(95, 95, 2, 800.00, 1, '2026-06-22 11:06:30', 1),
(96, 96, 4, 800.00, 1, '2026-06-22 11:06:30', 1),
(97, 97, 4, 300.00, 1, '2026-06-22 11:06:30', 1),
(98, 98, 3, 500.00, 1, '2026-06-22 11:06:30', 1),
(99, 99, 1, 500.00, 1, '2026-06-22 11:06:30', 1),
(100, 100, 2, 300.00, 1, '2026-06-22 11:06:30', 1),
(101, 101, 3, 800.00, 1, '2026-06-22 11:06:30', 1),
(102, 102, 1, 300.00, 1, '2026-06-22 11:06:30', 1),
(103, 103, 3, 500.00, 1, '2026-06-22 11:06:30', 1),
(104, 104, 2, 300.00, 1, '2026-06-22 11:06:30', 1),
(105, 105, 2, 500.00, 1, '2026-06-22 11:06:30', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `templeados`
--

CREATE TABLE `templeados` (
  `carnetEmpleado` int(11) NOT NULL,
  `idUsuario` int(10) UNSIGNED NOT NULL,
  `idSucursal` int(10) UNSIGNED NOT NULL,
  `sueldo` decimal(10,2) NOT NULL,
  `especialidad` int(11) NOT NULL,
  `fechaContratoInicio` date NOT NULL,
  `fechaContratoFin` date DEFAULT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `templeados`
--

INSERT INTO `templeados` (`carnetEmpleado`, `idUsuario`, `idSucursal`, `sueldo`, `especialidad`, `fechaContratoInicio`, `fechaContratoFin`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1001, 3, 1, 1500.00, 1, '2024-01-15', NULL, 1, '2026-06-22 11:06:29', 1),
(1002, 4, 1, 2000.00, 2, '2024-02-01', NULL, 1, '2026-06-22 11:06:29', 1),
(2001, 10, 1, 1800.00, 1, '2025-06-01', NULL, 1, '2026-06-22 11:06:29', 1),
(2002, 11, 1, 2200.00, 3, '2025-03-15', NULL, 1, '2026-06-22 11:06:29', 1),
(2003, 12, 1, 2500.00, 4, '2025-09-01', NULL, 1, '2026-06-22 11:06:29', 1),
(12345678, 19, 3, 3000.00, 1, '2026-06-23', NULL, 1, '2026-06-23 19:33:05', 1),
(88888888, 20, 3, 2800.00, 1, '2026-06-24', NULL, 1, '2026-06-24 13:05:33', 1),
(88998558, 13, 2, 2500.00, 1, '2026-06-22', NULL, 1, '2026-06-22 15:22:57', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tequipamientos`
--

CREATE TABLE `tequipamientos` (
  `idEquipo` int(10) UNSIGNED NOT NULL,
  `idSucursal` int(10) UNSIGNED NOT NULL,
  `idMarca` int(10) UNSIGNED NOT NULL,
  `nombreEquipo` varchar(100) NOT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `fechaAdquisicion` date DEFAULT NULL,
  `estadoEquipo` enum('Operativo','En Mantenimiento','Fuera de Servicio','De Baja') NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tequipamientos`
--

INSERT INTO `tequipamientos` (`idEquipo`, `idSucursal`, `idMarca`, `nombreEquipo`, `modelo`, `fechaAdquisicion`, `estadoEquipo`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 1, 1, 'Cinta de Correr', 'Run 700', '2025-02-17', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(2, 1, 2, 'Cinta de Correr', 'TR 9500', '2024-11-26', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(3, 1, 4, 'Cinta de Correr', 'E1', '2024-12-17', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(4, 1, 2, 'Bicicleta Estatica', 'IC5', '2025-04-11', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(5, 1, 1, 'Bicicleta Estatica', 'Excite Top', '2025-09-23', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(6, 1, 3, 'Bicicleta Estatica', 'UB 100', '2025-02-13', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(7, 1, 1, 'Eliptica', 'Cross Personal', '2025-04-18', 'En Mantenimiento', 1, '2026-06-22 11:06:30', 1),
(8, 1, 2, 'Eliptica', 'Eliptical EFX', '2025-03-04', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(9, 1, 3, 'Eliptica', 'E30', '2025-05-24', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(10, 1, 1, 'Remo', 'Skillbike', '2024-10-02', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(11, 1, 2, 'Remo', 'RW100', '2024-01-14', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(12, 1, 4, 'Mancuernas', 'Pro 20kg', '2024-09-16', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(13, 1, 4, 'Mancuernas', 'Adjustable 25kg', '2024-10-08', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(14, 1, 3, 'Mancuernas', 'Rubber Hex 15kg', '2024-05-18', 'De Baja', 1, '2026-06-22 11:06:30', 1),
(15, 1, 4, 'Pesa Rusa', 'Kettlebell 16kg', '2026-10-10', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(16, 1, 4, 'Pesa Rusa', 'Kettlebell 24kg', '2024-12-01', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(17, 1, 3, 'Polea Alta', 'Lat Pulldown', '2025-07-04', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(18, 1, 3, 'Prensa de Piernas', 'Leg Press 45', '2024-04-16', 'En Mantenimiento', 1, '2026-06-22 11:06:30', 1),
(19, 1, 4, 'Banco Plano', 'Pro FB', '2025-06-20', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(20, 1, 4, 'Banco Inclinado', 'Adjustable AB', '2025-06-03', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(21, 1, 3, 'Sillon de Cuadriceps', 'Leg Extension', '2025-12-17', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(22, 1, 3, 'Sillon de Femoral', 'Leg Curl', '2024-07-20', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(23, 1, 2, 'Maquina Multipower', 'Smith Machine', '2025-04-10', 'En Mantenimiento', 1, '2026-06-22 11:06:30', 1),
(24, 1, 4, 'Jaula de Sentadillas', 'Squat Rack Pro', '2024-10-25', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(25, 1, 4, 'Barra Olimpica', '20kg Olympic', '2024-10-27', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(26, 1, 4, 'Barra Olimpica', '15kg Women', '2025-03-07', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(27, 1, 4, 'Disco Peso', 'Bumper 10kg', '2025-03-03', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(28, 1, 4, 'Disco Peso', 'Bumper 20kg', '2025-09-01', 'De Baja', 1, '2026-06-22 11:06:30', 1),
(29, 1, 4, 'Disco Peso', 'Iron 5kg', '2024-06-21', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(30, 1, 4, 'Disco Peso', 'Iron 15kg', '2025-06-25', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(31, 1, 4, 'Cuerda para Saltar', 'Speed Rope', '2025-04-16', 'De Baja', 1, '2026-06-22 11:06:30', 1),
(32, 1, 1, 'Colchoneta Yoga', 'Pro 6mm', '2025-01-11', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(33, 1, 4, 'Pelota Medicinal', 'Med Ball 6kg', '2025-10-15', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(34, 1, 3, 'Pelota Suiza', 'Exercise Ball 75cm', '2026-10-08', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(35, 1, 2, 'TRX', 'Suspension Pro', '2025-10-28', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(36, 1, 3, 'Polea Baja', 'Low Row', '2026-03-19', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(37, 1, 2, 'Escaladora', 'StairMaster 7000', '2024-05-21', 'En Mantenimiento', 1, '2026-06-22 11:06:30', 1),
(38, 1, 1, 'Bicicleta Reclinada', 'Recumbent R30', '2024-04-05', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(39, 1, 1, 'Bicicleta Spinning', 'Spinner Pro', '2024-06-23', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(40, 1, 2, 'Bicicleta Spinning', 'Sprint 8', '2024-07-09', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(41, 1, 3, 'Bicicleta Spinning', 'SB 100', '2025-06-23', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(42, 1, 3, 'Maquina de Abdominales', 'Ab Crunch', '2024-03-25', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(43, 1, 3, 'Maquina de Gluteos', 'Glute Bridge', '2024-02-15', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(44, 1, 3, 'Maquina de Hombros', 'Shoulder Press', '2025-05-04', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(45, 1, 3, 'Maquina de Pecho', 'Chest Press', '2024-10-08', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(46, 1, 3, 'Maquina de Espalda', 'Row Back', '2025-11-25', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(47, 1, 1, 'Fitness Bike', 'Excite Recline', '2024-05-20', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(48, 1, 3, 'Escaladora', 'Gauntlet', '2026-04-24', 'En Mantenimiento', 1, '2026-06-22 11:06:30', 1),
(49, 1, 2, 'Caminadora Curva', 'Curve Runner', '2026-12-08', 'Operativo', 1, '2026-06-22 11:06:30', 1),
(50, 1, 4, 'Ventilador Aspirador', 'Industrial Fan', '2025-07-23', 'Operativo', 1, '2026-06-22 11:06:30', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tesquemasueldos`
--

CREATE TABLE `tesquemasueldos` (
  `idEsquemaSueldo` int(10) UNSIGNED NOT NULL,
  `carnetEmpleado` int(11) NOT NULL,
  `modalidadPago` varchar(50) NOT NULL,
  `montoBase` decimal(10,2) NOT NULL,
  `tarifaHoraOClase` int(11) NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tesquemasueldos`
--

INSERT INTO `tesquemasueldos` (`idEsquemaSueldo`, `carnetEmpleado`, `modalidadPago`, `montoBase`, `tarifaHoraOClase`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 1001, 'Mensual', 1500.00, 0, 1, '2026-06-22 11:06:29', 1),
(2, 1001, 'Bonificacion', 200.00, 0, 1, '2026-06-22 11:06:29', 1),
(3, 1001, 'Horas Extra', 0.00, 25, 1, '2026-06-22 11:06:29', 1),
(4, 1001, 'Comision', 0.00, 50, 1, '2026-06-22 11:06:29', 1),
(5, 1002, 'Mensual', 2000.00, 0, 1, '2026-06-22 11:06:29', 1),
(6, 1002, 'Bonificacion', 300.00, 0, 1, '2026-06-22 11:06:29', 1),
(7, 1002, 'Horas Extra', 0.00, 35, 1, '2026-06-22 11:06:29', 1),
(8, 1002, 'Comision', 0.00, 75, 1, '2026-06-22 11:06:29', 1),
(9, 1001, 'Mensual', 1600.00, 0, 1, '2026-06-22 11:06:29', 1),
(10, 1002, 'Mensual', 2100.00, 0, 1, '2026-06-22 11:06:29', 1),
(11, 1001, 'Bono Productividad', 150.00, 0, 1, '2026-06-22 11:06:29', 1),
(12, 1002, 'Bono Productividad', 200.00, 0, 1, '2026-06-22 11:06:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `thorariolaborales`
--

CREATE TABLE `thorariolaborales` (
  `idHorario` int(10) UNSIGNED NOT NULL,
  `carnetEmpleado` int(11) NOT NULL,
  `diaSemana` varchar(20) NOT NULL,
  `horaEntradaEsperada` time NOT NULL,
  `horaSalidaEsperada` time NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `thorariolaborales`
--

INSERT INTO `thorariolaborales` (`idHorario`, `carnetEmpleado`, `diaSemana`, `horaEntradaEsperada`, `horaSalidaEsperada`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 1001, 'Lunes', '08:00:00', '17:00:00', 1, '2026-06-22 11:06:29', 1),
(2, 1001, 'Martes', '08:00:00', '17:00:00', 1, '2026-06-22 11:06:29', 1),
(3, 1001, 'Miercoles', '08:00:00', '17:00:00', 1, '2026-06-22 11:06:29', 1),
(4, 1001, 'Jueves', '08:00:00', '17:00:00', 1, '2026-06-22 11:06:29', 1),
(5, 1001, 'Viernes', '08:00:00', '17:00:00', 1, '2026-06-22 11:06:29', 1),
(6, 1001, 'Sabado', '08:00:00', '12:00:00', 1, '2026-06-22 11:06:29', 1),
(7, 1002, 'Lunes', '06:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(8, 1002, 'Martes', '06:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(9, 1002, 'Miercoles', '06:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(10, 1002, 'Jueves', '06:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(11, 1002, 'Viernes', '06:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(12, 1002, 'Sabado', '06:00:00', '10:00:00', 1, '2026-06-22 11:06:29', 1),
(13, 2001, 'Lunes', '14:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(14, 2001, 'Martes', '14:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(15, 2001, 'Miercoles', '14:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(16, 2001, 'Jueves', '14:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(17, 2001, 'Viernes', '14:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(18, 2001, 'Sabado', '10:00:00', '18:00:00', 1, '2026-06-22 11:06:29', 1),
(19, 2002, 'Lunes', '07:00:00', '15:00:00', 1, '2026-06-22 11:06:29', 1),
(20, 2002, 'Martes', '07:00:00', '15:00:00', 1, '2026-06-22 11:06:29', 1),
(21, 2002, 'Miercoles', '07:00:00', '15:00:00', 1, '2026-06-22 11:06:29', 1),
(22, 2002, 'Jueves', '07:00:00', '15:00:00', 1, '2026-06-22 11:06:29', 1),
(23, 2002, 'Viernes', '07:00:00', '15:00:00', 1, '2026-06-22 11:06:29', 1),
(24, 2002, 'Sabado', '08:00:00', '12:00:00', 1, '2026-06-22 11:06:29', 1),
(25, 2003, 'Lunes', '09:00:00', '18:00:00', 1, '2026-06-22 11:06:29', 1),
(26, 2003, 'Martes', '09:00:00', '18:00:00', 1, '2026-06-22 11:06:29', 1),
(27, 2003, 'Miercoles', '09:00:00', '18:00:00', 1, '2026-06-22 11:06:29', 1),
(28, 2003, 'Jueves', '09:00:00', '18:00:00', 1, '2026-06-22 11:06:29', 1),
(29, 2003, 'Viernes', '09:00:00', '18:00:00', 1, '2026-06-22 11:06:29', 1),
(30, 2003, 'Sabado', '08:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(31, 1001, 'Domingo', '08:00:00', '12:00:00', 1, '2026-06-22 11:06:29', 1),
(32, 2002, 'Domingo', '08:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(33, 1002, 'Domingo', '08:00:00', '12:00:00', 1, '2026-06-22 11:06:29', 1),
(34, 2001, 'Domingo', '10:00:00', '16:00:00', 1, '2026-06-22 11:06:29', 1),
(35, 2003, 'Domingo', '09:00:00', '15:00:00', 1, '2026-06-22 11:06:29', 1),
(36, 1001, 'Lunes', '13:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(37, 1001, 'Martes', '13:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(38, 1002, 'Lunes', '14:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(39, 1002, 'Martes', '14:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(40, 1002, 'Miercoles', '14:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(41, 2001, 'Lunes', '06:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(42, 2001, 'Martes', '06:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(43, 2002, 'Lunes', '15:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(44, 2002, 'Martes', '15:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(45, 2003, 'Lunes', '06:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(46, 2003, 'Martes', '06:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(47, 2003, 'Miercoles', '06:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(48, 1001, 'Miercoles', '13:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(49, 1002, 'Jueves', '14:00:00', '22:00:00', 1, '2026-06-22 11:06:29', 1),
(50, 2001, 'Miercoles', '06:00:00', '14:00:00', 1, '2026-06-22 11:06:29', 1),
(51, 88998558, 'Lunes', '08:00:00', '12:00:00', 1, '2026-06-22 16:52:53', 1),
(52, 12345678, 'Lunes', '12:00:00', '15:00:00', 1, '2026-06-24 12:42:23', 1),
(53, 88888888, 'Lunes', '09:30:00', '12:30:00', 1, '2026-06-24 13:08:27', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tmantenimientopreventivos`
--

CREATE TABLE `tmantenimientopreventivos` (
  `idMantenimiento` int(10) UNSIGNED NOT NULL,
  `idEquipo` int(10) UNSIGNED NOT NULL,
  `fechaProgramada` date NOT NULL,
  `fechaRealizada` date DEFAULT NULL,
  `descripcionMantenimiento` text DEFAULT NULL,
  `costoMantenimiento` decimal(10,2) DEFAULT NULL,
  `tecnicoAsignado` varchar(150) DEFAULT NULL,
  `estadoMantenimiento` enum('Pendiente','Realizado','Cancelado') NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tmantenimientopreventivos`
--

INSERT INTO `tmantenimientopreventivos` (`idMantenimiento`, `idEquipo`, `fechaProgramada`, `fechaRealizada`, `descripcionMantenimiento`, `costoMantenimiento`, `tecnicoAsignado`, `estadoMantenimiento`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 7, '2026-06-20', NULL, 'Mantenimiento preventivo programado - 2', 489.00, 'Servicio tecnico Matrix', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(2, 7, '2026-06-08', NULL, 'Mantenimiento preventivo programado - 5', 141.00, 'Personal interno', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(3, 18, '2026-06-07', NULL, 'Mantenimiento preventivo programado - 1', 294.00, 'Servicio tecnico Matrix', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(4, 18, '2026-06-06', NULL, 'Mantenimiento preventivo programado - 5', 438.00, 'Tecnico externo', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(5, 23, '2026-06-20', NULL, 'Mantenimiento preventivo programado - 2', 294.00, 'Tecnico externo', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(6, 23, '2026-06-04', NULL, 'Mantenimiento preventivo programado - 5', 383.00, 'Personal interno', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(7, 37, '2026-06-21', NULL, 'Mantenimiento preventivo programado - 2', 86.00, 'Tecnico externo', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(8, 37, '2026-06-20', NULL, 'Mantenimiento preventivo programado - 5', 566.00, 'Personal interno', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(9, 48, '2026-06-24', NULL, 'Mantenimiento preventivo programado - 1', 186.00, 'Proveedor oficial', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(10, 30, '2026-02-27', NULL, 'Mantenimiento preventivo de rutina - 1', 239.00, 'Personal interno', 'Cancelado', 1, '2026-06-22 11:06:30', 1),
(11, 11, '2026-04-12', '2026-04-12', 'Mantenimiento preventivo de rutina - 2', 238.00, 'Personal interno', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(12, 19, '2026-03-19', '2026-03-19', 'Mantenimiento preventivo de rutina - 5', 337.00, 'Personal interno', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(13, 46, '2026-04-22', '2026-04-26', 'Mantenimiento preventivo de rutina - 3', 88.00, 'Personal interno', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(14, 37, '2026-02-08', '2026-02-10', 'Mantenimiento preventivo de rutina - 4', 449.00, 'Tecnico externo', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(15, 44, '2026-05-07', NULL, 'Mantenimiento preventivo de rutina - 2', 222.00, 'Tecnico externo', 'Cancelado', 1, '2026-06-22 11:06:30', 1),
(16, 15, '2026-05-09', NULL, 'Mantenimiento preventivo de rutina - 4', 216.00, 'Tecnico externo', 'Cancelado', 1, '2026-06-22 11:06:30', 1),
(17, 35, '2026-05-21', '2026-05-24', 'Mantenimiento preventivo de rutina - 1', 362.00, 'Tecnico externo', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(18, 11, '2026-04-28', '2026-04-30', 'Mantenimiento preventivo de rutina - 2', 162.00, 'Personal interno', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(19, 14, '2026-05-11', '2026-05-12', 'Mantenimiento preventivo de rutina - 5', 88.00, 'Proveedor oficial', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(20, 42, '2026-05-10', '2026-05-11', 'Mantenimiento preventivo de rutina - 5', 413.00, 'Servicio tecnico Matrix', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(21, 43, '2026-06-08', '2026-06-09', 'Mantenimiento preventivo de rutina - 5', 221.00, 'Proveedor oficial', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(22, 16, '2026-04-26', '2026-05-01', 'Mantenimiento preventivo de rutina - 3', 261.00, 'Servicio tecnico Matrix', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(23, 34, '2026-05-15', '2026-05-17', 'Mantenimiento preventivo de rutina - 5', 213.00, 'Personal interno', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(24, 4, '2026-05-15', '2026-05-16', 'Mantenimiento preventivo de rutina - 3', 162.00, 'Tecnico externo', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(25, 17, '2026-04-09', '2026-04-09', 'Mantenimiento preventivo de rutina - 3', 247.00, 'Tecnico externo', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(26, 40, '2026-03-05', '2026-03-07', 'Mantenimiento preventivo de rutina - 2', 66.00, 'Personal interno', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(27, 17, '2026-05-26', '2026-05-27', 'Mantenimiento preventivo de rutina - 3', 241.00, 'Servicio tecnico Matrix', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(28, 38, '2026-05-06', '2026-05-11', 'Mantenimiento preventivo de rutina - 5', 252.00, 'Proveedor oficial', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(29, 42, '2026-04-01', '2026-04-01', 'Mantenimiento preventivo de rutina - 4', 426.00, 'Proveedor oficial', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(30, 43, '2026-05-30', '2026-06-02', 'Mantenimiento preventivo de rutina - 2', 220.00, 'Tecnico externo', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(31, 14, '2026-06-05', '2026-06-08', 'Mantenimiento preventivo de rutina - 2', 302.00, 'Servicio tecnico Matrix', 'Realizado', 1, '2026-06-22 11:06:30', 1),
(32, 25, '2026-03-23', '2026-03-28', 'Mantenimiento preventivo de rutina - 5', 348.00, 'Servicio tecnico Matrix', 'Realizado', 1, '2026-06-22 11:06:30', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tmarcas`
--

CREATE TABLE `tmarcas` (
  `idMarca` int(10) UNSIGNED NOT NULL,
  `nombreMarca` varchar(100) NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tmarcas`
--

INSERT INTO `tmarcas` (`idMarca`, `nombreMarca`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 'Technogym', 1, '2026-06-22 11:06:29', 1),
(2, 'Life Fitness', 1, '2026-06-22 11:06:29', 1),
(3, 'Matrix', 1, '2026-06-22 11:06:29', 1),
(4, 'Hammer Strength', 1, '2026-06-22 11:06:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tmembresias`
--

CREATE TABLE `tmembresias` (
  `idMembresia` int(10) UNSIGNED NOT NULL,
  `idPlan` int(10) UNSIGNED NOT NULL,
  `carnetSocio` int(10) UNSIGNED NOT NULL,
  `idSucursal` int(10) UNSIGNED NOT NULL,
  `fechaInicioMembresia` date NOT NULL,
  `fechaFinMembresia` date NOT NULL,
  `estadoMembresia` varchar(50) NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tmembresias`
--

INSERT INTO `tmembresias` (`idMembresia`, `idPlan`, `carnetSocio`, `idSucursal`, `fechaInicioMembresia`, `fechaFinMembresia`, `estadoMembresia`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 1, 6700001, 1, '2026-01-01', '2026-06-30', 'Activa', 1, '2026-06-22 11:06:29', 1),
(2, 2, 6700002, 1, '2026-01-15', '2026-07-15', 'Activa', 1, '2026-06-22 11:06:29', 1),
(3, 3, 6700003, 1, '2026-02-01', '2026-08-01', 'Activa', 1, '2026-06-22 11:06:29', 1),
(4, 1, 6700004, 1, '2025-12-01', '2026-05-31', 'Vencida', 1, '2026-06-22 11:06:29', 1),
(5, 2, 6700005, 1, '2026-03-01', '2026-04-01', 'Vencida', 1, '2026-06-22 11:06:29', 1),
(6, 2, 6700005, 1, '2026-04-15', '2026-10-15', 'Activa', 1, '2026-06-22 11:06:29', 1),
(7, 2, 54652132, 2, '2026-06-22', '2026-07-22', 'Activa', 1, '2026-06-22 15:34:32', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tmetodopagos`
--

CREATE TABLE `tmetodopagos` (
  `idMetodoPago` int(10) UNSIGNED NOT NULL,
  `nombreMetodoPago` varchar(50) NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tmetodopagos`
--

INSERT INTO `tmetodopagos` (`idMetodoPago`, `nombreMetodoPago`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 'Efectivo', 1, '2026-06-22 11:06:29', 1),
(2, 'Tarjeta', 1, '2026-06-22 11:06:29', 1),
(3, 'QR', 1, '2026-06-22 11:06:29', 1),
(4, 'Transferencia', 1, '2026-06-22 11:06:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tnotificaciones`
--

CREATE TABLE `tnotificaciones` (
  `idNotificacion` int(10) UNSIGNED NOT NULL,
  `carnetSocio` int(10) UNSIGNED NOT NULL,
  `tipoNotificacion` varchar(50) NOT NULL,
  `mensaje` text NOT NULL,
  `canal` varchar(50) NOT NULL,
  `fechaEnvio` date NOT NULL,
  `estado` varchar(50) NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tnotificaciones`
--

INSERT INTO `tnotificaciones` (`idNotificacion`, `carnetSocio`, `tipoNotificacion`, `mensaje`, `canal`, `fechaEnvio`, `estado`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 6700001, 'Recordatorio', 'Su membresia esta por vencer el 30/06/2026. Renueve ahora.', 'Correo', '2026-06-25', 'Enviado', 1, '2026-06-22 11:06:29', 1),
(2, 6700002, 'Bienvenida', 'Bienvenido a GimnasioV1! Su codigo de acceso es ACC6700002.', 'WhatsApp', '2026-01-15', 'Enviado', 1, '2026-06-22 11:06:29', 1),
(3, 6700003, 'Promocion', 'Tenemos un descuento especial en planes premium para usted.', 'Correo', '2026-03-01', 'Enviado', 1, '2026-06-22 11:06:29', 1),
(4, 6700004, 'Alerta', 'Su membresia ha vencido el 31/05/2026. Acuda a renovar.', 'SMS', '2026-06-01', 'Pendiente', 1, '2026-06-22 11:06:29', 1),
(5, 6700005, 'Recordatorio', 'Tiene 2 strikes acumulados. Recuerde las normas del gimnasio.', 'Correo', '2026-05-20', 'Enviado', 1, '2026-06-22 11:06:29', 1),
(6, 6700001, 'Promocion', 'Clase gratis de Yoga este sabado!', 'WhatsApp', '2026-04-10', 'Enviado', 1, '2026-06-22 11:06:29', 1),
(7, 6700002, 'Alerta', 'Penalizacion registrada por inasistencia a clase reservada.', 'Correo', '2026-03-10', 'Enviado', 1, '2026-06-22 11:06:29', 1),
(8, 6700004, 'Recordatorio', 'Su plan basico se ha completado. Contrate uno nuevo.', 'Correo', '2026-06-05', 'Pendiente', 1, '2026-06-22 11:06:29', 1),
(9, 6700003, 'Bienvenida', 'Bienvenido! Su codigo ACC6700003 ya esta activo.', 'WhatsApp', '2026-02-01', 'Enviado', 1, '2026-06-22 11:06:29', 1),
(10, 6700005, 'Promocion', 'Refiere a un amigo y obtenga 1 mes gratis!', 'Correo', '2026-04-01', 'Enviado', 1, '2026-06-22 11:06:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tpagosueldos`
--

CREATE TABLE `tpagosueldos` (
  `idPagoSueldo` int(10) UNSIGNED NOT NULL,
  `carnetEmpleado` int(11) NOT NULL,
  `fechaPago` datetime NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tpagosueldos`
--

INSERT INTO `tpagosueldos` (`idPagoSueldo`, `carnetEmpleado`, `fechaPago`, `monto`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 1001, '2026-01-15 12:00:00', 1541.00, 1, '2026-06-22 11:06:29', 1),
(2, 1001, '2026-02-15 12:00:00', 1650.00, 1, '2026-06-22 11:06:29', 1),
(3, 1001, '2026-03-15 12:00:00', 1512.00, 1, '2026-06-22 11:06:29', 1),
(4, 1001, '2026-04-15 12:00:00', 1487.00, 1, '2026-06-22 11:06:29', 1),
(5, 1001, '2026-05-15 12:00:00', 1463.00, 1, '2026-06-22 11:06:29', 1),
(6, 1001, '2026-06-15 12:00:00', 1575.00, 1, '2026-06-22 11:06:29', 1),
(7, 1002, '2026-01-15 12:00:00', 2071.00, 1, '2026-06-22 11:06:29', 1),
(8, 1002, '2026-02-15 12:00:00', 2175.00, 1, '2026-06-22 11:06:29', 1),
(9, 1002, '2026-03-15 12:00:00', 1979.00, 1, '2026-06-22 11:06:29', 1),
(10, 1002, '2026-04-15 12:00:00', 2063.00, 1, '2026-06-22 11:06:29', 1),
(11, 1002, '2026-05-15 12:00:00', 1947.00, 1, '2026-06-22 11:06:29', 1),
(12, 1002, '2026-06-15 12:00:00', 1972.00, 1, '2026-06-22 11:06:29', 1),
(13, 2001, '2026-01-15 12:00:00', 1979.00, 1, '2026-06-22 11:06:29', 1),
(14, 2001, '2026-02-15 12:00:00', 1911.00, 1, '2026-06-22 11:06:29', 1),
(15, 2001, '2026-03-15 12:00:00', 1733.00, 1, '2026-06-22 11:06:29', 1),
(16, 2001, '2026-04-15 12:00:00', 1713.00, 1, '2026-06-22 11:06:29', 1),
(17, 2001, '2026-05-15 12:00:00', 1975.00, 1, '2026-06-22 11:06:29', 1),
(18, 2001, '2026-06-15 12:00:00', 1703.00, 1, '2026-06-22 11:06:29', 1),
(19, 2002, '2026-01-15 12:00:00', 2307.00, 1, '2026-06-22 11:06:29', 1),
(20, 2002, '2026-02-15 12:00:00', 2354.00, 1, '2026-06-22 11:06:29', 1),
(21, 2002, '2026-03-15 12:00:00', 2140.00, 1, '2026-06-22 11:06:29', 1),
(22, 2002, '2026-04-15 12:00:00', 2257.00, 1, '2026-06-22 11:06:29', 1),
(23, 2002, '2026-05-15 12:00:00', 2230.00, 1, '2026-06-22 11:06:29', 1),
(24, 2002, '2026-06-15 12:00:00', 2369.00, 1, '2026-06-22 11:06:29', 1),
(25, 2003, '2026-01-15 12:00:00', 2557.00, 1, '2026-06-22 11:06:29', 1),
(26, 2003, '2026-02-15 12:00:00', 2662.00, 1, '2026-06-22 11:06:29', 1),
(27, 2003, '2026-03-15 12:00:00', 2655.00, 1, '2026-06-22 11:06:29', 1),
(28, 2003, '2026-04-15 12:00:00', 2421.00, 1, '2026-06-22 11:06:29', 1),
(29, 2003, '2026-05-15 12:00:00', 2605.00, 1, '2026-06-22 11:06:29', 1),
(30, 2003, '2026-06-15 12:00:00', 2499.00, 1, '2026-06-22 11:06:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tpenalizaciones`
--

CREATE TABLE `tpenalizaciones` (
  `idPenalizacion` int(10) UNSIGNED NOT NULL,
  `carnetSocio` int(10) UNSIGNED NOT NULL,
  `idReserva` int(10) UNSIGNED DEFAULT NULL,
  `fecha` date NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tpenalizaciones`
--

INSERT INTO `tpenalizaciones` (`idPenalizacion`, `carnetSocio`, `idReserva`, `fecha`, `estado`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 6700001, NULL, '2026-02-15', 1, 1, '2026-06-22 11:06:29', 1),
(2, 6700002, NULL, '2026-03-10', 0, 1, '2026-06-22 11:06:29', 1),
(3, 6700003, NULL, '2026-04-20', 1, 1, '2026-06-22 11:06:29', 1),
(4, 6700005, NULL, '2026-05-05', 1, 1, '2026-06-22 11:06:29', 1),
(5, 6700001, NULL, '2026-06-01', 0, 1, '2026-06-22 11:06:29', 1),
(6, 6700004, NULL, '2026-03-25', 1, 1, '2026-06-22 11:06:29', 1),
(7, 6700003, NULL, '2026-01-30', 1, 1, '2026-06-22 11:06:29', 1),
(8, 6700005, NULL, '2026-02-28', 1, 1, '2026-06-22 11:06:29', 1),
(9, 6700002, NULL, '2026-05-15', 1, 1, '2026-06-22 11:06:29', 1),
(10, 6700001, NULL, '2026-04-10', 0, 1, '2026-06-22 11:06:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tplanes`
--

CREATE TABLE `tplanes` (
  `idPlan` int(10) UNSIGNED NOT NULL,
  `nombrePlan` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `costoPlan` decimal(10,2) NOT NULL,
  `duracionDias` int(11) NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tplanes`
--

INSERT INTO `tplanes` (`idPlan`, `nombrePlan`, `descripcion`, `costoPlan`, `duracionDias`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 'Plan Básico', 'Acceso a instalaciones básicas de 6:00 a 22:00', 300.00, 30, 1, '2026-06-22 11:06:29', 1),
(2, 'Plan Premium', 'Acceso completo a instalaciones y clases grupales', 500.00, 30, 1, '2026-06-22 11:06:29', 1),
(3, 'Plan VIP', 'Acceso ilimitado con servicios adicionales y entrenador personal', 800.00, 30, 1, '2026-06-22 11:06:29', 1),
(4, 'Mayores de edad', 'Para los de la 3ra edad', 100.00, 30, 1, '2026-06-22 15:46:29', 1),
(5, 'Plan Estudiantil', 'Para niños de 8 a 17 años', 50.00, 20, 1, '2026-06-23 19:35:26', 1),
(6, 'Plan madres', 'Madres primeras', 50.00, 30, 1, '2026-06-24 13:24:31', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trecibos`
--

CREATE TABLE `trecibos` (
  `idRecibo` int(10) UNSIGNED NOT NULL,
  `idCaja` int(10) UNSIGNED NOT NULL,
  `idMembresia` int(10) UNSIGNED NOT NULL,
  `nroRecibo` varchar(50) NOT NULL,
  `montoTotal` decimal(10,2) NOT NULL,
  `fechaPago` datetime NOT NULL,
  `estadoRecibo` enum('Emitido','Anulado') NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `trecibos`
--

INSERT INTO `trecibos` (`idRecibo`, `idCaja`, `idMembresia`, `nroRecibo`, `montoTotal`, `fechaPago`, `estadoRecibo`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 1, 1, 'REC-000002', 800.00, '2026-01-01 11:03:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(2, 2, 6, 'REC-000003', 500.00, '2026-01-02 09:17:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(3, 3, 6, 'REC-000004', 300.00, '2026-01-05 16:45:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(4, 4, 2, 'REC-000005', 500.00, '2026-01-06 17:16:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(5, 5, 2, 'REC-000006', 800.00, '2026-01-07 15:04:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(6, 6, 1, 'REC-000007', 500.00, '2026-01-08 14:02:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(7, 7, 2, 'REC-000008', 300.00, '2026-01-09 11:29:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(8, 8, 6, 'REC-000009', 800.00, '2026-01-12 17:38:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(9, 9, 4, 'REC-000010', 800.00, '2026-01-13 14:32:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(10, 10, 2, 'REC-000011', 300.00, '2026-01-14 15:22:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(11, 11, 4, 'REC-000012', 800.00, '2026-01-15 12:36:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(12, 12, 3, 'REC-000013', 800.00, '2026-01-16 11:27:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(13, 13, 3, 'REC-000014', 800.00, '2026-01-19 09:05:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(14, 14, 1, 'REC-000015', 300.00, '2026-01-20 12:52:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(15, 15, 1, 'REC-000016', 300.00, '2026-01-21 13:01:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(16, 16, 4, 'REC-000017', 300.00, '2026-01-22 17:17:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(17, 17, 4, 'REC-000018', 800.00, '2026-01-23 12:50:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(18, 18, 6, 'REC-000019', 500.00, '2026-01-26 17:02:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(19, 19, 4, 'REC-000020', 300.00, '2026-01-27 14:46:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(20, 20, 1, 'REC-000021', 500.00, '2026-01-28 15:31:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(21, 21, 4, 'REC-000022', 500.00, '2026-01-29 14:08:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(22, 22, 4, 'REC-000023', 300.00, '2026-01-30 09:22:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(23, 23, 6, 'REC-000024', 300.00, '2026-02-02 11:04:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(24, 24, 2, 'REC-000025', 500.00, '2026-02-03 11:13:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(25, 25, 4, 'REC-000026', 300.00, '2026-02-04 15:31:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(26, 26, 6, 'REC-000027', 800.00, '2026-02-05 12:01:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(27, 27, 3, 'REC-000028', 500.00, '2026-02-06 15:24:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(28, 28, 6, 'REC-000029', 500.00, '2026-02-09 14:12:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(29, 29, 6, 'REC-000030', 500.00, '2026-02-10 09:05:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(30, 30, 4, 'REC-000031', 300.00, '2026-02-11 14:59:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(31, 31, 1, 'REC-000032', 800.00, '2026-02-12 12:35:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(32, 32, 6, 'REC-000033', 300.00, '2026-02-13 16:36:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(33, 33, 6, 'REC-000034', 500.00, '2026-02-16 12:38:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(34, 34, 3, 'REC-000035', 300.00, '2026-02-17 09:19:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(35, 35, 3, 'REC-000036', 300.00, '2026-02-18 13:07:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(36, 36, 3, 'REC-000037', 500.00, '2026-02-19 14:35:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(37, 37, 1, 'REC-000038', 800.00, '2026-02-20 13:32:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(38, 38, 4, 'REC-000039', 300.00, '2026-02-23 09:58:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(39, 39, 6, 'REC-000040', 500.00, '2026-02-24 10:13:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(40, 40, 3, 'REC-000041', 500.00, '2026-02-25 10:52:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(41, 41, 6, 'REC-000042', 800.00, '2026-02-26 09:28:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(42, 42, 1, 'REC-000043', 800.00, '2026-02-27 13:50:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(43, 43, 2, 'REC-000044', 800.00, '2026-03-02 09:58:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(44, 44, 1, 'REC-000045', 800.00, '2026-03-03 09:51:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(45, 45, 6, 'REC-000046', 800.00, '2026-03-04 11:29:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(46, 46, 3, 'REC-000047', 800.00, '2026-03-05 16:18:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(47, 47, 4, 'REC-000048', 500.00, '2026-03-06 13:51:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(48, 48, 2, 'REC-000049', 800.00, '2026-03-09 15:47:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(49, 49, 6, 'REC-000050', 300.00, '2026-03-10 14:45:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(50, 50, 2, 'REC-000051', 500.00, '2026-03-11 14:46:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(51, 51, 6, 'REC-000052', 500.00, '2026-03-12 16:12:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(52, 52, 6, 'REC-000053', 300.00, '2026-03-13 11:18:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(53, 53, 6, 'REC-000054', 300.00, '2026-03-16 09:58:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(54, 54, 1, 'REC-000055', 300.00, '2026-03-17 11:07:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(55, 55, 3, 'REC-000056', 800.00, '2026-03-18 15:30:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(56, 56, 6, 'REC-000057', 300.00, '2026-03-19 16:19:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(57, 57, 2, 'REC-000058', 300.00, '2026-03-20 14:18:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(58, 58, 2, 'REC-000059', 300.00, '2026-03-23 10:33:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(59, 59, 1, 'REC-000060', 300.00, '2026-03-24 09:27:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(60, 60, 3, 'REC-000061', 800.00, '2026-03-25 14:45:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(61, 61, 6, 'REC-000062', 300.00, '2026-03-26 13:05:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(62, 62, 3, 'REC-000063', 500.00, '2026-03-27 13:25:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(63, 63, 2, 'REC-000064', 500.00, '2026-03-30 17:05:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(64, 64, 6, 'REC-000065', 800.00, '2026-03-31 17:02:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(65, 65, 1, 'REC-000066', 500.00, '2026-04-01 16:23:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(66, 66, 6, 'REC-000067', 500.00, '2026-04-02 12:59:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(67, 67, 2, 'REC-000068', 300.00, '2026-04-03 17:29:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(68, 68, 1, 'REC-000069', 800.00, '2026-04-06 10:17:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(69, 69, 4, 'REC-000070', 800.00, '2026-04-07 12:55:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(70, 70, 4, 'REC-000071', 300.00, '2026-04-08 10:47:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(71, 71, 6, 'REC-000072', 800.00, '2026-04-09 14:40:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(72, 72, 2, 'REC-000073', 800.00, '2026-04-10 12:14:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(73, 73, 3, 'REC-000074', 300.00, '2026-04-13 16:19:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(74, 74, 1, 'REC-000075', 800.00, '2026-04-14 12:27:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(75, 75, 6, 'REC-000076', 300.00, '2026-04-15 17:46:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(76, 76, 1, 'REC-000077', 300.00, '2026-04-16 17:38:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(77, 77, 3, 'REC-000078', 300.00, '2026-04-17 16:50:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(78, 78, 1, 'REC-000079', 500.00, '2026-04-20 13:02:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(79, 79, 2, 'REC-000080', 500.00, '2026-04-21 10:24:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(80, 80, 4, 'REC-000081', 800.00, '2026-04-22 16:26:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(81, 81, 6, 'REC-000082', 800.00, '2026-04-23 15:40:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(82, 82, 3, 'REC-000083', 300.00, '2026-04-24 10:44:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(83, 83, 1, 'REC-000084', 300.00, '2026-04-27 10:06:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(84, 84, 4, 'REC-000085', 500.00, '2026-04-28 14:23:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(85, 85, 2, 'REC-000086', 300.00, '2026-04-29 09:25:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(86, 86, 3, 'REC-000087', 500.00, '2026-04-30 16:39:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(87, 87, 3, 'REC-000088', 800.00, '2026-05-01 11:20:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(88, 88, 4, 'REC-000089', 500.00, '2026-05-04 11:40:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(89, 89, 2, 'REC-000090', 500.00, '2026-05-05 17:08:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(90, 90, 1, 'REC-000091', 300.00, '2026-05-06 17:33:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(91, 91, 3, 'REC-000092', 800.00, '2026-05-07 16:38:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(92, 92, 4, 'REC-000093', 500.00, '2026-05-08 09:47:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(93, 93, 4, 'REC-000094', 500.00, '2026-05-11 16:09:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(94, 94, 1, 'REC-000095', 300.00, '2026-05-12 17:21:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(95, 95, 3, 'REC-000096', 800.00, '2026-05-13 09:50:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(96, 96, 3, 'REC-000097', 800.00, '2026-05-14 12:12:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(97, 97, 1, 'REC-000098', 300.00, '2026-05-15 09:07:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(98, 98, 1, 'REC-000099', 500.00, '2026-05-18 17:23:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(99, 99, 1, 'REC-000100', 500.00, '2026-05-19 11:25:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(100, 100, 6, 'REC-000101', 300.00, '2026-05-20 17:24:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(101, 101, 1, 'REC-000102', 800.00, '2026-05-21 17:23:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(102, 102, 3, 'REC-000103', 300.00, '2026-05-22 12:48:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(103, 103, 6, 'REC-000104', 500.00, '2026-05-25 10:52:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(104, 104, 6, 'REC-000105', 300.00, '2026-05-26 09:50:00', 'Emitido', 1, '2026-06-22 11:06:30', 1),
(105, 105, 1, 'REC-000106', 500.00, '2026-05-27 14:52:00', 'Emitido', 1, '2026-06-22 11:06:30', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `treportefallas`
--

CREATE TABLE `treportefallas` (
  `idReporteFalla` int(10) UNSIGNED NOT NULL,
  `idEquipo` int(10) UNSIGNED NOT NULL,
  `carnetEmpleado` int(11) NOT NULL,
  `fechaReporte` datetime NOT NULL,
  `descripcionFalla` text NOT NULL,
  `gravedad` varchar(50) NOT NULL,
  `estadoReporte` enum('Pendiente','En Revision','Solucionado') NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `treportefallas`
--

INSERT INTO `treportefallas` (`idReporteFalla`, `idEquipo`, `carnetEmpleado`, `fechaReporte`, `descripcionFalla`, `gravedad`, `estadoReporte`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 36, 1001, '2026-06-17 11:05:00', 'Sensor de frecuencia cardiaco no funciona', 'Alta', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(2, 9, 1001, '2026-01-22 11:22:00', 'Pieza suelta en la estructura principal', 'Critica', 'Solucionado', 1, '2026-06-22 11:06:30', 1),
(3, 1, 1001, '2026-01-05 15:24:00', 'El equipo no enciende correctamente', 'Critica', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(4, 1, 1001, '2026-04-14 08:42:00', 'Sensor de frecuencia cardiaco no funciona', 'Critica', 'Solucionado', 1, '2026-06-22 11:06:30', 1),
(5, 1, 1001, '2026-01-22 08:10:00', 'Pernos de sujecion flojos', 'Media', 'Solucionado', 1, '2026-06-22 11:06:30', 1),
(6, 29, 1001, '2026-03-14 18:51:00', 'Fugas de lubricante en el sistema', 'Critica', 'En Revision', 1, '2026-06-22 11:06:30', 1),
(7, 44, 1001, '2026-03-24 13:03:00', 'Ruido anormal durante el funcionamiento', 'Alta', 'En Revision', 1, '2026-06-22 11:06:30', 1),
(8, 16, 1001, '2026-04-17 09:45:00', 'Sensor de frecuencia cardiaco no funciona', 'Critica', 'En Revision', 1, '2026-06-22 11:06:30', 1),
(9, 21, 1001, '2026-02-14 15:45:00', 'Resistencia al movimiento irregular', 'Media', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(10, 44, 1001, '2026-02-21 16:08:00', 'Fugas de lubricante en el sistema', 'Critica', 'Solucionado', 1, '2026-06-22 11:06:30', 1),
(11, 22, 1001, '2026-01-10 14:00:00', 'Fugas de lubricante en el sistema', 'Baja', 'Solucionado', 1, '2026-06-22 11:06:30', 1),
(12, 22, 1001, '2026-06-10 14:09:00', 'Resistencia al movimiento irregular', 'Critica', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(13, 45, 1001, '2026-01-19 18:32:00', 'Pieza suelta en la estructura principal', 'Critica', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(14, 6, 1001, '2026-04-12 17:25:00', 'Pieza suelta en la estructura principal', 'Alta', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(15, 25, 1001, '2026-02-17 13:29:00', 'Pantalla de control no funciona', 'Baja', 'En Revision', 1, '2026-06-22 11:06:30', 1),
(16, 38, 1001, '2026-04-18 15:35:00', 'Resistencia al movimiento irregular', 'Alta', 'En Revision', 1, '2026-06-22 11:06:30', 1),
(17, 25, 1001, '2026-06-17 12:32:00', 'Fugas de lubricante en el sistema', 'Alta', 'Solucionado', 1, '2026-06-22 11:06:30', 1),
(18, 44, 1001, '2026-04-11 12:33:00', 'Pieza suelta en la estructura principal', 'Baja', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(19, 32, 1001, '2026-02-21 18:46:00', 'Fugas de lubricante en el sistema', 'Critica', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(20, 47, 1001, '2026-01-14 08:43:00', 'Pantalla de control no funciona', 'Critica', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(21, 1, 1001, '2026-01-12 09:00:00', 'Pieza suelta en la estructura principal', 'Critica', 'En Revision', 1, '2026-06-22 11:06:30', 1),
(22, 4, 1001, '2026-04-05 15:35:00', 'Fugas de lubricante en el sistema', 'Baja', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(23, 11, 1001, '2026-04-07 10:52:00', 'Pantalla de control no funciona', 'Baja', 'Solucionado', 1, '2026-06-22 11:06:30', 1),
(24, 17, 1001, '2026-05-27 17:25:00', 'El equipo no enciende correctamente', 'Alta', 'En Revision', 1, '2026-06-22 11:06:30', 1),
(25, 7, 1001, '2026-03-24 12:37:00', 'Resistencia al movimiento irregular', 'Critica', 'En Revision', 1, '2026-06-22 11:06:30', 1),
(26, 30, 1001, '2026-03-08 17:53:00', 'Pieza suelta en la estructura principal', 'Critica', 'En Revision', 1, '2026-06-22 11:06:30', 1),
(27, 20, 1001, '2026-02-01 18:07:00', 'Cable de alimentacion danado', 'Media', 'En Revision', 1, '2026-06-22 11:06:30', 1),
(28, 31, 1001, '2026-04-09 17:16:00', 'Sensor de frecuencia cardiaco no funciona', 'Baja', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(29, 37, 1001, '2026-03-10 18:08:00', 'Fugas de lubricante en el sistema', 'Critica', 'Pendiente', 1, '2026-06-22 11:06:30', 1),
(30, 2, 1001, '2026-04-15 18:11:00', 'Cable de alimentacion danado', 'Baja', 'En Revision', 1, '2026-06-22 11:06:30', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `treservas`
--

CREATE TABLE `treservas` (
  `idReserva` int(10) UNSIGNED NOT NULL,
  `idClaseGrupal` int(10) UNSIGNED NOT NULL,
  `carnetSocio` int(10) UNSIGNED NOT NULL,
  `fechaReserva` datetime NOT NULL,
  `estadoReserva` enum('Reservado','Asistido','Cancelado','Penalizado') NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `treservas`
--

INSERT INTO `treservas` (`idReserva`, `idClaseGrupal`, `carnetSocio`, `fechaReserva`, `estadoReserva`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 95, 6700005, '1970-01-01 12:29:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(2, 50, 6700004, '1970-01-01 16:10:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(3, 47, 6700002, '1970-01-01 16:39:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(4, 94, 6700004, '1970-01-01 17:24:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(5, 46, 6700002, '2026-01-30 13:05:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(6, 19, 6700001, '1970-01-01 16:26:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(7, 57, 6700002, '1970-01-01 11:08:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(8, 48, 6700005, '1970-01-01 17:04:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(9, 8, 6700002, '1970-01-01 12:00:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(10, 36, 6700002, '1970-01-01 14:17:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(11, 81, 6700005, '2026-02-24 18:04:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(12, 57, 6700001, '1970-01-01 17:27:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(13, 103, 6700005, '1970-01-01 11:48:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(14, 17, 6700002, '1970-01-01 16:35:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(15, 26, 6700001, '2026-01-19 09:15:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(16, 97, 6700004, '1970-01-01 17:32:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(17, 29, 6700002, '1970-01-01 15:10:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(18, 4, 6700004, '1970-01-01 09:59:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(19, 53, 6700002, '1970-01-01 16:39:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(20, 49, 6700001, '1970-01-01 14:10:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(21, 70, 6700002, '2026-02-17 13:24:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(22, 67, 6700005, '1970-01-01 11:13:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(23, 4, 6700003, '1970-01-01 11:21:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(24, 18, 6700001, '1970-01-01 13:37:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(25, 3, 6700004, '1970-01-01 11:11:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(26, 102, 6700001, '2026-03-06 15:28:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(27, 37, 6700004, '1970-01-01 09:25:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(28, 60, 6700001, '1970-01-01 18:00:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(29, 79, 6700002, '1970-01-01 08:02:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(30, 56, 6700001, '2026-02-09 18:10:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(31, 48, 6700001, '1970-01-01 11:47:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(32, 60, 6700002, '1970-01-01 17:51:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(33, 82, 6700005, '2026-02-25 13:09:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(34, 22, 6700002, '1970-01-01 10:15:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(35, 65, 6700003, '1970-01-01 11:18:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(36, 51, 6700004, '2026-02-04 11:14:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(37, 13, 6700005, '1970-01-01 15:45:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(38, 92, 6700001, '1970-01-01 08:11:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(39, 54, 6700004, '1970-01-01 14:48:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(40, 44, 6700003, '1970-01-01 16:38:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(41, 66, 6700005, '2026-02-16 12:40:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(42, 80, 6700003, '1970-01-01 17:56:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(43, 52, 6700001, '1970-01-01 11:02:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(44, 78, 6700001, '1970-01-01 11:23:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(45, 12, 6700003, '2026-01-07 16:57:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(46, 42, 6700001, '1970-01-01 12:26:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(47, 8, 6700002, '1970-01-01 15:06:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(48, 63, 6700005, '1970-01-01 18:30:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(49, 20, 6700001, '1970-01-01 18:29:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(50, 103, 6700001, '2026-03-09 11:18:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(51, 60, 6700002, '1970-01-01 13:31:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(52, 40, 6700001, '1970-01-01 12:35:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(53, 23, 6700005, '1970-01-01 09:06:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(54, 65, 6700002, '1970-01-01 18:31:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(55, 99, 6700001, '2026-03-05 10:04:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(56, 32, 6700002, '1970-01-01 12:41:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(57, 62, 6700001, '1970-01-01 09:18:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(58, 58, 6700003, '1970-01-01 11:50:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(59, 74, 6700004, '2026-02-19 10:56:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(60, 40, 6700001, '1970-01-01 13:25:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(61, 77, 6700005, '1970-01-01 08:57:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(62, 61, 6700004, '1970-01-01 12:26:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(63, 88, 6700004, '1970-01-01 10:53:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(64, 34, 6700003, '1970-01-01 09:53:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(65, 57, 6700002, '1970-01-01 10:43:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(66, 93, 6700005, '2026-03-03 08:08:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(67, 26, 6700005, '2026-01-19 18:24:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(68, 62, 6700005, '1970-01-01 09:29:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(69, 43, 6700002, '1970-01-01 09:42:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(70, 97, 6700005, '1970-01-01 12:52:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(71, 95, 6700001, '1970-01-01 15:12:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(72, 59, 6700004, '1970-01-01 18:44:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(73, 34, 6700002, '2026-01-23 16:36:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(74, 52, 6700003, '2026-02-05 13:18:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(75, 7, 6700002, '2026-01-05 08:32:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(76, 1, 6700002, '1970-01-01 12:08:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(77, 51, 6700001, '2026-02-04 08:51:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(78, 20, 6700004, '1970-01-01 12:25:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(79, 56, 6700005, '1970-01-01 15:14:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(80, 92, 6700004, '1970-01-01 14:44:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(81, 47, 6700003, '1970-01-01 14:38:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(82, 6, 6700002, '1970-01-01 18:58:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(83, 91, 6700001, '1970-01-01 10:45:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(84, 70, 6700003, '1970-01-01 14:39:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(85, 98, 6700001, '1970-01-01 13:03:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(86, 79, 6700003, '1970-01-01 08:10:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(87, 25, 6700003, '1970-01-01 09:37:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(88, 28, 6700002, '1970-01-01 10:13:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(89, 92, 6700001, '1970-01-01 12:00:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(90, 105, 6700003, '1970-01-01 09:19:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(91, 41, 6700004, '1970-01-01 17:37:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(92, 44, 6700001, '1970-01-01 08:09:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(93, 31, 6700004, '1970-01-01 10:26:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(94, 70, 6700003, '1970-01-01 09:34:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(95, 71, 6700005, '1970-01-01 10:26:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(96, 3, 6700002, '1970-01-01 09:09:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(97, 25, 6700005, '1970-01-01 15:19:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(98, 4, 6700001, '1970-01-01 11:53:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(99, 41, 6700003, '1970-01-01 11:32:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(100, 46, 6700002, '1970-01-01 14:00:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(101, 103, 6700003, '1970-01-01 14:31:00', 'Penalizado', 1, '2026-06-22 11:06:29', 1),
(102, 20, 6700002, '1970-01-01 11:15:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1),
(103, 99, 6700003, '2026-03-05 08:19:00', 'Reservado', 1, '2026-06-22 11:06:29', 1),
(104, 14, 6700001, '1970-01-01 15:32:00', 'Asistido', 1, '2026-06-22 11:06:29', 1),
(105, 69, 6700004, '1970-01-01 16:58:00', 'Cancelado', 1, '2026-06-22 11:06:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `troles`
--

CREATE TABLE `troles` (
  `idRol` int(10) UNSIGNED NOT NULL,
  `nombreRol` varchar(50) NOT NULL,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `troles`
--

INSERT INTO `troles` (`idRol`, `nombreRol`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 'Administrador', 1, '2026-06-22 11:06:25', 1),
(2, 'Recepcionista', 1, '2026-06-22 11:06:25', 1),
(3, 'Entrenador', 1, '2026-06-22 11:06:25', 1),
(4, 'Socio', 1, '2026-06-22 11:06:25', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tsocios`
--

CREATE TABLE `tsocios` (
  `carnetSocio` int(10) UNSIGNED NOT NULL,
  `idUsuario` int(10) UNSIGNED NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `fotografiaUrl` varchar(255) DEFAULT NULL,
  `nombreContactoEmergencia` varchar(150) DEFAULT NULL,
  `telefonoContactoEmergencia` int(11) DEFAULT NULL,
  `observacionesMedicas` text DEFAULT NULL,
  `estadoSocio` enum('Activo','Inactivo','Congelado') NOT NULL,
  `strikes` int(11) NOT NULL DEFAULT 0,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tsocios`
--

INSERT INTO `tsocios` (`carnetSocio`, `idUsuario`, `direccion`, `fotografiaUrl`, `nombreContactoEmergencia`, `telefonoContactoEmergencia`, `observacionesMedicas`, `estadoSocio`, `strikes`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(6700001, 5, 'Av. Siempre Viva 742', 'fotos_socios/S-6700001.jpeg', 'Maria Apaza', 98765433, 'Ninguna', 'Activo', 0, 1, '2026-06-22 11:06:29', 1),
(6700002, 6, 'Calle Bolivar 456', 'fotos_socios/S-6700002.jpeg', 'Luis Garcia', 98765434, 'Asma controlada', 'Activo', 0, 1, '2026-06-22 11:06:29', 1),
(6700003, 7, 'Av. America 789', 'fotos_socios/S-6700003.jpeg', 'Rosa Ruiz', 98765435, 'Ninguna', 'Activo', 1, 1, '2026-06-22 11:06:29', 1),
(6700004, 8, 'Calle Junin 321', 'fotos_socios/S-6700004.jpeg', 'Jorge Herrera', 98765436, 'Lesion de rodilla 2023', 'Activo', 0, 1, '2026-06-22 11:06:29', 1),
(6700005, 9, 'Av. Heroinas 159', 'fotos_socios/S-6700005.jpeg', 'Sofia Rojas', 98765437, 'Hipertension controlada', 'Activo', 2, 1, '2026-06-22 11:06:29', 1),
(54652132, 16, 'Sucre', 'fotos_socios/S-54652132.jpg', 'Lucas', 12345678, NULL, 'Activo', 0, 1, '2026-06-22 15:34:56', 1),
(3216549877, 17, 'Plaza Peruana', 'fotos_socios/S-3216549877.jpg', 'Pedrin', 62514895, NULL, 'Activo', 0, 1, '2026-06-23 19:23:26', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tsucursales`
--

CREATE TABLE `tsucursales` (
  `idSucursal` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tsucursales`
--

INSERT INTO `tsucursales` (`idSucursal`, `nombre`, `direccion`, `telefono`, `estado`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 'Sucursal Central', 'Av. Principal #123, Col. Centro', '555-0100', 1, 1, '2026-06-22 11:06:29', 1),
(2, 'Sucursal Sucre', 'Sucre', '12346788', 1, 0, '2026-06-22 11:14:27', 1),
(3, 'Sucursal Avaroa', 'Plaza Avaroa', '85858584', 1, 1, '2026-06-23 15:16:50', 1),
(4, 'Sucursal Cruce', 'Cruce', '65656522', 1, 0, '2026-06-24 08:52:32', 1),
(5, 'Sucursal Cruce 2', 'Cruce', '78787878788', 1, 1, '2026-06-24 08:56:12', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tusuarios`
--

CREATE TABLE `tusuarios` (
  `idUsuario` int(10) UNSIGNED NOT NULL,
  `idRol` int(10) UNSIGNED NOT NULL,
  `nombre1` varchar(100) NOT NULL,
  `nombre2` varchar(100) DEFAULT NULL,
  `apellido1` varchar(100) NOT NULL,
  `apellido2` varchar(100) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `telefono` int(11) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `estadoA` tinyint(1) NOT NULL DEFAULT 1,
  `fechaA` datetime NOT NULL DEFAULT current_timestamp(),
  `usuarioA` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tusuarios`
--

INSERT INTO `tusuarios` (`idUsuario`, `idRol`, `nombre1`, `nombre2`, `apellido1`, `apellido2`, `correo`, `telefono`, `contrasena`, `estado`, `estadoA`, `fechaA`, `usuarioA`) VALUES
(1, 1, 'Admin', NULL, 'Sistema', NULL, 'admin@gimnasio.com', 12345678, '$2y$12$/xvP7U6NgNDhIkVT13YPXuxvU.AYpsutWqwRly0EVMonHjV3S/PRG', 1, 1, '2026-06-22 11:06:25', NULL),
(2, 1, 'Favio Estefano', NULL, 'Sandy Gonzales', NULL, 'favio@gmail.com', 23456789, '$2y$12$PD9UiqttdC9zpyEJZHToD.nPj/LDzart6AHVWlW0QJW.kwrf/m/ri', 1, 1, '2026-06-22 11:06:26', 1),
(3, 2, 'Juan Enrique', NULL, 'Quenallata Escobar', NULL, 'kike@gmail.com', 34567890, '$2y$12$bApi.xV7kqOj9Jp9bKzDluaBPs/n6PMh2/wgZMKVZwPcxFhb.nryO', 1, 1, '2026-06-22 11:06:26', 1),
(4, 3, 'Samuel Ignacio', NULL, 'Jimenez Aliaga', NULL, 'max@gmail.com', 45678901, '$2y$12$.5mn.bzY0.9X5AXDZOgvyeQOPtD2MUYP.TPh7lZRm614WJx6724Cm', 1, 1, '2026-06-22 11:06:29', 1),
(5, 4, 'Eddy Limber', NULL, 'Vargas Apaza', NULL, 'eddy@gmail.com', 56789012, '$2y$12$PhkqlPQRV72XURYYF.JlAuInT6SZZdm5xSOZj9EGpVJOZd61HUrr.', 1, 1, '2026-06-22 11:06:29', 1),
(6, 4, 'Maria Fernanda', NULL, 'Garcia Lopez', NULL, 'maria@gmail.com', 67890123, '$2y$12$FpvOE4bPfLwZ3cHavh0ZFegHuYtCUIGj1.8I.uiT.c7FjTZua2ZfW', 1, 1, '2026-06-22 11:06:29', 1),
(7, 4, 'Charles James', NULL, 'Kirk Ruiz', NULL, 'carlos@gmail.com', 78901234, '$2y$12$zPzgj/vL8LX0s0sXsbcdc.EdGm2PeW8QZJdX1e3omenvSfgBGOAHq', 1, 1, '2026-06-22 11:06:29', 1),
(8, 4, 'Ana Sofia', NULL, 'Torrico Herrera', NULL, 'ana@gmail.com', 89012345, '$2y$12$gwCAGvn5neplV1r54cpIZezg1cUCCg./42LBeKatSYDoTtw6Oo3vu', 1, 1, '2026-06-22 11:06:29', 1),
(9, 4, 'Pedro Luis', NULL, 'Camacho Rojas', NULL, 'pedro@gmail.com', 90123456, '$2y$12$K/qXtuiTOpvabIgr2V3bteKYzbxI0.enZCD3zrlZuhtgJto4fM3ie', 1, 1, '2026-06-22 11:06:29', 1),
(10, 2, 'Carlos', NULL, 'Ruiz Martinez', NULL, 'carlos.ruiz@gmail.com', 11111111, '$2y$12$enCoDsrH6zjPwEdHvomY3u2ZHkVB1qJvOyHaX7Zx6y44M1g0SO2fe', 1, 1, '2026-06-22 11:06:29', 1),
(11, 3, 'Lucia', NULL, 'Morales Fernandez', NULL, 'lucia@gmail.com', 22222222, '$2y$12$xwqoOwGNFUxcLytEtRBy5uKMQ6cvb1LKNB0DMZTfEd.EccqgCjARW', 1, 1, '2026-06-22 11:06:29', 1),
(12, 3, 'Roberto', NULL, 'Vega Castillo', NULL, 'roberto@gmail.com', 33333333, '$2y$12$YI9y715lBSQgFLQ0TluEe.rDv9YzjcA//7hnQIoBr5E2v9GNmvLKe', 1, 1, '2026-06-22 11:06:29', 1),
(13, 3, 'Sebastian', NULL, 'Alcachofa', NULL, 'sebas@gmail.com', 87986545, '$2y$12$7ipQLmddGFkSWJpVmmHBZeZvbPha8bzj3XZsG0EzpcbzA2qSPJeWK', 1, 1, '2026-06-22 11:20:05', 1),
(16, 4, 'Juanito', NULL, 'Calani', NULL, 'juan@gmail.com', 87654321, '$2y$12$nqZFQ7jD0YibapZEs9Mdu.Y4poWihFHbxPgcaZGRr/2Df9h6qEbe.', 1, 1, '2026-06-22 11:34:32', 1),
(17, 4, 'Milei', 'Mileo', 'Pamilei', 'Mamilei', 'milei@gmail.com', 64758523, '$2y$12$zxcOGCMccGCUgwzjODKZ8u58VueTVQjgOZjP35U2R87SV0eWaBb6i', 1, 1, '2026-06-23 15:23:26', 1),
(19, 3, 'Andres', NULL, 'Salazar', NULL, 'andres@gmail.com', 789865221, '$2y$12$nmffsP08tVKpaVbrBgPJAevpwcX5Krw/9cZqVm9hw.mrEnQJIWlli', 1, 1, '2026-06-23 19:33:05', 1),
(20, 3, 'Pepe', NULL, 'Alcachofa', NULL, 'pepe@gmail.com', 756942536, '$2y$12$8U883wy50zZMGGOtRNV6wOVadQbtT/8UzHNSfItYEAgn8hgrPKxtm', 1, 1, '2026-06-24 13:05:33', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `tactividades`
--
ALTER TABLE `tactividades`
  ADD PRIMARY KEY (`idActividad`),
  ADD KEY `fk_tactividades_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tasistenciaspersonal`
--
ALTER TABLE `tasistenciaspersonal`
  ADD PRIMARY KEY (`idAsistencia`),
  ADD KEY `tasistenciaspersonal_carnetempleado_foreign` (`carnetEmpleado`);

--
-- Indices de la tabla `tauditorias`
--
ALTER TABLE `tauditorias`
  ADD PRIMARY KEY (`idAuditoria`),
  ADD KEY `fk_tauditoria_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tcajas`
--
ALTER TABLE `tcajas`
  ADD PRIMARY KEY (`idCaja`),
  ADD KEY `tcajas_idsucursal_foreign` (`idSucursal`),
  ADD KEY `tcajas_carnetempleado_foreign` (`carnetEmpleado`),
  ADD KEY `fk_tcajas_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tclasegrupales`
--
ALTER TABLE `tclasegrupales`
  ADD PRIMARY KEY (`idClaseGrupal`),
  ADD KEY `tclasegrupales_idactividad_foreign` (`idActividad`),
  ADD KEY `tclasegrupales_carnetempleado_foreign` (`carnetEmpleado`),
  ADD KEY `tclasegrupales_idsucursal_foreign` (`idSucursal`),
  ADD KEY `fk_tclasegrupales_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tcontrolaccesos`
--
ALTER TABLE `tcontrolaccesos`
  ADD PRIMARY KEY (`idControlAcceso`),
  ADD KEY `tcontrolaccesos_carnetsocio_foreign` (`carnetSocio`),
  ADD KEY `tcontrolaccesos_idsucursal_foreign` (`idSucursal`),
  ADD KEY `fk_taccesos_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tcontrolasistencias`
--
ALTER TABLE `tcontrolasistencias`
  ADD PRIMARY KEY (`idAsistencia`),
  ADD KEY `tcontrolasistencias_carnetempleado_foreign` (`carnetEmpleado`),
  ADD KEY `fk_tasistencias_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tdetallemetodopagos`
--
ALTER TABLE `tdetallemetodopagos`
  ADD PRIMARY KEY (`idMetodoPago`),
  ADD KEY `tdetallemetodopagos_idrecibo_foreign` (`idRecibo`),
  ADD KEY `fk_tdetallepago_usuarioA` (`usuarioA`),
  ADD KEY `tdetallemetodopagos_idmetodopagofk_foreign` (`idMetodoPagoFK`);

--
-- Indices de la tabla `templeados`
--
ALTER TABLE `templeados`
  ADD PRIMARY KEY (`carnetEmpleado`),
  ADD UNIQUE KEY `templeados_idusuario_unique` (`idUsuario`),
  ADD KEY `templeados_idsucursal_foreign` (`idSucursal`),
  ADD KEY `fk_templeados_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tequipamientos`
--
ALTER TABLE `tequipamientos`
  ADD PRIMARY KEY (`idEquipo`),
  ADD KEY `tequipamientos_idsucursal_foreign` (`idSucursal`),
  ADD KEY `tequipamientos_idmarca_foreign` (`idMarca`),
  ADD KEY `fk_tequipamientos_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tesquemasueldos`
--
ALTER TABLE `tesquemasueldos`
  ADD PRIMARY KEY (`idEsquemaSueldo`),
  ADD KEY `tesquemasueldos_carnetempleado_foreign` (`carnetEmpleado`),
  ADD KEY `fk_tesquemasueldos_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `thorariolaborales`
--
ALTER TABLE `thorariolaborales`
  ADD PRIMARY KEY (`idHorario`),
  ADD KEY `thorariolaborales_carnetempleado_foreign` (`carnetEmpleado`),
  ADD KEY `fk_thorario_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tmantenimientopreventivos`
--
ALTER TABLE `tmantenimientopreventivos`
  ADD PRIMARY KEY (`idMantenimiento`),
  ADD KEY `tmantenimientopreventivos_idequipo_foreign` (`idEquipo`),
  ADD KEY `fk_tmantenimiento_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tmarcas`
--
ALTER TABLE `tmarcas`
  ADD PRIMARY KEY (`idMarca`),
  ADD KEY `fk_tmarcas_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tmembresias`
--
ALTER TABLE `tmembresias`
  ADD PRIMARY KEY (`idMembresia`),
  ADD KEY `tmembresias_idplan_foreign` (`idPlan`),
  ADD KEY `tmembresias_carnetsocio_foreign` (`carnetSocio`),
  ADD KEY `tmembresias_idsucursal_foreign` (`idSucursal`),
  ADD KEY `fk_tmembresias_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tmetodopagos`
--
ALTER TABLE `tmetodopagos`
  ADD PRIMARY KEY (`idMetodoPago`);

--
-- Indices de la tabla `tnotificaciones`
--
ALTER TABLE `tnotificaciones`
  ADD PRIMARY KEY (`idNotificacion`),
  ADD KEY `tnotificaciones_carnetsocio_foreign` (`carnetSocio`),
  ADD KEY `fk_tnotificaciones_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tpagosueldos`
--
ALTER TABLE `tpagosueldos`
  ADD PRIMARY KEY (`idPagoSueldo`),
  ADD KEY `tpagosueldos_carnetempleado_foreign` (`carnetEmpleado`);

--
-- Indices de la tabla `tpenalizaciones`
--
ALTER TABLE `tpenalizaciones`
  ADD PRIMARY KEY (`idPenalizacion`),
  ADD KEY `tpenalizaciones_carnetsocio_foreign` (`carnetSocio`),
  ADD KEY `fk_tpenalizaciones_usuarioA` (`usuarioA`),
  ADD KEY `tpenalizaciones_idreserva_foreign` (`idReserva`);

--
-- Indices de la tabla `tplanes`
--
ALTER TABLE `tplanes`
  ADD PRIMARY KEY (`idPlan`),
  ADD KEY `fk_tplanes_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `trecibos`
--
ALTER TABLE `trecibos`
  ADD PRIMARY KEY (`idRecibo`),
  ADD UNIQUE KEY `trecibos_nrorecibo_unique` (`nroRecibo`),
  ADD KEY `trecibos_idcaja_foreign` (`idCaja`),
  ADD KEY `trecibos_idmembresia_foreign` (`idMembresia`),
  ADD KEY `fk_trecibos_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `treportefallas`
--
ALTER TABLE `treportefallas`
  ADD PRIMARY KEY (`idReporteFalla`),
  ADD KEY `treportefallas_idequipo_foreign` (`idEquipo`),
  ADD KEY `treportefallas_carnetempleado_foreign` (`carnetEmpleado`),
  ADD KEY `fk_treportefallas_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `treservas`
--
ALTER TABLE `treservas`
  ADD PRIMARY KEY (`idReserva`),
  ADD KEY `treservas_idclasegrupal_foreign` (`idClaseGrupal`),
  ADD KEY `treservas_carnetsocio_foreign` (`carnetSocio`),
  ADD KEY `fk_treservas_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `troles`
--
ALTER TABLE `troles`
  ADD PRIMARY KEY (`idRol`),
  ADD KEY `fk_troles_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tsocios`
--
ALTER TABLE `tsocios`
  ADD PRIMARY KEY (`carnetSocio`),
  ADD UNIQUE KEY `tsocios_idusuario_unique` (`idUsuario`),
  ADD KEY `fk_tsocios_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tsucursales`
--
ALTER TABLE `tsucursales`
  ADD PRIMARY KEY (`idSucursal`),
  ADD KEY `fk_tsucursales_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `tusuarios`
--
ALTER TABLE `tusuarios`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `tusuarios_correo_unique` (`correo`),
  ADD KEY `tusuarios_idrol_foreign` (`idRol`),
  ADD KEY `fk_tusuarios_usuarioA` (`usuarioA`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `tactividades`
--
ALTER TABLE `tactividades`
  MODIFY `idActividad` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tasistenciaspersonal`
--
ALTER TABLE `tasistenciaspersonal`
  MODIFY `idAsistencia` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tauditorias`
--
ALTER TABLE `tauditorias`
  MODIFY `idAuditoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `tcajas`
--
ALTER TABLE `tcajas`
  MODIFY `idCaja` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT de la tabla `tclasegrupales`
--
ALTER TABLE `tclasegrupales`
  MODIFY `idClaseGrupal` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT de la tabla `tcontrolaccesos`
--
ALTER TABLE `tcontrolaccesos`
  MODIFY `idControlAcceso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `tcontrolasistencias`
--
ALTER TABLE `tcontrolasistencias`
  MODIFY `idAsistencia` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT de la tabla `tdetallemetodopagos`
--
ALTER TABLE `tdetallemetodopagos`
  MODIFY `idMetodoPago` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT de la tabla `tequipamientos`
--
ALTER TABLE `tequipamientos`
  MODIFY `idEquipo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `tesquemasueldos`
--
ALTER TABLE `tesquemasueldos`
  MODIFY `idEsquemaSueldo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `thorariolaborales`
--
ALTER TABLE `thorariolaborales`
  MODIFY `idHorario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `tmantenimientopreventivos`
--
ALTER TABLE `tmantenimientopreventivos`
  MODIFY `idMantenimiento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `tmarcas`
--
ALTER TABLE `tmarcas`
  MODIFY `idMarca` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tmembresias`
--
ALTER TABLE `tmembresias`
  MODIFY `idMembresia` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tmetodopagos`
--
ALTER TABLE `tmetodopagos`
  MODIFY `idMetodoPago` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tnotificaciones`
--
ALTER TABLE `tnotificaciones`
  MODIFY `idNotificacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tpagosueldos`
--
ALTER TABLE `tpagosueldos`
  MODIFY `idPagoSueldo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `tpenalizaciones`
--
ALTER TABLE `tpenalizaciones`
  MODIFY `idPenalizacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `tplanes`
--
ALTER TABLE `tplanes`
  MODIFY `idPlan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `trecibos`
--
ALTER TABLE `trecibos`
  MODIFY `idRecibo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT de la tabla `treportefallas`
--
ALTER TABLE `treportefallas`
  MODIFY `idReporteFalla` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `treservas`
--
ALTER TABLE `treservas`
  MODIFY `idReserva` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT de la tabla `troles`
--
ALTER TABLE `troles`
  MODIFY `idRol` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tsocios`
--
ALTER TABLE `tsocios`
  MODIFY `carnetSocio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3216549878;

--
-- AUTO_INCREMENT de la tabla `tsucursales`
--
ALTER TABLE `tsucursales`
  MODIFY `idSucursal` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tusuarios`
--
ALTER TABLE `tusuarios`
  MODIFY `idUsuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tactividades`
--
ALTER TABLE `tactividades`
  ADD CONSTRAINT `fk_tactividades_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`);

--
-- Filtros para la tabla `tasistenciaspersonal`
--
ALTER TABLE `tasistenciaspersonal`
  ADD CONSTRAINT `tasistenciaspersonal_carnetempleado_foreign` FOREIGN KEY (`carnetEmpleado`) REFERENCES `templeados` (`carnetEmpleado`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tauditorias`
--
ALTER TABLE `tauditorias`
  ADD CONSTRAINT `fk_tauditoria_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`);

--
-- Filtros para la tabla `tcajas`
--
ALTER TABLE `tcajas`
  ADD CONSTRAINT `fk_tcajas_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tcajas_carnetempleado_foreign` FOREIGN KEY (`carnetEmpleado`) REFERENCES `templeados` (`carnetEmpleado`),
  ADD CONSTRAINT `tcajas_idsucursal_foreign` FOREIGN KEY (`idSucursal`) REFERENCES `tsucursales` (`idSucursal`);

--
-- Filtros para la tabla `tclasegrupales`
--
ALTER TABLE `tclasegrupales`
  ADD CONSTRAINT `fk_tclasegrupales_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tclasegrupales_carnetempleado_foreign` FOREIGN KEY (`carnetEmpleado`) REFERENCES `templeados` (`carnetEmpleado`),
  ADD CONSTRAINT `tclasegrupales_idactividad_foreign` FOREIGN KEY (`idActividad`) REFERENCES `tactividades` (`idActividad`),
  ADD CONSTRAINT `tclasegrupales_idsucursal_foreign` FOREIGN KEY (`idSucursal`) REFERENCES `tsucursales` (`idSucursal`);

--
-- Filtros para la tabla `tcontrolaccesos`
--
ALTER TABLE `tcontrolaccesos`
  ADD CONSTRAINT `fk_taccesos_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tcontrolaccesos_carnetsocio_foreign` FOREIGN KEY (`carnetSocio`) REFERENCES `tsocios` (`carnetSocio`),
  ADD CONSTRAINT `tcontrolaccesos_idsucursal_foreign` FOREIGN KEY (`idSucursal`) REFERENCES `tsucursales` (`idSucursal`);

--
-- Filtros para la tabla `tcontrolasistencias`
--
ALTER TABLE `tcontrolasistencias`
  ADD CONSTRAINT `fk_tasistencias_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tcontrolasistencias_carnetempleado_foreign` FOREIGN KEY (`carnetEmpleado`) REFERENCES `templeados` (`carnetEmpleado`);

--
-- Filtros para la tabla `tdetallemetodopagos`
--
ALTER TABLE `tdetallemetodopagos`
  ADD CONSTRAINT `fk_tdetallepago_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tdetallemetodopagos_idmetodopagofk_foreign` FOREIGN KEY (`idMetodoPagoFK`) REFERENCES `tmetodopagos` (`idMetodoPago`),
  ADD CONSTRAINT `tdetallemetodopagos_idrecibo_foreign` FOREIGN KEY (`idRecibo`) REFERENCES `trecibos` (`idRecibo`);

--
-- Filtros para la tabla `templeados`
--
ALTER TABLE `templeados`
  ADD CONSTRAINT `fk_templeados_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `templeados_idsucursal_foreign` FOREIGN KEY (`idSucursal`) REFERENCES `tsucursales` (`idSucursal`),
  ADD CONSTRAINT `templeados_idusuario_foreign` FOREIGN KEY (`idUsuario`) REFERENCES `tusuarios` (`idUsuario`);

--
-- Filtros para la tabla `tequipamientos`
--
ALTER TABLE `tequipamientos`
  ADD CONSTRAINT `fk_tequipamientos_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tequipamientos_idmarca_foreign` FOREIGN KEY (`idMarca`) REFERENCES `tmarcas` (`idMarca`),
  ADD CONSTRAINT `tequipamientos_idsucursal_foreign` FOREIGN KEY (`idSucursal`) REFERENCES `tsucursales` (`idSucursal`);

--
-- Filtros para la tabla `tesquemasueldos`
--
ALTER TABLE `tesquemasueldos`
  ADD CONSTRAINT `fk_tesquemasueldos_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tesquemasueldos_carnetempleado_foreign` FOREIGN KEY (`carnetEmpleado`) REFERENCES `templeados` (`carnetEmpleado`);

--
-- Filtros para la tabla `thorariolaborales`
--
ALTER TABLE `thorariolaborales`
  ADD CONSTRAINT `fk_thorario_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `thorariolaborales_carnetempleado_foreign` FOREIGN KEY (`carnetEmpleado`) REFERENCES `templeados` (`carnetEmpleado`);

--
-- Filtros para la tabla `tmantenimientopreventivos`
--
ALTER TABLE `tmantenimientopreventivos`
  ADD CONSTRAINT `fk_tmantenimiento_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tmantenimientopreventivos_idequipo_foreign` FOREIGN KEY (`idEquipo`) REFERENCES `tequipamientos` (`idEquipo`);

--
-- Filtros para la tabla `tmarcas`
--
ALTER TABLE `tmarcas`
  ADD CONSTRAINT `fk_tmarcas_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`);

--
-- Filtros para la tabla `tmembresias`
--
ALTER TABLE `tmembresias`
  ADD CONSTRAINT `fk_tmembresias_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tmembresias_carnetsocio_foreign` FOREIGN KEY (`carnetSocio`) REFERENCES `tsocios` (`carnetSocio`),
  ADD CONSTRAINT `tmembresias_idplan_foreign` FOREIGN KEY (`idPlan`) REFERENCES `tplanes` (`idPlan`),
  ADD CONSTRAINT `tmembresias_idsucursal_foreign` FOREIGN KEY (`idSucursal`) REFERENCES `tsucursales` (`idSucursal`);

--
-- Filtros para la tabla `tnotificaciones`
--
ALTER TABLE `tnotificaciones`
  ADD CONSTRAINT `fk_tnotificaciones_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tnotificaciones_carnetsocio_foreign` FOREIGN KEY (`carnetSocio`) REFERENCES `tsocios` (`carnetSocio`);

--
-- Filtros para la tabla `tpagosueldos`
--
ALTER TABLE `tpagosueldos`
  ADD CONSTRAINT `tpagosueldos_carnetempleado_foreign` FOREIGN KEY (`carnetEmpleado`) REFERENCES `templeados` (`carnetEmpleado`);

--
-- Filtros para la tabla `tpenalizaciones`
--
ALTER TABLE `tpenalizaciones`
  ADD CONSTRAINT `fk_tpenalizaciones_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tpenalizaciones_carnetsocio_foreign` FOREIGN KEY (`carnetSocio`) REFERENCES `tsocios` (`carnetSocio`),
  ADD CONSTRAINT `tpenalizaciones_idreserva_foreign` FOREIGN KEY (`idReserva`) REFERENCES `treservas` (`idReserva`);

--
-- Filtros para la tabla `tplanes`
--
ALTER TABLE `tplanes`
  ADD CONSTRAINT `fk_tplanes_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`);

--
-- Filtros para la tabla `trecibos`
--
ALTER TABLE `trecibos`
  ADD CONSTRAINT `fk_trecibos_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `trecibos_idcaja_foreign` FOREIGN KEY (`idCaja`) REFERENCES `tcajas` (`idCaja`),
  ADD CONSTRAINT `trecibos_idmembresia_foreign` FOREIGN KEY (`idMembresia`) REFERENCES `tmembresias` (`idMembresia`);

--
-- Filtros para la tabla `treportefallas`
--
ALTER TABLE `treportefallas`
  ADD CONSTRAINT `fk_treportefallas_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `treportefallas_carnetempleado_foreign` FOREIGN KEY (`carnetEmpleado`) REFERENCES `templeados` (`carnetEmpleado`),
  ADD CONSTRAINT `treportefallas_idequipo_foreign` FOREIGN KEY (`idEquipo`) REFERENCES `tequipamientos` (`idEquipo`);

--
-- Filtros para la tabla `treservas`
--
ALTER TABLE `treservas`
  ADD CONSTRAINT `fk_treservas_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `treservas_carnetsocio_foreign` FOREIGN KEY (`carnetSocio`) REFERENCES `tsocios` (`carnetSocio`),
  ADD CONSTRAINT `treservas_idclasegrupal_foreign` FOREIGN KEY (`idClaseGrupal`) REFERENCES `tclasegrupales` (`idClaseGrupal`);

--
-- Filtros para la tabla `troles`
--
ALTER TABLE `troles`
  ADD CONSTRAINT `fk_troles_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`);

--
-- Filtros para la tabla `tsocios`
--
ALTER TABLE `tsocios`
  ADD CONSTRAINT `fk_tsocios_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tsocios_idusuario_foreign` FOREIGN KEY (`idUsuario`) REFERENCES `tusuarios` (`idUsuario`);

--
-- Filtros para la tabla `tsucursales`
--
ALTER TABLE `tsucursales`
  ADD CONSTRAINT `fk_tsucursales_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`);

--
-- Filtros para la tabla `tusuarios`
--
ALTER TABLE `tusuarios`
  ADD CONSTRAINT `fk_tusuarios_usuarioA` FOREIGN KEY (`usuarioA`) REFERENCES `tusuarios` (`idUsuario`),
  ADD CONSTRAINT `tusuarios_idrol_foreign` FOREIGN KEY (`idRol`) REFERENCES `troles` (`idRol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

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
        // 1. sp_TClaseGrupales_ActualizarEstados
        //    Actualiza en lote los estados según la hora actual.
        //    Lógica:
        //      - fecha + horaFin < NOW()  => 'Finalizada'
        //      - fecha + horaInicio <= NOW() < fecha + horaFin  => 'Cursandose'
        //      - else mantiene 'Programada'
        //    No toca 'Cancelada' ni 'Finalizada' manual.
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TClaseGrupales_ActualizarEstados");
        $pdo->exec("
CREATE PROCEDURE sp_TClaseGrupales_ActualizarEstados()
BEGIN
    -- Finalizar clases cuya hora fin ya pasó
    UPDATE TClaseGrupales
    SET estadoClase = 'Finalizada'
    WHERE estadoA = 1
      AND estadoClase IN ('Programada', 'Cursandose')
      AND CONCAT(fecha, ' ', horaFin) < NOW();

    -- Marcar como 'Cursandose' las que están en curso
    UPDATE TClaseGrupales
    SET estadoClase = 'Cursandose'
    WHERE estadoA = 1
      AND estadoClase = 'Programada'
      AND CONCAT(fecha, ' ', horaInicio) <= NOW()
      AND CONCAT(fecha, ' ', horaFin) >= NOW();
END
        ");

        // =========================================================================
        // 2. sp_TClaseGrupales_Update (con auditoría obligatoria)
        //    Actualiza una clase grupal y registra auditoría en TAuditorias.
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TClaseGrupales_Update");
        $pdo->exec("
CREATE PROCEDURE sp_TClaseGrupales_Update(
    IN p_idClaseGrupal INT,
    IN p_idActividad INT,
    IN p_carnetEmpleado INT,
    IN p_idSucursal INT,
    IN p_fecha DATE,
    IN p_horaInicio TIME,
    IN p_horaFin TIME,
    IN p_cupoMaximo INT,
    IN p_estadoClase VARCHAR(20),
    IN p_usuarioA INT,
    IN p_direccionIP VARCHAR(50)
)
BEGIN
    DECLARE v_old_actividad INT;
    DECLARE v_old_empleado INT;
    DECLARE v_old_fecha DATE;
    DECLARE v_old_horaInicio TIME;
    DECLARE v_old_horaFin TIME;
    DECLARE v_old_cupo INT;
    DECLARE v_old_estado VARCHAR(20);
    DECLARE v_detalles TEXT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    -- Obtener valores anteriores
    SELECT idActividad, carnetEmpleado, fecha, horaInicio, horaFin, cupoMaximo, estadoClase
    INTO v_old_actividad, v_old_empleado, v_old_fecha, v_old_horaInicio, v_old_horaFin, v_old_cupo, v_old_estado
    FROM TClaseGrupales
    WHERE idClaseGrupal = p_idClaseGrupal
    FOR UPDATE;

    -- Actualizar
    UPDATE TClaseGrupales
    SET idActividad = p_idActividad,
        carnetEmpleado = p_carnetEmpleado,
        idSucursal = p_idSucursal,
        fecha = p_fecha,
        horaInicio = p_horaInicio,
        horaFin = p_horaFin,
        cupoMaximo = p_cupoMaximo,
        estadoClase = p_estadoClase,
        usuarioA = p_usuarioA
    WHERE idClaseGrupal = p_idClaseGrupal;

    -- Auditoría por cada campo cambiado
    SET v_detalles = CONCAT('Clase #', p_idClaseGrupal, ' actualizada');

    IF v_old_actividad != p_idActividad THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TClaseGrupales', p_idClaseGrupal, 'U', 'idActividad',
                CAST(v_old_actividad AS CHAR), CAST(p_idActividad AS CHAR),
                p_usuarioA, NOW(), p_direccionIP, v_detalles);
    END IF;

    IF v_old_empleado != p_carnetEmpleado THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TClaseGrupales', p_idClaseGrupal, 'U', 'carnetEmpleado',
                CAST(v_old_empleado AS CHAR), CAST(p_carnetEmpleado AS CHAR),
                p_usuarioA, NOW(), p_direccionIP, v_detalles);
    END IF;

    IF v_old_fecha != p_fecha THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TClaseGrupales', p_idClaseGrupal, 'U', 'fecha',
                CAST(v_old_fecha AS CHAR), CAST(p_fecha AS CHAR),
                p_usuarioA, NOW(), p_direccionIP, v_detalles);
    END IF;

    IF v_old_horaInicio != p_horaInicio THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TClaseGrupales', p_idClaseGrupal, 'U', 'horaInicio',
                CAST(v_old_horaInicio AS CHAR), CAST(p_horaInicio AS CHAR),
                p_usuarioA, NOW(), p_direccionIP, v_detalles);
    END IF;

    IF v_old_horaFin != p_horaFin THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TClaseGrupales', p_idClaseGrupal, 'U', 'horaFin',
                CAST(v_old_horaFin AS CHAR), CAST(p_horaFin AS CHAR),
                p_usuarioA, NOW(), p_direccionIP, v_detalles);
    END IF;

    IF v_old_cupo != p_cupoMaximo THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TClaseGrupales', p_idClaseGrupal, 'U', 'cupoMaximo',
                CAST(v_old_cupo AS CHAR), CAST(p_cupoMaximo AS CHAR),
                p_usuarioA, NOW(), p_direccionIP, v_detalles);
    END IF;

    IF v_old_estado != p_estadoClase THEN
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES ('TClaseGrupales', p_idClaseGrupal, 'U', 'estadoClase',
                v_old_estado, p_estadoClase,
                p_usuarioA, NOW(), p_direccionIP,
                CONCAT('Cambio de estado: ', v_old_estado, ' -> ', p_estadoClase));
    END IF;

    COMMIT;

    SELECT TRUE AS success, 'Clase actualizada correctamente.' AS message;
END
        ");

        // =========================================================================
        // 3. sp_TClaseGrupales_GetByEntrenador
        //    Lista las clases asignadas a un entrenador con estado dinámico.
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TClaseGrupales_GetByEntrenador");
        $pdo->exec("
CREATE PROCEDURE sp_TClaseGrupales_GetByEntrenador(
    IN p_carnetEmpleado INT
)
BEGIN
    SELECT
        cg.idClaseGrupal,
        cg.fecha,
        cg.horaInicio,
        cg.horaFin,
        cg.cupoMaximo,
        cg.estadoClase,
        a.nombreActividad,
        s.nombre AS nombreSucursal,
        CASE
            WHEN CONCAT(cg.fecha, ' ', cg.horaFin) < NOW() THEN 'Finalizada'
            WHEN CONCAT(cg.fecha, ' ', cg.horaInicio) <= NOW()
                 AND CONCAT(cg.fecha, ' ', cg.horaFin) >= NOW() THEN 'Cursandose'
            ELSE cg.estadoClase
        END AS estadoActual,
        (SELECT COUNT(*) FROM TReservas r
         WHERE r.idClaseGrupal = cg.idClaseGrupal AND r.estadoA = 1) AS totalReservas,
        (SELECT COUNT(*) FROM TReservas r
         WHERE r.idClaseGrupal = cg.idClaseGrupal
           AND r.estadoReserva = 'Asistido' AND r.estadoA = 1) AS asistieron,
        (SELECT COUNT(*) FROM TReservas r
         WHERE r.idClaseGrupal = cg.idClaseGrupal
           AND r.estadoReserva = 'Reservado' AND r.estadoA = 1) AS reservados
    FROM TClaseGrupales cg
    INNER JOIN TActividades a ON a.idActividad = cg.idActividad
    INNER JOIN TSucursales s ON s.idSucursal = cg.idSucursal
    WHERE cg.carnetEmpleado = p_carnetEmpleado
      AND cg.estadoA = 1
    ORDER BY cg.fecha DESC, cg.horaInicio DESC;
END
        ");

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }

    public function down(): void
    {
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TClaseGrupales_ActualizarEstados");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TClaseGrupales_Update");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TClaseGrupales_GetByEntrenador");

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }
};

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
        // 1. sp_TMembresias_SincronizarEstado
        //    Sincroniza el estado de membresías con la fecha actual:
        //      - Activa -> Vencida cuando fechaFinMembresia < CURDATE()
        //      - Vencida -> Activa cuando fechaFinMembresia >= CURDATE()
        //    También reactiva el socio si su membresía se restaura.
        //    Registra todos los cambios en TAuditorias.
        // =========================================================================
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TMembresias_SincronizarEstado");
        $pdo->exec("
CREATE PROCEDURE sp_TMembresias_SincronizarEstado()
BEGIN
    DECLARE v_done INT DEFAULT FALSE;
    DECLARE v_idMembresia INT;
    DECLARE v_carnetSocio INT;
    DECLARE v_estadoAnterior VARCHAR(20);
    DECLARE v_estadoNuevo VARCHAR(20);

    DECLARE cur CURSOR FOR
        SELECT idMembresia, carnetSocio, estadoMembresia
        FROM TMembresias
        WHERE estadoA = 1
          AND (
            (estadoMembresia = 'Activa' AND fechaFinMembresia < CURDATE())
            OR
            (estadoMembresia = 'Vencida' AND fechaFinMembresia >= CURDATE())
          )
        FOR UPDATE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = TRUE;

    START TRANSACTION;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO v_idMembresia, v_carnetSocio, v_estadoAnterior;
        IF v_done THEN
            LEAVE read_loop;
        END IF;

        -- Determinar nuevo estado
        IF v_estadoAnterior = 'Activa' THEN
            SET v_estadoNuevo = 'Vencida';
        ELSE
            SET v_estadoNuevo = 'Activa';
        END IF;

        -- Actualizar membresía
        UPDATE TMembresias
        SET estadoMembresia = v_estadoNuevo,
            fechaA = NOW()
        WHERE idMembresia = v_idMembresia;

        -- Auditoría
        INSERT INTO TAuditorias (tablaNombre, registroId, accion, campo, valorAnterior, valorNuevo, usuarioA, fechaA, direccionIP, detalles)
        VALUES (
            'TMembresias', v_idMembresia, 'U',
            'estadoMembresia',
            v_estadoAnterior,
            v_estadoNuevo,
            1, NOW(), '127.0.0.1',
            CONCAT('Sincronización automática de estado. ', v_estadoAnterior, ' -> ', v_estadoNuevo, '. Socio: ', v_carnetSocio)
        );

        -- Si la membresía se reactiva y el socio está Vencido/Inactivo, restaurarlo
        IF v_estadoNuevo = 'Activa' THEN
            UPDATE TSocios
            SET estadoSocio = 'Activo',
                fechaA = NOW()
            WHERE carnetSocio = v_carnetSocio
              AND estadoSocio IN ('Vencido', 'Inactivo')
              AND estadoA = 1;
        END IF;
    END LOOP;

    CLOSE cur;
    COMMIT;
END
        ");

        // =========================================================================
        // 2. trg_TControlAccesos_BeforeInsert
        //    Trigger BEFORE INSERT que sincroniza el estado de membresías
        //    automáticamente antes de cada intento de registro de acceso.
        //    Garantiza que la validación de membresía siempre vea datos actualizados.
        // =========================================================================
        // NOTA: El trigger fue eliminado porque MySQL no permite CALL dentro de triggers.
        // La sincronización se realiza desde el controlador (ControlIngresoController::listarTodos()).
        $pdo->exec("DROP TRIGGER IF EXISTS trg_TControlAccesos_BeforeInsert");

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }

    public function down(): void
    {
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

        $pdo->exec("DROP TRIGGER IF EXISTS trg_TControlAccesos_BeforeInsert");
        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TMembresias_SincronizarEstado");

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TClaseGrupales_GetAvailable");
        $pdo->exec("
CREATE PROCEDURE sp_TClaseGrupales_GetAvailable()
BEGIN
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
    ORDER BY cg.fecha ASC, cg.horaInicio ASC;
END
        ");

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }

    public function down(): void
    {
        $pdo = DB::getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);

        $pdo->exec("DROP PROCEDURE IF EXISTS sp_TClaseGrupales_GetAvailable");
        $pdo->exec("
CREATE PROCEDURE sp_TClaseGrupales_GetAvailable()
BEGIN
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
END
        ");

        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, false);
    }
};

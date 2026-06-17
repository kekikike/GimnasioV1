-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS gestion_gimnasio;
USE gestion_gimnasio;

-- 1. Tabla Roles
CREATE TABLE Roles (
    idRol INT AUTO_INCREMENT PRIMARY KEY,
    nombreRol VARCHAR(50) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL
);

-- 2. Tabla Usuario
CREATE TABLE Usuario (
    idUsuario INT AUTO_INCREMENT PRIMARY KEY,
    idRol INT NOT NULL,
    nombre1 VARCHAR(100) NOT NULL,
    nombre2 VARCHAR(100) NULL,
    apellido1 VARCHAR(100) NOT NULL,
    apellido2 VARCHAR(100) NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    telefono INT NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (idRol) REFERENCES Roles(idRol)
);

-- 3. Tabla Sucursal
CREATE TABLE Sucursal (
    idSucursal INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL
);

-- 4. Tabla Empleado
CREATE TABLE Empleado (
    carnetEmpleado INT PRIMARY KEY, -- Se asume número de documento ingresado manualmente
    idUsuario INT NOT NULL UNIQUE,
    idSucursal INT NOT NULL,
    sueldo DECIMAL(10,2) NOT NULL,
    especialidad INT NOT NULL,
    fechaContratoInicio DATE NOT NULL,
    fechaContratoFin DATE NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario),
    FOREIGN KEY (idSucursal) REFERENCES Sucursal(idSucursal)
);

-- 5. Tabla HorarioLaboral
CREATE TABLE HorarioLaboral (
    idHorario INT AUTO_INCREMENT PRIMARY KEY,
    carnetEmpleado INT NOT NULL,
    diaSemana VARCHAR(20) NOT NULL,
    horaEntradaEsperada TIME NOT NULL,
    horaSalidaEsperada TIME NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (carnetEmpleado) REFERENCES Empleado(carnetEmpleado)
);

-- 6. Tabla ControlAsistencia
CREATE TABLE ControlAsistencia (
    idAsistencia INT AUTO_INCREMENT PRIMARY KEY,
    carnetEmpleado INT NOT NULL,
    fecha DATE NOT NULL,
    horaEntrada TIME NOT NULL,
    horaSalida TIME NULL,
    estadoAsistencia VARCHAR(50) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (carnetEmpleado) REFERENCES Empleado(carnetEmpleado)
);

-- 7. Tabla EsquemaSueldo
CREATE TABLE EsquemaSueldo (
    idEsquemaSueldo INT AUTO_INCREMENT PRIMARY KEY,
    carnetEmpleado INT NOT NULL,
    modalidadPago VARCHAR(50) NOT NULL,
    montoBase DECIMAL(10,2) NOT NULL,
    tarifaHoraOClase INT NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (carnetEmpleado) REFERENCES Empleado(carnetEmpleado)
);

-- 8. Tabla Socio
CREATE TABLE Socio (
    carnetSocio INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT NOT NULL UNIQUE,
    codigoAcceso VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NULL,
    fotografiaUrl VARCHAR(255) NULL,
    nombreContactoEmergencia VARCHAR(150) NULL,
    telefonoContactoEmergencia INT NULL,
    observacionesMedicas TEXT NULL,
    estadoSocio VARCHAR(50) NOT NULL,
    Asistencias INT NOT NULL DEFAULT 0,
    Faltas INT NOT NULL DEFAULT 0,
    strikes INT NOT NULL DEFAULT 0,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (idUsuario) REFERENCES Usuario(idUsuario)
);

-- Configuración del inicio de secuencia requerido para socios
ALTER TABLE Socio AUTO_INCREMENT = 6700001;

-- 9. Tabla Plan
CREATE TABLE Plan (
    idPlan INT AUTO_INCREMENT PRIMARY KEY,
    nombrePlan VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    costoPlan DECIMAL(10,2) NOT NULL,
    duracionDias INT NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL
);

-- 10. Tabla Membresia
CREATE TABLE Membresia (
    idMembresia INT AUTO_INCREMENT PRIMARY KEY,
    idPlan INT NOT NULL,
    carnetSocio INT NOT NULL,
    idSucursal INT NOT NULL,
    fechaInicioMembresia DATE NOT NULL,
    fechaFinMembresia DATE NOT NULL,
    estadoMembresia VARCHAR(50) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (idPlan) REFERENCES Plan(idPlan),
    FOREIGN KEY (carnetSocio) REFERENCES Socio(carnetSocio),
    FOREIGN KEY (idSucursal) REFERENCES Sucursal(idSucursal)
);

-- 11. Tabla ControlAcceso
CREATE TABLE ControlAcceso (
    idControlAcceso INT AUTO_INCREMENT PRIMARY KEY,
    carnetSocio INT NOT NULL,
    idSucursal INT NOT NULL,
    fechaAcceso DATE NOT NULL,
    horaAcceso TIME NOT NULL,
    bloqueo BOOLEAN NOT NULL DEFAULT FALSE,
    motivoDenegacion VARCHAR(255) NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (carnetSocio) REFERENCES Socio(carnetSocio),
    FOREIGN KEY (idSucursal) REFERENCES Sucursal(idSucursal)
);

-- 12. Tabla Penalizacion
CREATE TABLE Penalizacion (
    idPenalizacion INT AUTO_INCREMENT PRIMARY KEY,
    carnetSocio INT NOT NULL,
    fecha DATE NOT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (carnetSocio) REFERENCES Socio(carnetSocio)
);

-- 13. Tabla Notificacion
CREATE TABLE Notificacion (
    idNotificacion INT AUTO_INCREMENT PRIMARY KEY,
    carnetSocio INT NOT NULL,
    tipoNotificacion VARCHAR(50) NOT NULL,
    mensaje TEXT NOT NULL,
    canal VARCHAR(50) NOT NULL,
    fechaEnvio DATE NOT NULL,
    estado VARCHAR(50) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (carnetSocio) REFERENCES Socio(carnetSocio)
);

-- 14. Tabla Actividad
CREATE TABLE Actividad (
    idActividad INT AUTO_INCREMENT PRIMARY KEY,
    nombreActividad VARCHAR(100) NOT NULL,
    descripcionActividad TEXT NULL,
    estado BOOLEAN NOT NULL DEFAULT TRUE,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL
);

-- 15. Tabla ClaseGrupal
CREATE TABLE ClaseGrupal (
    idClaseGrupal INT AUTO_INCREMENT PRIMARY KEY,
    idActividad INT NOT NULL,
    carnetEmpleado INT NOT NULL,
    idSucursal INT NOT NULL,
    fecha DATE NOT NULL,
    horaInicio TIME NOT NULL,
    horaFin TIME NOT NULL,
    cupoMaximo INT NOT NULL,
    cupoDisponible INT NOT NULL,
    estadoClase VARCHAR(50) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (idActividad) REFERENCES Actividad(idActividad),
    FOREIGN KEY (carnetEmpleado) REFERENCES Empleado(carnetEmpleado),
    FOREIGN KEY (idSucursal) REFERENCES Sucursal(idSucursal)
);

-- 16. Tabla Reserva
CREATE TABLE Reserva (
    idReserva INT AUTO_INCREMENT PRIMARY KEY,
    idClaseGrupal INT NOT NULL,
    carnetSocio INT NOT NULL,
    fechaReserva DATE NOT NULL,
    horaReserva TIME NOT NULL,
    estadoReserva VARCHAR(50) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (idClaseGrupal) REFERENCES ClaseGrupal(idClaseGrupal),
    FOREIGN KEY (carnetSocio) REFERENCES Socio(carnetSocio)
);

-- 17. Tabla Caja
CREATE TABLE Caja (
    idCaja INT AUTO_INCREMENT PRIMARY KEY,
    idSucursal INT NOT NULL,
    carnetEmpleado INT NOT NULL,
    fechaApertura DATE NOT NULL,
    horaApertura TIME NOT NULL,
    montoApertura DECIMAL(10,2) NOT NULL,
    montoCierre DECIMAL(10,2) NULL,
    montoCierreCalculado DECIMAL(10,2) NULL,
    diferenciaArqueo DECIMAL(10,2) NULL,
    estadoCaja VARCHAR(50) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (idSucursal) REFERENCES Sucursal(idSucursal),
    FOREIGN KEY (carnetEmpleado) REFERENCES Empleado(carnetEmpleado)
);

-- 18. Tabla Recibo
CREATE TABLE Recibo (
    idRecibo INT AUTO_INCREMENT PRIMARY KEY,
    idCaja INT NOT NULL,
    idMembresia INT NOT NULL,
    nroRecibo VARCHAR(50) NOT NULL UNIQUE,
    montoTotal DECIMAL(10,2) NOT NULL,
    fechaPago DATE NOT NULL,
    horaPago TIME NOT NULL,
    estadoRecibo VARCHAR(50) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (idCaja) REFERENCES Caja(idCaja),
    FOREIGN KEY (idMembresia) REFERENCES Membresia(idMembresia)
);

-- 19. Tabla DetalleMetodoPago
CREATE TABLE DetalleMetodoPago (
    idMetodoPago INT AUTO_INCREMENT PRIMARY KEY,
    idRecibo INT NOT NULL,
    tipoPago VARCHAR(50) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (idRecibo) REFERENCES Recibo(idRecibo)
);

-- 20. Tabla Marca
CREATE TABLE Marca (
    idMarca INT AUTO_INCREMENT PRIMARY KEY,
    nombreMarca VARCHAR(100) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL
);

-- 21. Tabla Equipamiento
CREATE TABLE Equipamiento (
    idEquipo INT AUTO_INCREMENT PRIMARY KEY,
    idSucursal INT NOT NULL,
    idMarca INT NOT NULL,
    nombreEquipo VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NULL,
    fechaAdquisicion DATE NULL,
    estadoEquipo VARCHAR(50) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (idSucursal) REFERENCES Sucursal(idSucursal),
    FOREIGN KEY (idMarca) REFERENCES Marca(idMarca)
);

-- 22. Tabla MantenimientoPreventivo
CREATE TABLE MantenimientoPreventivo (
    idMantenimiento INT AUTO_INCREMENT PRIMARY KEY,
    idEquipo INT NOT NULL,
    fechaProgramada DATE NOT NULL,
    fechaRealizada DATE NULL,
    descripcionMantenimiento TEXT NULL,
    costoMantenimiento DECIMAL(10,2) NULL,
    tecnicoAsignado VARCHAR(150) NULL,
    estadoMantenimiento VARCHAR(50) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (idEquipo) REFERENCES Equipamiento(idEquipo)
);

-- 23. Tabla ReporteFalla
CREATE TABLE ReporteFalla (
    idReporteFalla INT AUTO_INCREMENT PRIMARY KEY,
    idEquipo INT NOT NULL,
    carnetEmpleado INT NOT NULL,
    fechaReporte DATE NOT NULL,
    horaReporte TIME NOT NULL,
    descripcionFalla TEXT NOT NULL,
    gravedad VARCHAR(50) NOT NULL,
    estadoReporte VARCHAR(50) NOT NULL,
    estadoA BOOLEAN NOT NULL DEFAULT TRUE,
    fechaA DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuarioA INT NULL,
    FOREIGN KEY (idEquipo) REFERENCES Equipamiento(idEquipo),
    FOREIGN KEY (carnetEmpleado) REFERENCES Empleado(carnetEmpleado)
);

-- 24. Tabla tAuditoria
CREATE TABLE tAuditoria (
    idAuditoria INT AUTO_INCREMENT PRIMARY KEY,
    tablaNombre VARCHAR(50) NULL,
    registroId INT NULL,
    accion VARCHAR(50) NULL,
    campo VARCHAR(100) NULL,
    valorAnterior TEXT NULL,  -- En MySQL usamos TEXT para simular el varchar(MAX) de tu imagen
    valorNuevo TEXT NULL,     -- En MySQL usamos TEXT para simular el varchar(MAX) de tu imagen
    usuarioA INT NULL,
    fechaA DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
    direccionIP VARCHAR(50) NULL,
    detalles VARCHAR(500) NULL
);

-- ────────────────────────────────────────────────────────
-- Vinculación de las columnas de auditoría (usuarioA -> Usuario)
-- ────────────────────────────────────────────────────────

ALTER TABLE Roles ADD CONSTRAINT fk_roles_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Usuario ADD CONSTRAINT fk_usuario_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Sucursal ADD CONSTRAINT fk_sucursal_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Empleado ADD CONSTRAINT fk_empleado_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE HorarioLaboral ADD CONSTRAINT fk_horario_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE ControlAsistencia ADD CONSTRAINT fk_asistencia_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE EsquemaSueldo ADD CONSTRAINT fk_sueldo_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Socio ADD CONSTRAINT fk_socio_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Plan ADD CONSTRAINT fk_plan_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Membresia ADD CONSTRAINT fk_membresia_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE ControlAcceso ADD CONSTRAINT fk_acceso_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Penalizacion ADD CONSTRAINT fk_penalizacion_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Notificacion ADD CONSTRAINT fk_notificacion_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Actividad ADD CONSTRAINT fk_actividad_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE ClaseGrupal ADD CONSTRAINT fk_clase_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Reserva ADD CONSTRAINT fk_reserva_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Caja ADD CONSTRAINT fk_caja_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Recibo ADD CONSTRAINT fk_recibo_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE DetalleMetodoPago ADD CONSTRAINT fk_detallepago_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Marca ADD CONSTRAINT fk_marca_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE Equipamiento ADD CONSTRAINT fk_equipamiento_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE MantenimientoPreventivo ADD CONSTRAINT fk_mantenimiento_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE ReporteFalla ADD CONSTRAINT fk_reportefalla_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
ALTER TABLE tAuditoria ADD CONSTRAINT fk_tauditoria_usuarioA FOREIGN KEY (usuarioA) REFERENCES Usuario(idUsuario);
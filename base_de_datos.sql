-- =========================================================
-- Base de datos: control_pacientes
-- Importar en phpMyAdmin (XAMPP)
-- =========================================================

CREATE DATABASE IF NOT EXISTS control_pacientes
  CHARACTER SET utf8 COLLATE utf8_general_ci;

USE control_pacientes;

-- ---------------------------------------------------------
-- Tabla: usuarios (login de recepcionista / admin)
-- ---------------------------------------------------------
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

INSERT INTO usuarios (usuario, password) VALUES ('admin', '1234');

-- ---------------------------------------------------------
-- Tabla: pacientes
-- ---------------------------------------------------------
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(80) NOT NULL,
    apellido VARCHAR(80) NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(150),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabla: medicos
-- ---------------------------------------------------------
CREATE TABLE medicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    especialidad VARCHAR(80) NOT NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabla: consultas
-- estado: 'activa'          -> en curso, aun no se atiende/termina
--         'pendiente_pago'  -> ya se dio de baja, se le calcula mora
--         'pagada'          -> ya se cobro
-- ---------------------------------------------------------
CREATE TABLE consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    id_medico INT NOT NULL,
    fecha_consulta VARCHAR(20) NOT NULL,
    valor_consulta DECIMAL(10,2) NOT NULL,
    estado ENUM('activa','pendiente_pago','pagada') NOT NULL DEFAULT 'activa',
    fecha_baja DATETIME NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id),
    FOREIGN KEY (id_medico) REFERENCES medicos(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tabla: cobros (historial de pagos ya procesados)
-- ---------------------------------------------------------
CREATE TABLE cobros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_consulta INT NOT NULL,
    valor_consulta DECIMAL(10,2) NOT NULL,
    dias_atraso INT NOT NULL DEFAULT 0,
    mora DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_pagado DECIMAL(10,2) NOT NULL,
    monto_recibido DECIMAL(10,2) NOT NULL,
    cambio DECIMAL(10,2) NOT NULL DEFAULT 0,
    fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_consulta) REFERENCES consultas(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Datos de ejemplo (opcional, para probar rapido)
-- ---------------------------------------------------------
INSERT INTO medicos (nombre, especialidad) VALUES
('Dr. Carlos Mejia', 'Medicina General'),
('Dra. Ana Funez', 'Pediatria');

INSERT INTO pacientes (codigo, nombre, apellido, telefono, direccion) VALUES
('P001', 'Jose', 'Ramirez', '9999-0001', 'Col. Kennedy'),
('P002', 'Maria', 'Lopez', '9999-0002', 'Barrio Abajo');

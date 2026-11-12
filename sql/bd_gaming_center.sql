DROP DATABASE IF EXISTS db_pj5_gamingcenter;
CREATE DATABASE db_pj5_gamingcenter CHARACTER SET utf8mb4;
USE db_pj5_gamingcenter;

-- Usuarios (Trabajadores del local: Gamemasters y Admins)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom_real VARCHAR(50) NOT NULL,
    cognom_real VARCHAR(80) NOT NULL,
    rol ENUM('gamemaster','admin') DEFAULT 'gamemaster'
) ENGINE=InnoDB;

-- Salas del local (PC Standard, Simuladores/Consolas y VIP)
CREATE TABLE salas (
    id_sala INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    tipo ENUM('pc','consola','vip') NOT NULL
) ENGINE=InnoDB;

-- Módulos (Los puestos de juego dentro de cada sala)
CREATE TABLE modulos (
    id_modulo INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    tipo ENUM('solo','duo','squad','party') NOT NULL,
    capacidad TINYINT NOT NULL,
    estado ENUM('lliure','ocupat') DEFAULT 'lliure',
    FOREIGN KEY (id_sala) REFERENCES salas (id_sala)
) ENGINE=InnoDB;

-- Ocupaciones (Control en tiempo real e Histórico)
-- Si fecha_fin es NULL, están jugando ahora. Si tiene fecha, ya han terminado.
CREATE TABLE ocupaciones (
    id_ocupacion INT AUTO_INCREMENT PRIMARY KEY,
    id_modulo INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_fin DATETIME NULL,
    FOREIGN KEY (id_modulo) REFERENCES modulos (id_modulo),
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB;

INSERT INTO usuarios (username, password, nom_real, cognom_real, rol) VALUES
('admin', '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Admin', 'Global', 'admin'),
('izan',  '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Izan', 'Apellido', 'gamemaster'),
('hao',   '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Hao', 'Apellido', 'gamemaster'),
('enric', '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Enric', 'Apellido', 'gamemaster'),
('dani',  '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Dani', 'Apellido', 'gamemaster');


-- 1. Creamos las salas de PC (asegúrate de que los IDs coincidan con tu tabla)
INSERT INTO salas (nombre, tipo) VALUES 
('SALA 1: OVERCLOCK ROOM', 'pc'),
('SALA 2: PC STANDARD', 'pc'),
('SALA 3: PC STANDARD', 'pc'),
('SALA 4: PC STANDARD', 'pc');

-- 2. Metemos los PCs de la SALA 1 (suponiendo que la Sala 1 tiene id_sala = 1)
-- En el plano teníais: 1 Squad (5 PCs) y 2 Dúos (4 PCs).
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES 
-- Módulo Squad (5 puestos)
(1, 'PC-01', 'squad', 1, 'lliure'),
(1, 'PC-02', 'squad', 1, 'lliure'),
(1, 'PC-03', 'squad', 1, 'lliure'),
(1, 'PC-04', 'squad', 1, 'lliure'),
(1, 'PC-05', 'squad', 1, 'lliure'),

-- Módulos Dúo (2 parejas = 4 puestos)
(1, 'PC-06', 'duo', 1, 'lliure'),
(1, 'PC-07', 'duo', 1, 'lliure'),
(1, 'PC-08', 'duo', 1, 'lliure'),
(1, 'PC-09', 'duo', 1, 'lliure'),

-- (Opcional) Si quieres añadir un par de Solos para probar la isleta de Solos
(1, 'PC-10', 'solo', 1, 'lliure'),
(1, 'PC-11', 'solo', 1, 'lliure');


ALTER TABLE usuarios ENGINE=InnoDB;
ALTER TABLE salas ENGINE=InnoDB;
ALTER TABLE modulos ENGINE=InnoDB;
ALTER TABLE ocupaciones ENGINE=InnoDB;  
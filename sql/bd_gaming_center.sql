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
('izan',  '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Izan', 'Naranjo', 'gamemaster'),
('hao',   '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Hao', 'Xiang', 'gamemaster'),
('enric', '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Enric', 'Venanci', 'gamemaster'),
('dani',  '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Dani', 'Mayorov', 'gamemaster');


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

-- ========================================================================
-- 1. LIMPIEZA DE PUESTOS DE PRUEBA (SALA 1)
-- Borramos los PCs de prueba de la Sala 1 para que no se dupliquen con la Sala 2
-- ========================================================================
DELETE FROM modulos 
WHERE id_sala = 1 AND nombre IN ('PC-10', 'PC-11');


-- ========================================================================
-- 2. INSERTS DE LA SALA 2: PC STANDARD (id_sala = 2)
-- Distribución: 1 Arena Squad (5 puestos) y 2 Dúos (4 puestos)
-- Total: 9 ordenadores (del PC-10 al PC-18)
-- ========================================================================
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES 
-- Módulo 2.1: Arena Squad (5 puestos)
(2, 'PC-10', 'squad', 1, 'lliure'),
(2, 'PC-11', 'squad', 1, 'lliure'),
(2, 'PC-12', 'squad', 1, 'lliure'),
(2, 'PC-13', 'squad', 1, 'lliure'),
(2, 'PC-14', 'squad', 1, 'lliure'),

-- Módulos 2.2 y 2.3: Zona Dúo (2 parejas = 4 puestos)
(2, 'PC-15', 'duo', 1, 'lliure'),
(2, 'PC-16', 'duo', 1, 'lliure'),
(2, 'PC-17', 'duo', 1, 'lliure'),
(2, 'PC-18', 'duo', 1, 'lliure');


-- ========================================================================
-- 3. INSERTS DE LA SALA 3: PC STANDARD (id_sala = 3)
-- Distribución: 1 Arena Squad (5 puestos), 2 Dúos (4 puestos) y 2 Solos (2 puestos)
-- Total: 11 ordenadores (del PC-19 al PC-29)
-- ========================================================================
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES 
-- Módulo 3.1: Arena Squad (5 puestos)
(3, 'PC-19', 'squad', 1, 'lliure'),
(3, 'PC-20', 'squad', 1, 'lliure'),
(3, 'PC-21', 'squad', 1, 'lliure'),
(3, 'PC-22', 'squad', 1, 'lliure'),
(3, 'PC-23', 'squad', 1, 'lliure'),

-- Módulos 3.2 y 3.3: Zona Dúo (2 parejas = 4 puestos)
(3, 'PC-24', 'duo', 1, 'lliure'),
(3, 'PC-25', 'duo', 1, 'lliure'),
(3, 'PC-26', 'duo', 1, 'lliure'),
(3, 'PC-27', 'duo', 1, 'lliure'),

-- Módulos 3.4 y 3.5: Zona Solo (2 puestos)
(3, 'PC-28', 'solo', 1, 'lliure'),
(3, 'PC-29', 'solo', 1, 'lliure');


-- ========================================================================
-- 4. INSERTS DE LA SALA 4: PC STANDARD (id_sala = 4)
-- Distribución: 1 Arena Squad (5 puestos), 2 Dúos (4 puestos) y 2 Solos (2 puestos)
-- Total: 11 ordenadores (del PC-30 al PC-40)
-- ========================================================================
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES 
-- Módulo 4.1: Arena Squad (5 puestos)
(4, 'PC-30', 'squad', 1, 'lliure'),
(4, 'PC-31', 'squad', 1, 'lliure'),
(4, 'PC-32', 'squad', 1, 'lliure'),
(4, 'PC-33', 'squad', 1, 'lliure'),
(4, 'PC-34', 'squad', 1, 'lliure'),

-- Módulos 4.2 y 4.3: Zona Dúo (2 parejas = 4 puestos)
(4, 'PC-35', 'duo', 1, 'lliure'),
(4, 'PC-36', 'duo', 1, 'lliure'),
(4, 'PC-37', 'duo', 1, 'lliure'),
(4, 'PC-38', 'duo', 1, 'lliure'),

-- Módulos 4.4 y 4.5: Zona Solo (2 puestos)
(4, 'PC-39', 'solo', 1, 'lliure'),
(4, 'PC-40', 'solo', 1, 'lliure');

-- Salas de consolas / simuladores
INSERT INTO salas (nombre, tipo) VALUES
('SALA 5: CONSOLE ZONE', 'consola'),
('SALA 6: VR', 'consola'),
('SALA 7: SIMULADORES', 'consola');

-- SALA 5: Party (sofás, 4 jugadores), Dúo (2 jugadores) y Solo
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
((SELECT id_sala FROM salas WHERE nombre = 'SALA 5: CONSOLE ZONE'), 'MND-01', 'party', 4, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 5: CONSOLE ZONE'), 'MND-02', 'party', 4, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 5: CONSOLE ZONE'), 'MND-03', 'party', 4, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 5: CONSOLE ZONE'), 'MND-04', 'party', 4, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 5: CONSOLE ZONE'), 'SIM-01', 'duo', 2, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 5: CONSOLE ZONE'), 'SIM-02', 'duo', 2, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 5: CONSOLE ZONE'), 'SIM-03', 'duo', 2, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 5: CONSOLE ZONE'), 'SIM-04', 'duo', 2, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 5: CONSOLE ZONE'), 'SIM-05', 'solo', 1, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 5: CONSOLE ZONE'), 'SIM-06', 'solo', 1, 'lliure');

-- SALA 6: VR (todos solo)
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
((SELECT id_sala FROM salas WHERE nombre = 'SALA 6: VR'), 'MND-05', 'party', 4, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 6: VR'), 'MND-06', 'party', 4, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 6: VR'), 'MND-07', 'party', 4, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 6: VR'), 'MND-08', 'party', 4, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 6: VR'), 'VR-01', 'duo', 2, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 6: VR'), 'VR-02', 'duo', 2, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 6: VR'), 'VR-03', 'duo', 2, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 6: VR'), 'VR-04', 'duo', 2, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 6: VR'), 'VR-05', 'solo', 1, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 6: VR'), 'VR-06', 'solo', 1, 'lliure');

-- SALA 7: Simuladores/Consolas (todos solo)
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
((SELECT id_sala FROM salas WHERE nombre = 'SALA 7: SIMULADORES'), 'MND-09', 'party', 4, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 7: SIMULADORES'), 'MND-10', 'party', 4, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 7: SIMULADORES'), 'MND-11', 'party', 4, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 7: SIMULADORES'), 'MND-12', 'party', 4, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 7: SIMULADORES'), 'SIM-07', 'duo', 2, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 7: SIMULADORES'), 'SIM-08', 'duo', 2, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 7: SIMULADORES'), 'SIM-09', 'duo', 2, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 7: SIMULADORES'), 'SIM-10', 'duo', 2, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 7: SIMULADORES'), 'SIM-11', 'solo', 1, 'lliure'),
((SELECT id_sala FROM salas WHERE nombre = 'SALA 7: SIMULADORES'), 'SIM-12', 'solo', 1, 'lliure');


-- Salas VIP
INSERT INTO salas (nombre, tipo) VALUES
('SALA 8: SALA VIP', 'vip'),
('SALA 9: SALA VIP', 'vip');

-- SALA 8 VIP: 5 puestos squad, 2 parejas duo y 2 puestos solo
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
(8, 'VIP-01', 'squad', 3, 'lliure'),
(8, 'VIP-02', 'squad', 3, 'lliure'),
(8, 'VIP-03', 'squad', 3, 'lliure'),
(8, 'VIP-04', 'squad', 3, 'lliure'),
(8, 'VIP-05', 'squad', 3, 'lliure'),
(8, 'VIP-06', 'duo', 3, 'lliure'),
(8, 'VIP-07', 'duo', 3, 'lliure'),
(8, 'VIP-08', 'duo', 3, 'lliure'),
(8, 'VIP-09', 'duo', 3, 'lliure'),
(8, 'VIP-10', 'solo', 3, 'lliure'),
(8, 'VIP-11', 'solo', 3, 'lliure');

-- SALA 9 VIP: 5 puestos squad, 2 parejas duo y 2 puestos solo
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
(9, 'VIP-12', 'squad', 3, 'lliure'),
(9, 'VIP-13', 'squad', 3, 'lliure'),
(9, 'VIP-14', 'squad', 3, 'lliure'),
(9, 'VIP-15', 'squad', 3, 'lliure'),
(9, 'VIP-16', 'squad', 3, 'lliure'),
(9, 'VIP-17', 'duo', 3, 'lliure'),
(9, 'VIP-18', 'duo', 3, 'lliure'),
(9, 'VIP-19', 'duo', 3, 'lliure'),
(9, 'VIP-20', 'duo', 3, 'lliure'),
(9, 'VIP-21', 'solo', 3, 'lliure'),
(9, 'VIP-22', 'solo', 3, 'lliure');

ALTER TABLE usuarios ENGINE=InnoDB;
ALTER TABLE salas ENGINE=InnoDB;
ALTER TABLE modulos ENGINE=InnoDB;
ALTER TABLE ocupaciones ENGINE=InnoDB;  
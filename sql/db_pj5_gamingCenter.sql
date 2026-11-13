DROP DATABASE IF EXISTS db_pj5_gamingcenter;
CREATE DATABASE db_pj5_gamingcenter CHARACTER SET utf8mb4;
USE db_pj5_gamingcenter;

-- Todas las tablas en InnoDB: es el motor que permite transacciones (rollBack)
-- y que respeta las claves foráneas

-- Usuarios (trabajadores del local: gamemasters y admins)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom_real VARCHAR(50) NOT NULL,
    cognom_real VARCHAR(80) NOT NULL,
    rol ENUM('gamemaster','admin') DEFAULT 'gamemaster'
) ENGINE=InnoDB;

-- Salas del local (PC Standard, Consolas/Simuladores y VIP)
CREATE TABLE salas (
    id_sala INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    tipo ENUM('pc','consola','vip') NOT NULL
) ENGINE=InnoDB;

-- Módulos: cada fila es UN puesto (un PC, un mando, unas gafas...).
-- El tipo dice a qué grupo pertenece (solo, duo, squad o party)
CREATE TABLE modulos (
    id_modulo INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    tipo ENUM('solo','duo','squad','party') NOT NULL,
    capacidad TINYINT NOT NULL,
    estado ENUM('lliure','ocupat') DEFAULT 'lliure',
    FOREIGN KEY (id_sala) REFERENCES salas (id_sala)
) ENGINE=InnoDB;

-- Ocupaciones (control en tiempo real e histórico)
-- Si fecha_fin es NULL están jugando ahora; si tiene fecha, ya han terminado
CREATE TABLE ocupaciones (
    id_ocupacion INT AUTO_INCREMENT PRIMARY KEY,
    id_modulo INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_fin DATETIME NULL,
    FOREIGN KEY (id_modulo) REFERENCES modulos (id_modulo),
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB;


-- ========================================================================
-- USUARIOS (ids 1 a 5). Todos tienen la misma contraseña de prueba
-- ========================================================================
INSERT INTO usuarios (username, password, nom_real, cognom_real, rol) VALUES
('admin', '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Admin', 'Global', 'admin'),
('izan',  '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Izan', 'Naranjo', 'gamemaster'),
('hao',   '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Hao', 'Xiang', 'gamemaster'),
('enric', '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Enric', 'Venanci', 'gamemaster'),
('dani',  '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Dani', 'Mayorov', 'gamemaster');


-- ========================================================================
-- SALAS (ids 1 a 9)
-- ========================================================================
INSERT INTO salas (nombre, tipo) VALUES
('SALA 1: OVERCLOCK ROOM', 'pc'),
('SALA 2: PC STANDARD', 'pc'),
('SALA 3: PC STANDARD', 'pc'),
('SALA 4: PC STANDARD', 'pc'),
('SALA 5: CONSOLE ZONE', 'consola'),
('SALA 6: VR', 'consola'),
('SALA 7: SIMULADORES', 'consola'),
('SALA 8: SALA VIP', 'vip'),
('SALA 9: SALA VIP', 'vip');


-- ========================================================================
-- MÓDULOS: capacidad = 1 en todos porque cada fila es un puesto individual.
-- Los ids salen en orden (1, 2, 3...) y se indican en cada sala porque
-- las ocupaciones de abajo los usan.
-- Los 5 puestos con 'ocupat' son los que tienen una ocupación abierta al final.
-- ========================================================================

-- SALA 1: 1 Arena Squad (5 puestos) y 2 módulos Dúo (4 puestos). Ids 1 a 9
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
(1, 'PC-01', 'squad', 1, 'lliure'),
(1, 'PC-02', 'squad', 1, 'lliure'),
(1, 'PC-03', 'squad', 1, 'ocupat'),
(1, 'PC-04', 'squad', 1, 'lliure'),
(1, 'PC-05', 'squad', 1, 'lliure'),
(1, 'PC-06', 'duo', 1, 'lliure'),
(1, 'PC-07', 'duo', 1, 'lliure'),
(1, 'PC-08', 'duo', 1, 'lliure'),
(1, 'PC-09', 'duo', 1, 'lliure');

-- SALA 2: 1 Arena Squad (5 puestos) y 2 módulos Dúo (4 puestos). Ids 10 a 18
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
(2, 'PC-10', 'squad', 1, 'lliure'),
(2, 'PC-11', 'squad', 1, 'lliure'),
(2, 'PC-12', 'squad', 1, 'lliure'),
(2, 'PC-13', 'squad', 1, 'lliure'),
(2, 'PC-14', 'squad', 1, 'lliure'),
(2, 'PC-15', 'duo', 1, 'lliure'),
(2, 'PC-16', 'duo', 1, 'lliure'),
(2, 'PC-17', 'duo', 1, 'lliure'),
(2, 'PC-18', 'duo', 1, 'lliure');

-- SALA 3: 1 Arena Squad (5), 2 módulos Dúo (4) y 2 Solos (2). Ids 19 a 29
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
(3, 'PC-19', 'squad', 1, 'lliure'),
(3, 'PC-20', 'squad', 1, 'lliure'),
(3, 'PC-21', 'squad', 1, 'lliure'),
(3, 'PC-22', 'squad', 1, 'lliure'),
(3, 'PC-23', 'squad', 1, 'lliure'),
(3, 'PC-24', 'duo', 1, 'lliure'),
(3, 'PC-25', 'duo', 1, 'lliure'),
(3, 'PC-26', 'duo', 1, 'ocupat'),
(3, 'PC-27', 'duo', 1, 'lliure'),
(3, 'PC-28', 'solo', 1, 'lliure'),
(3, 'PC-29', 'solo', 1, 'lliure');

-- SALA 4: 1 Arena Squad (5), 2 módulos Dúo (4) y 2 Solos (2). Ids 30 a 40
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
(4, 'PC-30', 'squad', 1, 'lliure'),
(4, 'PC-31', 'squad', 1, 'lliure'),
(4, 'PC-32', 'squad', 1, 'lliure'),
(4, 'PC-33', 'squad', 1, 'lliure'),
(4, 'PC-34', 'squad', 1, 'lliure'),
(4, 'PC-35', 'duo', 1, 'lliure'),
(4, 'PC-36', 'duo', 1, 'lliure'),
(4, 'PC-37', 'duo', 1, 'lliure'),
(4, 'PC-38', 'duo', 1, 'lliure'),
(4, 'PC-39', 'solo', 1, 'lliure'),
(4, 'PC-40', 'solo', 1, 'lliure');

-- SALA 5: Party (4 mandos en los sofás), 2 módulos Dúo de simuladores y 2 Solos. Ids 41 a 50
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
(5, 'MND-01', 'party', 1, 'lliure'),
(5, 'MND-02', 'party', 1, 'ocupat'),
(5, 'MND-03', 'party', 1, 'lliure'),
(5, 'MND-04', 'party', 1, 'lliure'),
(5, 'SIM-01', 'duo', 1, 'lliure'),
(5, 'SIM-02', 'duo', 1, 'lliure'),
(5, 'SIM-03', 'duo', 1, 'lliure'),
(5, 'SIM-04', 'duo', 1, 'lliure'),
(5, 'SIM-05', 'solo', 1, 'lliure'),
(5, 'SIM-06', 'solo', 1, 'lliure');

-- SALA 6: Party (4 mandos), 2 módulos Dúo de gafas VR y 2 gafas VR en Solo. Ids 51 a 60
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
(6, 'MND-05', 'party', 1, 'lliure'),
(6, 'MND-06', 'party', 1, 'lliure'),
(6, 'MND-07', 'party', 1, 'lliure'),
(6, 'MND-08', 'party', 1, 'lliure'),
(6, 'VR-01', 'duo', 1, 'lliure'),
(6, 'VR-02', 'duo', 1, 'lliure'),
(6, 'VR-03', 'duo', 1, 'lliure'),
(6, 'VR-04', 'duo', 1, 'lliure'),
(6, 'VR-05', 'solo', 1, 'ocupat'),
(6, 'VR-06', 'solo', 1, 'lliure');

-- SALA 7: Party (4 mandos), 2 módulos Dúo de simuladores y 2 simuladores en Solo. Ids 61 a 70
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
(7, 'MND-09', 'party', 1, 'lliure'),
(7, 'MND-10', 'party', 1, 'lliure'),
(7, 'MND-11', 'party', 1, 'lliure'),
(7, 'MND-12', 'party', 1, 'lliure'),
(7, 'SIM-07', 'duo', 1, 'lliure'),
(7, 'SIM-08', 'duo', 1, 'lliure'),
(7, 'SIM-09', 'duo', 1, 'lliure'),
(7, 'SIM-10', 'duo', 1, 'lliure'),
(7, 'SIM-11', 'solo', 1, 'lliure'),
(7, 'SIM-12', 'solo', 1, 'lliure');

-- SALA 8 VIP: 1 Arena Squad (5), 2 módulos Dúo (4) y 2 Solos (2). Ids 71 a 81
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
(8, 'VIP-01', 'squad', 1, 'lliure'),
(8, 'VIP-02', 'squad', 1, 'lliure'),
(8, 'VIP-03', 'squad', 1, 'lliure'),
(8, 'VIP-04', 'squad', 1, 'lliure'),
(8, 'VIP-05', 'squad', 1, 'lliure'),
(8, 'VIP-06', 'duo', 1, 'lliure'),
(8, 'VIP-07', 'duo', 1, 'ocupat'),
(8, 'VIP-08', 'duo', 1, 'lliure'),
(8, 'VIP-09', 'duo', 1, 'lliure'),
(8, 'VIP-10', 'solo', 1, 'lliure'),
(8, 'VIP-11', 'solo', 1, 'lliure');

-- SALA 9 VIP: 1 Arena Squad (5), 2 módulos Dúo (4) y 2 Solos (2). Ids 82 a 92
INSERT INTO modulos (id_sala, nombre, tipo, capacidad, estado) VALUES
(9, 'VIP-12', 'squad', 1, 'lliure'),
(9, 'VIP-13', 'squad', 1, 'lliure'),
(9, 'VIP-14', 'squad', 1, 'lliure'),
(9, 'VIP-15', 'squad', 1, 'lliure'),
(9, 'VIP-16', 'squad', 1, 'lliure'),
(9, 'VIP-17', 'duo', 1, 'lliure'),
(9, 'VIP-18', 'duo', 1, 'lliure'),
(9, 'VIP-19', 'duo', 1, 'lliure'),
(9, 'VIP-20', 'duo', 1, 'lliure'),
(9, 'VIP-21', 'solo', 1, 'lliure'),
(9, 'VIP-22', 'solo', 1, 'lliure');


-- ========================================================================
-- OCUPACIONES DE EJEMPLO (35): 30 ya terminadas y 5 en curso.
-- Usuarios: 1 admin, 2 izan, 3 hao, 4 enric, 5 dani
-- ========================================================================
INSERT INTO ocupaciones (id_modulo, id_usuario, fecha_inicio, fecha_fin) VALUES
-- Lunes 9
(1,  1, '2026-11-09 10:00:00', '2026-11-09 12:00:00'),  -- PC-01  (Sala 1)
(2,  5, '2026-11-09 10:15:00', '2026-11-09 11:45:00'),  -- PC-02  (Sala 1)
(6,  4, '2026-11-09 11:00:00', '2026-11-09 13:30:00'),  -- PC-06  (Sala 1)
(12, 3, '2026-11-09 16:00:00', '2026-11-09 18:00:00'),  -- PC-12  (Sala 2)
(24, 2, '2026-11-09 17:00:00', '2026-11-09 19:30:00'),  -- PC-24  (Sala 3)
(41, 5, '2026-11-09 18:00:00', '2026-11-09 20:00:00'),  -- MND-01 (Sala 5)
(45, 4, '2026-11-09 18:30:00', '2026-11-09 19:15:00'),  -- SIM-01 (Sala 5)
(71, 1, '2026-11-09 20:00:00', '2026-11-09 23:00:00'),  -- VIP-01 (Sala 8)
-- Martes 10
(3,  2, '2026-11-10 09:30:00', '2026-11-10 11:00:00'),  -- PC-03  (Sala 1)
(15, 3, '2026-11-10 10:00:00', '2026-11-10 12:30:00'),  -- PC-15  (Sala 2)
(28, 5, '2026-11-10 12:00:00', '2026-11-10 13:00:00'),  -- PC-28  (Sala 3)
(30, 4, '2026-11-10 16:00:00', '2026-11-10 18:45:00'),  -- PC-30  (Sala 4)
(51, 1, '2026-11-10 17:00:00', '2026-11-10 18:00:00'),  -- MND-05 (Sala 6)
(55, 2, '2026-11-10 17:30:00', '2026-11-10 18:30:00'),  -- VR-01  (Sala 6)
(65, 3, '2026-11-10 19:00:00', '2026-11-10 20:30:00'),  -- SIM-07 (Sala 7)
(87, 5, '2026-11-10 21:00:00', '2026-11-10 23:30:00'),  -- VIP-17 (Sala 9)
-- Miércoles 11
(5,  4, '2026-11-11 10:00:00', '2026-11-11 12:00:00'),  -- PC-05  (Sala 1)
(9,  1, '2026-11-11 10:30:00', '2026-11-11 11:30:00'),  -- PC-09  (Sala 1)
(19, 5, '2026-11-11 15:00:00', '2026-11-11 17:00:00'),  -- PC-19  (Sala 3)
(35, 2, '2026-11-11 16:00:00', '2026-11-11 17:30:00'),  -- PC-35  (Sala 4)
(43, 3, '2026-11-11 17:00:00', '2026-11-11 19:00:00'),  -- MND-03 (Sala 5)
(60, 4, '2026-11-11 18:00:00', '2026-11-11 18:40:00'),  -- VR-06  (Sala 6)
(69, 5, '2026-11-11 19:00:00', '2026-11-11 21:00:00'),  -- SIM-11 (Sala 7)
(80, 1, '2026-11-11 20:00:00', '2026-11-11 22:30:00'),  -- VIP-10 (Sala 8)
-- Jueves 12
(10, 2, '2026-11-12 09:00:00', '2026-11-12 10:30:00'),  -- PC-10  (Sala 2)
(22, 3, '2026-11-12 11:00:00', '2026-11-12 13:00:00'),  -- PC-22  (Sala 3)
(39, 5, '2026-11-12 16:00:00', '2026-11-12 18:00:00'),  -- PC-39  (Sala 4)
(48, 4, '2026-11-12 17:00:00', '2026-11-12 18:30:00'),  -- SIM-04 (Sala 5)
(63, 1, '2026-11-12 18:00:00', '2026-11-12 20:00:00'),  -- MND-11 (Sala 7)
(91, 2, '2026-11-12 20:00:00', '2026-11-12 22:00:00'),  -- VIP-21 (Sala 9)
-- Viernes 13: siguen jugando (fecha_fin NULL). Sus puestos están en 'ocupat' arriba
(3,  5, '2026-11-13 10:00:00', NULL),                   -- PC-03  (Sala 1)
(26, 3, '2026-11-13 10:30:00', NULL),                   -- PC-26  (Sala 3)
(42, 4, '2026-11-13 11:00:00', NULL),                   -- MND-02 (Sala 5)
(59, 2, '2026-11-13 11:15:00', NULL),                   -- VR-05  (Sala 6)
(77, 1, '2026-11-13 12:00:00', NULL);                   -- VIP-07 (Sala 8)

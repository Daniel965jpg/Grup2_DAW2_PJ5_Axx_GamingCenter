DROP DATABASE IF EXISTS db_pj5_gamingcenter;
CREATE DATABASE db_pj5_gamingcenter CHARACTER SET utf8mb4;
USE db_pj5_gamingcenter;

-- Todas las tablas en InnoDB, que es el motor que permite transacciones

-- Usuarios (los trabajadores: gamemasters y admin)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom_real VARCHAR(50) NOT NULL,
    cognom_real VARCHAR(80) NOT NULL,
    rol ENUM('gamemaster','admin') DEFAULT 'gamemaster'
) ENGINE=InnoDB;

-- Salas del local (PC, consolas y VIP)
CREATE TABLE salas (
    id_sala INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    tipo ENUM('pc','consola','vip') NOT NULL
) ENGINE=InnoDB;

-- Módulos (cada fila es un puesto: un PC, un mando, unas gafas...)
CREATE TABLE modulos (
    id_modulo INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    tipo ENUM('solo','duo','squad','party') NOT NULL,
    capacidad TINYINT NOT NULL,
    estado ENUM('lliure','ocupat') DEFAULT 'lliure',
    FOREIGN KEY (id_sala) REFERENCES salas (id_sala)
) ENGINE=InnoDB;

-- Ocupaciones (histórico y puestos en uso: si fecha_fin es NULL, siguen jugando)
CREATE TABLE ocupaciones (
    id_ocupacion INT AUTO_INCREMENT PRIMARY KEY,
    id_modulo INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_fin DATETIME NULL,
    FOREIGN KEY (id_modulo) REFERENCES modulos (id_modulo),
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB;


-- Usuarios de prueba (ids 1 a 5), todos con la misma contraseña
INSERT INTO usuarios (username, password, nom_real, cognom_real, rol) VALUES
('admin', '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Admin', 'Global', 'admin'),
('izan',  '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Izan', 'Naranjo', 'gamemaster'),
('hao',   '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Hao', 'Xiang', 'gamemaster'),
('enric', '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Enric', 'Venanci', 'gamemaster'),
('dani',  '$2y$10$GEMxzSEDQlhZwZY0S0sCZuLrD7/5cGiLNyj.FwRLf.7lKe06HoScO', 'Dani', 'Mayorov', 'gamemaster');


-- Las 9 salas (ids 1 a 9)
INSERT INTO salas (nombre, tipo) VALUES
('SALA 1: PC STANDARD', 'pc'),
('SALA 2: PC STANDARD', 'pc'),
('SALA 3: PC STANDARD', 'pc'),
('SALA 4: PC STANDARD', 'pc'),
('SALA 5: CONSOLE ZONE', 'consola'),
('SALA 6: VR', 'consola'),
('SALA 7: SIMULADORES', 'consola'),
('SALA 8: SALA VIP', 'vip'),
('SALA 9: SALA VIP', 'vip');


-- Puestos de cada sala (capacidad 1 porque cada fila es un puesto). Apuntamos los ids
-- porque las ocupaciones de abajo los usan; los 'ocupat' tienen una ocupación abierta

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


-- 35 ocupaciones de ejemplo (30 terminadas y 5 en curso), solo de gamemasters:
-- 2 izan, 3 hao, 4 enric y 5 dani (el admin no ocupa puestos)
INSERT INTO ocupaciones (id_modulo, id_usuario, fecha_inicio, fecha_fin) VALUES
-- Lunes 9
(1,  2, '2026-11-09 10:07:23', '2026-11-09 12:21:48'),  -- PC-01  (Sala 1)
(2,  5, '2026-11-09 10:18:51', '2026-11-09 11:02:09'),  -- PC-02  (Sala 1)
(6,  4, '2026-11-09 11:26:04', '2026-11-09 13:57:37'),  -- PC-06  (Sala 1)
(12, 3, '2026-11-09 16:12:45', '2026-11-09 17:38:10'),  -- PC-12  (Sala 2)
(24, 2, '2026-11-09 17:43:19', '2026-11-09 21:11:52'),  -- PC-24  (Sala 3)
(41, 5, '2026-11-09 18:04:33', '2026-11-09 19:51:06'),  -- MND-01 (Sala 5)
(45, 4, '2026-11-09 18:37:58', '2026-11-09 19:06:41'),  -- SIM-01 (Sala 5)
(71, 3, '2026-11-09 20:13:27', '2026-11-09 23:47:14'),  -- VIP-01 (Sala 8)
-- Martes 10
(3,  2, '2026-11-10 09:41:12', '2026-11-10 11:08:39'),  -- PC-03  (Sala 1)
(15, 3, '2026-11-10 10:23:47', '2026-11-10 13:52:05'),  -- PC-15  (Sala 2)
(28, 5, '2026-11-10 12:16:31', '2026-11-10 12:49:58'),  -- PC-28  (Sala 3)
(30, 4, '2026-11-10 16:08:14', '2026-11-10 18:57:26'),  -- PC-30  (Sala 4)
(51, 4, '2026-11-10 17:22:40', '2026-11-10 18:14:03'),  -- MND-05 (Sala 6)
(55, 2, '2026-11-10 17:36:09', '2026-11-10 18:03:47'),  -- VR-01  (Sala 6)
(65, 3, '2026-11-10 19:12:55', '2026-11-10 21:34:18'),  -- SIM-07 (Sala 7)
(87, 5, '2026-11-10 21:04:36', '2026-11-10 23:58:12'),  -- VIP-17 (Sala 9)
-- Miércoles 11
(5,  4, '2026-11-11 10:02:18', '2026-11-11 11:46:51'),  -- PC-05  (Sala 1)
(9,  5, '2026-11-11 10:51:07', '2026-11-11 11:19:44'),  -- PC-09  (Sala 1)
(19, 5, '2026-11-11 15:09:33', '2026-11-11 18:42:16'),  -- PC-19  (Sala 3)
(35, 2, '2026-11-11 16:27:49', '2026-11-11 17:14:02'),  -- PC-35  (Sala 4)
(43, 3, '2026-11-11 17:18:21', '2026-11-11 19:33:45'),  -- MND-03 (Sala 5)
(60, 4, '2026-11-11 18:06:57', '2026-11-11 18:41:23'),  -- VR-06  (Sala 6)
(69, 5, '2026-11-11 19:24:08', '2026-11-11 21:17:39'),  -- SIM-11 (Sala 7)
(80, 2, '2026-11-11 20:11:46', '2026-11-11 23:29:13'),  -- VIP-10 (Sala 8)
-- Jueves 12
(10, 2, '2026-11-12 09:13:29', '2026-11-12 10:44:52'),  -- PC-10  (Sala 2)
(22, 3, '2026-11-12 11:37:03', '2026-11-12 12:08:31'),  -- PC-22  (Sala 3)
(39, 5, '2026-11-12 16:19:58', '2026-11-12 20:02:44'),  -- PC-39  (Sala 4)
(48, 4, '2026-11-12 17:06:12', '2026-11-12 18:22:37'),  -- SIM-04 (Sala 5)
(63, 3, '2026-11-12 18:44:39', '2026-11-12 20:51:08'),  -- MND-11 (Sala 7)
(91, 2, '2026-11-12 20:03:51', '2026-11-12 22:16:27'),  -- VIP-21 (Sala 9)
-- Viernes 13: siguen jugando (fecha_fin NULL) y sus puestos están en 'ocupat'
(3,  5, '2026-11-13 10:14:36', NULL),                   -- PC-03  (Sala 1)
(26, 3, '2026-11-13 10:38:02', NULL),                   -- PC-26  (Sala 3)
(42, 4, '2026-11-13 11:21:47', NULL),                   -- MND-02 (Sala 5)
(59, 2, '2026-11-13 11:52:19', NULL),                   -- VR-05  (Sala 6)
(77, 4, '2026-11-13 12:07:55', NULL);                   -- VIP-07 (Sala 8)

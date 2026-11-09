DROP DATABASE IF EXISTS db_pj5_gamingcenter;
CREATE DATABASE db_pj5_gamingcenter CHARACTER SET utf8mb4;
USE db_pj5_gamingcenter;

-- 1. USUARIOS (los gamemasters, trabajadores del local; no hay clientes)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- hash generado con password_hash()
    nom_real VARCHAR(50) NOT NULL,
    cognom_real VARCHAR(80) NOT NULL
);

-- 2. SALAS (4 de PC, 3 de consolas/simuladores, 2 VIP)
CREATE TABLE salas (
    id_sala INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    tipo ENUM('pc', 'consola', 'vip') NOT NULL
);

-- 3. MÓDULOS (los puestos de juego de cada sala)
CREATE TABLE modulos (
    id_modulo INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    tipo ENUM('solo', 'duo', 'squad', 'party') NOT NULL,
    capacidad TINYINT NOT NULL,
    estado ENUM('lliure', 'ocupat') NOT NULL DEFAULT 'lliure',
    FOREIGN KEY (id_sala) REFERENCES salas (id_sala)
        ON UPDATE CASCADE ON DELETE RESTRICT
);

-- 4. OCUPACIONES (estado actual + HISTÓRICO)
CREATE TABLE ocupaciones (
    id_ocupacion INT AUTO_INCREMENT PRIMARY KEY,
    id_modulo INT NOT NULL,
    id_usuario INT NOT NULL,          -- gamemaster que ocupó el módulo
    num_jugadores TINYINT NOT NULL,   -- para validar que no supere la capacidad
    fecha_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- cuándo se ocupa
    fecha_fin DATETIME NULL,          -- cuándo se libera (NULL = jugando ahora)
    FOREIGN KEY (id_modulo) REFERENCES modulos (id_modulo)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT
);
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
);

-- Salas del local (PC Standard, Simuladores/Consolas y VIP)
CREATE TABLE salas (
    id_sala INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    tipo ENUM('pc','consola','vip') NOT NULL
);

-- Módulos (Los puestos de juego dentro de cada sala)
CREATE TABLE modulos (
    id_modulo INT AUTO_INCREMENT PRIMARY KEY,
    id_sala INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    tipo ENUM('solo','duo','squad','party') NOT NULL,
    capacidad TINYINT NOT NULL,
    estado ENUM('lliure','ocupat') DEFAULT 'lliure',
    FOREIGN KEY (id_sala) REFERENCES salas (id_sala)
);

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
);
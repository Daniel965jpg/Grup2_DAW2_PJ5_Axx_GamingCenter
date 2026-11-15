<?php
// CONEXIÓN A LA BASE DE DATOS
// La incluyen con require_once las vistas y los procesos que usan la BD.

$servidor = 'localhost';
$nombre_bd = 'db_pj5_gamingcenter';
$usuario_bd = 'root';
$contrasena_bd = '';

// Conectamos con utf8mb4 (tildes y eñes) y hacemos que los errores de SQL salten como excepción
try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$nombre_bd;charset=utf8mb4", $usuario_bd, $contrasena_bd);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Error de conexión: ' . $e->getMessage();
    die();
}
?>
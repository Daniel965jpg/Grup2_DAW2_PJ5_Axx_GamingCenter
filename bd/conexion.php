<?php
// CONEXIÓN A LA BASE DE DATOS
// Se incluye con require_once en las vistas y procesos que usan la BD.

$servidor = 'localhost';
$nombre_bd = 'db_pj5_gamingcenter';
$usuario_bd = 'root';
$contrasena_bd = '';

try {
    // utf8mb4 para que se guarden bien las tildes y las eñes
    $conexion = new PDO("mysql:host=$servidor;dbname=$nombre_bd;charset=utf8mb4", $usuario_bd, $contrasena_bd);
    // Así cualquier error de SQL salta como excepción y lo cogemos en el catch
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Error de conexión: ' . $e->getMessage();
    die();
}
?>

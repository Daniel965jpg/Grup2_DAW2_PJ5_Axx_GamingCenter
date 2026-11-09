<?php
// CONEXIÓN A LA BASE DE DATOS
// Se incluye con require_once en cada vista o proceso que necesite consultar la BD.

$host = 'localhost';
$dbname = 'db_pj5_gamingcenter';
$username = 'root';
$password = '';

try {
    // utf8mb4 para que las tildes y las eñes se guarden y se lean bien
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Con ERRMODE_EXCEPTION cualquier fallo de SQL lanza una excepción que podemos capturar con try/catch
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Error de conexión: ' . $e->getMessage();
    die();
}
?>

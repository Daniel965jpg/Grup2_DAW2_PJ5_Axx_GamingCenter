<?php
// CONEXIÓN A LA BASE DE DATOS
// Se incluye con require_once en cada vista o proceso que necesite consultar la BD.

$servidor = 'localhost';
$nombre_bd = 'db_pj5_gamingcenter';
$usuario_bd = 'root';
$contrasena_bd = '';

try {
    // host= y dbname= son palabras fijas de PDO; lo que va detrás son nuestras variables.
    // utf8mb4 para que las tildes y las eñes se guarden y se lean bien
    $conexion = new PDO("mysql:host=$servidor;dbname=$nombre_bd;charset=utf8mb4", $usuario_bd, $contrasena_bd);
    // Con ERRMODE_EXCEPTION cualquier fallo de SQL lanza una excepción que podemos capturar con try/catch
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Error de conexión: ' . $e->getMessage();
    die();
}
?>

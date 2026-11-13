<?php
// PROCESO: CIERRE DE LOCAL
// Se llama desde el botón "Cierre de Local" de ocupados.php (después de confirmarlo).
// Libera todos los puestos, los haya ocupado quien sea.

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../vistas/login.php');
    exit();
}

require_once '../bd/conexion.php';

// Dos UPDATE que van juntos: si falla uno, se deshace el otro
try {
    $conexion->beginTransaction();

    // Cerramos todas las ocupaciones que seguían abiertas
    $stmt1 = $conexion->prepare("UPDATE ocupaciones SET fecha_fin = NOW() WHERE fecha_fin IS NULL");
    $stmt1->execute();
    // rowCount() = cuántas filas ha cambiado, para el mensaje final
    $total_cerradas = $stmt1->rowCount();

    // Y todos los puestos ocupados pasan a libres
    $stmt2 = $conexion->prepare("UPDATE modulos SET estado = 'lliure' WHERE estado = 'ocupat'");
    $stmt2->execute();

    $conexion->commit();
} catch (PDOException $e) {
    $conexion->rollBack();
    echo "Error en el cierre de local: " . $e->getMessage();
    die();
}

$_SESSION['exito'] = 'Cierre de local hecho: se han liberado ' . $total_cerradas . ' puestos.';
header('Location: ../vistas/ocupados.php');
exit();
?>

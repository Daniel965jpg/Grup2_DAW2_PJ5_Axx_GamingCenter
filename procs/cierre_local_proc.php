<?php
// PROCESO: CIERRE DE LOCAL
// Se llama desde el botón "Cierre de Local" de ocupados.php (después de confirmarlo).
// Libera todos los puestos, los haya ocupado quien sea. Solo lo puede hacer un admin.

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: ../vistas/login.php');
    exit();
}

require_once '../bd/conexion.php';

$mi_id = $_SESSION['usuario'];

// El rol se mira en la BD: esconder el botón no basta, cualquiera podría escribir esta URL
try {
    $stmt = $conexion->prepare("SELECT rol FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$mi_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al comprobar el usuario: " . $e->getMessage();
    die();
}

if (!$usuario || $usuario['rol'] != 'admin') {
    $_SESSION['error'] = 'Acceso denegado: solo un administrador puede hacer el cierre de local.';
    header('Location: ../vistas/ocupados.php');
    exit();
}

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

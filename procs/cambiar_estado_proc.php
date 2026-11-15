<?php
// PROCESO: OCUPAR / LIBERAR UN PUESTO
// Se llama desde las tarjetas de sala.php y ocupados.php (cambiar_estado_proc.php?id_modulo=5).

session_start();

// Sin sesión al login, y sin puesto en la URL al panel
if (!isset($_SESSION['usuario'])) {
    header('Location: ../vistas/login.php');
    exit();
}

if (!isset($_GET['id_modulo'])) {
    header('Location: ../vistas/dashboard.php');
    exit();
}

require_once '../bd/conexion.php';
require_once '../includes/funciones.php';

$mi_id = $_SESSION['usuario'];
$id_modulo = limpiarDato($_GET['id_modulo']);

// Miramos en la BD el estado del puesto y su sala (para volver a ella al terminar)
try {
    $sql = "SELECT modulos.estado, salas.id_sala, salas.tipo
            FROM modulos
            JOIN salas ON modulos.id_sala = salas.id_sala
            WHERE modulos.id_modulo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_modulo]);
    $puesto = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al buscar el puesto: " . $e->getMessage();
    die();
}

// Si el id no existe (escrito a mano en la URL), volvemos al panel
if (!$puesto) {
    $_SESSION['error'] = 'El puesto que has pulsado no existe.';
    header('Location: ../vistas/dashboard.php');
    exit();
}

// Decidimos a dónde volver: ocupados.php manda &desde=ocupados, si no volvemos a la sala
if (isset($_GET['desde']) && $_GET['desde'] == 'ocupados') {
    $volver = '../vistas/ocupados.php';
} else {
    $volver = '../vistas/sala.php?tipo=' . $puesto['tipo'] . '&id_sala=' . $puesto['id_sala'];
}

// El admin no ocupa ni libera puestos, solo hace el cierre de local.
// Miramos su rol en la BD y no en la sesión, para que no se pueda saltar
try {
    $stmt = $conexion->prepare("SELECT rol FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$mi_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al comprobar el usuario: " . $e->getMessage();
    die();
}

if (!$usuario || $usuario['rol'] == 'admin') {
    $_SESSION['error'] = 'El administrador solo puede hacer el cierre de local';
    header('Location: ' . $volver);
    exit();
}

// Si el puesto está ocupado, solo puede liberarlo el gamemaster que lo ocupó
if ($puesto['estado'] == 'ocupat') {
    try {
        $sql = "SELECT id_usuario FROM ocupaciones WHERE id_modulo = ? AND fecha_fin IS NULL";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id_modulo]);
        $ocupacion = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al comprobar la ocupación: " . $e->getMessage();
        die();
    }

    if ($ocupacion && $ocupacion['id_usuario'] != $mi_id) {
        $_SESSION['error'] = 'Acceso denegado: Solo el Gamemaster que ocupó este puesto puede liberarlo';
        header('Location: ' . $volver);
        exit();
    }
}

// Cambiamos dos tablas (modulos y ocupaciones) que tienen que ir a la par,
// así que usamos una transacción: o se hacen los dos cambios o ninguno
try {
    $conexion->beginTransaction();

    if ($puesto['estado'] == 'lliure') {
        // Ocupar: con "AND estado = 'lliure'", si dos gamemasters pulsan el mismo puesto
        // a la vez solo el primero lo cambia; al segundo rowCount() le devuelve 0
        $stmt1 = $conexion->prepare("UPDATE modulos SET estado = 'ocupat' WHERE id_modulo = ? AND estado = 'lliure'");
        $stmt1->execute([$id_modulo]);

        if ($stmt1->rowCount() === 1) {
            // Lo hemos ocupado nosotros: apuntamos quién y a qué hora
            $stmt2 = $conexion->prepare("INSERT INTO ocupaciones (id_modulo, id_usuario, fecha_inicio) VALUES (?, ?, NOW())");
            $stmt2->execute([$id_modulo, $mi_id]);
        } else {
            // Otro lo ha ocupado justo antes: deshacemos y avisamos
            $conexion->rollBack();
            $_SESSION['error'] = 'Este puesto lo acaba de ocupar otro gamemaster.';
            header('Location: ' . $volver);
            exit();
        }
    } else {
        // Liberar: cerramos su ocupación abierta y el puesto vuelve a libre
        $stmt1 = $conexion->prepare("UPDATE ocupaciones SET fecha_fin = NOW() WHERE id_modulo = ? AND id_usuario = ? AND fecha_fin IS NULL");
        $stmt1->execute([$id_modulo, $mi_id]);

        $stmt2 = $conexion->prepare("UPDATE modulos SET estado = 'lliure' WHERE id_modulo = ?");
        $stmt2->execute([$id_modulo]);
    }

    $conexion->commit();
} catch (PDOException $e) {
    $conexion->rollBack();
    echo "Error al cambiar el estado del puesto: " . $e->getMessage();
    die();
}

// Volvemos a la vista: así, al recargar, no se cambia el estado otra vez
header('Location: ' . $volver);
exit();
?>

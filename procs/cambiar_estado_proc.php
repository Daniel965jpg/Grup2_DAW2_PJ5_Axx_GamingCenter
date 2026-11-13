<?php
// PROCESO: OCUPAR / LIBERAR UN PUESTO
// Se llama desde las tarjetas de sala.php y ocupados.php (cambiar_estado_proc.php?id_modulo=5).
// Si el puesto está libre lo ocupa y si está ocupado lo libera.
// Solo puede liberar un puesto el gamemaster que lo ocupó (regla de la rúbrica).

session_start();

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

// Miramos el estado del puesto en la BD (no nos fiamos de lo que venga en la URL)
// y su sala, para volver a ella al terminar
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

// Por si alguien escribe a mano un id que no existe
if (!$puesto) {
    $_SESSION['error'] = 'El puesto que has pulsado no existe.';
    header('Location: ../vistas/dashboard.php');
    exit();
}

// Desde ocupados.php el enlace lleva &desde=ocupados; si no, venimos de la sala
if (isset($_GET['desde']) && $_GET['desde'] == 'ocupados') {
    $volver = '../vistas/ocupados.php';
} else {
    $volver = '../vistas/sala.php?tipo=' . $puesto['tipo'] . '&id_sala=' . $puesto['id_sala'];
}

// Antes de liberar comprobamos si el usuario actual es el que ocupó el puesto
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

// Dos cambios que van juntos (el estado del puesto y la tabla ocupaciones):
// transacción para que se hagan los dos o ninguno
try {
    $conexion->beginTransaction();

    if ($puesto['estado'] == 'lliure') {
        // OCUPAR: el "AND estado = 'lliure'" hace que, si dos gamemasters pulsan el mismo
        // puesto a la vez, solo el primero lo cambie. Al segundo le sale 0 filas cambiadas.
        $stmt1 = $conexion->prepare("UPDATE modulos SET estado = 'ocupat' WHERE id_modulo = ? AND estado = 'lliure'");
        $stmt1->execute([$id_modulo]);

        if ($stmt1->rowCount() === 1) {
            // Lo hemos ocupado nosotros: apuntamos quién y cuándo
            $stmt2 = $conexion->prepare("INSERT INTO ocupaciones (id_modulo, id_usuario, fecha_inicio) VALUES (?, ?, NOW())");
            $stmt2->execute([$id_modulo, $mi_id]);
        } else {
            // Otro lo ha ocupado justo antes: cerramos la transacción sin guardar nada
            $conexion->rollBack();
            $_SESSION['error'] = 'Este puesto lo acaba de ocupar otro gamemaster.';
            header('Location: ' . $volver);
            exit();
        }
    } else {
        // LIBERAR: cerramos su ocupación (la que no tiene fecha_fin) y el puesto vuelve a libre
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

// Redirigimos: así si se recarga la página no se vuelve a cambiar el estado
header('Location: ' . $volver);
exit();
?>

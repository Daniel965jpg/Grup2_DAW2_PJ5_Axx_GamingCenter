<?php

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

try {
    // Leer el estado con bloqueo para que dos gamemasters no cambien el mismo puesto a la vez.
    $conexion->beginTransaction();
    $sql = "SELECT modulos.estado, salas.id_sala, salas.tipo
            FROM modulos
            JOIN salas ON modulos.id_sala = salas.id_sala
            WHERE modulos.id_modulo = ?
            FOR UPDATE";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$id_modulo]);
    $puesto = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si alguien escribe a mano un id que no existe
    if (!$puesto) {
        $conexion->rollBack();
        $_SESSION['error'] = 'El puesto que has pulsado no existe.';
        header('Location: ../vistas/dashboard.php');
        exit();
    }

    if ($puesto['estado'] == 'lliure') {
        // OCUPAR: el PC pasa a ocupado y se apunta quién lo ha ocupado y cuándo empieza
        $stmt1 = $conexion->prepare("UPDATE modulos SET estado = 'ocupat' WHERE id_modulo = ?");
        $stmt1->execute([$id_modulo]);

        $stmt2 = $conexion->prepare("INSERT INTO ocupaciones (id_modulo, id_usuario) VALUES (?, ?)");
        $stmt2->execute([$id_modulo, $mi_id]);
    } else {
        // Solo puede liberar el gamemaster responsable de la ocupación abierta.
        $stmt2 = $conexion->prepare("UPDATE ocupaciones
                                     SET fecha_fin = NOW()
                                     WHERE id_modulo = ? AND id_usuario = ? AND fecha_fin IS NULL");
        $stmt2->execute([$id_modulo, $mi_id]);

        if ($stmt2->rowCount() !== 1) {
            $conexion->rollBack();
            $_SESSION['error'] = 'Solo el gamemaster que ocupó este módulo puede liberarlo.';
            header('Location: ../vistas/sala.php?tipo=' . $puesto['tipo'] . '&id_sala=' . $puesto['id_sala']);
            exit();
        }

        $stmt1 = $conexion->prepare("UPDATE modulos SET estado = 'lliure' WHERE id_modulo = ?");
        $stmt1->execute([$id_modulo]);
    }

    $conexion->commit();
} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    echo "Error al cambiar el estado del puesto: " . $e->getMessage();
    die();
}

// Volvemos a la misma sala. Al redirigir, si se pulsa F5 no se vuelve a cambiar el estado.
header('Location: ../vistas/sala.php?tipo=' . $puesto['tipo'] . '&id_sala=' . $puesto['id_sala']);
exit();
?>

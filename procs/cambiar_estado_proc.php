<?php
// PROCESO: CAMBIAR ESTADO DE UN PC (OCUPAR / LIBERAR)
// Se llama desde el enlace de cada PC en vistas/sala.php (cambiar_estado_proc.php?id_modulo=5).
// Si el PC está libre lo ocupa y si está ocupado lo libera. Después vuelve a la misma sala.
// Es un proceso intermedio por GET, como eliminar_pedido.php en clase: la vista no puede hacer UPDATE.

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
    // Estado actual del PC y la sala a la que pertenece (para volver después a ella).
    // El nuevo estado lo decidimos aquí con lo que hay en la BD, no con lo que venga en la URL.
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

// Si alguien escribe a mano un id que no existe
if (!$puesto) {
    $_SESSION['error'] = 'El puesto que has pulsado no existe.';
    header('Location: ../vistas/dashboard.php');
    exit();
}

// Son dos modificaciones encadenadas (el estado del PC y el registro en ocupaciones),
// así que van en una transacción: o se hacen las dos o ninguna.
try {
    $conexion->beginTransaction();

    if ($puesto['estado'] == 'lliure') {
        // OCUPAR: el PC pasa a ocupado y se apunta quién lo ha ocupado y cuándo empieza
        $stmt1 = $conexion->prepare("UPDATE modulos SET estado = 'ocupat' WHERE id_modulo = ?");
        $stmt1->execute([$id_modulo]);

        $stmt2 = $conexion->prepare("INSERT INTO ocupaciones (id_modulo, id_usuario) VALUES (?, ?)");
        $stmt2->execute([$id_modulo, $mi_id]);
    } else {
        // LIBERAR: el PC vuelve a libre y se cierra la ocupación que estaba abierta (fecha_fin vacía)
        $stmt1 = $conexion->prepare("UPDATE modulos SET estado = 'lliure' WHERE id_modulo = ?");
        $stmt1->execute([$id_modulo]);

        $stmt2 = $conexion->prepare("UPDATE ocupaciones SET fecha_fin = NOW() WHERE id_modulo = ? AND fecha_fin IS NULL");
        $stmt2->execute([$id_modulo]);
    }

    $conexion->commit();
} catch (PDOException $e) {
    $conexion->rollBack();
    echo "Error al cambiar el estado del puesto: " . $e->getMessage();
    die();
}

// Volvemos a la misma sala. Al redirigir, si se pulsa F5 no se vuelve a cambiar el estado.
header('Location: ../vistas/sala.php?tipo=' . $puesto['tipo'] . '&id_sala=' . $puesto['id_sala']);
exit();
?>

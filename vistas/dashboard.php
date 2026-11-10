<?php
// VISTA: DASHBOARD
// Pantalla principal tras el login: una tarjeta por sala con sus módulos libres y ocupados.

session_start();

// Sin sesión no se puede ver el panel, así que lo mandamos al login
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require_once '../bd/conexion.php';

//Recoger variable de sesión de login
$mi_id = $_SESSION['usuario'];

try {
    // En la sesión solo guardamos el id, así que el nombre y el rol los sacamos de la BD
    $sql = "SELECT nom_real, rol FROM usuarios WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$mi_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Por cada módulo de la sala, CASE devuelve 1 si cumple el estado y 0 si no,
    // y SUM suma esos 1: así sale cuántos hay libres y cuántos ocupados.
    // LEFT JOIN para que una sala sin módulos también salga (con 0 y 0).
    $sql = "SELECT salas.id_sala, salas.nombre, salas.tipo,
                   SUM(CASE WHEN modulos.estado = 'lliure' THEN 1 ELSE 0 END) AS libres,
                   SUM(CASE WHEN modulos.estado = 'ocupat' THEN 1 ELSE 0 END) AS ocupados
            FROM salas
            LEFT JOIN modulos ON salas.id_sala = modulos.id_sala
            GROUP BY salas.id_sala, salas.nombre, salas.tipo";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al cargar el dashboard: " . $e->getMessage();
    die();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Gaming Center</title>
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header class="cabecera">
        <h1 class="cabecera-titulo">Panel de Control</h1>
        <div class="cabecera-usuario">
            <span><?php echo $usuario['nom_real']; ?> (<?php echo $usuario['rol']; ?>)</span>
            <a href="../procs/logout.php" class="boton-salir">Cerrar sesión</a>
        </div>
    </header>

    <main class="contenedor-tarjetas">
        <?php if (empty($salas)) { ?>
            <p class="mensaje-vacio">Todavía no hay salas registradas.</p>
        <?php } ?>

        <?php foreach ($salas as $sala) { ?>
            <!-- La segunda clase depende del tipo (pc, consola o vip) y le da el color de la sala -->
            <div class="tarjeta-sala tarjeta-<?php echo $sala['tipo']; ?>">
                <h2 class="tarjeta-nombre"><?php echo $sala['nombre']; ?></h2>
                <p class="tarjeta-tipo"><?php echo $sala['tipo']; ?></p>

                <div class="tarjeta-estados">
                    <p class="estado-libre">Libres: <?php echo $sala['libres']; ?></p>
                    <p class="estado-ocupado">Ocupados: <?php echo $sala['ocupados']; ?></p>
                </div>

                <a href="sala.php?id=<?php echo $sala['id_sala']; ?>" class="boton-sala">Entrar a la sala</a>
            </div>
        <?php } ?>
    </main>
</body>
</html>

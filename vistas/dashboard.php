<?php
// VISTA: DASHBOARD
// Pantalla principal tras el login: cuatro tarjetas grandes para entrar a cada zona del local
// (PC Standard, Consolas, VIP) o al histórico. Cada zona enseña cuántos puestos hay libres y ocupados.

session_start();

// Sin sesión no se puede ver el panel, así que lo mandamos al login
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require_once '../bd/conexion.php';

$mi_id = $_SESSION['usuario'];

try {
    // En la sesión solo guardamos el id, así que el nombre y el rol los sacamos de la BD
    $sql = "SELECT nom_real, rol FROM usuarios WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$mi_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cuenta los puestos de un estado dentro de las salas de un tipo.
    // Se prepara una sola vez y luego se ejecuta para cada zona dentro del foreach.
    $sql = "SELECT COUNT(*) AS total FROM modulos
            WHERE estado = ? AND id_sala IN (SELECT id_sala FROM salas WHERE tipo = ?)";
    $stmt_contar = $conexion->prepare($sql);

    $tipos = ['pc', 'consola', 'vip'];
    $resumen = [];
    foreach ($tipos as $tipo) {
        $stmt_contar->execute(['lliure', $tipo]);
        $fila = $stmt_contar->fetch(PDO::FETCH_ASSOC);
        $resumen[$tipo]['libres'] = $fila['total'];

        $stmt_contar->execute(['ocupat', $tipo]);
        $fila = $stmt_contar->fetch(PDO::FETCH_ASSOC);
        $resumen[$tipo]['ocupados'] = $fila['total'];
    }
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
<body class="pagina-panel">
    <header class="cabecera">
        <div>
            <h1 class="cabecera-titulo">Panel de Control</h1>
            <p class="cabecera-bienvenida">Bienvenido, <?php echo $usuario['nom_real']; ?>. Selecciona una zona para gestionar la ocupación de las salas.</p>
        </div>
        <div class="cabecera-usuario">
            <span><?php echo $usuario['nom_real']; ?> (<?php echo $usuario['rol']; ?>)</span>
            <a href="../procs/logout.php" class="boton-salir">Cerrar sesión</a>
        </div>
    </header>

    <?php
    // Por ejemplo, si sala.php nos devuelve aquí porque la zona no tiene salas
    if (isset($_SESSION['error'])) {
        echo "<div class='mensaje-aviso mensaje-aviso-error'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    ?>

    <!-- Las 4 tarjetas ocupan todo el alto que queda debajo de la cabecera -->
    <main class="zonas">
        <!-- El tipo va por GET en el enlace: sala.php enseña solo las salas de ese tipo -->
        <a href="sala.php?tipo=pc" class="tarjeta-zona tarjeta-pc">
            <h2>PC Standard</h2>
            <p class="zona-descripcion">Ordenadores para jugar solo, en dúo o en squad</p>
            <div class="zona-estados">
                <span class="estado-libre"><?php echo $resumen['pc']['libres']; ?> libres</span>
                <span class="estado-ocupado"><?php echo $resumen['pc']['ocupados']; ?> ocupados</span>
            </div>
        </a>

        <a href="sala.php?tipo=consola" class="tarjeta-zona tarjeta-consola">
            <h2>Consolas y Simuladores</h2>
            <p class="zona-descripcion">PS5, volantes y simuladores</p>
            <div class="zona-estados">
                <span class="estado-libre"><?php echo $resumen['consola']['libres']; ?> libres</span>
                <span class="estado-ocupado"><?php echo $resumen['consola']['ocupados']; ?> ocupados</span>
            </div>
        </a>

        <a href="sala.php?tipo=vip" class="tarjeta-zona tarjeta-vip">
            <h2>Salas VIP</h2>
            <p class="zona-descripcion">Equipos Pro para grupos</p>
            <div class="zona-estados">
                <span class="estado-libre"><?php echo $resumen['vip']['libres']; ?> libres</span>
                <span class="estado-ocupado"><?php echo $resumen['vip']['ocupados']; ?> ocupados</span>
            </div>
        </a>

        <a href="historico.php" class="tarjeta-zona tarjeta-historico">
            <h2>Histórico</h2>
            <p class="zona-descripcion">Registro de todas las ocupaciones</p>
        </a>
    </main>
</body>
</html>
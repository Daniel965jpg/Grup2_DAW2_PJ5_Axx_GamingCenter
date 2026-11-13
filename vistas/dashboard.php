<?php
// VISTA: PANEL DE CONTROL
// Página principal después del login: una tarjeta por zona (PC, consolas, VIP) con sus
// puestos libres y ocupados, y otra para el histórico.

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require_once '../bd/conexion.php';

$mi_id = $_SESSION['usuario'];

try {
    // Nombre y rol del usuario para la bienvenida
    $sql = "SELECT nom_real, rol FROM usuarios WHERE id_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$mi_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cuántos puestos hay en un estado dentro de las salas de un tipo.
    // Se prepara una vez fuera y se ejecuta dentro del foreach para cada zona.
    $sql = "SELECT COUNT(*) AS total FROM modulos
            WHERE estado = ? AND id_sala IN (SELECT id_sala FROM salas WHERE tipo = ?)";
    $stmt_contar = $conexion->prepare($sql);

    // Queda así: $resumen['pc']['libres'], $resumen['vip']['ocupados'], etc.
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
    echo "Error al cargar el panel: " . $e->getMessage();
    die();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - Hidden Arena</title>
    <link rel="icon" type="image/png" href="../img/logo-hidden-mini.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="pagina-panel">
    <video id="bg-video" class="video-fondo" autoplay loop muted playsinline>
        <source src="../img/video-fondo-gamingcenter.mp4" type="video/mp4">
    </video>

    <header class="cabecera">
        <div class="cabecera-marca">
            <img src="../img/logo-hidden-mini.png" alt="Hidden Arena" class="logo-cabecera">
            <div>
                <h1 class="cabecera-titulo">Panel de Control</h1>
                <p class="cabecera-bienvenida">Bienvenido, <?php echo htmlspecialchars($usuario['nom_real']); ?> (<?php echo htmlspecialchars($usuario['rol']); ?>). Selecciona una zona para gestionar la ocupación de las salas.</p>
            </div>
        </div>
        <div class="cabecera-acciones">
            <a href="../procs/logout.php" class="boton-salir">Cerrar sesión</a>
            <a href="ocupados.php" class="boton-cabecera">Ver Ocupados</a>
        </div>
    </header>

    <?php
    // Por ejemplo, cuando sala.php nos devuelve aquí porque la zona no tiene salas
    if (isset($_SESSION['error'])) {
        echo "<div class='mensaje-aviso mensaje-aviso-error'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    ?>

    <main class="zonas">
        <!-- El tipo va en el enlace: sala.php enseña solo las salas de ese tipo -->
        <a href="sala.php?tipo=pc" class="tarjeta-zona tarjeta-pc">
            <i class="fa-solid fa-computer icono-dashboard pc-neon"></i>
            <h2>PC Standard</h2>
            <p class="zona-descripcion">Ordenadores para jugar solo, en dúo o en squad</p>
            <div class="zona-estados">
                <span class="estado-libre"><?php echo $resumen['pc']['libres']; ?> libres</span>
                <span class="estado-ocupado"><?php echo $resumen['pc']['ocupados']; ?> ocupados</span>
            </div>
        </a>

        <a href="sala.php?tipo=consola" class="tarjeta-zona tarjeta-consola">
            <i class="fa-solid fa-gamepad icono-dashboard consola-neon"></i>
            <h2>Consolas y Simuladores</h2>
            <p class="zona-descripcion">PS5, volantes y simuladores</p>
            <div class="zona-estados">
                <span class="estado-libre"><?php echo $resumen['consola']['libres']; ?> libres</span>
                <span class="estado-ocupado"><?php echo $resumen['consola']['ocupados']; ?> ocupados</span>
            </div>
        </a>

        <a href="sala.php?tipo=vip" class="tarjeta-zona tarjeta-vip">
            <i class="fa-solid fa-crown icono-dashboard vip-neon"></i>
            <h2>Salas VIP</h2>
            <p class="zona-descripcion">Equipos Pro para grupos</p>
            <div class="zona-estados">
                <span class="estado-libre"><?php echo $resumen['vip']['libres']; ?> libres</span>
                <span class="estado-ocupado"><?php echo $resumen['vip']['ocupados']; ?> ocupados</span>
            </div>
        </a>

        <a href="historico.php" class="tarjeta-zona tarjeta-historico">
            <i class="fa-solid fa-clock-rotate-left icono-dashboard historico-neon"></i>
            <h2>Histórico</h2>
            <p class="zona-descripcion">Registro de todas las ocupaciones</p>
        </a>
    </main>
</body>
</html>

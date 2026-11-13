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
require_once '../includes/funciones.php';

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

    // Lista de salas de cada zona con su capacidad (número de puestos) y sus puestos libres
    $stmt = $conexion->prepare("SELECT id_sala, nombre, tipo FROM salas ORDER BY id_sala");
    $stmt->execute();
    $lista_salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Se preparan fuera y se ejecutan para cada sala dentro del foreach
    $stmt_puestos = $conexion->prepare("SELECT COUNT(*) AS puestos FROM modulos WHERE id_sala = ?");
    $stmt_libres = $conexion->prepare("SELECT COUNT(*) AS libres FROM modulos WHERE id_sala = ? AND estado = 'lliure'");

    // Queda así: $salas_zona['pc'] = [sala 1, sala 2...], cada una con sus números
    $salas_zona = ['pc' => [], 'consola' => [], 'vip' => []];
    foreach ($lista_salas as $sala) {
        $stmt_puestos->execute([$sala['id_sala']]);
        $fila = $stmt_puestos->fetch(PDO::FETCH_ASSOC);
        $sala['puestos'] = $fila['puestos'];

        $stmt_libres->execute([$sala['id_sala']]);
        $fila = $stmt_libres->fetch(PDO::FETCH_ASSOC);
        $sala['libres'] = $fila['libres'];

        $salas_zona[$sala['tipo']][] = $sala;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Hidden Arena</title>
    <link rel="icon" type="image/png" href="../img/logo-hidden-mini.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/responsive.css">
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
        <!-- Cada tarjeta es un div (un enlace no puede ir dentro de otro enlace):
             arriba el enlace a la zona y debajo la lista de sus salas, cada una con su enlace -->
        <div class="tarjeta-zona tarjeta-pc">
            <a href="sala.php?tipo=pc" class="enlace-zona">
                <i class="fa-solid fa-computer icono-dashboard pc-neon"></i>
                <h2>PC Standard</h2>
                <p class="zona-descripcion">Ordenadores para jugar solo, en dúo o en squad</p>
                <div class="zona-estados">
                    <span class="estado-libre"><?php echo textoCantidad($resumen['pc']['libres'], 'libre', 'libres'); ?></span>
                    <span class="estado-ocupado"><?php echo textoCantidad($resumen['pc']['ocupados'], 'ocupado', 'ocupados'); ?></span>
                </div>
            </a>
            <ul class="lista-salas">
                <?php foreach ($salas_zona['pc'] as $sala) { ?>
                    <li>
                        <a href="sala.php?tipo=pc&id_sala=<?php echo $sala['id_sala']; ?>">
                            <span class="nombre-sala"><?php echo htmlspecialchars($sala['nombre']); ?></span>
                            <span class="datos-sala"><?php echo $sala['libres']; ?>/<?php echo $sala['puestos']; ?> libres · Cap. <?php echo $sala['puestos']; ?></span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <div class="tarjeta-zona tarjeta-consola">
            <a href="sala.php?tipo=consola" class="enlace-zona">
                <i class="fa-solid fa-gamepad icono-dashboard consola-neon"></i>
                <h2>Consolas y Simuladores</h2>
                <p class="zona-descripcion">PS5, simuladores y VR</p>
                <div class="zona-estados">
                    <span class="estado-libre"><?php echo textoCantidad($resumen['consola']['libres'], 'libre', 'libres'); ?></span>
                    <span class="estado-ocupado"><?php echo textoCantidad($resumen['consola']['ocupados'], 'ocupado', 'ocupados'); ?></span>
                </div>
            </a>
            <ul class="lista-salas">
                <?php foreach ($salas_zona['consola'] as $sala) { ?>
                    <li>
                        <a href="sala.php?tipo=consola&id_sala=<?php echo $sala['id_sala']; ?>">
                            <span class="nombre-sala"><?php echo htmlspecialchars($sala['nombre']); ?></span>
                            <span class="datos-sala"><?php echo $sala['libres']; ?>/<?php echo $sala['puestos']; ?> libres · Cap. <?php echo $sala['puestos']; ?></span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <div class="tarjeta-zona tarjeta-vip">
            <a href="sala.php?tipo=vip" class="enlace-zona">
                <i class="fa-solid fa-crown icono-dashboard vip-neon"></i>
                <h2>Salas VIP</h2>
                <p class="zona-descripcion">Equipos Pro para grupos</p>
                <div class="zona-estados">
                    <span class="estado-libre"><?php echo textoCantidad($resumen['vip']['libres'], 'libre', 'libres'); ?></span>
                    <span class="estado-ocupado"><?php echo textoCantidad($resumen['vip']['ocupados'], 'ocupado', 'ocupados'); ?></span>
                </div>
            </a>
            <ul class="lista-salas">
                <?php foreach ($salas_zona['vip'] as $sala) { ?>
                    <li>
                        <a href="sala.php?tipo=vip&id_sala=<?php echo $sala['id_sala']; ?>">
                            <span class="nombre-sala"><?php echo htmlspecialchars($sala['nombre']); ?></span>
                            <span class="datos-sala"><?php echo $sala['libres']; ?>/<?php echo $sala['puestos']; ?> libres · Cap. <?php echo $sala['puestos']; ?></span>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <a href="historico.php" class="tarjeta-zona tarjeta-historico">
            <i class="fa-solid fa-clock-rotate-left icono-dashboard historico-neon"></i>
            <h2>Histórico</h2>
            <p class="zona-descripcion">Registro de todas las ocupaciones</p>
        </a>
    </main>
</body>
</html>

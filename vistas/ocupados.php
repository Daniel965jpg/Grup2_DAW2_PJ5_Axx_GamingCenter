<?php
// VISTA: PUESTOS OCUPADOS
// Todos los puestos ocupados del local, de cualquier sala, con las mismas tarjetas que sala.php.
// Al pulsar uno se va al proceso cambiar_estado_proc.php (con &desde=ocupados), que lo libera
// y vuelve aquí, así que el puesto ya no sale en la lista.

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require_once '../bd/conexion.php';
require_once '../includes/funciones.php';

try {
    // El nombre y el tipo de la sala hacen falta para enseñar de dónde es cada puesto y elegir su imagen
    $sql = "SELECT modulos.id_modulo, modulos.nombre, salas.nombre AS sala, salas.tipo AS tipo_sala
            FROM modulos
            JOIN salas ON modulos.id_sala = salas.id_sala
            WHERE modulos.estado = 'ocupat'
            ORDER BY salas.id_sala, modulos.nombre";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $ocupados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al cargar los puestos ocupados: " . $e->getMessage();
    die();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Puestos ocupados - Gaming Center</title>
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header class="cabecera">
        <a href="dashboard.php" class="boton-volver">&lt; Volver al panel</a>
        <h1 class="cabecera-titulo">Puestos ocupados (<?php echo count($ocupados); ?>)</h1>
        <a href="historico.php" class="boton-cabecera">Historial</a>
    </header>

    <?php
    if (isset($_SESSION['error'])) {
        echo "<div class='mensaje-aviso mensaje-aviso-error'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    ?>

    <main class="vista-ocupados">
        <?php if (empty($ocupados)) { ?>
            <p class="mensaje-vacio">Ahora mismo no hay ningún puesto ocupado.</p>
        <?php } ?>

        <div class="lista-ocupados">
            <?php foreach ($ocupados as $puesto) { ?>
                <!-- Misma tarjeta que en sala.php. Aquí todos están ocupados, así que pulsar = liberar -->
                <a href="../procs/cambiar_estado_proc.php?id_modulo=<?php echo $puesto['id_modulo']; ?>&desde=ocupados" title="Liberar <?php echo htmlspecialchars($puesto['nombre']); ?>" class="tarjeta-puesto estado-ocupado">
                    <div class="indicador-estado">
                        <span class="icono-pantalla"></span>
                        <span class="texto-estado">OCUPADO</span>
                    </div>
                    <div class="grafico-pc">
                        <img src="<?php echo imagenPuesto($puesto['nombre'], $puesto['tipo_sala']); ?>" alt="<?php echo htmlspecialchars($puesto['nombre']); ?>" class="img-consola">
                    </div>
                    <div class="nombre-puesto"><?php echo htmlspecialchars($puesto['nombre']); ?></div>
                    <div class="sala-puesto"><?php echo htmlspecialchars($puesto['sala']); ?></div>
                </a>
            <?php } ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <script src="../js/confirmar.js"></script>
</body>
</html>

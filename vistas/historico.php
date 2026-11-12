<?php
// VISTA: HISTORIAL GLOBAL DE OCUPACIONES
// Muestra en una sola tabla las ocupaciones de todos los módulos y salas.

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require_once '../bd/conexion.php';

try {
    $sql = "SELECT salas.nombre AS sala, modulos.nombre AS modulo,
                   usuarios.username, usuarios.nom_real, usuarios.cognom_real,
                   ocupaciones.fecha_inicio, ocupaciones.fecha_fin
            FROM ocupaciones
            JOIN modulos ON ocupaciones.id_modulo = modulos.id_modulo
            JOIN salas ON modulos.id_sala = salas.id_sala
            JOIN usuarios ON ocupaciones.id_usuario = usuarios.id_usuario
            ORDER BY ocupaciones.fecha_inicio DESC, ocupaciones.id_ocupacion DESC";
    $stmt = $conexion->query($sql);
    $ocupaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al cargar el historial de ocupaciones: " . $e->getMessage();
    die();
}

function escaparHistorial($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historial de ocupación - Gaming Center</title>
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="pagina-historico">
    <header class="cabecera cabecera-historico">
        <div>
            <a href="dashboard.php" class="boton-volver">&lt; Volver al panel</a>
            <h1 class="cabecera-titulo">Historial de ocupación</h1>
            <p class="cabecera-bienvenida">Registro global de todas las salas y módulos.</p>
        </div>
        <a href="../procs/logout.php" class="boton-salir">Cerrar sesión</a>
    </header>

    <main class="contenido-historico">
        <section class="panel-historico" aria-labelledby="titulo-tabla-historico">
            <div class="cabecera-historico-tabla">
                <h2 id="titulo-tabla-historico">Todas las ocupaciones</h2>
                <span><?php echo count($ocupaciones); ?> registros</span>
            </div>

            <?php if (empty($ocupaciones)) { ?>
                <p class="mensaje-vacio">Todavía no hay ocupaciones registradas.</p>
            <?php } else { ?>
                <div class="tabla-historico-contenedor">
                    <table class="tabla-historico">
                        <thead>
                            <tr>
                                <th>Sala</th>
                                <th>Módulo</th>
                                <th>Gamemaster</th>
                                <th>Fecha inicial</th>
                                <th>Fecha final</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ocupaciones as $ocupacion) { ?>
                                <tr>
                                    <td><?php echo escaparHistorial($ocupacion['sala']); ?></td>
                                    <td><?php echo escaparHistorial($ocupacion['modulo']); ?></td>
                                    <td>
                                        <?php echo escaparHistorial($ocupacion['nom_real'] . ' ' . $ocupacion['cognom_real']); ?>
                                        <span class="usuario-historico">@<?php echo escaparHistorial($ocupacion['username']); ?></span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i:s', strtotime($ocupacion['fecha_inicio'])); ?></td>
                                    <td>
                                        <?php if ($ocupacion['fecha_fin'] === null) { ?>
                                            <span class="estado-ocupado">En curso</span>
                                        <?php } else { ?>
                                            <?php echo date('d/m/Y H:i:s', strtotime($ocupacion['fecha_fin'])); ?>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if ($ocupacion['fecha_fin'] === null) { ?>
                                            <span class="estado-ocupado">Ocupado</span>
                                        <?php } else { ?>
                                            <span class="estado-libre">Finalizada</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </section>
    </main>
</body>
</html>

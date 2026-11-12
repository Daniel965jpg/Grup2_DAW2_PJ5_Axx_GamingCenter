<?php
// VISTA: SALA
// Muestra los puestos de una sala agrupados por formato (Squad, Dúo y Solo).
// Llega por GET el tipo de zona desde las tarjetas del dashboard (sala.php?tipo=pc)
// y, si se ha elegido en el desplegable, también la sala (sala.php?tipo=pc&id_sala=3).
// Al pulsar un PC se va a procs/cambiar_estado_proc.php, que lo ocupa o lo libera y vuelve aquí.

session_start();

// Página privada: solo para trabajadores que han iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Sin zona en la URL no sabemos qué enseñar, así que volvemos al panel
if (!isset($_GET['tipo'])) {
    header('Location: dashboard.php');
    exit();
}

require_once '../bd/conexion.php';
require_once '../includes/funciones.php';

$tipo = limpiarDato($_GET['tipo']);

try {
    // Salas de esta zona, para el desplegable de la cabecera
    $sql = "SELECT id_sala, nombre, tipo FROM salas WHERE tipo = ? ORDER BY id_sala";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$tipo]);
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si la zona no tiene salas (o el tipo de la URL es inventado) no hay nada que enseñar
    if (empty($salas)) {
        $_SESSION['error'] = 'Esta zona todavía no tiene salas.';
        header('Location: dashboard.php');
        exit();
    }

    // Por defecto se ve la primera sala de la zona. Si en el desplegable se ha elegido otra,
    // la buscamos entre las salas de esta zona: un id de otra zona o inventado se ignora.
    $sala_activa = $salas[0];
    if (isset($_GET['id_sala'])) {
        $id_elegido = limpiarDato($_GET['id_sala']);
        foreach ($salas as $sala) {
            if ($sala['id_sala'] == $id_elegido) {
                $sala_activa = $sala;
            }
        }
    }

    // Puestos de la sala. El id_modulo hace falta para el enlace que lo ocupa o lo libera.
    $sql = "SELECT id_modulo, nombre, tipo, estado FROM modulos WHERE id_sala = ? ORDER BY nombre";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$sala_activa['id_sala']]);
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al cargar la sala: " . $e->getMessage();
    die();
}

// Repartimos los puestos en tres listas según su formato, para pintar cada bloque por separado.
// De paso dejamos preparada la clase CSS y el texto del estado, así el HTML solo tiene que hacer echo.
$puestos_squad = [];
$puestos_duo = [];
$puestos_solo = [];

foreach ($modulos as $modulo) {
    // 'accion' es lo que pasará al pulsarlo; js/sala.js lo usa para la pregunta del SweetAlert
    if ($modulo['estado'] == 'lliure') {
        $modulo['clase_estado'] = 'estado-libre';
        $modulo['texto_estado'] = 'LIBRE';
        $modulo['accion'] = 'Ocupar';
    } else {
        $modulo['clase_estado'] = 'estado-ocupado';
        $modulo['texto_estado'] = 'OCUPADO';
        $modulo['accion'] = 'Liberar';
    }

    if ($modulo['tipo'] == 'squad') {
        $puestos_squad[] = $modulo;
    } elseif ($modulo['tipo'] == 'duo') {
        $puestos_duo[] = $modulo;
    } elseif ($modulo['tipo'] == 'solo') {
        $puestos_solo[] = $modulo;
    }
}

// Los dúos se pintan por parejas, cada una en su caja.
// Vamos llenando $pareja y, cuando ya tiene 2 ordenadores, la guardamos y empezamos otra.
$parejas = [];
$pareja = [];
foreach ($puestos_duo as $puesto) {
    $pareja[] = $puesto;
    if (count($pareja) == 2) {
        $parejas[] = $pareja;
        $pareja = [];
    }
}
// Si el número de dúos es impar, el último se queda sin pareja y también hay que guardarlo
if (!empty($pareja)) {
    $parejas[] = $pareja;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($sala_activa['nombre']); ?> - Gaming Center</title>
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="pagina-sala">
    <!-- La segunda clase (cabecera-pc, cabecera-consola o cabecera-vip) pinta la línea con el color de la sala -->
    <header class="cabecera cabecera-<?php echo $sala_activa['tipo']; ?>">
        <a href="dashboard.php" class="boton-volver">&lt; Volver</a>

        <h1 class="cabecera-titulo"><?php echo htmlspecialchars($sala_activa['nombre']); ?></h1>

        <!-- Formulario por GET: solo sirve para navegar a otra sala, no modifica nada.
             Se envía solo al cambiar el desplegable (lo hace js/sala.js). -->
        <form method="GET" action="sala.php" id="formulario-sala" class="selector-sala">
            <!-- Oculto: reenvía la zona para que al cambiar de sala no se pierda -->
            <input type="hidden" name="tipo" value="<?php echo $sala_activa['tipo']; ?>">
            <label for="id_sala">Sala:</label>
            <select name="id_sala" id="id_sala">
                <?php foreach ($salas as $sala) { ?>
                    <?php if ($sala['id_sala'] == $sala_activa['id_sala']) { ?>
                        <option value="<?php echo $sala['id_sala']; ?>" selected><?php echo htmlspecialchars($sala['nombre']); ?></option>
                    <?php } else { ?>
                        <option value="<?php echo $sala['id_sala']; ?>"><?php echo htmlspecialchars($sala['nombre']); ?></option>
                    <?php } ?>
                <?php } ?>
            </select>
        </form>
    </header>

    <?php
    // Por ejemplo, si el proceso no encuentra el puesto que se ha pulsado
    if (isset($_SESSION['error'])) {
        echo "<div class='mensaje-aviso mensaje-aviso-error'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    ?>

    <main class="vista-puestos">
        <?php if (empty($modulos)) { ?>
            <p class="mensaje-vacio">Esta sala todavía no tiene puestos.</p>
        <?php } ?>

        <!-- BLOQUE 1: SQUADS -->
        <?php if (!empty($puestos_squad)) { ?>
            <section class="grupo-formato">
                <h3 class="titulo-formato">Arena Squad</h3>
                <div class="lista-squad">
                    <?php foreach ($puestos_squad as $puesto) { ?>
                        <!-- Cada PC es un enlace al proceso que lo ocupa (si está libre) o lo libera (si está ocupado) -->
                        <a href="../procs/cambiar_estado_proc.php?id_modulo=<?php echo $puesto['id_modulo']; ?>" title="<?php echo $puesto['accion'] . ' ' . htmlspecialchars($puesto['nombre']); ?>" class="tarjeta-puesto <?php echo $puesto['clase_estado']; ?>">
                            <div class="indicador-estado">
                                <span class="icono-pantalla"></span>
                                <span class="texto-estado"><?php echo $puesto['texto_estado']; ?></span>
                            </div>
                            <div class="grafico-pc">
                                <img src="../img/ordenador-sala1.png" alt="PC" class="img-consola">
                            </div>
                            <div class="nombre-puesto"><?php echo htmlspecialchars($puesto['nombre']); ?></div>
                        </a>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>

        <!-- BLOQUE 2: DÚOS -->
        <?php if (!empty($parejas)) { ?>
            <section class="grupo-formato">
                <h3 class="titulo-formato">Zona Dúo</h3>
                <div class="lista-duo">
                    <!-- Una caja por pareja y, dentro, sus ordenadores -->
                    <?php foreach ($parejas as $pareja) { ?>
                        <div class="caja-pareja">
                            <?php foreach ($pareja as $puesto) { ?>
                                <a href="../procs/cambiar_estado_proc.php?id_modulo=<?php echo $puesto['id_modulo']; ?>" title="<?php echo $puesto['accion'] . ' ' . htmlspecialchars($puesto['nombre']); ?>" class="tarjeta-puesto <?php echo $puesto['clase_estado']; ?>">
                                    <div class="indicador-estado">
                                        <span class="icono-pantalla"></span>
                                        <span class="texto-estado"><?php echo $puesto['texto_estado']; ?></span>
                                    </div>
                                    <div class="grafico-pc">
                                        <img src="../img/ordenador-sala1.png" alt="PC" class="img-consola">
                                    </div>
                                    <div class="nombre-puesto"><?php echo htmlspecialchars($puesto['nombre']); ?></div>
                                </a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>

        <!-- BLOQUE 3: SOLOS -->
        <?php if (!empty($puestos_solo)) { ?>
            <section class="grupo-formato">
                <h3 class="titulo-formato">Zona Solo</h3>
                <div class="lista-solo">
                    <?php foreach ($puestos_solo as $puesto) { ?>
                        <a href="../procs/cambiar_estado_proc.php?id_modulo=<?php echo $puesto['id_modulo']; ?>" title="<?php echo $puesto['accion'] . ' ' . htmlspecialchars($puesto['nombre']); ?>" class="tarjeta-puesto <?php echo $puesto['clase_estado']; ?>">
                            <div class="indicador-estado">
                                <span class="icono-pantalla"></span>
                                <span class="texto-estado"><?php echo $puesto['texto_estado']; ?></span>
                            </div>
                            <div class="grafico-pc">
                                <img src="../img/ordenador-sala1.png" alt="PC" class="img-consola">
                            </div>
                            <div class="nombre-puesto"><?php echo htmlspecialchars($puesto['nombre']); ?></div>
                        </a>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <script src="../js/sala.js"></script>
</body>
</html>

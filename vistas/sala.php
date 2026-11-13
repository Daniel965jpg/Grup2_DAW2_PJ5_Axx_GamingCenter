<?php
// VISTA: SALA
// Puestos de una sala agrupados por formato (Squad, Party, Dúo y Solo).
// Llega por GET la zona desde el panel (sala.php?tipo=pc) y, si se ha elegido
// en el desplegable, también la sala (sala.php?tipo=pc&id_sala=3).

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Sin zona en la URL no sabemos qué enseñar
if (!isset($_GET['tipo'])) {
    header('Location: dashboard.php');
    exit();
}

require_once '../bd/conexion.php';
require_once '../includes/funciones.php';

$tipo = limpiarDato($_GET['tipo']);

try {
    // Salas de esta zona, para el desplegable
    $sql = "SELECT id_sala, nombre, tipo FROM salas WHERE tipo = ? ORDER BY id_sala";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$tipo]);
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Zona sin salas (o tipo inventado en la URL)
    if (empty($salas)) {
        $_SESSION['error'] = 'Esta zona todavía no tiene salas.';
        header('Location: dashboard.php');
        exit();
    }

    // Por defecto la primera sala de la zona. Si se ha elegido otra, la buscamos
    // entre las de esta zona (así un id de otra zona o inventado no hace nada)
    $sala_activa = $salas[0];
    if (isset($_GET['id_sala'])) {
        $id_elegido = limpiarDato($_GET['id_sala']);
        foreach ($salas as $sala) {
            if ($sala['id_sala'] == $id_elegido) {
                $sala_activa = $sala;
            }
        }
    }

    // Puestos de la sala elegida
    $sql = "SELECT id_modulo, nombre, tipo, estado FROM modulos WHERE id_sala = ? ORDER BY nombre";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$sala_activa['id_sala']]);
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al cargar la sala: " . $e->getMessage();
    die();
}

// Preparamos los datos de cada tarjeta y separamos los puestos por formato
$puestos_squad = [];
$puestos_party = [];
$puestos_duo = [];
$puestos_solo = [];

foreach ($modulos as $modulo) {
    if ($modulo['estado'] == 'lliure') {
        $modulo['clase_estado'] = 'estado-libre';
        $modulo['texto_estado'] = 'LIBRE';
        $modulo['accion'] = 'Ocupar';
    } else {
        $modulo['clase_estado'] = 'estado-ocupado';
        $modulo['texto_estado'] = 'OCUPADO';
        $modulo['accion'] = 'Liberar';
    }

    $modulo['enlace'] = '../procs/cambiar_estado_proc.php?id_modulo=' . $modulo['id_modulo'];
    $modulo['tipo_sala'] = $sala_activa['tipo'];
    $modulo['imagen'] = imagenPuesto($modulo['nombre'], $sala_activa['tipo']);

    if ($modulo['tipo'] == 'squad') {
        $puestos_squad[] = $modulo;
    } elseif ($modulo['tipo'] == 'party') {
        $puestos_party[] = $modulo;
    } elseif ($modulo['tipo'] == 'duo') {
        $puestos_duo[] = $modulo;
    } elseif ($modulo['tipo'] == 'solo') {
        $puestos_solo[] = $modulo;
    }
}

// Los dúos se pintan de dos en dos: vamos llenando $pareja y cuando tiene 2 la guardamos
$parejas = [];
$pareja = [];
foreach ($puestos_duo as $puesto) {
    $pareja[] = $puesto;
    if (count($pareja) == 2) {
        $parejas[] = $pareja;
        $pareja = [];
    }
}
// Si hay un número impar, el último se queda solo y también hay que guardarlo
if (!empty($pareja)) {
    $parejas[] = $pareja;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($sala_activa['nombre']); ?> - Hidden Arena</title>
    <link rel="icon" type="image/png" href="../img/logo-hidden-mini.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="pagina-sala">
    <!-- cabecera-pc, cabecera-consola o cabecera-vip: la línea de abajo sale del color de la zona -->
    <header class="cabecera cabecera-<?php echo $sala_activa['tipo']; ?>">
        <a href="dashboard.php" class="boton-volver">&lt; Volver</a>

        <h1 class="cabecera-titulo"><?php echo htmlspecialchars($sala_activa['nombre']); ?></h1>

        <div class="cabecera-acciones">
            <!-- Quién está conectado (se guarda en la sesión al hacer login) -->
            <?php if (isset($_SESSION['nom_real'])) { ?>
                <div class="usuario-activo"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($_SESSION['nom_real']); ?> (<?php echo htmlspecialchars($_SESSION['rol']); ?>)</div>
            <?php } ?>

            <a href="historico.php?id_sala=<?php echo $sala_activa['id_sala']; ?>" class="boton-cabecera">Historial de Sala</a>

            <!-- Desplegable para cambiar de sala: script.js envía el formulario al cambiarlo -->
            <form method="GET" action="sala.php" id="formulario-sala" class="selector-sala">
                <!-- Oculto: reenvía la zona para no perderla al cambiar de sala -->
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
        </div>
    </header>

    <?php
    // Error oculto (p. ej. "Acceso denegado"): script.js lo enseña en un SweetAlert
    if (isset($_SESSION['error'])) {
        echo "<div id='aviso-error' class='oculto'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    ?>

    <main class="vista-puestos">
        <?php if (empty($modulos)) { ?>
            <p class="mensaje-vacio">Esta sala todavía no tiene puestos.</p>
        <?php } ?>

        <?php if (!empty($puestos_squad)) { ?>
            <section class="grupo-formato">
                <h3 class="titulo-formato">Arena Squad</h3>
                <div class="lista-squad">
                    <?php foreach ($puestos_squad as $puesto) { ?>
                        <?php include '../includes/tarjeta_puesto.php'; ?>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>

        <?php if (!empty($puestos_party)) { ?>
            <section class="grupo-formato">
                <h3 class="titulo-formato">Zona Party</h3>
                <div class="lista-party">
                    <?php foreach ($puestos_party as $puesto) { ?>
                        <?php include '../includes/tarjeta_puesto.php'; ?>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>

        <?php if (!empty($parejas)) { ?>
            <section class="grupo-formato">
                <h3 class="titulo-formato">Zona Dúo</h3>
                <div class="lista-duo">
                    <!-- Una caja por pareja y dentro sus dos puestos -->
                    <?php foreach ($parejas as $pareja) { ?>
                        <div class="caja-pareja">
                            <?php foreach ($pareja as $puesto) { ?>
                                <?php include '../includes/tarjeta_puesto.php'; ?>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>

        <?php if (!empty($puestos_solo)) { ?>
            <section class="grupo-formato">
                <h3 class="titulo-formato">Zona Solo</h3>
                <div class="lista-solo">
                    <?php foreach ($puestos_solo as $puesto) { ?>
                        <?php include '../includes/tarjeta_puesto.php'; ?>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <script src="../js/script.js"></script>
</body>
</html>

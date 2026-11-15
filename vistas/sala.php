<?php
// VISTA: SALA
// Puestos de una sala por GET (sala.php?tipo=pc&id_sala=3), agrupados en Squad, Party, Dúo y Solo.

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Sin zona en la URL no sabemos qué sala enseñar, así que volvemos al panel
if (!isset($_GET['tipo'])) {
    header('Location: dashboard.php');
    exit();
}

require_once '../bd/conexion.php';
require_once '../includes/funciones.php';

$tipo = limpiarDato($_GET['tipo']);

try {
    // 1. Salas de esta zona para el desplegable
    $sql = "SELECT id_sala, nombre, tipo FROM salas WHERE tipo = ? ORDER BY id_sala";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$tipo]);
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si la zona no tiene salas (o el tipo es inventado), volvemos al panel con un aviso
    if (empty($salas)) {
        $_SESSION['error'] = 'Esta zona todavía no tiene salas.';
        header('Location: dashboard.php');
        exit();
    }

    // Por defecto enseñamos la primera sala; si llega otra, la buscamos entre las de esta zona
    $sala_activa = $salas[0];
    if (isset($_GET['id_sala'])) {
        $id_elegido = limpiarDato($_GET['id_sala']);
        foreach ($salas as $sala) {
            if ($sala['id_sala'] == $id_elegido) {
                $sala_activa = $sala;
            }
        }
    }

    // 2. Puestos de la sala elegida y quién los tiene ocupados (la ocupación con fecha_fin IS NULL).
    // Usamos LEFT JOIN para que salgan también los puestos libres, que no tienen ocupación
    // abierta: en ellos ocupado_por llega vacío
    $sql = "SELECT modulos.id_modulo, modulos.nombre, modulos.tipo, modulos.estado,
                   usuarios.nom_real AS ocupado_por
            FROM modulos
            LEFT JOIN ocupaciones ON ocupaciones.id_modulo = modulos.id_modulo AND ocupaciones.fecha_fin IS NULL
            LEFT JOIN usuarios ON ocupaciones.id_usuario = usuarios.id_usuario
            WHERE modulos.id_sala = ?
            ORDER BY modulos.nombre";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$sala_activa['id_sala']]);
    $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al cargar la sala: " . $e->getMessage();
    die();
}

// Preparamos los datos de cada tarjeta y separamos los puestos por tipo de módulo
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

// Agrupamos los dúos de dos en dos: llenamos $pareja y, cuando tiene 2, la guardamos
$parejas = [];
$pareja = [];
foreach ($puestos_duo as $puesto) {
    $pareja[] = $puesto;
    if (count($pareja) == 2) {
        $parejas[] = $pareja;
        $pareja = [];
    }
}
// Si sobra uno (número impar), también lo guardamos
if (!empty($pareja)) {
    $parejas[] = $pareja;
}

// A cada pareja le ponemos su número (Módulo Dúo 1, 2...) y contamos sus libres
$modulos_duo = [];
$numero_modulo = 1;
foreach ($parejas as $pareja) {
    $modulos_duo[] = [
        'numero' => $numero_modulo,
        'puestos' => $pareja,
        'resumen' => resumenPuestos($pareja)
    ];
    $numero_modulo++;
}

// Puestos y libres de la sala entera y de cada bloque
$total_sala = resumenPuestos($modulos);
$total_squad = resumenPuestos($puestos_squad);
$total_party = resumenPuestos($puestos_party);
$total_duo = resumenPuestos($puestos_duo);
$total_solo = resumenPuestos($puestos_solo);

// Contamos las filas de zonas (Squad/Party, Dúo y Solo) para que el CSS reparta el alto
$filas = 0;
if (!empty($puestos_squad) || !empty($puestos_party)) {
    $filas++;
}
if (!empty($parejas)) {
    $filas++;
}
if (!empty($puestos_solo)) {
    $filas++;
}

// Y las tarjetas de la fila más larga (5 con Squad, 4 en consolas) para que reparta el ancho
$columnas = max(count($puestos_squad), count($puestos_party), count($puestos_duo), count($puestos_solo));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($sala_activa['nombre']); ?> - Hidden Arena</title>
    <link rel="icon" type="image/png" href="../img/logo-hidden-mini.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body class="pagina-sala">
    <!-- Cabecera: volver, nombre de la sala, capacidad y selector -->
    <header class="cabecera cabecera-<?php echo $sala_activa['tipo']; ?>">
        <a href="dashboard.php" class="boton-volver">&lt; Volver</a>

        <div class="titulo-sala">
            <h1 class="cabecera-titulo"><?php echo htmlspecialchars($sala_activa['nombre']); ?></h1>
            <!-- Capacidad total y puestos libres y ocupados ahora -->
            <span class="badge-capacidad">
                Capacidad total: <?php echo $total_sala['puestos']; ?> puestos
                | <span class="estado-libre"><?php echo textoCantidad($total_sala['libres'], 'libre', 'libres'); ?></span>
                · <span class="estado-ocupado"><?php echo textoCantidad($total_sala['puestos'] - $total_sala['libres'], 'ocupado', 'ocupados'); ?></span>
            </span>
        </div>

        <div class="cabecera-acciones">
            <!-- Quién está conectado -->
            <?php if (isset($_SESSION['nom_real'])) { ?>
                <div class="usuario-activo"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($_SESSION['nom_real']); ?> (<?php echo htmlspecialchars($_SESSION['rol']); ?>)</div>
            <?php } ?>

            <a href="historico.php?id_sala=<?php echo $sala_activa['id_sala']; ?>" class="boton-cabecera">Historial de Sala</a>

            <!-- Selector de sala: script.js envía el formulario al cambiarlo -->
            <form method="GET" action="sala.php" id="formulario-sala" class="selector-sala">
                <!-- Reenviamos la zona para no perderla al cambiar de sala -->
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
    // Dejamos el error en un div oculto y script.js lo enseña en un SweetAlert
    if (isset($_SESSION['error'])) {
        echo "<div id='aviso-error' class='oculto'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    ?>

    <!-- Puestos por zonas; filas-N y columnas-N sirven al CSS para el tamaño de las tarjetas -->
    <main class="vista-puestos filas-<?php echo $filas; ?> columnas-<?php echo $columnas; ?>">
        <?php if (empty($modulos)) { ?>
            <p class="mensaje-vacio">Esta sala todavía no tiene puestos.</p>
        <?php } ?>

        <?php if (!empty($puestos_squad)) { ?>
            <section class="grupo-formato">
                <h3 class="titulo-formato">Arena Squad <span class="capacidad-bloque">— Capacidad: <?php echo $total_squad['puestos']; ?> puestos (<?php echo $total_squad['libres']; ?>/<?php echo $total_squad['puestos']; ?> libres)</span></h3>
                <div class="lista-squad">
                    <?php foreach ($puestos_squad as $puesto) { ?>
                        <?php include '../includes/tarjeta_puesto.php'; ?>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>

        <?php if (!empty($puestos_party)) { ?>
            <section class="grupo-formato">
                <h3 class="titulo-formato">Zona Party <span class="capacidad-bloque">— Capacidad: <?php echo $total_party['puestos']; ?> puestos (<?php echo $total_party['libres']; ?>/<?php echo $total_party['puestos']; ?> libres)</span></h3>
                <div class="lista-party">
                    <?php foreach ($puestos_party as $puesto) { ?>
                        <?php include '../includes/tarjeta_puesto.php'; ?>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>

        <?php if (!empty($parejas)) { ?>
            <section class="grupo-formato">
                <h3 class="titulo-formato">Zona Dúo <span class="capacidad-bloque">— <?php echo count($modulos_duo); ?> módulos de 2 puestos (<?php echo $total_duo['libres']; ?>/<?php echo $total_duo['puestos']; ?> libres)</span></h3>
                <div class="lista-duo">
                    <!-- Una caja por módulo dúo: arriba su nombre y libres, debajo sus dos puestos -->
                    <?php foreach ($modulos_duo as $modulo_duo) { ?>
                        <div class="caja-pareja">
                            <p class="titulo-modulo">
                                Módulo Dúo <?php echo $modulo_duo['numero']; ?>
                                (Cap. <?php echo $modulo_duo['resumen']['puestos']; ?> puestos)
                                — <?php echo $modulo_duo['resumen']['libres']; ?>/<?php echo $modulo_duo['resumen']['puestos']; ?> libres
                            </p>
                            <?php foreach ($modulo_duo['puestos'] as $puesto) { ?>
                                <?php include '../includes/tarjeta_puesto.php'; ?>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </section>
        <?php } ?>

        <?php if (!empty($puestos_solo)) { ?>
            <section class="grupo-formato">
                <h3 class="titulo-formato">Zona Solo <span class="capacidad-bloque">— Capacidad: <?php echo $total_solo['puestos']; ?> puestos (<?php echo $total_solo['libres']; ?>/<?php echo $total_solo['puestos']; ?> libres)</span></h3>
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

<?php
// VISTA: PUESTOS OCUPADOS
// Todos los puestos ocupados del local, con filtros por sala y por gamemaster.
// Al pulsar uno se libera (si lo ocupó quien está conectado) y "Cierre de Local" los libera todos.

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require_once '../bd/conexion.php';
require_once '../includes/funciones.php';

// Filtros: vacíos = sin filtrar
$filtro_sala = '';
$filtro_usuario = '';

if (isset($_GET['id_sala'])) {
    $filtro_sala = limpiarDato($_GET['id_sala']);
}
if (isset($_GET['id_usuario'])) {
    $filtro_usuario = limpiarDato($_GET['id_usuario']);
}

try {
    // Opciones de los dos desplegables
    $stmt = $conexion->prepare("SELECT id_sala, nombre FROM salas ORDER BY id_sala");
    $stmt->execute();
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conexion->prepare("SELECT id_usuario, nom_real, cognom_real FROM usuarios ORDER BY nom_real");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Puestos ocupados con su sala y quién los tiene (la ocupación sin fecha_fin).
    // LEFT JOIN para que salga también un puesto ocupado que no tenga fila en ocupaciones.
    $sql = "SELECT modulos.id_modulo, modulos.nombre, salas.nombre AS sala, salas.tipo AS tipo_sala,
                   usuarios.nom_real AS ocupado_por
            FROM modulos
            JOIN salas ON modulos.id_sala = salas.id_sala
            LEFT JOIN ocupaciones ON ocupaciones.id_modulo = modulos.id_modulo AND ocupaciones.fecha_fin IS NULL
            LEFT JOIN usuarios ON ocupaciones.id_usuario = usuarios.id_usuario
            WHERE modulos.estado = 'ocupat'";
    $datos = [];

    // Filtro dinámico: un "AND ... = ?" por cada desplegable elegido
    if (!empty($filtro_sala)) {
        $sql = $sql . " AND salas.id_sala = ?";
        $datos[] = $filtro_sala;
    }
    if (!empty($filtro_usuario)) {
        $sql = $sql . " AND usuarios.id_usuario = ?";
        $datos[] = $filtro_usuario;
    }

    $sql = $sql . " ORDER BY salas.id_sala, modulos.nombre";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($datos);
    $ocupados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al cargar los puestos ocupados: " . $e->getMessage();
    die();
}

// Datos que necesita cada tarjeta (aquí todos están ocupados, así que pulsar = liberar)
$puestos = [];
foreach ($ocupados as $puesto) {
    $puesto['enlace'] = '../procs/cambiar_estado_proc.php?id_modulo=' . $puesto['id_modulo'] . '&desde=ocupados';
    $puesto['accion'] = 'Liberar';
    $puesto['clase_estado'] = 'estado-ocupado';
    $puesto['texto_estado'] = 'OCUPADO';
    $puesto['imagen'] = imagenPuesto($puesto['nombre'], $puesto['tipo_sala']);
    $puestos[] = $puesto;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Puestos ocupados - Hidden Arena</title>
    <link rel="icon" type="image/png" href="../img/logo-hidden-mini.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <header class="cabecera">
        <a href="dashboard.php" class="boton-volver">&lt; Volver al panel</a>
        <h1 class="cabecera-titulo">Puestos ocupados (<?php echo count($puestos); ?>)</h1>
        <div class="cabecera-acciones">
            <?php if (isset($_SESSION['nom_real'])) { ?>
                <div class="usuario-activo"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($_SESSION['nom_real']); ?> (<?php echo htmlspecialchars($_SESSION['rol']); ?>)</div>
            <?php } ?>
            <a href="historico.php" class="boton-cabecera">Historial</a>
            <!-- script.js pide confirmación antes de seguir este enlace -->
            <a href="../procs/cierre_local_proc.php" id="boton-cierre" class="boton-cierre">Cierre de Local</a>
        </div>
    </header>

    <?php
    // Mensajes ocultos: script.js los enseña en un SweetAlert
    if (isset($_SESSION['error'])) {
        echo "<div id='aviso-error' class='oculto'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['exito'])) {
        echo "<div id='aviso-exito' class='oculto'>" . $_SESSION['exito'] . "</div>";
        unset($_SESSION['exito']);
    }
    ?>

    <main class="vista-ocupados">
        <!-- Al cambiar un desplegable, script.js envía el formulario y se recarga filtrado -->
        <form method="GET" action="ocupados.php" id="formulario-filtros" class="caja-filtros">
            <div class="filtro">
                <label for="filtro-sala">Sala</label>
                <select name="id_sala" id="filtro-sala">
                    <option value="">Todas</option>
                    <?php foreach ($salas as $sala) { ?>
                        <?php if ($sala['id_sala'] == $filtro_sala) { ?>
                            <option value="<?php echo $sala['id_sala']; ?>" selected><?php echo htmlspecialchars($sala['nombre']); ?></option>
                        <?php } else { ?>
                            <option value="<?php echo $sala['id_sala']; ?>"><?php echo htmlspecialchars($sala['nombre']); ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>

            <div class="filtro">
                <label for="filtro-gamemaster">Gamemaster</label>
                <select name="id_usuario" id="filtro-gamemaster">
                    <option value="">Todos</option>
                    <?php foreach ($usuarios as $gamemaster) { ?>
                        <?php if ($gamemaster['id_usuario'] == $filtro_usuario) { ?>
                            <option value="<?php echo $gamemaster['id_usuario']; ?>" selected><?php echo htmlspecialchars($gamemaster['nom_real'] . ' ' . $gamemaster['cognom_real']); ?></option>
                        <?php } else { ?>
                            <option value="<?php echo $gamemaster['id_usuario']; ?>"><?php echo htmlspecialchars($gamemaster['nom_real'] . ' ' . $gamemaster['cognom_real']); ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>

            <div class="filtro-botones">
                <a href="ocupados.php" class="boton-limpiar">Quitar filtros</a>
            </div>
        </form>

        <?php if (empty($puestos)) { ?>
            <p class="mensaje-vacio">No hay puestos ocupados.</p>
        <?php } ?>

        <div class="lista-ocupados">
            <?php foreach ($puestos as $puesto) { ?>
                <?php include '../includes/tarjeta_puesto.php'; ?>
            <?php } ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <script src="../js/script.js"></script>
</body>
</html>

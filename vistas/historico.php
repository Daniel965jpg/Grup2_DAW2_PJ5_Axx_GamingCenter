<?php
// VISTA: HISTÓRICO DE OCUPACIONES
// Resumen por sala y tabla de todas las ocupaciones, con 5 filtros por GET.

session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

require_once '../bd/conexion.php';
require_once '../includes/funciones.php';

// Valores de los 5 filtros (los que no lleguen se quedan vacíos y no filtran)
$filtro_sala = '';
$filtro_usuario = '';
$filtro_tipo = '';
$filtro_modulo = '';
$filtro_fecha = '';

// Tipos de módulo de la BD y cómo los escribimos en pantalla
$nombres_tipo = [
    'squad' => 'Squad',
    'party' => 'Party',
    'duo' => 'Dúo',
    'solo' => 'Solo'
];

if (isset($_GET['id_sala'])) {
    $filtro_sala = limpiarDato($_GET['id_sala']);
}
if (isset($_GET['id_usuario'])) {
    $filtro_usuario = limpiarDato($_GET['id_usuario']);
}
if (isset($_GET['tipo_modulo'])) {
    $filtro_tipo = limpiarDato($_GET['tipo_modulo']);
}
if (isset($_GET['modulo'])) {
    $filtro_modulo = limpiarDato($_GET['modulo']);
}
if (isset($_GET['fecha'])) {
    $filtro_fecha = limpiarDato($_GET['fecha']);
}

try {
    // 1. Opciones de los desplegables de sala y gamemaster
    $stmt = $conexion->prepare("SELECT id_sala, nombre FROM salas ORDER BY id_sala");
    $stmt->execute();
    $salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Solo gamemasters: el admin nunca ocupa puestos
    $stmt = $conexion->prepare("SELECT id_usuario, nom_real, cognom_real FROM usuarios WHERE rol = ? ORDER BY nom_real");
    $stmt->execute(['gamemaster']);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Resumen de arriba: GROUP BY junta las ocupaciones de cada sala y COUNT las cuenta
    $sql = "SELECT salas.nombre, COUNT(*) AS total
            FROM ocupaciones
            JOIN modulos ON ocupaciones.id_modulo = modulos.id_modulo
            JOIN salas ON modulos.id_sala = salas.id_sala
            GROUP BY salas.id_sala, salas.nombre
            ORDER BY salas.id_sala";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $resumen_salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Ocupaciones con los filtros. WHERE 1=1 siempre se cumple, así cada filtro elegido
    //    solo tiene que añadir "AND ... = ?" detrás; los valores van en $datos en el mismo orden
    $sql = "SELECT salas.nombre AS sala, modulos.tipo AS tipo_modulo, modulos.nombre AS modulo,
                   usuarios.nom_real, usuarios.cognom_real,
                   ocupaciones.fecha_inicio, ocupaciones.fecha_fin
            FROM ocupaciones
            JOIN modulos ON ocupaciones.id_modulo = modulos.id_modulo
            JOIN salas ON modulos.id_sala = salas.id_sala
            JOIN usuarios ON ocupaciones.id_usuario = usuarios.id_usuario
            WHERE 1=1";
    $datos = [];

    if (!empty($filtro_sala)) {
        $sql = $sql . " AND salas.id_sala = ?";
        $datos[] = $filtro_sala;
    }
    if (!empty($filtro_usuario)) {
        $sql = $sql . " AND usuarios.id_usuario = ?";
        $datos[] = $filtro_usuario;
    }
    if (!empty($filtro_tipo)) {
        $sql = $sql . " AND modulos.tipo = ?";
        $datos[] = $filtro_tipo;
    }
    if (!empty($filtro_modulo)) {
        // Con los % basta con escribir una parte del nombre ("PC-0", "SIM"...)
        $sql = $sql . " AND modulos.nombre LIKE ?";
        $datos[] = '%' . $filtro_modulo . '%';
    }
    if (!empty($filtro_fecha)) {
        // DATE() quita la hora para comparar solo el día
        $sql = $sql . " AND DATE(ocupaciones.fecha_inicio) = ?";
        $datos[] = $filtro_fecha;
    }

    $sql = $sql . " ORDER BY ocupaciones.fecha_inicio DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($datos);
    $ocupaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al cargar el histórico: " . $e->getMessage();
    die();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico - Hidden Arena</title>
    <link rel="icon" type="image/png" href="../img/logo-hidden-mini.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body class="pagina-historico">
    <!-- Cabecera: volver al panel, título y enlace a ocupados -->
    <header class="cabecera">
        <a href="dashboard.php" class="boton-volver">&lt; Volver al panel</a>
        <h1 class="cabecera-titulo">Historial de ocupaciones</h1>
        <div class="cabecera-acciones">
            <?php if (isset($_SESSION['nom_real'])) { ?>
                <div class="usuario-activo"><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($_SESSION['nom_real']); ?> (<?php echo htmlspecialchars($_SESSION['rol']); ?>)</div>
            <?php } ?>
            <a href="ocupados.php" class="boton-cabecera">Ver Ocupados</a>
        </div>
    </header>

    <main class="contenido-historico">
        <!-- Resumen: total de ocupaciones de cada sala -->
        <?php if (!empty($resumen_salas)) { ?>
            <div class="resumen-salas">
                <?php foreach ($resumen_salas as $fila) { ?>
                    <div class="resumen-sala">
                        <span class="resumen-nombre"><?php echo htmlspecialchars($fila['nombre']); ?></span>
                        <span class="resumen-total"><?php echo $fila['total']; ?></span>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <!-- Filtros -->
        <form method="GET" action="historico.php" class="caja-filtros">
            <div class="filtro">
                <label for="id_sala">Sala</label>
                <select name="id_sala" id="id_sala">
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
                <label for="id_usuario">Gamemaster</label>
                <select name="id_usuario" id="id_usuario">
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

            <div class="filtro">
                <label for="tipo_modulo">Módulo</label>
                <select name="tipo_modulo" id="tipo_modulo">
                    <option value="">Todos</option>
                    <?php foreach ($nombres_tipo as $valor => $texto) { ?>
                        <?php if ($valor == $filtro_tipo) { ?>
                            <option value="<?php echo $valor; ?>" selected><?php echo $texto; ?></option>
                        <?php } else { ?>
                            <option value="<?php echo $valor; ?>"><?php echo $texto; ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>

            <div class="filtro">
                <label for="modulo">Puesto</label>
                <!-- Ya viene limpio de limpiarDato, así que lo pintamos tal cual -->
                <input type="text" name="modulo" id="modulo" placeholder="Ej: PC-01, SIM, VR..." value="<?php echo $filtro_modulo; ?>">
            </div>

            <div class="filtro">
                <label for="fecha">Día de inicio</label>
                <input type="date" name="fecha" id="fecha" value="<?php echo $filtro_fecha; ?>">
            </div>

            <div class="filtro-botones">
                <button type="submit" class="boton-filtrar">Filtrar</button>
                <a href="historico.php" class="boton-limpiar">Limpiar</a>
            </div>
        </form>

        <section class="panel-historico">
            <div class="cabecera-historico-tabla">
                <h2>Ocupaciones</h2>
                <span><?php echo count($ocupaciones); ?> registros</span>
            </div>

            <?php if (empty($ocupaciones)) { ?>
                <p class="mensaje-vacio">No hay ocupaciones con estos filtros.</p>
            <?php } else { ?>
                <!-- Tabla de ocupaciones (date() y strtotime() pasan las fechas a dd/mm/aaaa) -->
                <div class="tabla-historico-contenedor">
                    <table class="tabla-historico">
                        <thead>
                            <tr>
                                <th>Sala</th>
                                <th>Módulo</th>
                                <th>Puesto</th>
                                <th>Gamemaster</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ocupaciones as $ocupacion) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ocupacion['sala']); ?></td>
                                    <td><?php echo $nombres_tipo[$ocupacion['tipo_modulo']]; ?></td>
                                    <td><?php echo htmlspecialchars($ocupacion['modulo']); ?></td>
                                    <td><?php echo htmlspecialchars($ocupacion['nom_real'] . ' ' . $ocupacion['cognom_real']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($ocupacion['fecha_inicio'])); ?></td>
                                    <td>
                                        <?php if (empty($ocupacion['fecha_fin'])) { ?>
                                            <span class="estado-ocupado">En curso</span>
                                        <?php } else { ?>
                                            <?php echo date('d/m/Y H:i', strtotime($ocupacion['fecha_fin'])); ?>
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
